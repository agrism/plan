<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'vaditajs@komanda.lv',
            'name' => 'Jānis Vadītājs',
        ]);

        $this->tenant = Tenant::create([
            'owner_id' => $this->user->id,
            'name' => 'Galvenā Komanda',
            'slug' => 'galvena-komanda',
        ]);

        $this->user->tenants()->attach($this->tenant->id, ['role' => 'admin']);
    }

    public function test_it_creates_task_with_description_and_links(): void
    {
        $response = $this->actingAs($this->user)->post(route('ideas.store'), [
            'title' => 'Sistēmas arhitektūras izpēte',
            'description' => 'Nepieciešams izskatīt jaunos API dokumentus.',
            'category' => 'attistiba',
            'links' => [
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'https://laravel.com/docs',
                '', // empty link should be filtered
            ],
        ]);

        $response->assertRedirect(route('ideas.index'));

        $this->assertDatabaseHas('tasks', [
            'tenant_id' => $this->tenant->id,
            'title' => 'Sistēmas arhitektūras izpēte',
            'description' => 'Nepieciešams izskatīt jaunos API dokumentus.',
            'category' => 'attistiba',
        ]);

        $task = Task::first();
        $this->assertCount(2, $task->links);
        $this->assertEquals('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $task->links[0]);
        $this->assertEquals('https://laravel.com/docs', $task->links[1]);

        // Test processed links attribute
        $processed = $task->processed_links;
        $this->assertCount(2, $processed);
        
        // Check YouTube embed detection
        $this->assertEquals('dQw4w9WgXcQ', $processed[0]['youtube_id']);
        $this->assertEquals('https://www.youtube.com/embed/dQw4w9WgXcQ', $processed[0]['embed_url']);
        $this->assertEquals('youtube.com', $processed[0]['domain']);

        // Check standard link
        $this->assertNull($processed[1]['youtube_id']);
        $this->assertNull($processed[1]['embed_url']);
        $this->assertEquals('laravel.com', $processed[1]['domain']);
    }

    public function test_it_renders_edit_modal_and_updates_task(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'created_by_id' => $this->user->id,
            'title' => 'Sākotnējais virsraksts',
            'description' => 'Sākotnējais apraksts',
            'category' => 'ikdienas',
            'links' => ['https://google.com'],
        ]);

        // Test GET edit modal
        $editResponse = $this->actingAs($this->user)->get(route('ideas.edit', $task->id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Labot uzdevumu');
        $editResponse->assertSee('Sākotnējais virsraksts');
        $editResponse->assertSee('Sākotnējais apraksts');

        // Test POST/PUT update
        $updateResponse = $this->actingAs($this->user)->post(route('ideas.update', $task->id), [
            'title' => 'Atjaunināts virsraksts',
            'description' => 'Atjaunināts apraksts ar papildu info',
            'category' => 'steidzami',
            'links' => [
                'https://youtu.be/dQw4w9WgXcQ',
                'https://github.com/agrism/plan',
            ],
        ]);

        $updateResponse->assertRedirect(route('ideas.index'));

        $task->refresh();
        $this->assertEquals('Atjaunināts virsraksts', $task->title);
        $this->assertEquals('Atjaunināts apraksts ar papildu info', $task->description);
        $this->assertEquals('steidzami', $task->category);
        $this->assertCount(2, $task->links);
        $this->assertEquals('https://youtu.be/dQw4w9WgXcQ', $task->links[0]);
        $this->assertEquals('dQw4w9WgXcQ', $task->processed_links[0]['youtube_id']);
    }

    public function test_it_schedules_and_toggles_task(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'created_by_id' => $this->user->id,
            'title' => 'Testa uzdevums',
            'category' => 'projekti',
        ]);

        // Schedule
        $this->actingAs($this->user)->patch(route('ideas.schedule', $task->id), [
            'scheduled_date' => '2026-09-18',
            'scheduled_time_slot' => '10:00',
        ]);

        $task->refresh();
        $this->assertEquals('2026-09-18', $task->scheduled_date->toDateString());
        $this->assertEquals('10:00', $task->scheduled_time_slot);

        // Toggle complete
        $this->actingAs($this->user)->patch(route('ideas.toggle', $task->id));
        $task->refresh();
        $this->assertTrue($task->is_completed);

        // Toggle uncomplete
        $this->actingAs($this->user)->patch(route('ideas.toggle', $task->id));
        $task->refresh();
        $this->assertFalse($task->is_completed);
    }

    public function test_it_handles_youtube_shorts_and_short_urls(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'created_by_id' => $this->user->id,
            'title' => 'YouTube formātu tests',
            'category' => 'attistiba',
            'links' => [
                'https://youtu.be/abc123XYZ00',
                'https://www.youtube.com/shorts/shortId1234',
            ],
        ]);

        $processed = $task->processed_links;
        $this->assertCount(2, $processed);
        $this->assertEquals('abc123XYZ00', $processed[0]['youtube_id']);
        $this->assertEquals('https://www.youtube.com/embed/abc123XYZ00', $processed[0]['embed_url']);

        $this->assertEquals('shortId1234', $processed[1]['youtube_id']);
        $this->assertEquals('https://www.youtube.com/embed/shortId1234', $processed[1]['embed_url']);
    }

    public function test_it_supports_htmx_requests(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'created_by_id' => $this->user->id,
            'title' => 'HTMX Testa uzdevums',
            'category' => 'projekti',
        ]);

        $response = $this->actingAs($this->user)->withHeaders([
            'HX-Request' => 'true',
            'HX-Target' => 'backlog-container',
        ])->get(route('ideas.index'));

        $response->assertStatus(200);
        $response->assertSee('HTMX Testa uzdevums');
    }

    public function test_it_does_not_attach_image_automatically(): void
    {
        $response = $this->actingAs($this->user)->post(route('ideas.store'), [
            'title' => 'Uzdevums bez bildes',
            'category' => 'projekti',
        ]);

        $response->assertRedirect(route('ideas.index'));

        $task = Task::where('title', 'Uzdevums bez bildes')->first();
        $this->assertNotNull($task);
        $this->assertNull($task->image_url, 'Image URL must remain null when no image is uploaded');
    }

    public function test_it_creates_custom_category(): void
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Mārketings',
            'emoji' => '📢',
            'color' => 'indigo',
        ]);

        $response->assertRedirect(route('ideas.index'));

        $this->assertDatabaseHas('categories', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Mārketings',
            'emoji' => '📢',
            'color' => 'indigo',
        ]);

        $cat = \App\Models\Category::where('name', 'Mārketings')->first();

        // Create a task with this category
        $this->actingAs($this->user)->post(route('ideas.store'), [
            'title' => 'Sociālo tīklu kampaņa',
            'category_id' => $cat->id,
        ]);

        $task = Task::where('title', 'Sociālo tīklu kampaņa')->first();
        $this->assertEquals($cat->id, $task->category_id);
        $this->assertEquals('📢', $task->category_emoji);
        $this->assertEquals('Mārketings', $task->category_name);
        $this->assertStringContainsString('indigo', $task->category_badge_class);
    }

    public function test_it_schedules_to_arbitrary_calendar_date_and_unschedules_to_backlog(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'created_by_id' => $this->user->id,
            'title' => 'Nākotnes plānošanas darbs',
            'category' => 'projekti',
        ]);

        // Schedule to specific future date
        $scheduleResponse = $this->actingAs($this->user)->patch(route('ideas.schedule', $task->id), [
            'scheduled_date' => '2026-10-25',
        ]);

        $task->refresh();
        $this->assertEquals('2026-10-25', $task->scheduled_date->toDateString());

        // Check it renders on the weekend/plan view
        $planResponse = $this->actingAs($this->user)->withHeaders([
            'HX-Request' => 'true',
            'HX-Target' => 'weekend-container',
        ])->get(route('ideas.index'));

        $planResponse->assertStatus(200);
        $planResponse->assertSee('Nākotnes plānošanas darbs');
        $planResponse->assertSee(\Carbon\Carbon::parse('2026-10-25')->translatedFormat('j. F'));

        // Unschedule back to backlog
        $unscheduleResponse = $this->actingAs($this->user)->patch(route('ideas.schedule', $task->id), [
            'scheduled_date' => 'null',
        ]);

        $task->refresh();
        $this->assertNull($task->scheduled_date);
    }

    public function test_it_deletes_task_successfully(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'created_by_id' => $this->user->id,
            'title' => 'Uzdevums dzēšanai',
            'category' => 'ikdienas',
        ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);

        $response = $this->actingAs($this->user)->delete(route('ideas.destroy', $task->id));

        $response->assertRedirect(route('ideas.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
