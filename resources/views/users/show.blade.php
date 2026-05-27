@extends('layouts.app')

@section('content')

{{-- Hero banner --}}
<div class="player-hero">
    <div class="player-hero-fade"></div>
    <div class="player-hero-content">
        <div class="player-hero-profile">
            {{-- Left: avatar + name + stats --}}
            <div class="player-hero-identity">
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
                        @auth
                            @if(auth()->id() !== $user->id)
                                <div class="friend-btn-wrap" id="friend-btn-wrap">
                                    @php
                                        $status = $friendship?->status;
                                        $isSender = $friendship?->sender_id === auth()->id();
                                    @endphp

                                    @if(!$friendship)
                                        <button class="btn btn-primary btn-sm friend-btn" onclick="sendFriendRequest({{ $user->id }})">
                                            <i class="ti ti-user-plus"></i> Add Friend
                                        </button>

                                    @elseif($status === 'pending' && $isSender)
                                        <button class="btn btn-ghost btn-sm friend-btn" disabled>
                                            <i class="ti ti-clock"></i> Request Sent
                                        </button>

                                    @elseif($status === 'pending' && !$isSender)
                                        <button class="btn btn-primary btn-sm friend-btn" onclick="acceptRequest({{ $user->id }})">
                                            <i class="ti ti-check"></i> Accept Request
                                        </button>
                                        <button class="btn btn-ghost btn-sm friend-btn" onclick="declineRequest({{ $user->id }})">
                                            <i class="ti ti-x"></i> Decline
                                        </button>

                                    @elseif($status === 'accepted')
                                        <button class="btn btn-ghost btn-sm friend-btn friend-btn-remove" onclick="removeFriend({{ $user->id }})">
                                            <i class="ti ti-user-minus"></i> Remove Friend
                                        </button>

                                    @elseif($status === 'declined')
                                        <button class="btn btn-primary btn-sm friend-btn" onclick="sendFriendRequest({{ $user->id }})">
                                            <i class="ti ti-user-plus"></i> Add Friend
                                        </button>
                                    @endif
                                </div>
                            @endif
                        @endauth
                        <div class="player-stat">
                            <span class="player-stat-num">{{ count($reviews) }}</span>
                            <span class="player-stat-label">Reviews</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: back link + XP panel --}}
            <div class="hero-right">
                <a href="{{ route('users.index') }}" class="back-link">
                    <i class="ti ti-arrow-left" aria-hidden="true"></i> Back to Users
                </a>

                {{-- XP panel --}}
                <div class="profile-xp-panel">
                <div class="profile-xp-top">
                    <div class="profile-xp-badge">
                        <span class="profile-xp-num">{{ $userLevel->level }}</span>
                        <span class="profile-xp-lvl">LVL</span>
                    </div>
                    <div class="profile-xp-top-right">
                        <span class="profile-xp-sub">{{ $userLevel->xp }} / {{ $userLevel->xpForCurrentLevel() }} XP &mdash; {{ $userLevel->progressPercent() }}%</span>
                        <div class="profile-xp-bar-track" role="progressbar"
                             aria-valuenow="{{ $userLevel->xp }}"
                             aria-valuemin="0"
                             aria-valuemax="{{ $userLevel->xpForCurrentLevel() }}">
                            <div class="profile-xp-bar-fill" style="width: 0%" data-xp-target="{{ $userLevel->progressPercent() }}"></div>
                        </div>
                        <span class="profile-xp-hint">{{ $userLevel->xpToNextLevel() }} xp needed to level up</span>
                    </div>
                </div>

                <div class="profile-xp-stats">
                    <div class="profile-xp-stat">
                        <i class="ti ti-file-text" aria-hidden="true"></i>
                        <div class="profile-xp-stat-text">
                            <span class="profile-xp-stat-val">{{ $userLevel->review_count }}</span>
                            <span class="profile-xp-stat-label">Reviews</span>
                        </div>
                    </div>
                    <div class="profile-xp-stat">
                        <i class="ti ti-star" aria-hidden="true"></i>
                        <div class="profile-xp-stat-text">
                            <span class="profile-xp-stat-val">{{ number_format($userLevel->total_xp) }}</span>
                            <span class="profile-xp-stat-label">Total XP</span>
                        </div>
                    </div>
                </div>
                </div>{{-- /.profile-xp-panel --}}
            </div>{{-- /.hero-right --}}
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

<script>
// XP bar charge-up
setTimeout(() => {
    const bar = document.querySelector('.profile-xp-bar-fill');
    if (bar) bar.style.width = bar.dataset.xpTarget + '%';
}, 800);


async function friendFetch(url, method) {
    const res  = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });
    return res.json().catch(() => ({}));
}

function setFriendBtn(html) {
    document.getElementById('friend-btn-wrap').innerHTML = html;
}

async function sendFriendRequest(userId) {
    const data = await friendFetch(`/users/${userId}/friend`, 'POST');
    if (data.status === 'pending') {
        setFriendBtn(`
            <button class="btn btn-ghost btn-sm friend-btn" disabled>
                <i class="ti ti-clock"></i> Request Sent
            </button>
        `);
    }
}

async function acceptRequest(userId) {
    const data = await friendFetch(`/users/${userId}/friend/accept`, 'POST');
    if (data.status === 'accepted') {
        setFriendBtn(`
            <button class="btn btn-ghost btn-sm friend-btn friend-btn-remove" onclick="removeFriend(${userId})">
                <i class="ti ti-user-minus"></i> Remove Friend
            </button>
        `);
    }
}

async function declineRequest(userId) {
    const data = await friendFetch(`/users/${userId}/friend/decline`, 'POST');
    if (data.status === 'declined') {
        setFriendBtn(`
            <button class="btn btn-primary btn-sm friend-btn" onclick="sendFriendRequest(${userId})">
                <i class="ti ti-user-plus"></i> Add Friend
            </button>
        `);
    }
}

async function removeFriend(userId) {
    const data = await friendFetch(`/users/${userId}/friend`, 'DELETE');
    if (data.status === 'none') {
        setFriendBtn(`
            <button class="btn btn-primary btn-sm friend-btn" onclick="sendFriendRequest(${userId})">
                <i class="ti ti-user-plus"></i> Add Friend
            </button>
        `);
    }
}
</script>

@endsection
