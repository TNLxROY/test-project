@extends('layouts.app')

@section('content')

{{-- Hero banner --}}
<div class="player-hero">
    <div class="player-hero-fade"></div>
    <div class="player-hero-content">
        <a href="{{ route('users.index') }}" class="back-link">← Back to Players</a>
        <div class="player-hero-profile">
            <div class="player-hero-avatar-wrap">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="player-hero-avatar-img">
                @else
                    <div class="player-hero-avatar-initials">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div>
                <h1 class="player-hero-name">{{ $user->name }}</h1>
                <p class="player-hero-since">Member since {{ $user->created_at->format('F Y') }}</p>
                <div class="player-hero-stats">
                    <div class="player-stat">
                        <span class="player-stat-num">{{ count($reviews) }}</span>
                        <span class="player-stat-label">Reviews</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reviews --}}
<div class="reviews-layout">
    @if(count($reviews) === 0)
        <div class="reviews-empty">
            <i class="ti ti-message-off reviews-empty-icon"></i>
            <h3>No reviews yet</h3>
            <p>{{ $user->name }} hasn't reviewed any games yet.</p>
        </div>
    @else
        <h2 class="player-reviews-title">Reviews by {{ $user->name }}</h2>
        @foreach($reviews as $review)
        <a href="{{ route('games.show', $review->game_id) }}" class="review-card review-card-link">
            <div class="review-header">
                <div class="review-game-badge">
                    <i class="ti ti-device-gamepad-2"></i>
                </div>
                <div>
                    <div class="review-author">{{ $review->game_name }}</div>
                    <div class="review-date">{{ $review->created_at->format('M j, Y') }}</div>
                </div>
            </div>
            <p class="review-body">{{ $review->body }}</p>
        </a>
        @endforeach
    @endif
</div>

@endsection
