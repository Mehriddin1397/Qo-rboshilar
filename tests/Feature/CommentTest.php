<?php

namespace Tests\Feature;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\User;
use App\Models\Uzgolon;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_post_comment_and_is_redirected_to_login(): void
    {
        $qorboshi = Qorboshi::factory()->published()->create();

        $response = $this->post('/comments', [
            'commentable_type' => 'qorboshi',
            'commentable_id' => $qorboshi->id,
            'content' => 'Mehmon izohi',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_authenticated_user_can_create_comment_defaulting_to_pending(): void
    {
        $user = User::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'qorboshi',
            'commentable_id' => $qorboshi->id,
            'content' => 'Ajoyib maqola!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'author_id' => $user->id,
            'commentable_type' => Qorboshi::class,
            'commentable_id' => $qorboshi->id,
            'content' => 'Ajoyib maqola!',
            'status' => 'pending',
        ]);
    }

    public function test_comment_on_draft_qorboshi_is_rejected(): void
    {
        $user = User::factory()->create();
        $qorboshi = Qorboshi::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'qorboshi',
            'commentable_id' => $qorboshi->id,
            'content' => 'Draftga izoh',
        ]);

        $response->assertSessionHasErrors('commentable_id');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_on_pending_blog_is_rejected(): void
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->pending()->create();

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'blog',
            'commentable_id' => $blog->id,
            'content' => 'Pendingga izoh',
        ]);

        $response->assertSessionHasErrors('commentable_id');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_on_rejected_blog_is_rejected(): void
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create(['status' => BlogStatus::Rejected]);

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'blog',
            'commentable_id' => $blog->id,
            'content' => 'Rad etilganga izoh',
        ]);

        $response->assertSessionHasErrors('commentable_id');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_on_published_parent_is_accepted(): void
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->approved()->create();

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'blog',
            'commentable_id' => $blog->id,
            'content' => 'Approved blogga izoh',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('comments', 1);
    }

    public function test_invalid_commentable_type_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'user',
            'commentable_id' => $user->id,
            'content' => "Boshqa modelga izoh urinishi",
        ]);

        $response->assertSessionHasErrors('commentable_type');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_content_is_escaped_on_render(): void
    {
        $user = User::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();

        $comment = Comment::factory()->approved()->for($user, 'author')->for($qorboshi, 'commentable')->create([
            'content' => '<script>alert(1)</script>',
        ]);

        $response = $this->get(route('qorboshilar.show', $qorboshi));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_user_cannot_delete_another_users_comment(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();

        $comment = Comment::factory()->for($owner, 'author')->for($qorboshi, 'commentable')->create();

        $response = $this->actingAs($intruder)->delete("/comments/{$comment->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $owner = User::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();

        $comment = Comment::factory()->for($owner, 'author')->for($qorboshi, 'commentable')->create();

        $response = $this->actingAs($owner)->delete("/comments/{$comment->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_comment_rate_limit_blocks_excessive_requests(): void
    {
        $user = User::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post('/comments', [
                'commentable_type' => 'qorboshi',
                'commentable_id' => $qorboshi->id,
                'content' => "Izoh raqami {$i}",
            ]);
        }

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'qorboshi',
            'commentable_id' => $qorboshi->id,
            'content' => 'Limitdan oshgan izoh',
        ]);

        $response->assertStatus(429);
    }

    public function test_comment_is_polymorphically_linked_to_each_content_type(): void
    {
        $user = User::factory()->create();

        $models = [
            Qorboshi::factory()->published()->create(),
            Uzgolon::factory()->published()->create(),
            Literature::factory()->published()->create(),
            Video::factory()->published()->create(),
            Blog::factory()->approved()->create(),
        ];

        foreach ($models as $model) {
            $comment = Comment::factory()->for($user, 'author')->for($model, 'commentable')->create();

            $this->assertTrue($comment->commentable->is($model));
        }
    }

    public function test_approved_comments_query_does_not_grow_with_comment_count(): void
    {
        $qorboshi = Qorboshi::factory()->published()->create();
        $users = User::factory()->count(20)->create();

        foreach ($users->take(5) as $user) {
            Comment::factory()->approved()->for($user, 'author')->for($qorboshi, 'commentable')->create();
        }

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->get(route('qorboshilar.show', $qorboshi));
        $smallCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();

        foreach ($users as $user) {
            Comment::factory()->approved()->for($user, 'author')->for($qorboshi, 'commentable')->create();
        }

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->get(route('qorboshilar.show', $qorboshi));
        $largeCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }
}
