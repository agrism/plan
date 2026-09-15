<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('profile.partials.edit_modal', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|string|max:10',
            'current_password' => 'nullable|string|required_with:password',
            'password' => 'nullable|string|min:5|confirmed',
        ]);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => __('app.current_password_incorrect'),
                ])->withInput();
            }

            $user->password = Hash::make($validated['password']);
        }

        $user->name = trim($validated['name']);
        $user->email = trim($validated['email']);
        if (isset($validated['avatar'])) {
            $user->avatar = $validated['avatar'];
        }

        $emailChanged = $user->isDirty('email');
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        if ($request->header('HX-Request')) {
            return redirect()->route('ideas.index');
        }

        return redirect()->route('ideas.index')->with('status', __('app.profile_updated_successfully'));
    }
}
