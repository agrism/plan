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

    public function test_member_can_leave_workspace(): void
    {
        $this->tenant->users()->attach($this->member->id, ['role' => 'member']);

        // User also has personal workspace
        $personalTenant = Tenant::create([
            'name' => 'Annas Personīgā Darbavieta',
            'owner_id' => $this->member->id,
        ]);
        $personalTenant->users()->attach($this->member->id, ['role' => 'admin']);

        $response = $this->actingAs($this->member)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('tenants.leave', $this->tenant->id));

        $response->assertRedirect(route('ideas.index'));
        $this->assertFalse($this->tenant->users()->where('users.id', $this->member->id)->exists());
        $this->assertEquals($personalTenant->id, session('current_tenant_id'));
    }

    public function test_owner_leaving_workspace_transfers_ownership_to_remaining_member(): void
    {
        $this->tenant->users()->attach($this->member->id, ['role' => 'member']);

        $response = $this->actingAs($this->owner)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('tenants.leave', $this->tenant->id));

        $response->assertRedirect(route('ideas.index'));
        $this->assertFalse($this->tenant->users()->where('users.id', $this->owner->id)->exists());

        // Refresh tenant
        $this->tenant->refresh();
        $this->assertEquals($this->member->id, $this->tenant->owner_id);
        $this->assertEquals('admin', $this->tenant->users()->where('users.id', $this->member->id)->first()->pivot->role);
    }

    public function test_leaving_only_workspace_creates_default_workspace(): void
    {
        $this->tenant->users()->attach($this->member->id, ['role' => 'member']);

        $response = $this->actingAs($this->member)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('tenants.leave', $this->tenant->id));

        $response->assertRedirect(route('ideas.index'));
        $this->assertFalse($this->tenant->users()->where('users.id', $this->member->id)->exists());

        // A new default workspace should have been created for the user
        $newTenant = $this->member->tenants()->first();
        $this->assertNotNull($newTenant);
        $this->assertEquals($newTenant->id, session('current_tenant_id'));
    }

    public function test_owner_can_remove_member_from_workspace(): void
    {
        $this->tenant->users()->attach($this->member->id, ['role' => 'member']);

        $response = $this->actingAs($this->owner)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('tenants.members.destroy', [$this->tenant->id, $this->member->id]));

        $response->assertRedirect(route('ideas.index'));
        $this->assertFalse($this->tenant->users()->where('users.id', $this->member->id)->exists());
    }

    public function test_admin_can_remove_member_from_workspace(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@komanda.lv',
            'name' => 'Admins',
        ]);
        $this->tenant->users()->attach($admin->id, ['role' => 'admin']);
        $this->tenant->users()->attach($this->member->id, ['role' => 'member']);

        $response = $this->actingAs($admin)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('tenants.members.destroy', [$this->tenant->id, $this->member->id]));

        $response->assertRedirect(route('ideas.index'));
        $this->assertFalse($this->tenant->users()->where('users.id', $this->member->id)->exists());
    }

    public function test_admin_cannot_remove_workspace_owner(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@komanda.lv',
            'name' => 'Admins',
        ]);
        $this->tenant->users()->attach($admin->id, ['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('tenants.members.destroy', [$this->tenant->id, $this->owner->id]));

        $response->assertStatus(403);
        $this->assertTrue($this->tenant->users()->where('users.id', $this->owner->id)->exists());
    }

    public function test_regular_member_cannot_remove_another_member(): void
    {
        $otherMember = User::factory()->create([
            'email' => 'other@komanda.lv',
            'name' => 'Cits dalībnieks',
        ]);
        $this->tenant->users()->attach($this->member->id, ['role' => 'member']);
        $this->tenant->users()->attach($otherMember->id, ['role' => 'member']);

        $response = $this->actingAs($this->member)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('tenants.members.destroy', [$this->tenant->id, $otherMember->id]));

        $response->assertStatus(403);
        $this->assertTrue($this->tenant->users()->where('users.id', $otherMember->id)->exists());
    }

    public function test_user_can_remove_themselves_via_destroy_route(): void
    {
        $this->tenant->users()->attach($this->member->id, ['role' => 'member']);

        $response = $this->actingAs($this->member)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('tenants.members.destroy', [$this->tenant->id, $this->member->id]));

        $response->assertRedirect(route('ideas.index'));
        $this->assertFalse($this->tenant->users()->where('users.id', $this->member->id)->exists());
    }
}
