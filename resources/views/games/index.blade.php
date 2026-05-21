@extends('layouts.app')

@section('content')

<div id="page-home" class="page active">
    <div class="hero">
        <h1>Search for a <em>Game.</em></h1>
    </div>
</div>
    <x-search-bar :query="$query ?? ''" />


    @isset($games)
        <div class="posts-grid">

            @foreach($games as $game)
                <a href="{{ route('games.show', $game['id']) }}" class="post-card" style="text-decoration:none;color:inherit">

                    <div class="game-image-wrapper">
                        <img
                            src="{{ $game['background_image'] ?? '' }}"
                            style="
                            width:100%;
                            border-radius:10px;
                            margin-bottom:10px;
                            @if(!empty($game['is_adult'])) filter: blur(10px); @endif
                            "
                        >
                    </div>

                    <h3>{{ $game['name'] }}</h3>

                    <p>Released: {{ $game['released'] ?? 'Unknown' }}</p>
                    <p>Rating: {{ $game['rating'] ?? 'N/A' }}</p>

                    <p>
                        Developers:
                        {{ implode(', ', $game['developers'] ?? []) ?: 'N/A' }}
                    </p>
                </a>
            @endforeach
        </div>
    @endisset

</div>
@endsection
