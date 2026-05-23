@extends('layouts.app')

@section('content')

<div class="users-hero">
    <div class="users-hero-inner">
        <h1 class="users-hero-title">Friends</h1>
        <p class="users-hero-sub">Your friends and pending requests.</p>
    </div>
</div>

<div class="users-layout">

    {{-- Pending requests --}}
    @if($pending->count() > 0)
    <div class="friends-section">
        <h2 class="friends-section-title">
            <i class="ti ti-clock"></i>
            Pending Requests
            <span class="review-count-badge">{{ $pending->count() }}</span>
        </h2>
        <div class="player-list">
            @foreach($pending as $req)
            <div class="player-row" style="cursor:default" id="pending-{{ $req->sender->id }}">
                <div class="player-row-left">
                    <div class="player-avatar-wrap">
                        @if($req->sender->avatar)
                            <img src="{{ Storage::url($req->sender->avatar) }}" class="player-avatar-img" alt="{{ $req->sender->name }}">
                        @else
                            <div class="player-avatar-initials">{{ strtoupper(substr($req->sender->name, 0, 1)) }}</div>
                        @endif
                    </div>
                    <div class="player-info">
                        <span class="player-name">{{ $req->sender->name }}</span>
                        <span class="player-meta">Sent {{ $req->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="friend-request-actions">
                    <button class="btn btn-primary btn-sm" onclick="acceptFromList({{ $req->sender->id }})">
                        <i class="ti ti-check"></i> Accept
                    </button>
                    <button class="btn btn-ghost btn-sm" onclick="declineFromList({{ $req->sender->id }})">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Friends list --}}
    <div class="friends-section">
        <h2 class="friends-section-title">
            <i class="ti ti-users"></i>
            Friends
            <span class="review-count-badge">{{ $friends->count() }}</span>
        </h2>

        @if($friends->isEmpty())
            <div class="reviews-empty" style="padding:2rem">
                <i class="ti ti-user-off reviews-empty-icon"></i>
                <h3>No friends yet</h3>
                <p>Find players to add on the <a href="{{ route('users.index') }}" style="color:var(--accent2)">Players</a> page.</p>
            </div>
        @else
            <div class="player-list">
                @foreach($friends as $friend)
                <a href="{{ route('users.show', $friend) }}" class="player-row">
                    <div class="player-row-left">
                        <div class="player-avatar-wrap">
                            @if($friend->avatar)
                                <img src="{{ Storage::url($friend->avatar) }}" class="player-avatar-img" alt="{{ $friend->name }}">
                            @else
                                <div class="player-avatar-initials">{{ strtoupper(substr($friend->name, 0, 1)) }}</div>
                            @endif
                        </div>
                        <div class="player-info">
                            <span class="player-name">{{ $friend->name }}</span>
                            <span class="player-meta">{{ $friend->reviews()->count() }} reviews</span>
                        </div>
                    </div>
                    <div class="player-row-right">
                        <i class="ti ti-chevron-right player-chevron"></i>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

</div>

<script>
async function acceptFromList(userId) {
    const res = await fetch(`/users/${userId}/friend/accept`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });
    if (res.ok) location.reload();
}

async function declineFromList(userId) {
    const res = await fetch(`/users/${userId}/friend/decline`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });
    if (res.ok) document.getElementById('pending-' + userId)?.remove();
}
</script>

@endsection
