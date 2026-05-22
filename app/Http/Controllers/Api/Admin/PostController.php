<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostCommentVisibilityRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PostController extends Controller
{
    use EnsuresAdminAccess;
    use PaginatesApiResults;

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.community.posts.view')) {
            return $response;
        }

        $posts = Post::query()
            ->with([
                'author:id,full_name,email',
                'comments.author:id,full_name,email',
                'comments.hiddenBy:id,full_name,email',
            ])
            ->withCount(['likes', 'comments'])
            ->orderByDesc('created_at')
            ->paginate($this->perPage($request));

        return response()->json(
            $this->transformPaginator($posts, fn (Post $post): array => $this->postPayload($post))
        );
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.community.posts.create')) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();

        $post = Post::query()->create([
            ...$this->preparePostData($request->validated()),
            'created_by_user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Post created successfully.',
            'data' => $this->postPayload($post->load(['author:id,full_name,email', 'comments.author:id,full_name,email'])->loadCount(['likes', 'comments'])),
        ], 201);
    }

    public function update(UpdatePostRequest $request, int $post): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.community.posts.update')) {
            return $response;
        }

        $postModel = Post::query()->find($post);

        if (! $postModel) {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }

        $postModel->update($this->preparePostData($request->validated(), $postModel));

        return response()->json([
            'message' => 'Post updated successfully.',
            'data' => $this->postPayload($postModel->refresh()->load([
                'author:id,full_name,email',
                'comments.author:id,full_name,email',
                'comments.hiddenBy:id,full_name,email',
            ])->loadCount(['likes', 'comments'])),
        ]);
    }

    public function destroy(Request $request, int $post): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.community.posts.delete')) {
            return $response;
        }

        $postModel = Post::query()->find($post);

        if (! $postModel) {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }

        $postModel->delete();

        return response()->json([
            'message' => 'Post deleted successfully.',
        ]);
    }

    public function updateCommentVisibility(
        UpdatePostCommentVisibilityRequest $request,
        int $comment
    ): JsonResponse {
        if ($response = $this->ensureAdmin($request, 'admin.community.comments.moderate')) {
            return $response;
        }

        $commentModel = PostComment::query()
            ->with(['author:id,full_name,email', 'hiddenBy:id,full_name,email'])
            ->find($comment);

        if (! $commentModel) {
            return response()->json([
                'message' => 'Comment not found.',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user();
        $status = $request->validated('status');

        $commentModel->update([
            'status' => $status,
            'hidden_by_user_id' => $status === PostComment::STATUS_HIDDEN ? $user->id : null,
            'hidden_at' => $status === PostComment::STATUS_HIDDEN ? now() : null,
        ]);

        return response()->json([
            'message' => 'Comment visibility updated successfully.',
            'data' => $this->commentPayload($commentModel->refresh()->load([
                'author:id,full_name,email',
                'hiddenBy:id,full_name,email',
            ])),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePostData(array $data, ?Post $existingPost = null): array
    {
        $status = $data['status'] ?? Post::STATUS_DRAFT;
        $publishedAt = null;

        if ($status === Post::STATUS_PUBLISHED) {
            $publishedAt = isset($data['published_at'])
                ? Carbon::parse((string) $data['published_at'])
                : ($existingPost?->published_at ?? now());
        }

        return [
            'title' => trim((string) $data['title']),
            'excerpt' => isset($data['excerpt']) ? trim((string) $data['excerpt']) : null,
            'body' => trim((string) $data['body']),
            'cover_image_url' => isset($data['cover_image_url']) ? trim((string) $data['cover_image_url']) : null,
            'status' => $status,
            'published_at' => $publishedAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function postPayload(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'cover_image_url' => $post->cover_image_url,
            'status' => $post->status,
            'published_at' => optional($post->published_at)->toISOString(),
            'created_at' => optional($post->created_at)->toISOString(),
            'updated_at' => optional($post->updated_at)->toISOString(),
            'author' => $post->author ? [
                'id' => $post->author->id,
                'full_name' => $post->author->full_name,
                'email' => $post->author->email,
            ] : null,
            'likes_count' => (int) ($post->getAttribute('likes_count') ?? $post->likes()->count()),
            'comments_count' => (int) ($post->getAttribute('comments_count') ?? $post->comments()->count()),
            'comments' => $post->relationLoaded('comments')
                ? $post->comments
                    ->map(fn (PostComment $comment): array => $this->commentPayload($comment))
                    ->values()
                    ->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function commentPayload(PostComment $comment): array
    {
        return [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'user_id' => $comment->user_id,
            'content' => $comment->content,
            'status' => $comment->status,
            'hidden_at' => optional($comment->hidden_at)->toISOString(),
            'created_at' => optional($comment->created_at)->toISOString(),
            'updated_at' => optional($comment->updated_at)->toISOString(),
            'author' => $comment->author ? [
                'id' => $comment->author->id,
                'full_name' => $comment->author->full_name,
                'email' => $comment->author->email,
            ] : null,
            'hidden_by' => $comment->hiddenBy ? [
                'id' => $comment->hiddenBy->id,
                'full_name' => $comment->hiddenBy->full_name,
                'email' => $comment->hiddenBy->email,
            ] : null,
        ];
    }
}
