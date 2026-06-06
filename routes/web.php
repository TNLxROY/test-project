<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/about', function () {
    return view('about');
})->name('about');

// Auth routes — must use 'web' middleware so sessions are persisted
Route::post('/login', [AuthController::class, 'login'])->middleware('web');
Route::post('/register', [AuthController::class, 'register'])->middleware('web');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out']);
})->middleware('web');

Route::get('/api/user', function () {
    $user = auth()->user();
    return response()->json([
        'user' => $user ? [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'avatar_url' => $user->avatar ? Storage::url($user->avatar) : null,
        ] : null
    ]);
})->middleware('web');

Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])
    ->middleware('auth')
    ->name('profile');

Route::middleware('auth')->group(function () {
    Route::get('/profile',                  [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::post('/profile/name',            [App\Http\Controllers\ProfileController::class, 'updateName']);
    Route::post('/profile/password',        [App\Http\Controllers\ProfileController::class, 'updatePassword']);
    Route::delete('/profile/delete',        [App\Http\Controllers\ProfileController::class, 'deleteAccount']);
    Route::post('/profile/avatar',   [App\Http\Controllers\ProfileController::class, 'updateAvatar']);
    Route::delete('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'deleteAvatar']);
});

Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/search', [GameController::class, 'search'])->name('games.search');
Route::get('/games/{id}', [GameController::class, 'show'])->name('games.show');

Route::middleware('auth')->group(function () {
    Route::post('/games/{gameId}/reviews',           [App\Http\Controllers\ReviewController::class, 'store']);
    Route::delete('/games/{gameId}/reviews/{reviewId}', [App\Http\Controllers\ReviewController::class, 'destroy']);
});

Route::get('/users',        [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');

Route::middleware('auth')->group(function () {
    // ... existing auth routes ...
    Route::get('/friends',                          [App\Http\Controllers\FriendshipController::class, 'index'])->name('friends.index');
    Route::post('/users/{user}/friend',             [App\Http\Controllers\FriendshipController::class, 'send'])->name('friends.send');
    Route::post('/users/{user}/friend/accept',      [App\Http\Controllers\FriendshipController::class, 'accept'])->name('friends.accept');
    Route::post('/users/{user}/friend/decline',     [App\Http\Controllers\FriendshipController::class, 'decline'])->name('friends.decline');
    Route::delete('/users/{user}/friend',           [App\Http\Controllers\FriendshipController::class, 'remove'])->name('friends.remove');
});

Route::get('/achievements', [App\Http\Controllers\AchievementController::class, 'index'])->name('achievements.index');
