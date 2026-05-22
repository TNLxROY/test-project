@extends('layouts.app')
@section('content')
<div id="page-home" class="page active">
    <div class="hero">
        <h1>Search for a <em>Game.</em></h1>
    </div>
</div>
    <x-search-bar :query="$query ?? ''" />

    @isset($games)
        <div class="results-section">
            <div class="results-header">
                <p class="results-count">
                    Showing <span>{{ count($games) }}</span> result{{ count($games) !== 1 ? 's' : '' }}
                    @if(!empty($query)) for <span>"{{ $query }}"</span>@endif
                </p>
            </div>

            <div class="games-grid">
                @foreach($games as $game)
                    <a href="{{ route('games.show', $game['id']) }}" class="game-card">

                        <div class="card-img" @if(!empty($game['is_adult'])) style="filter:blur(10px)"@endif>
                            <img
                                src="{{ $game['background_image'] ?? '' }}"
                                alt="{{ $game['name'] }}"
                                loading="lazy"
                            >
                            @if(!empty($game['rating']))
                                <div class="rating-badge">
                                    <span class="star">★</span> {{ $game['rating'] }}
                                </div>
                            @endif
                            @if(!empty($game['is_adult']))
                                <div class="adult-overlay">
                                    <span class="adult-badge">18+ Content</span>
                                </div>
                            @endif
                        </div>

                        <div class="card-body">
                            <div class="genre-pills">
                                @if(!empty($game['genres']))
                                    @foreach(array_slice($game['genres'], 0, 3) as $genre)
                                        <span class="genre-pill">{{ $genre['name'] }}</span>
                                    @endforeach
                                @else
                                    <span class="genre-pill genre-pill--undefined">Genre undefined.</span>
                                @endif
                            </div>

                            <h3 class="card-title">{{ $game['name'] }}</h3>

                            <hr class="divider-line">

                            <div class="card-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Released</span>
                                    <span class="meta-value">{{ $game['released'] ?? 'Unknown' }}</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Rating</span>
                                    <span class="meta-value">{{ $game['rating'] ?? 'N/A' }} / 5</span>
                                </div>
                                <div class="meta-item full">
                                    <span class="meta-label">Developer</span>
                                    <span class="meta-value">
                                        {{ implode(', ', $game['developers'] ?? []) ?: 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </a>
                @endforeach
            </div>
        </div>
    @endisset
</div>
@endsection
