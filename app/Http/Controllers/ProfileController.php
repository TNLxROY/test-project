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
use App\Services\AchievementService;
use App\Services\RawgService;

class ProfileController extends Controller
{
    public function show(XpService $xpService, AchievementService $achievementService, RawgService $rawgService)
    {
        $user      = Auth::user();
        $userLevel = $xpService->getOrCreate($user);
        $reviews   = \App\Models\Review::where('user_id', $user->id)
            ->latest()
            ->get();
        $titles     = $achievementService->titlesForUser($user);
        $genreStats = $this->buildGenreStats($reviews, $rawgService);

        return view('profile', [
            'user'       => $user,
            'userLevel'  => $userLevel,
            'reviews'    => $reviews,
            'titles'     => $titles,
            'genreStats' => $genreStats,
        ]);
    }

    /**
     * Build the "reviews per genre" breakdown for the profile's Genres tab.
     * Always returns every genre RAWG knows about (count 0 if the user
     * hasn't reviewed anything in that genre yet), sorted by review count
     * descending, with each entry's bar width pre-computed relative to
     * the user's most-reviewed genre.
     */
    private function buildGenreStats($reviews, RawgService $rawgService): array
    {
        $counts = [];

        foreach ($reviews as $review) {
            foreach ($review->genres ?? [] as $genre) {
                $name = $genre['name'] ?? null;
                if (!$name) {
                    continue;
                }
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }

        $maxCount = empty($counts) ? 0 : max($counts);

        $stats = collect($rawgService->getGenres())
            ->map(function ($genre) use ($counts, $maxCount) {
                $count = $counts[$genre['name']] ?? 0;

                return [
                    'id'      => $genre['id'],
                    'name'    => $genre['name'],
                    'slug'    => $genre['slug'],
                    'count'   => $count,
                    'percent' => $maxCount > 0 ? (int) round(($count / $maxCount) * 100) : 0,
                ];
            })
            ->all();

        usort($stats, fn ($a, $b) => $b['count'] <=> $a['count'] ?: strcmp($a['name'], $b['name']));

        return $stats;
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

    public function updateBio(Request $request)
    {
        $data = $request->validate([
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        Auth::user()->update(['bio' => $data['bio'] ?? null]);

        return response()->json(['message' => 'Bio updated.']);
    }

    public function updateFavouriteGame(Request $request)
    {
        $data = $request->validate([
            'game_id'    => ['required', 'integer'],
            'game_name'  => ['required', 'string', 'max:255'],
            'game_cover' => ['nullable', 'string', 'max:500'],
        ]);

        Auth::user()->update([
            'favourite_game_id'    => $data['game_id'],
            'favourite_game_name'  => $data['game_name'],
            'favourite_game_cover' => $data['game_cover'] ?? null,
        ]);

        return response()->json(['message' => 'Favourite game saved.']);
    }

    public function removeFavouriteGame()
    {
        Auth::user()->update([
            'favourite_game_id'    => null,
            'favourite_game_name'  => null,
            'favourite_game_cover' => null,
        ]);

        return response()->json(['message' => 'Favourite game removed.']);
    }

    public function equipTitle(Request $request, AchievementService $achievementService)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $user   = Auth::user();
        $titles = $achievementService->titlesForUser($user);

        // Ensure the requested title is actually unlocked by this user
        $unlocked = collect($titles)
            ->where('unlocked', true)
            ->pluck('label')
            ->contains($data['title']);

        if (!$unlocked) {
            return response()->json(['message' => 'Title not unlocked.'], 403);
        }

        $user->update(['active_title' => $data['title']]);

        return response()->json(['message' => 'Title equipped.', 'active_title' => $data['title']]);
    }

    public function clearTitle()
    {
        Auth::user()->update(['active_title' => null]);

        return response()->json(['message' => 'Title removed.']);
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

        $cropX    = max(0, min($cropX, $origW - 1));
        $cropY    = max(0, min($cropY, $origH - 1));
        $cropSize = min($cropSize, $origW - $cropX, $origH - $cropY);

        $image->crop($cropSize, $cropSize, $cropX, $cropY);
        $image->resize(200, 200);

        Storage::disk('public')->makeDirectory('avatars');
        $filename = 'avatars/' . $user->id . '_' . time() . '.png';
        Storage::disk('public')->put($filename, $image->toPng());

        $user->update(['avatar' => $filename]);

        app(AchievementService::class)->checkAndAward(Auth::user());

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
