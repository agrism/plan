<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function switchTenant($id)
    {
        $user = auth()->user();
        $tenant = $user->tenants()->findOrFail($id);

        session(['current_tenant_id' => $tenant->id]);

        return redirect()->route('ideas.index');
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'owner_id' => $user->id,
        ]);

        $tenant->users()->attach($user->id, ['role' => 'admin']);

        session(['current_tenant_id' => $tenant->id]);

        return redirect()->route('ideas.index');
    }

    public function settings()
    {
        $user = auth()->user();
        $tenant = $user->currentTenant();

        if (!$tenant) {
            return response('Darbavieta nav atrasta', 404);
        }

        $tenant->ensureDefaultCategories();
        $categories = $tenant->categories()->withCount('tasks')->get();

        return view('tenants.partials.settings_modal', compact('tenant', 'user', 'categories'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $user = auth()->user();

        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tenant->update([
            'name' => $validated['name'],
        ]);

        if ($request->header('HX-Request')) {
            return redirect()->route('ideas.index');
        }

        return redirect()->route('ideas.index');
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => 'required|string',
        ]);

        $code = trim($validated['invite_code']);
        $tenant = Tenant::where('invite_code', $code)->first();

        if (!$tenant) {
            return back()->withErrors(['invite_code' => __('app.invalid_invite_code')]);
        }

        $user = auth()->user();
        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            $tenant->users()->attach($user->id, ['role' => 'member']);
        }

        session(['current_tenant_id' => $tenant->id]);

        return redirect()->route('ideas.index');
    }

    public function joinByCode(string $code)
    {
        $code = trim($code);
        $tenant = Tenant::where('invite_code', $code)->first();

        if (!$tenant) {
            return redirect()->route('login')->withErrors(['invite_code' => __('app.invalid_invite_code')]);
        }

        if (auth()->check()) {
            $user = auth()->user();
            if (!$tenant->users()->where('users.id', $user->id)->exists()) {
                $tenant->users()->attach($user->id, ['role' => 'member']);
            }
            session(['current_tenant_id' => $tenant->id]);
            return redirect()->route('ideas.index');
        }

        session(['pending_invite_code' => $code]);

        return redirect()->route('register')->with('info', __('app.join_workspace_subtitle'));
    }

    public function leave(Request $request, Tenant $tenant)
    {
        $user = auth()->user();

        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            abort(403);
        }

        // Owner cannot leave while other members exist; they must transfer ownership first
        if ($tenant->owner_id === $user->id) {
            $otherMembersCount = $tenant->users()->where('users.id', '!=', $user->id)->count();
            if ($otherMembersCount > 0) {
                abort(403, __('app.owner_cannot_leave_must_transfer'));
            }
            // If sole member, deleting the workspace
            $tenant->delete();
        } else {
            // Detach regular user from workspace
            $tenant->users()->detach($user->id);
        }

        // Switch to remaining workspace or create fresh default workspace
        $nextTenant = $user->tenants()->first();
        if (!$nextTenant) {
            $nextTenant = Tenant::create([
                'name' => __('app.default_workspace_name', ['name' => $user->name]),
                'owner_id' => $user->id,
            ]);
            $nextTenant->users()->attach($user->id, ['role' => 'admin']);
        }

        session(['current_tenant_id' => $nextTenant->id]);

        return redirect()->route('ideas.index');
    }

    public function transferOwnership(Request $request, Tenant $tenant, User $user)
    {
        $currentUser = auth()->user();

        // Ensure current user is in the tenant
        if (!$tenant->users()->where('users.id', $currentUser->id)->exists()) {
            abort(403);
        }

        // Only the owner can transfer ownership
        if ($tenant->owner_id !== $currentUser->id) {
            abort(403, __('app.only_owner_can_transfer'));
        }

        // Ensure target user is a member of the tenant
        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            abort(404, __('app.not_a_member'));
        }

        // Cannot transfer to yourself
        if ($user->id === $currentUser->id) {
            return redirect()->route('ideas.index');
        }

        // Transfer ownership
        $tenant->update(['owner_id' => $user->id]);

        // Promote new owner to admin
        $tenant->users()->updateExistingPivot($user->id, ['role' => 'admin']);

        // Demote previous owner to regular member as requested
        $tenant->users()->updateExistingPivot($currentUser->id, ['role' => 'member']);

        if ($request->header('HX-Request')) {
            return redirect()->route('ideas.index');
        }

        return redirect()->route('ideas.index')->with('status', __('app.ownership_transferred_success', ['name' => $user->name]));
    }

    public function removeMember(Request $request, Tenant $tenant, User $user)
    {
        $currentUser = auth()->user();

        // Ensure current user is in the tenant
        if (!$tenant->users()->where('users.id', $currentUser->id)->exists()) {
            abort(403);
        }

        // Ensure target user is in the tenant
        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            abort(404, __('app.not_a_member'));
        }

        // If user is removing themselves, run leave logic
        if ($user->id === $currentUser->id) {
            return $this->leave($request, $tenant);
        }

        // Check if current user is owner or admin
        $isOwner = ($tenant->owner_id === $currentUser->id);
        $isAdmin = $tenant->users()->where('users.id', $currentUser->id)->wherePivot('role', 'admin')->exists();

        if (!$isOwner && !$isAdmin) {
            abort(403, __('app.unauthorized'));
        }

        // The tenant owner cannot be removed by others
        if ($tenant->owner_id === $user->id) {
            abort(403, __('app.cannot_remove_owner'));
        }

        // Detach target user
        $tenant->users()->detach($user->id);

        if ($request->header('HX-Request')) {
            return redirect()->route('ideas.index');
        }

        return redirect()->route('ideas.index')->with('status', __('app.member_removed_success', ['name' => $user->name]));
    }
}
