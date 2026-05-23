@extends('layouts.app')

@section('content')

<div class="users-hero">
    <div class="users-hero-inner">
        <h1 class="users-hero-title">Players</h1>
        <p class="users-hero-sub">Search for other players and view their reviews.</p>
        <form action="{{ route('users.index') }}" method="GET" class="users-search-form">
            <input
                type="text"
                name="q"
                value="{{ $query ?? '' }}"
                placeholder="Search by username..."
                class="users-search-input"
                autocomplete="off"
            >
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</div>

<div class="users-layout">

    @if($users->isEmpty())
        <div class="reviews-empty">
            <i class="ti ti-users-off reviews-empty-icon"></i>
            <h3>No players found</h3>
            <p>Try a different search term.</p>
        </div>
    @else
        <div class="player-list">
            @foreach($users as $user)
            <a href="{{ route('users.show', $user) }}" class="player-row">
                <div class="player-row-left">
                    <div class="player-avatar-wrap">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="player-avatar-img">
                        @else
                            <div class="player-avatar-initials">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="player-info">
                        <span class="player-name">{{ $user->name }}</span>
                        <span class="player-meta">{{ $user->reviews_count ?? 0 }} reviews · joined {{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
                <div class="player-row-right">
                    <i class="ti ti-chevron-right player-chevron"></i>
                </div>
            </a>
            @endforeach
        </div>

        <div class="users-pagination">
            {{ $users->appends(['q' => $query])->links() }}
        </div>
    @endif

</div>

@endsection
