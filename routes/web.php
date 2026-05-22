<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => $user->avatar ?? null,
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
});

Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/search', [GameController::class, 'search'])->name('games.search');
Route::get('/games/{id}', [GameController::class, 'show'])->name('games.show');
