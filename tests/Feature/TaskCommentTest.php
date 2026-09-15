<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $member;
    protected User $outsider;
    protected Tenant $tenant;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Agris Īpašnieks', 'email' => 'agris@test.lv']);
        $this->member = User::factory()->create(['name' => 'Jānis Biedrs', 'email' => 'janis@test.lv']);
        $this->outsider = User::factory()->create(['name' => 'Svešinieks', 'email' => 'cits@test.lv']);

        $this->tenant = Tenant::create([
            'name' => 'Plānotāja Komanda',
            'owner_id' => $this->owner->id,
        ]);
        $this->tenant->users()->attach($this->owner->id, ['role' => 'admin']);
        $this->tenant->users()->attach($this->member->id, ['role' => 'member']);

        $this->task = $this->tenant->tasks()->create([
            'created_by_id' => $this->owner->id,
            'title' => 'Aizbraukt uz Munamegi',
            'category' => 'attistiba',
        ]);
    }

    public function test_user_can_add_comment_to_task(): void
    {
        $response = $this->actingAs($this->member)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('ideas.comments.store', $this->task->id), [
                'content' => 'Lieliska ideja, es ņemšu līdzi telti!',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('task_comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->member->id,
            'content' => 'Lieliska ideja, es ņemšu līdzi telti!',
        ]);
    }

    public function test_comment_is_returned_for_htmx_request(): void
    {
        $response = $this->actingAs($this->member)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->withHeaders(['HX-Request' => 'true'])
            ->post(route('ideas.comments.store', $this->task->id), [
                'content' => 'Komentārs ar HTMX pieprasījumu.',
            ]);

        $response->assertStatus(200);
        $response->assertSee('Komentārs ar HTMX pieprasījumu.');
        $response->assertSee('Jānis Biedrs');
    }

    public function test_user_can_delete_own_comment(): void
    {
        $comment = $this->task->comments()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->member->id,
            'content' => 'Šo komentāru izdzēsīšu.',
        ]);

        $response = $this->actingAs($this->member)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('ideas.comments.destroy', [$this->task->id, $comment->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('task_comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_other_user_comment_unless_admin(): void
    {
        $commentByOwner = $this->task->comments()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->owner->id,
            'content' => 'Īpašnieka svarīgs komentārs.',
        ]);

        // Regular member attempts to delete owner's comment -> 403 Forbidden
        $response = $this->actingAs($this->member)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('ideas.comments.destroy', [$this->task->id, $commentByOwner->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('task_comments', ['id' => $commentByOwner->id]);

        // Tenant owner can delete any comment
        $commentByMember = $this->task->comments()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->member->id,
            'content' => 'Biedra komentārs.',
        ]);

        $ownerResponse = $this->actingAs($this->owner)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('ideas.comments.destroy', [$this->task->id, $commentByMember->id]));

        $ownerResponse->assertRedirect();
        $this->assertDatabaseMissing('task_comments', ['id' => $commentByMember->id]);
    }

    public function test_user_cannot_comment_on_tasks_from_other_tenants(): void
    {
        $response = $this->actingAs($this->outsider)
            ->post(route('ideas.comments.store', $this->task->id), [
                'content' => 'Mēģinājums komentēt bez piekļuves.',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('task_comments', 0);
    }
}
