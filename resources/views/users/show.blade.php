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

{{-- Favourite game + bio banner --}}
@if($user->favourite_game_id || $user->bio)
<div class="player-about-banner-wrap">
<div class="player-about-banner">
    @if($user->favourite_game_id)
    <a href="{{ route('games.show', $user->favourite_game_id) }}" class="player-fav-game">
        @if($user->favourite_game_cover)
            <img src="{{ $user->favourite_game_cover }}" alt="{{ $user->favourite_game_name }}" class="player-fav-cover">
        @else
            <div class="player-fav-cover player-fav-cover--placeholder">
                <i class="ti ti-device-gamepad-2" aria-hidden="true"></i>
            </div>
        @endif
        <div class="player-fav-info">
            <span class="player-fav-label"><i class="ti ti-heart-filled" aria-hidden="true"></i> Favourite Game</span>
            <span class="player-fav-name">{{ $user->favourite_game_name }}</span>
        </div>
    </a>
    @endif

    @if($user->bio)
        @if($user->favourite_game_id)
            <div class="player-about-divider"></div>
        @endif
        <div class="player-about-bio-wrap">
            <span class="player-about-bio-label">About</span>
            <p class="player-about-bio">{{ $user->bio }}</p>
        </div>
    @endif
</div>
</div>
@endif

{{-- Reviews --}}
<div class="show-layout">
<div class="show-main">

    @forelse($reviews as $review)
    <div class="show-card review-profile-card"
         @if(!empty($review->game_cover)) data-cover="{{ $review->game_cover }}" @endif>

        <div class="review-profile-body">

            {{-- Top row: thumbnail · meta · score pill --}}
            <div class="review-profile-header">

                {{-- Small cover thumbnail --}}
                <div class="review-profile-cover">
                    @if(!empty($review->game_cover))
                        <img src="{{ $review->game_cover }}" alt="{{ $review->game_name }} cover" crossorigin="anonymous">
                    @else
                        <i class="ti ti-device-gamepad-2 review-profile-cover-placeholder" aria-hidden="true"></i>
                    @endif
                </div>

                {{-- Game name + date + badge --}}
                <div class="review-profile-meta">
                    <a href="{{ route('games.show', $review->game_id) }}" class="review-profile-game-link">
                        {{ $review->game_name }}
                    </a>
                    <div class="review-profile-sub">
                        <span class="review-profile-date">{{ $review->created_at->format('M j, Y') }}</span>
                        @if($review->is_detailed)
                            <span class="review-type-badge review-type-badge--detailed">Detailed</span>
                        @endif
                    </div>
                </div>

                {{-- Score pill --}}
                @if($review->rating)
                <div class="review-profile-actions">
                    <div class="review-profile-score-pill">
                        <span class="review-profile-score-big">{{ number_format($review->rating, 1) }}</span>
                        <span class="review-profile-score-denom">/10</span>
                    </div>
                </div>
                @endif

            </div>{{-- /.review-profile-header --}}

            {{-- Review body text --}}
            @if($review->body)
                <p class="review-body">{{ $review->body }}</p>
            @endif

            {{-- Category chips --}}
            @if($review->categories)
            <div class="review-profile-cats">
                @foreach($review->categories as $cat)
                <div class="review-profile-cat-row">
                    <span class="review-profile-cat-name">{{ $cat['name'] }}</span>
                    <span class="review-profile-cat-rating">{{ number_format($cat['rating'], 1) }}</span>
                </div>
                @endforeach
            </div>
            @endif

        </div>{{-- /.review-profile-body --}}

    </div>{{-- /.review-profile-card --}}
    @empty
    <div class="show-card" style="text-align:center;padding:2.5rem 1rem">
        <i class="ti ti-message-off" style="font-size:2.5rem;color:var(--text-muted);display:block;margin-bottom:.75rem" aria-hidden="true"></i>
        <h3 style="margin:0 0 .5rem">No reviews yet</h3>
        <p style="color:var(--text-muted);margin:0">{{ $user->name }} hasn't reviewed any games yet.</p>
    </div>
    @endforelse

</div>

<div class="show-sidebar">
    <div class="show-card">
        <h2 class="show-card-title">Review Stats</h2>
        <dl class="detail-list">
            <div class="detail-row">
                <dt>Total reviews</dt>
                <dd>{{ count($reviews) }}</dd>
            </div>
            @if(count($reviews) > 0)
            <div class="detail-row">
                <dt>Member since</dt>
                <dd>{{ $user->created_at->format('M j, Y') }}</dd>
            </div>
            <div class="detail-row">
                <dt>Level</dt>
                <dd>{{ $userLevel->level }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>

</div>

<script>
// XP bar charge-up
setTimeout(() => {
    const bar = document.querySelector('.profile-xp-bar-fill');
    if (bar) bar.style.width = bar.dataset.xpTarget + '%';
}, 800);

// Cover art dominant-colour tint via Canvas
function tintReviewCards() {
    document.querySelectorAll('.review-profile-card[data-cover]').forEach(card => {
        if (card.dataset.tinted) return;
        const coverUrl = card.dataset.cover;
        if (!coverUrl) return;

        const img = new Image();
        img.crossOrigin = 'anonymous';

        img.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = canvas.height = 16;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, 16, 16);

                const { data } = ctx.getImageData(0, 0, 16, 16);
                let r = 0, g = 0, b = 0, count = 0;

                for (let i = 0; i < data.length; i += 4) {
                    if (data[i + 3] < 128) continue;
                    const brightness = (data[i] + data[i + 1] + data[i + 2]) / 3;
                    if (brightness < 20 || brightness > 235) continue;
                    r += data[i]; g += data[i + 1]; b += data[i + 2];
                    count++;
                }

                if (!count) return;

                r = Math.round(r / count);
                g = Math.round(g / count);
                b = Math.round(b / count);

                card.style.setProperty('--card-tint', `rgb(${r},${g},${b})`);
                card.style.borderLeftColor = `rgba(${r},${g},${b},0.45)`;
                card.style.borderLeftWidth = '3px';
                card.dataset.tinted = '1';
            } catch (_) { /* CORS or security error — skip silently */ }
        };

        img.src = coverUrl;
    });
}

tintReviewCards();


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
