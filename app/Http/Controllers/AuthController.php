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
        $demoUsers = User::all();
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
            'password' => 'required|string|min:6|confirmed',
            'workspace_name' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'avatar' => '👤',
            'password' => Hash::make($validated['password']),
        ]);

        // Create initial workspace tenant
        $tenant = Tenant::create([
            'name' => $validated['workspace_name'],
            'owner_id' => $user->id,
        ]);

        $tenant->users()->attach($user->id, ['role' => 'admin']);

        Auth::login($user);
        session(['current_tenant_id' => $tenant->id]);

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
