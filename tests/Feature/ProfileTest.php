<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Jānis Bērziņš',
            'email' => 'janis@example.com',
            'avatar' => '👤',
            'password' => Hash::make('password123'),
        ]);

        $this->tenant = Tenant::create([
            'owner_id' => $this->user->id,
            'name' => 'Testa Komanda',
            'invite_code' => 'TESTINVITE123',
        ]);
        $this->tenant->users()->attach($this->user->id, ['role' => 'admin']);
    }

    public function test_profile_modal_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee(__('app.profile_settings'));
        $response->assertSee('janis@example.com');
        $response->assertSee('Jānis Bērziņš');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('profile.update'), [
                'name' => 'Jānis Jaunais',
                'email' => 'janis.jaunais@example.com',
                'avatar' => '🚀',
            ]);

        $response->assertRedirect(route('ideas.index'));

        $this->user->refresh();
        $this->assertEquals('Jānis Jaunais', $this->user->name);
        $this->assertEquals('janis.jaunais@example.com', $this->user->email);
        $this->assertEquals('🚀', $this->user->avatar);
    }

    public function test_password_can_be_updated_with_correct_current_password(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('profile.update'), [
                'name' => 'Jānis Bērziņš',
                'email' => 'janis@example.com',
                'avatar' => '👤',
                'current_password' => 'password123',
                'password' => 'newsecret',
                'password_confirmation' => 'newsecret',
            ]);

        $response->assertRedirect(route('ideas.index'));

        $this->user->refresh();
        $this->assertTrue(Hash::check('newsecret', $this->user->password));
    }

    public function test_password_update_fails_with_incorrect_current_password(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('profile.update'), [
                'name' => 'Jānis Bērziņš',
                'email' => 'janis@example.com',
                'avatar' => '👤',
                'current_password' => 'wrongpassword',
                'password' => 'newsecret',
                'password_confirmation' => 'newsecret',
            ]);

        $response->assertSessionHasErrors('current_password');

        $this->user->refresh();
        $this->assertTrue(Hash::check('password123', $this->user->password));
    }

    public function test_password_update_requires_minimum_5_characters(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('profile.update'), [
                'name' => 'Jānis Bērziņš',
                'email' => 'janis@example.com',
                'avatar' => '👤',
                'current_password' => 'password123',
                'password' => '1234',
                'password_confirmation' => '1234',
            ]);

        $response->assertSessionHasErrors('password');
    }
}
