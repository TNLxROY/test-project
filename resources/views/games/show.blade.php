@extends('layouts.app')

@section('content')

<div class="section">

    <h1 class="section-title">{{ $game['name'] }}</h1>

    <img src="{{ $game['image'] }}" style="width:100%;max-width:600px;border-radius:12px;margin:20px 0">

    <p style="color:var(--muted)">
        {!! $game['description_raw'] ?? 'No description available.' !!}
    </p>

    <p><strong>Rating:</strong> {{ $game['rating'] }}</p>
    <p><strong>Released:</strong> {{ $game['released'] }}</p>

</div>

@endsection
