<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        $demoUsers = app()->environment('local') ? User::all() : collect();
        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            
            // Check if there is a pending invite code
            if ($pendingCode = session()->pull('pending_invite_code')) {
                if ($inviteTenant = Tenant::where('invite_code', $pendingCode)->first()) {
                    if (!$inviteTenant->users()->where('users.id', $user->id)->exists()) {
                        $inviteTenant->users()->attach($user->id, ['role' => 'member']);
                    }
                    session(['current_tenant_id' => $inviteTenant->id]);
                    return redirect()->route('ideas.index');
                }
            }

            if ($tenant = $user->tenants()->first()) {
                session(['current_tenant_id' => $tenant->id]);
            }
            return redirect()->intended(route('ideas.index'));
        }

        return back()->withErrors([
            'email' => 'Nepareizs e-pasts vai parole.',
        ])->onlyInput('email');
    }

    public function quickLogin(User $user)
    {
        Auth::login($user);
        request()->session()->regenerate();

        if ($pendingCode = session()->pull('pending_invite_code')) {
            if ($inviteTenant = Tenant::where('invite_code', $pendingCode)->first()) {
                if (!$inviteTenant->users()->where('users.id', $user->id)->exists()) {
                    $inviteTenant->users()->attach($user->id, ['role' => 'member']);
                }
                session(['current_tenant_id' => $inviteTenant->id]);
                return redirect()->route('ideas.index');
            }
        }

        if ($tenant = $user->tenants()->first()) {
            session(['current_tenant_id' => $tenant->id]);
        }
        return redirect()->route('ideas.index');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5|confirmed',
            'workspace_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'avatar' => '👤',
            'password' => Hash::make($validated['password']),
        ]);

        $pendingCode = session()->pull('pending_invite_code');
        $inviteTenant = $pendingCode ? Tenant::where('invite_code', $pendingCode)->first() : null;

        if ($inviteTenant) {
            $inviteTenant->users()->attach($user->id, ['role' => 'member']);
            $currentTenantId = $inviteTenant->id;
        } else {
            // Create initial workspace tenant
            $tenant = Tenant::create([
                'name' => $validated['workspace_name'] ?: ($user->name . ' Komanda'),
                'owner_id' => $user->id,
            ]);
            $tenant->users()->attach($user->id, ['role' => 'admin']);
            $currentTenantId = $tenant->id;
        }

        Auth::login($user);
        session(['current_tenant_id' => $currentTenantId]);

        return redirect()->route('ideas.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
