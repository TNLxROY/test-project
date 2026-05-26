<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Services\XpService;

class ProfileController extends Controller
{
    public function show(XpService $xpService)
    {
        $user      = Auth::user();
        $userLevel = $xpService->getOrCreate($user);

        return view('profile', [
            'user' => Auth::user(),
            'userLevel' => $userLevel
        ]);
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

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar'    => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'crop_x'    => ['required', 'numeric', 'min:0'],
            'crop_y'    => ['required', 'numeric', 'min:0'],
            'crop_size' => ['required', 'numeric', 'min:1'],
        ]);

        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $manager  = new ImageManager(new Driver());
        $image    = $manager->read($request->file('avatar')->getRealPath());

        $origW    = $image->width();
        $origH    = $image->height();

        $cropX    = (int) $request->crop_x;
        $cropY    = (int) $request->crop_y;
        $cropSize = (int) $request->crop_size;

        // clamp to image bounds
        $cropX    = max(0, min($cropX, $origW - 1));
        $cropY    = max(0, min($cropY, $origH - 1));
        $cropSize = min($cropSize, $origW - $cropX, $origH - $cropY);

        $image->crop($cropSize, $cropSize, $cropX, $cropY);
        $image->resize(200, 200);

        Storage::disk('public')->makeDirectory('avatars');
        $filename = 'avatars/' . $user->id . '_' . time() . '.png';
        Storage::disk('public')->put($filename, $image->toPng());

        $user->update(['avatar' => $filename]);

        return response()->json([
            'message'    => 'Avatar updated.',
            'avatar_url' => Storage::url($filename) . '?v=' . time(),
        ]);
    }

    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json(['message' => 'Avatar removed.']);
    }
}
