<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantJoinTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $member;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'email' => 'owner@komanda.lv',
            'name' => 'Jānis Vadītājs',
        ]);

        $this->tenant = Tenant::create([
            'owner_id' => $this->owner->id,
            'name' => 'Jaunuzņēmums ABC',
            'invite_code' => 'INV-TEST123456',
        ]);
        $this->tenant->users()->attach($this->owner->id, ['role' => 'admin']);

        $this->member = User::factory()->create([
            'email' => 'anna@komanda.lv',
            'name' => 'Anna Kolēģe',
        ]);
    }

    public function test_it_joins_tenant_with_invite_code_via_form(): void
    {
        $response = $this->actingAs($this->member)->post(route('tenants.join'), [
            'invite_code' => 'INV-TEST123456',
        ]);

        $response->assertRedirect(route('ideas.index'));
        $this->assertTrue($this->tenant->users()->where('users.id', $this->member->id)->exists());
        $this->assertEquals($this->tenant->id, session('current_tenant_id'));
    }

    public function test_it_rejects_invalid_invite_code(): void
    {
        $response = $this->actingAs($this->member)->post(route('tenants.join'), [
            'invite_code' => 'INVALID-CODE-999',
        ]);

        $response->assertSessionHasErrors('invite_code');
        $this->assertFalse($this->tenant->users()->where('users.id', $this->member->id)->exists());
    }

    public function test_it_joins_via_direct_url_when_logged_in(): void
    {
        $response = $this->actingAs($this->member)->get(route('tenants.join_code', 'INV-TEST123456'));

        $response->assertRedirect(route('ideas.index'));
        $this->assertTrue($this->tenant->users()->where('users.id', $this->member->id)->exists());
        $this->assertEquals($this->tenant->id, session('current_tenant_id'));
    }

    public function test_it_joins_via_direct_url_upon_registration_when_guest(): void
    {
        // 1. Visit join link as guest
        $response = $this->get(route('tenants.join_code', 'INV-TEST123456'));
        $response->assertRedirect(route('register'));
        $response->assertSessionHas('pending_invite_code', 'INV-TEST123456');

        // 2. Register as new user
        $registerResponse = $this->withSession(['pending_invite_code' => 'INV-TEST123456'])
            ->post(route('register'), [
                'name' => 'Pēteris Jauniņais',
                'email' => 'peteris@komanda.lv',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'workspace_name' => '',
            ]);

        $registerResponse->assertRedirect(route('ideas.index'));
        $newUser = User::where('email', 'peteris@komanda.lv')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($this->tenant->users()->where('users.id', $newUser->id)->exists());
        $this->assertEquals($this->tenant->id, session('current_tenant_id'));
    }
}
