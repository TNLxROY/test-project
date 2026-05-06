@extends('layouts.app')

@section('content')

<div class="section">

    <h1 class="section-title">Search Games</h1>

    <form action="{{ route('games.search') }}" method="GET">
        <input type="text" name="q" value="{{ $query ?? '' }}">
        <button type="submit">Search</button>
    </form>

    <hr class="divider">

    @isset($games)
        <div class="posts-grid">

            @foreach($games as $game)
                <a href="{{ route('games.show', $game['id']) }}" class="post-card" style="text-decoration:none;color:inherit">
                    <div class="game-image-wrapper">
                        <img src="{{ $game['background_image'] ?? '' }}" style="width:100%;border-radius:10px;margin-bottom:10px">
                    </div>

                    <h3>{{ $game['name'] }}</h3>

                    <p>Released: {{ $game['released'] ?? 'Unknown' }}</p>

                    <p>Rating: {{ $game['rating'] ?? 'N/A' }}</p>

                </a>
            @endforeach

        </div>
    @endisset

</div>
@endsection
