<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\EnsuresCustomerAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostCommentRequest;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use EnsuresCustomerAccess;

    public function index(): JsonResponse
    {
        $posts = Post::query()
            ->published()
            ->with([
                'author:id,full_name,email',
                'visibleComments.author:id,full_name,email',
            ])
            ->withCount(['likes', 'visibleComments as comments_count'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Posts retrieved successfully.',
            'data' => $posts->map(fn (Post $post): array => $this->postPayload($post))->values(),
        ]);
    }

    public function myLikes(Request $request): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();

        $postIds = PostLike::query()
            ->where('user_id', $user->id)
            ->whereHas('post', fn ($query) => $query->published())
            ->orderByDesc('created_at')
            ->pluck('post_id')
            ->values();

        return response()->json([
            'message' => 'Post likes retrieved successfully.',
            'data' => [
                'post_ids' => $postIds,
            ],
        ]);
    }

    public function storeComment(StorePostCommentRequest $request, int $post): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        $postModel = $this->findPublishedPost($post);

        if (! $postModel) {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user();

        $comment = $postModel->comments()->create([
            'user_id' => $user->id,
            'content' => $request->validated('content'),
            'status' => PostComment::STATUS_VISIBLE,
        ])->load('author:id,full_name,email');

        return response()->json([
            'message' => 'Comment created successfully.',
            'data' => $this->commentPayload($comment),
            'meta' => [
                'post_id' => $postModel->id,
                'comments_count' => $postModel->visibleComments()->count(),
            ],
        ], 201);
    }

    public function like(Request $request, int $post): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        $postModel = $this->findPublishedPost($post);

        if (! $postModel) {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user();

        PostLike::query()->firstOrCreate([
            'post_id' => $postModel->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Post liked successfully.',
            'data' => [
                'post_id' => $postModel->id,
                'liked' => true,
                'likes_count' => $postModel->likes()->count(),
            ],
        ]);
    }

    public function unlike(Request $request, int $post): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        $postModel = $this->findPublishedPost($post);

        if (! $postModel) {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user();

        PostLike::query()
            ->where('post_id', $postModel->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Post unliked successfully.',
            'data' => [
                'post_id' => $postModel->id,
                'liked' => false,
                'likes_count' => $postModel->likes()->count(),
            ],
        ]);
    }

    private function findPublishedPost(int $postId): ?Post
    {
        return Post::query()
            ->published()
            ->find($postId);
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
            'comments_count' => (int) ($post->getAttribute('comments_count') ?? $post->visibleComments()->count()),
            'comments' => $post->relationLoaded('visibleComments')
                ? $post->visibleComments
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
            'created_at' => optional($comment->created_at)->toISOString(),
            'updated_at' => optional($comment->updated_at)->toISOString(),
            'author' => $comment->author ? [
                'id' => $comment->author->id,
                'full_name' => $comment->author->full_name,
                'email' => $comment->author->email,
            ] : null,
        ];
    }
}
