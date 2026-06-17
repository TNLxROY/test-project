@extends('layouts.app')

@section('title', 'Challenges — Find a Game')

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────── --}}
<div class="challenges-hero">
    <div class="challenges-hero-inner">
        <div class="hero-tag">Challenge Mode</div>
        <h1 class="challenges-hero-title">Find a Game to <em>Challenge</em></h1>
        <p class="challenges-hero-sub">Search for any game and browse community-made challenge rulesets — or create your own.</p>

        <div class="challenges-search-wrap" id="challenges-search-wrap">
            <div class="challenges-search-box">
                <svg class="challenges-search-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input
                    type="text"
                    id="challenges-search-input"
                    class="challenges-search-input"
                    placeholder="Search for a game…"
                    autocomplete="off"
                    autofocus
                >
                <div class="challenges-search-spinner" id="challenges-spinner" style="display:none">
                    <div class="spinner-ring"></div>
                </div>
            </div>
            <div class="challenges-search-results" id="challenges-results" style="display:none"></div>
        </div>
    </div>
</div>

{{-- ── Popular / recent games ────────────────────────────────────── --}}
<div class="results-section" id="popular-section">
    <div class="results-header">
        <div>
            <h2 class="section-title" style="font-size:1.2rem;margin-bottom:.2rem">Popular Games</h2>
            <p class="results-count">Browse top-rated games with active challenge rulesets</p>
        </div>
    </div>
    <div class="games-grid" id="popular-grid">
        @foreach($popularGames as $game)
            <a href="{{ route('challenges.game', $game['id']) }}" class="game-card">
                <div class="card-img">
                    @if(!empty($game['background_image']))
                        <img src="{{ $game['background_image'] }}" alt="{{ $game['name'] }}" loading="lazy">
                    @endif
                    @if(!empty($game['rating']))
                        <div class="rating-badge">
                            <span class="star">★</span> {{ number_format($game['rating'], 1) }}
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="card-title">{{ $game['name'] }}</div>
                    <div class="genre-pills">
                        @foreach(array_slice($game['genres'] ?? [], 0, 3) as $genre)
                            <span class="genre-pill">{{ $genre['name'] }}</span>
                        @endforeach
                    </div>
                    <hr class="divider-line">
                    <div class="card-meta">
                        <div class="meta-item">
                            <span class="meta-label">Released</span>
                            <span class="meta-value">{{ !empty($game['released']) ? \Carbon\Carbon::parse($game['released'])->format('Y') : '—' }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Rulesets</span>
                            <span class="meta-value" style="color:var(--accent2)">
                                {{ \App\Models\Ruleset::where('rawg_id', $game['id'])->where('is_public', true)->count() }}
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>

{{-- ── Search results (shown by JS, hidden initially) ────────────── --}}
<div class="results-section" id="search-results-section" style="display:none">
    <div class="results-header">
        <div>
            <h2 class="section-title" style="font-size:1.2rem;margin-bottom:.2rem">Search Results</h2>
            <p class="results-count">Found <span id="results-count-num">0</span> games</p>
        </div>
        <button class="btn btn-ghost btn-sm" id="clear-search-btn">Clear search</button>
    </div>
    <div class="games-grid" id="search-results-grid"></div>
</div>
@endsection
