<?php

namespace Tests\Feature;

use App\Models\AdminRole;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_only_sees_published_posts(): void
    {
        $author = User::factory()->create();
        $published = $this->createPost($author, Post::STATUS_PUBLISHED, 'Published post');
        $this->createPost($author, Post::STATUS_DRAFT, 'Draft post');

        $response = $this->getJson('/api/posts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonPath('data.0.title', 'Published post')
            ->assertJsonPath('data.0.status', Post::STATUS_PUBLISHED);
    }

    public function test_admin_can_create_update_publish_and_delete_posts(): void
    {
        $admin = $this->seededSuperAdmin();
        $token = $admin->createToken('admin')->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/admin/posts', [
            'title' => 'New community post',
            'excerpt' => 'Seasonal update',
            'body' => 'Fresh post body',
            'cover_image_url' => 'https://example.com/post.jpg',
            'status' => Post::STATUS_PUBLISHED,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'New community post')
            ->assertJsonPath('data.status', Post::STATUS_PUBLISHED);

        $postId = $createResponse->json('data.id');
        $this->assertDatabaseHas('posts', [
            'id' => $postId,
            'created_by_user_id' => $admin->id,
            'status' => Post::STATUS_PUBLISHED,
        ]);

        $updateResponse = $this->withToken($token)->putJson("/api/admin/posts/{$postId}", [
            'title' => 'Edited community post',
            'excerpt' => 'Edited excerpt',
            'body' => 'Edited post body',
            'cover_image_url' => null,
            'status' => Post::STATUS_DRAFT,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.title', 'Edited community post')
            ->assertJsonPath('data.status', Post::STATUS_DRAFT)
            ->assertJsonPath('data.published_at', null);

        $this->withToken($token)->deleteJson("/api/admin/posts/{$postId}")
            ->assertOk();

        $this->assertDatabaseMissing('posts', ['id' => $postId]);
    }

    public function test_customer_can_comment_and_guest_is_blocked(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        $customer = User::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'Guest comment',
        ])->assertUnauthorized();

        $response = $this->withToken($token)->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'This is useful.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.content', 'This is useful.')
            ->assertJsonPath('data.status', PostComment::STATUS_VISIBLE)
            ->assertJsonPath('meta.comments_count', 1);

        $this->assertDatabaseHas('post_comments', [
            'post_id' => $post->id,
            'user_id' => $customer->id,
            'content' => 'This is useful.',
            'status' => PostComment::STATUS_VISIBLE,
        ]);
    }

    public function test_customer_like_and_unlike_are_idempotent(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        $customer = User::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)->postJson("/api/posts/{$post->id}/likes")
            ->assertOk()
            ->assertJsonPath('data.liked', true)
            ->assertJsonPath('data.likes_count', 1);

        $this->withToken($token)->postJson("/api/posts/{$post->id}/likes")
            ->assertOk()
            ->assertJsonPath('data.liked', true)
            ->assertJsonPath('data.likes_count', 1);

        $this->assertSame(1, PostLike::query()->where('post_id', $post->id)->count());

        $this->withToken($token)->deleteJson("/api/posts/{$post->id}/likes")
            ->assertOk()
            ->assertJsonPath('data.liked', false)
            ->assertJsonPath('data.likes_count', 0);
    }

    public function test_admin_can_hide_comment_from_public_feed(): void
    {
        $admin = $this->seededSuperAdmin();
        $author = User::factory()->create();
        $customer = User::factory()->create();
        $post = $this->createPost($author);
        $comment = PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $customer->id,
            'content' => 'Visible first',
            'status' => PostComment::STATUS_VISIBLE,
        ]);

        $token = $admin->createToken('admin')->plainTextToken;

        $this->withToken($token)->patchJson("/api/admin/posts/comments/{$comment->id}/visibility", [
            'status' => PostComment::STATUS_HIDDEN,
        ])->assertOk()
            ->assertJsonPath('data.status', PostComment::STATUS_HIDDEN);

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonPath('data.0.comments_count', 0)
            ->assertJsonCount(0, 'data.0.comments');
    }

    private function createPost(
        User $author,
        string $status = Post::STATUS_PUBLISHED,
        string $title = 'Community post'
    ): Post {
        return Post::query()->create([
            'created_by_user_id' => $author->id,
            'title' => $title,
            'excerpt' => 'Post excerpt',
            'body' => 'Post body',
            'cover_image_url' => 'https://example.com/cover.jpg',
            'status' => $status,
            'published_at' => $status === Post::STATUS_PUBLISHED ? now() : null,
        ]);
    }

    private function seededSuperAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::query()
            ->whereHas('adminRole', fn ($query) => $query->where('slug', AdminRole::SUPER_ADMIN_SLUG))
            ->firstOrFail()
            ->load(['adminRole.permissions']);
    }
}
