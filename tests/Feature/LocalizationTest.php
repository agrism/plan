<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'janis@komanda.lv',
            'name' => 'Jānis Vadītājs',
        ]);

        $this->tenant = Tenant::create([
            'owner_id' => $this->user->id,
            'name' => 'Galvenā Komanda',
            'slug' => 'galvena-komanda',
        ]);

        $this->user->tenants()->attach($this->tenant->id, ['role' => 'admin']);
    }

    public function test_default_locale_is_latvian_and_renders_latvian_strings(): void
    {
        $response = $this->actingAs($this->user)->get(route('ideas.index'));

        $response->assertStatus(200);
        $response->assertSee('Uzdevumu Krātuve');
        $response->assertSee('Dienu Plāns');
        $response->assertSee('Pievienot');
    }

    public function test_it_switches_locale_to_english_via_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('locale.switch', 'en'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');

        // Access page with session locale set to 'en'
        $pageResponse = $this->actingAs($this->user)
            ->withSession(['locale' => 'en'])
            ->get(route('ideas.index'));

        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Task Backlog');
        $pageResponse->assertSee('Daily Plan');
        $pageResponse->assertSee('Add');
        $pageResponse->assertSee('Your Workspaces');
    }

    public function test_it_switches_locale_back_to_latvian(): void
    {
        $this->actingAs($this->user)
            ->withSession(['locale' => 'en'])
            ->get(route('locale.switch', 'lv'))
            ->assertRedirect()
            ->assertSessionHas('locale', 'lv');

        $pageResponse = $this->actingAs($this->user)
            ->withSession(['locale' => 'lv'])
            ->get(route('ideas.index'));

        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Uzdevumu Krātuve');
        $pageResponse->assertSee('Dienu Plāns');
        $pageResponse->assertSee('Tavas Darbavietas');
    }

    public function test_invalid_locale_defaults_gracefully(): void
    {
        $response = $this->actingAs($this->user)->get(route('locale.switch', 'de'));

        $response->assertRedirect();
        // Should not store invalid locale 'de'
        $this->assertNotEquals('de', session('locale'));
    }

    public function test_auth_screens_render_in_selected_locale(): void
    {
        // Latvian Login
        $lvResponse = $this->withSession(['locale' => 'lv'])->get(route('login'));
        $lvResponse->assertStatus(200);
        $lvResponse->assertSee('Plānotājs');
        $lvResponse->assertSee('Pieslēgties');

        // English Login
        $enResponse = $this->withSession(['locale' => 'en'])->get(route('login'));
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Planner');
        $enResponse->assertSee('Sign In');
    }
}
