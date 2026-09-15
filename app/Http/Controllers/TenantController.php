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

    public function join(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => 'required|string',
        ]);

        $tenant = Tenant::where('invite_code', $validated['invite_code'])->first();

        if (!$tenant) {
            return back()->withErrors(['invite_code' => 'Kods nav atrasts vai ir derīgums beidzies.']);
        }

        $user = auth()->user();
        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            $tenant->users()->attach($user->id, ['role' => 'member']);
        }

        session(['current_tenant_id' => $tenant->id]);

        return redirect()->route('ideas.index');
    }
}
