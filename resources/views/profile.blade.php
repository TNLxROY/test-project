@extends('layouts.app')

@section('content')

{{-- ── PROFILE HERO ─────────────────────────────────────────────────────── --}}
<div class="profile-hero">
    <div class="profile-hero-inner">

        <div class="profile-hero-identity">
            <div class="profile-avatar-wrap" id="avatar-wrap">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="profile-avatar-img" id="avatar-img">
                @else
                    <div class="profile-avatar-lg" id="avatar-initials">
                        {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name.' ', ' '), 1, 1)) }}
                    </div>
                @endif
                <button class="avatar-edit-btn" id="avatar-edit-btn" title="Change profile picture">
                    <i class="ti ti-camera" aria-hidden="true"></i>
                </button>
            </div>

            <div>
                <h1 class="profile-name" id="profile-display-name">{{ $user->name }}</h1>
                @if($user->active_title)
                    <span class="title-badge title-badge--active" id="profile-hero-title" data-title-full="{{ $user->active_title }}">{{ $user->active_title }}</span>
                @endif
                <p class="profile-email">{{ $user->email }}</p>
                <p class="profile-joined">Member since {{ $user->created_at->format('F Y') }}</p>
                @if($user->avatar)
                    <button id="remove-avatar-btn" class="btn btn-ghost btn-sm" style="margin-top:.5rem;font-size:.75rem" data-action="remove-avatar">
                        Remove photo
                    </button>
                @endif
            </div>
        </div>

        <div class="hero-right">
            <a href="{{ route('home') }}" class="back-link">
                <i class="ti ti-home" aria-hidden="true"></i> Home
            </a>
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
            </div>
        </div>

    </div>
</div>

{{-- ── TAB BAR ───────────────────────────────────────────────────────────── --}}
<div class="show-hero" style="background:none;min-height:unset;padding:0">
    <div class="show-hero-overlay" style="display:none"></div>
    <div class="show-tabs-bar">
        <div class="show-tabs">
            <button class="show-tab active" id="tab-about" data-profile-tab="about">
                <i class="ti ti-user" aria-hidden="true"></i> About Me
            </button>
            <button class="show-tab" id="tab-reviews" data-profile-tab="reviews">
                <i class="ti ti-file-text" aria-hidden="true"></i> My Reviews
                @if($userLevel->review_count > 0)
                    <span class="review-count-chip">{{ $userLevel->review_count }}</span>
                @endif
            </button>
            <button class="show-tab" id="tab-genres" data-profile-tab="genres">
                <i class="ti ti-chart-bar" aria-hidden="true"></i> Genres
                @php $genresExploredCount = collect($genreStats ?? [])->where('count', '>', 0)->count(); @endphp
                @if($genresExploredCount > 0)
                    <span class="review-count-chip">{{ $genresExploredCount }}</span>
                @endif
            </button>
            <button class="show-tab" id="tab-titles" data-profile-tab="titles">
                <i class="ti ti-tag" aria-hidden="true"></i> Titles
                @php $unlockedTitles = collect($titles ?? [])->where('unlocked', true); @endphp
                @if($unlockedTitles->count() > 0)
                    <span class="review-count-chip">{{ $unlockedTitles->count() }}</span>
                @endif
            </button>
            <button class="show-tab" id="tab-settings" data-profile-tab="settings">
                <i class="ti ti-settings" aria-hidden="true"></i> Settings
            </button>
        </div>
    </div>
</div>

{{-- ── PANEL: ABOUT ME ───────────────────────────────────────────────────── --}}
<div id="panel-about">
<div class="show-layout">

    <div class="show-main">

        {{-- Bio --}}
        <div class="show-card">
            <h2 class="show-card-title">Bio</h2>
            <textarea id="about-bio" class="review-textarea wr-textarea"
                placeholder="Tell others a bit about yourself… (max 500 characters)"
                maxlength="500"
                style="min-height:120px">{{ $user->bio ?? '' }}</textarea>
            <div class="review-char-count">
                <span id="bio-chars">{{ strlen($user->bio ?? '') }}</span> / 500
            </div>
            <button class="btn btn-primary btn-sm" style="margin-top:.75rem" data-action="save-bio">
                Save bio
            </button>
        </div>

        {{-- Favourite game --}}
        <div class="show-card">
            <h2 class="show-card-title">Favourite Game</h2>

            <div id="fav-current" style="{{ $user->favourite_game_id ? '' : 'display:none' }}">
                <div class="platform-item" style="margin-bottom:1rem">
                    <div class="platform-item-header">
                        <div class="platform-logo-wrap" style="width:52px;height:52px;overflow:hidden;border-radius:6px;flex-shrink:0">
                            <img id="fav-cover" src="{{ $user->favourite_game_cover ?? '' }}"
                                 alt="" style="width:100%;height:100%;object-fit:cover">
                        </div>
                        <div class="platform-info">
                            <span class="platform-name" id="fav-name">{{ $user->favourite_game_name ?? '' }}</span>
                            <span class="platform-date" id="fav-meta"></span>
                        </div>
                        <button class="sysreq-toggle" title="Remove" data-action="remove-fav-game" aria-label="Remove favourite game">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:.5rem">
                <label>Search for a game</label>
                <div style="display:flex;gap:.5rem">
                    <input type="text" id="fav-search-input"
                           placeholder="Type here…"
                           autocomplete="nope"
                           readonly
                           onfocus="this.removeAttribute('readonly')"
                           style="flex:1">
                    <button class="btn btn-ghost btn-sm" data-action="fav-search" style="white-space:nowrap">
                        <i class="ti ti-search" aria-hidden="true"></i> Search
                    </button>
                </div>
            </div>

            <div id="fav-results" style="display:none">
                <div id="fav-results-inner" class="store-list" style="flex-direction:column;gap:.5rem"></div>
                <p id="fav-no-results" style="display:none;color:var(--text-muted);font-size:.875rem;margin-top:.5rem">
                    No games found.
                </p>
            </div>

            <div class="auth-msg" id="fav-msg" style="margin-top:.5rem"></div>
        </div>

    </div>

    <div class="show-sidebar">

        <div class="show-card">
            <h2 class="show-card-title">Public Profile</h2>
            <dl class="detail-list">
                <div class="detail-row">
                    <dt>Username</dt>
                    <dd id="pub-name">{{ $user->name }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Member since</dt>
                    <dd>{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Reviews written</dt>
                    <dd>{{ $userLevel->review_count }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Level</dt>
                    <dd>{{ $userLevel->level }}</dd>
                </div>
            </dl>
        </div>

        @if($user->favourite_game_id)
        <div class="show-card" id="fav-sidebar-card">
            <h2 class="show-card-title">Favourite Game</h2>
            <a href="{{ route('games.show', $user->favourite_game_id) }}" class="store-btn" id="fav-sidebar-link">
                <i class="ti ti-device-gamepad-2" aria-hidden="true"></i>
                <span id="fav-sidebar-name">{{ $user->favourite_game_name ?? '' }}</span>
            </a>
        </div>
        @else
        <div class="show-card" id="fav-sidebar-card" style="display:none">
            <h2 class="show-card-title">Favourite Game</h2>
            <a href="#" class="store-btn" id="fav-sidebar-link">
                <i class="ti ti-device-gamepad-2" aria-hidden="true"></i>
                <span id="fav-sidebar-name"></span>
            </a>
        </div>
        @endif

        <div class="show-card">
            <h2 class="show-card-title">Quick Links</h2>
            <div class="store-list">
                <a href="{{ route('games.index') }}" class="store-btn">Browse Games</a>
                <a href="{{ route('home') }}" class="store-btn">Home</a>
            </div>
        </div>

    </div>
</div>
</div>{{-- /#panel-about --}}


{{-- ── PANEL: MY REVIEWS ─────────────────────────────────────────────────── --}}
<div id="panel-reviews" style="display:none">
<div class="show-layout">

    <div class="show-main">

        @forelse($reviews as $review)
        <div class="show-card review-profile-card" id="profile-review-{{ $review->id }}"
             @if(!empty($review->game_cover)) data-cover="{{ $review->game_cover }}" @endif>

            <div class="review-profile-body">

                {{-- Top row: thumbnail · meta · score pill · delete --}}
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

                    {{-- Score pill + delete --}}
                    <div class="review-profile-actions">
                        @if($review->rating)
                        <div class="review-profile-score-pill">
                            <span class="review-profile-score-big">{{ number_format($review->rating, 1) }}</span>
                            <span class="review-profile-score-denom">/10</span>
                        </div>
                        @endif
                        <button class="review-delete-btn"
                                data-action="delete-profile-review"
                                data-review-id="{{ $review->id }}"
                                data-game-id="{{ $review->game_id }}"
                                title="Delete review"
                                aria-label="Delete review for {{ $review->game_name }}">
                            <i class="ti ti-trash" aria-hidden="true"></i>
                        </button>
                    </div>

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
            <p style="color:var(--text-muted);margin:0">You haven't written any reviews yet.</p>
            <a href="{{ route('games.index') }}" class="btn btn-primary btn-sm" style="margin-top:1rem;display:inline-block">Browse Games</a>
        </div>
        @endforelse

    </div>

    <div class="show-sidebar">
        <div class="show-card">
            <h2 class="show-card-title">Review Stats</h2>
            <dl class="detail-list">
                <div class="detail-row">
                    <dt>Total reviews</dt>
                    <dd>{{ $userLevel->review_count }}</dd>
                </div>
                @if($reviews->count() > 0)
                <div class="detail-row">
                    <dt>Average rating</dt>
                    <dd>{{ number_format($reviews->avg('rating'), 1) }} / 10</dd>
                </div>
                <div class="detail-row">
                    <dt>Detailed reviews</dt>
                    <dd>{{ $reviews->where('is_detailed', true)->count() }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

</div>
</div>{{-- /#panel-reviews --}}


{{-- ── PANEL: GENRES ─────────────────────────────────────────────────────── --}}
<div id="panel-genres" style="display:none">
<div class="show-layout">

    <div class="show-main">
        <div class="show-card">
            <h2 class="show-card-title">Reviews by Genre</h2>

            @if($reviews->count() === 0)
                <p style="color:var(--text-muted);margin:0 0 1rem">
                    You haven't written any reviews yet — once you do, your genre breakdown will show up here.
                </p>
            @endif

            <div class="ratings-row">
                @foreach($genreStats as $g)
                <div class="rating-bar-item">
                    <div class="rating-bar-label">
                        <span>{{ $g['name'] }}</span>
                        <span class="rating-bar-count">{{ $g['count'] }}</span>
                    </div>
                    <div class="rating-bar-track">
                        <div class="rating-bar-fill" style="width: {{ $g['percent'] }}%; background: var(--accent)"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="show-sidebar">
        <div class="show-card">
            <h2 class="show-card-title">Genre Stats</h2>
            <dl class="detail-list">
                <div class="detail-row">
                    <dt>Genres explored</dt>
                    <dd>{{ $genresExploredCount }} / {{ count($genreStats) }}</dd>
                </div>
                @php $topGenre = collect($genreStats)->first(fn($g) => $g['count'] > 0); @endphp
                @if($topGenre)
                <div class="detail-row">
                    <dt>Most reviewed</dt>
                    <dd>{{ $topGenre['name'] }} ({{ $topGenre['count'] }})</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

</div>
</div>{{-- /#panel-genres --}}


{{-- ── PANEL: TITLES ─────────────────────────────────────────────────────── --}}
<div id="panel-titles" style="display:none">
<div class="show-layout">

    <div class="show-main">

        @php
            $unlockedTitles = collect($titles ?? [])->where('unlocked', true);
            $lockedTitles   = collect($titles ?? [])->where('unlocked', false);
        @endphp

        {{-- Active title selector --}}
        <div class="show-card">
            <h2 class="show-card-title">Active Title</h2>
            @if($unlockedTitles->count() > 0)
                <p style="color:var(--text-muted);font-size:.875rem;margin-bottom:1rem">
                    Select the title displayed on your profile. Click any unlocked title below to equip it.
                </p>
                <div class="title-active-display" id="title-active-display">
                    @if($user->active_title)
                        <span class="title-badge title-badge--active">{{ $user->active_title }}</span>
                        <button class="btn btn-ghost btn-sm" data-action="clear-title" style="font-size:.75rem">
                            Remove
                        </button>
                    @else
                        <span style="color:var(--text-muted);font-size:.875rem">No title equipped — pick one below.</span>
                    @endif
                </div>
            @else
                <p style="color:var(--text-muted);font-size:.875rem">
                    You haven't unlocked any titles yet. Complete achievements to earn them!
                </p>
            @endif
        </div>

        {{-- Unlocked titles --}}
        @if($unlockedTitles->count() > 0)
        <div class="show-card">
            <h2 class="show-card-title">
                <i class="ti ti-trophy" style="color:var(--accent)"></i>
                Unlocked
                <span class="review-count-badge" style="margin-left:.4rem">{{ $unlockedTitles->count() }}</span>
            </h2>
            <div class="titles-grid">
                @foreach($unlockedTitles as $t)
                <button class="title-card title-card--unlocked {{ ($user->active_title === $t['label']) ? 'title-card--equipped' : '' }}"
                        data-action="equip-title"
                        data-title="{{ $t['label'] }}"
                        title="Equip: {{ $t['label'] }}">
                    <div class="title-card-header">
                        <span class="title-card-label">{{ $t['label'] }}</span>
                        @if($user->active_title === $t['label'])
                            <span class="title-card-equipped-chip">Equipped</span>
                        @endif
                    </div>
                    <span class="title-card-source">Unlocked by completing the '{{ $t['achievement'] }}' achievement.</span>
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Locked titles --}}
        @if($lockedTitles->count() > 0)
        <div class="show-card">
            <h2 class="show-card-title" style="color:var(--text-muted)">
                <i class="ti ti-lock"></i>
                Locked
                <span class="review-count-badge" style="margin-left:.4rem">{{ $lockedTitles->count() }}</span>
            </h2>
            <div class="titles-grid">
                @foreach($lockedTitles as $t)
                <div class="title-card title-card--locked">
                    @if($t['secret'] ?? false)
                        <span class="title-card-label" style="color:var(--text-muted)">???</span>
                        <span class="title-card-source">Secret title — keep playing to discover it.</span>
                    @else
                        <span class="title-card-label" style="color:var(--text-muted)">{{ $t['label'] }}</span>
                        <span class="title-card-source">Unlock by completing the '{{ $t['achievement'] }}' achievement.</span>
                    @endif
                    <i class="ti ti-lock title-card-lock-icon"></i>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <div class="show-sidebar">
        <div class="show-card">
            <h2 class="show-card-title">About Titles</h2>
            <p style="color:var(--text-muted);font-size:.875rem;line-height:1.6">
                Titles are short labels that appear next to your name. Earn them by completing achievements.
                Only one title can be active at a time.
            </p>
            <dl class="detail-list" style="margin-top:1rem">
                <div class="detail-row">
                    <dt>Unlocked</dt>
                    <dd>{{ $unlockedTitles->count() }} / {{ collect($titles ?? [])->count() }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Active</dt>
                    <dd id="sidebar-active-title">{{ $user->active_title ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

</div>
</div>{{-- /#panel-titles --}}


{{-- ── PANEL: SETTINGS ──────────────────────────────────────────────────── --}}
<div id="panel-settings" style="display:none">
<div class="show-layout">

    <div class="show-main">

        <div class="show-card">
            <h2 class="show-card-title">Display Name</h2>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="profile-name" value="{{ $user->name }}" placeholder="Your name">
            </div>
            <button class="btn btn-primary btn-sm" data-action="save-name">Save changes</button>
        </div>

        <div class="show-card">
            <h2 class="show-card-title">Change Password</h2>
            <div class="form-group">
                <label>Current password</label>
                <input type="password" id="cur-pass" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>New password</label>
                <input type="password" id="new-pass" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>Confirm new password</label>
                <input type="password" id="new-pass-confirm" placeholder="••••••••">
            </div>
            <button class="btn btn-primary btn-sm" data-action="save-password">Update password</button>
        </div>

        <div class="show-card danger-card">
            <h2 class="show-card-title" style="color:var(--accent2)">Danger Zone</h2>
            <p class="danger-desc">Permanently delete your account and all associated data. This cannot be undone.</p>
            <button class="btn btn-ghost btn-sm danger-btn" data-action="open-delete-modal">Delete account</button>
        </div>

    </div>

    <div class="show-sidebar">
        <div class="show-card">
            <h2 class="show-card-title">Account Info</h2>
            <dl class="detail-list">
                <div class="detail-row">
                    <dt>User ID</dt>
                    <dd>#{{ $user->id }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Email</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Joined</dt>
                    <dd>{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Last updated</dt>
                    <dd>{{ $user->updated_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

</div>
</div>{{-- /#panel-settings --}}


{{-- ── AVATAR CROP MODAL ────────────────────────────────────────────────── --}}
<div class="modal-overlay" id="avatar-modal" style="display:none" data-action="close-avatar-modal-overlay">
    <div class="modal" style="max-width:380px">
        <button class="modal-close" data-action="close-avatar-modal">✕</button>
        <h2>Set Profile Photo</h2>
        <p class="modal-sub">Drag the image to reposition, then crop.</p>
        <div class="crop-zone" id="crop-zone">
            <img id="crop-img" src="" alt="" draggable="false">
            <div class="crop-circle-overlay"></div>
        </div>
        <input type="file" id="avatar-file-input" accept="image/png,image/jpeg,image/webp" style="display:none">
        <div style="display:flex;gap:.5rem;margin-top:1rem">
            <button class="btn btn-ghost btn-sm" style="flex:1" data-action="choose-avatar-file">
                Choose image
            </button>
            <button class="btn btn-primary btn-sm" style="flex:1" data-action="save-avatar">
                Save photo
            </button>
        </div>
        <div class="auth-msg" id="avatar-msg" style="margin-top:.75rem"></div>
    </div>
</div>

{{-- ── DELETE ACCOUNT MODAL ─────────────────────────────────────────────── --}}
<div class="modal-overlay" id="delete-modal-overlay" style="display:none" data-action="close-delete-modal-overlay">
    <div class="modal" id="delete-modal-box">
        <button class="modal-close" data-action="close-delete-modal">✕</button>
        <h2>Delete account?</h2>
        <p class="modal-sub">Enter your password to confirm. This action is permanent and cannot be reversed.</p>
        <div class="form-group">
            <label>Password</label>
            <input type="password" id="delete-pass" placeholder="••••••••">
        </div>
        <button class="btn btn-primary btn-sm" style="background:var(--accent);width:100%;padding:.75rem" data-action="confirm-delete">
            Yes, delete my account
        </button>
        <div class="auth-msg" id="delete-msg" style="margin-top:.75rem"></div>
    </div>
</div>

<div class="notification" id="profile-notif"></div>

@endsection
