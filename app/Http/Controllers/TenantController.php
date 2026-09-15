<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
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

        // Detach user from workspace
        $tenant->users()->detach($user->id);

        // If the user was the owner and other members exist, transfer ownership
        if ($tenant->owner_id === $user->id) {
            $remainingMember = $tenant->users()->first();
            if ($remainingMember) {
                $tenant->update(['owner_id' => $remainingMember->id]);
                $tenant->users()->updateExistingPivot($remainingMember->id, ['role' => 'admin']);
            } else {
                $tenant->delete();
            }
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
}
