<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile', ['user' => Auth::user()]);
    }

    public function updateName(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->update(['name' => $data['name']]);

        return response()->json(['message' => 'Name updated.', 'user' => ['name' => Auth::user()->name]]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', Password::min(8)->uncompromised(), 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], Auth::user()->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        Auth::user()->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password updated.']);
    }

    public function deleteAccount(Request $request)
    {
        $request->validate(['password' => ['required']]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['message' => 'Incorrect password.'], 422);
        }

        $user = Auth::user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $user->delete();

        return response()->json(['message' => 'Account deleted.']);
    }
}
