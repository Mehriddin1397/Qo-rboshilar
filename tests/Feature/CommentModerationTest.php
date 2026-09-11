<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Qorboshi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_comments_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/comments')->assertOk();
    }

    public function test_editor_can_view_and_moderate_comments(): void
    {
        $editor = User::factory()->editor()->create();
        $author = User::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();
        $comment = Comment::factory()->for($author, 'author')->for($qorboshi, 'commentable')->create();

        $this->actingAs($editor)->get('/admin/comments')->assertOk();

        $approve = $this->actingAs($editor)->post("/admin/comments/{$comment->id}/approve");
        $approve->assertRedirect();
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'status' => 'approved']);

        $reject = $this->actingAs($editor)->post("/admin/comments/{$comment->id}/reject");
        $reject->assertRedirect();
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'status' => 'rejected']);
    }

    public function test_editor_cannot_access_admin_only_pages(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/qorboshilar')->assertForbidden();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
    }

    public function test_guest_and_plain_user_cannot_access_comment_moderation(): void
    {
        $this->get('/admin/comments')->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/comments')->assertForbidden();
    }

    public function test_admin_can_delete_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $author = User::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();
        $comment = Comment::factory()->for($author, 'author')->for($qorboshi, 'commentable')->create();

        $response = $this->actingAs($admin)->delete("/admin/comments/{$comment->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_editor_cannot_delete_comment_they_do_not_own(): void
    {
        $editor = User::factory()->editor()->create();
        $author = User::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();
        $comment = Comment::factory()->for($author, 'author')->for($qorboshi, 'commentable')->create();

        $response = $this->actingAs($editor)->delete("/admin/comments/{$comment->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_admin_comment_search_filters_by_content_and_type(): void
    {
        $admin = User::factory()->admin()->create();
        $author = User::factory()->create(['name' => 'Filter Author']);
        $qorboshi = Qorboshi::factory()->published()->create();

        Comment::factory()->for($author, 'author')->for($qorboshi, 'commentable')->create([
            'content' => 'Noyob qidiruv kaliti',
        ]);

        $response = $this->actingAs($admin)->get('/admin/comments?search=Noyob');

        $response->assertOk();
        $response->assertSee('Noyob qidiruv kaliti');
    }
}
