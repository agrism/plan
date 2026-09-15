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

    public function test_registration_validation_messages_are_localized_in_latvian(): void
    {
        $response = $this->withSession(['locale' => 'lv'])->post(route('register'), [
            'name' => 'Agris',
            'email' => 'agris@test.lv',
            'password' => '1234', // 4 chars (less than min 5)
            'password_confirmation' => '1234',
        ]);

        $response->assertSessionHasErrors('password');
        $errors = session('errors')->get('password');
        $this->assertNotEmpty($errors);
        $this->assertStringNotContainsString('validation.min.string', $errors[0]);
        $this->assertStringContainsString('5', $errors[0]);
    }

    public function test_registration_accepts_five_character_password(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Agris Pieci',
            'email' => 'agris.pieci@test.lv',
            'password' => '12345',
            'password_confirmation' => '12345',
            'workspace_name' => 'Pieci Darbavieta',
        ]);

        $response->assertRedirect(route('ideas.index'));
        $this->assertDatabaseHas('users', ['email' => 'agris.pieci@test.lv']);
    }

    public function test_registration_validation_messages_are_localized_in_english(): void
    {
        $response = $this->withSession(['locale' => 'en'])->post(route('register'), [
            'name' => 'John',
            'email' => 'john@test.com',
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);

        $response->assertSessionHasErrors('password');
        $errors = session('errors')->get('password');
        $this->assertNotEmpty($errors);
        $this->assertStringNotContainsString('validation.min.string', $errors[0]);
        $this->assertStringContainsString('at least 5 characters', $errors[0]);
    }
}
