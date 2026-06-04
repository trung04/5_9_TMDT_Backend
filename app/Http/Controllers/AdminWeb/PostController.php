<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostCommentVisibilityRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PostController extends AdminWebController
{
    public function index(Request $request)
    {
        $posts = $this->postQuery()
            ->paginate((int) $request->integer('per_page', 10))
            ->withQueryString();

        return $this->render('admin-web.posts.index', [
            'posts' => $posts,
        ]);
    }

    public function create()
    {
        return $this->render('admin-web.posts.create', [
            'post' => new Post([
                'status' => Post::STATUS_DRAFT,
            ]),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::query()->create([
            ...$this->preparePostData($request->validated()),
            'created_by_user_id' => $this->adminUser()->id,
        ]);

        return redirect()->to(
            $this->canOpenPostReviewPage()
                ? route('admin-web.posts.edit', $post)
                : ($this->adminUser()->hasAdminPermission('admin.community.posts.view')
                    ? route('admin-web.posts.index')
                    : route('admin-web.posts.create'))
        )
            ->with('status', 'Đã tạo bài viết thành công.');
    }

    public function edit(int $post)
    {
        $editingPost = Post::query()
            ->with([
                'author:id,full_name,email',
                'comments.author:id,full_name,email',
                'comments.hiddenBy:id,full_name,email',
            ])
            ->withCount(['likes', 'comments'])
            ->findOrFail($post);

        return $this->render('admin-web.posts.edit', [
            'post' => $editingPost,
        ]);
    }

    public function update(UpdatePostRequest $request, int $post): RedirectResponse
    {
        $postModel = Post::query()->findOrFail($post);
        $postModel->update($this->preparePostData($request->validated(), $postModel));

        return redirect()
            ->route('admin-web.posts.edit', $postModel)
            ->with('status', 'Đã cập nhật bài viết thành công.');
    }

    public function destroy(int $post): RedirectResponse
    {
        $postModel = Post::query()->findOrFail($post);
        $postModel->delete();

        return redirect()
            ->route('admin-web.posts.index')
            ->with('status', 'Đã xóa bài viết thành công.');
    }

    public function updateCommentVisibility(
        UpdatePostCommentVisibilityRequest $request,
        int $comment
    ): RedirectResponse {
        $commentModel = PostComment::query()->findOrFail($comment);
        $status = (string) $request->validated('status');

        $commentModel->update([
            'status' => $status,
            'hidden_by_user_id' => $status === PostComment::STATUS_HIDDEN ? $this->adminUser()->id : null,
            'hidden_at' => $status === PostComment::STATUS_HIDDEN ? now() : null,
        ]);

        return back()->with('status', 'Đã cập nhật trạng thái hiển thị bình luận thành công.');
    }

    private function postQuery()
    {
        return Post::query()
            ->with([
                'author:id,full_name,email',
                'comments.author:id,full_name,email',
                'comments.hiddenBy:id,full_name,email',
            ])
            ->withCount(['likes', 'comments'])
            ->orderByDesc('created_at');
    }

    private function canOpenPostReviewPage(): bool
    {
        $user = $this->adminUser();

        return $user->hasAdminPermission('admin.community.posts.update')
            || $user->hasAdminPermission('admin.community.comments.moderate');
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
}
