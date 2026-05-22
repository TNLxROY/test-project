@extends('layouts.app')

@section('content')

{{-- Hero Banner --}}
<div class="show-hero" style="background-image: url('{{ $game['background_image_additional'] ?? $game['background_image'] ?? '' }}')">
    <div class="show-hero-overlay"></div>
    <div class="show-hero-content">
        <a href="{{ route('games.index') }}" class="back-link">
            ← Back to results
        </a>
        <div class="show-hero-meta">
            @if(!empty($game['esrb_rating']['name']))
                <span class="esrb-badge">{{ $game['esrb_rating']['name'] }}</span>
            @endif
            @if(!empty($game['tba']))
                <span class="genre-pill">TBA</span>
            @endif
        </div>
        <h1 class="show-title">{{ $game['name'] }}</h1>
        @if(!empty($game['name_original']) && $game['name_original'] !== $game['name'])
            <p class="show-original-title">{{ $game['name_original'] }}</p>
        @endif
        <div class="show-hero-tags">
            @foreach($game['genres'] ?? [] as $genre)
                <span class="genre-pill">{{ $genre['name'] }}</span>
            @endforeach
        </div>
    </div>
</div>

<div class="show-layout">

    {{-- LEFT COLUMN --}}
    <div class="show-main">

        {{-- Rating breakdown --}}
        @if(!empty($game['ratings']))
        <div class="show-card">
            <h2 class="show-card-title">Ratings</h2>
            <div class="ratings-row">
                @foreach($game['ratings'] as $r)
                <div class="rating-bar-item">
                    <div class="rating-bar-label">
                        <span>{{ ucfirst($r['title']) }}</span>
                        <span class="rating-bar-count">{{ $r['count'] }}</span>
                    </div>
                    <div class="rating-bar-track">
                        <div class="rating-bar-fill rating-{{ $r['title'] }}" style="width: {{ $r['percent'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="rating-summary">
                <span class="rating-big">{{ $game['rating'] ?? 'N/A' }}</span>
                <span class="rating-sub">/ 5 &nbsp;·&nbsp; {{ number_format($game['ratings_count'] ?? 0) }} ratings</span>
                @if(!empty($game['metacritic']))
                    <span class="metacritic-badge">MC {{ $game['metacritic'] }}</span>
                @endif
            </div>
        </div>
        @endif

        {{-- Description --}}
        @if(!empty($game['description_raw']))
        <div class="show-card">
            <h2 class="show-card-title">About</h2>
            <div class="show-description" id="desc-text">
                {{ $game['description_raw'] }}
            </div>
            <button class="read-more-btn" onclick="
                const el = document.getElementById('desc-text');
                const btn = this;
                el.classList.toggle('expanded');
                btn.textContent = el.classList.contains('expanded') ? 'Show less' : 'Read more';
            ">Read more</button>
        </div>
        @endif

        {{-- Platforms with system requirements --}}
        @if(!empty($game['platforms']))
        <div class="show-card">
            <h2 class="show-card-title">Platforms</h2>
            <div class="platform-grid">
                @php
                    $platformIcons = [
                        'pc'               => ['icon' => 'ti-device-desktop',  'color' => '#90caf9'],
                        'playstation4'     => ['icon' => 'ti-playstation-x', 'color' => '#7b9cff'],
                        'playstation5'     => ['icon' => 'ti-playstation-x', 'color' => '#7b9cff'],
                        'playstation3'     => ['icon' => 'ti-playstation-x', 'color' => '#7b9cff'],
                        'playstation2'     => ['icon' => 'ti-playstation-x', 'color' => '#7b9cff'],
                        'playstation1'     => ['icon' => 'ti-playstation-x', 'color' => '#7b9cff'],
                        'ps-vita'          => ['icon' => 'ti-playstation-x', 'color' => '#7b9cff'],
                        'psp'              => ['icon' => 'ti-playstation-x', 'color' => '#7b9cff'],
                        'xbox-one'         => ['icon' => 'ti-brand-xbox',       'color' => '#81c784'],
                        'xbox360'          => ['icon' => 'ti-brand-xbox',       'color' => '#81c784'],
                        'xbox-series-x'    => ['icon' => 'ti-brand-xbox',       'color' => '#81c784'],
                        'nintendo-switch'  => ['icon' => 'ti-device-gamepad-2', 'color' => '#ef5350'],
                        'wii'              => ['icon' => 'ti-device-gamepad-2', 'color' => '#ef5350'],
                        'wii-u'            => ['icon' => 'ti-device-gamepad-2', 'color' => '#ef5350'],
                        'nintendo-3ds'     => ['icon' => 'ti-device-gamepad-2', 'color' => '#ef5350'],
                        'nintendo-ds'      => ['icon' => 'ti-device-gamepad-2', 'color' => '#ef5350'],
                        'ios'              => ['icon' => 'ti-brand-apple',      'color' => '#bdbdbd'],
                        'macos'            => ['icon' => 'ti-brand-apple',      'color' => '#bdbdbd'],
                        'android'          => ['icon' => 'ti-brand-android',    'color' => '#a5d6a7'],
                        'linux'            => ['icon' => 'ti-brand-ubuntu',     'color' => '#ff8a65'],
                        'web'              => ['icon' => 'ti-world',            'color' => '#4fc3f7'],
                        'atari'            => ['icon' => 'ti-device-gamepad',   'color' => '#888899'],
                        'sega-saturn'      => ['icon' => 'ti-device-gamepad',   'color' => '#888899'],
                        'dreamcast'        => ['icon' => 'ti-device-gamepad',   'color' => '#888899'],
                        'game-boy'         => ['icon' => 'ti-device-gamepad',   'color' => '#888899'],
                        'game-boy-advance' => ['icon' => 'ti-device-gamepad',   'color' => '#888899'],
                        'gamecube'         => ['icon' => 'ti-device-gamepad',   'color' => '#888899'],
                    ];

                    $defaultPlatform = ['icon' => 'ti-device-gamepad', 'color' => '#888899'];
                @endphp

                @foreach($game['platforms'] as $p)
                    @php
                        $slug    = $p['platform']['slug'] ?? '';
                        $pi      = $platformIcons[$slug] ?? $defaultPlatform;
                    @endphp
                    <div class="platform-item">
                        <div class="platform-item-header">
                            <i class="ti {{ $pi['icon'] }} platform-icon"
                            style="color: {{ $pi['color'] }}"
                            aria-hidden="true"></i>
                        <span class="platform-name">{{ $p['platform']['name'] }}</span>
                    </div>
                        @if(!empty($p['released_at']))
                            <span class="platform-date">{{ $p['released_at'] }}</span>
                        @endif
                            @if(!empty($p['requirements']['minimum']) || !empty($p['requirements']['recommended']))
                            <div class="sysreq">
                                @if(!empty($p['requirements']['minimum']))
                                    <p class="sysreq-label">Minimum</p>
                                    <p class="sysreq-text">{{ $p['requirements']['minimum'] }}</p>
                                @endif
                                @if(!empty($p['requirements']['recommended']))
                                    <p class="sysreq-label">Recommended</p>
                                    <p class="sysreq-text">{{ $p['requirements']['recommended'] }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Metacritic per platform --}}
        @if(!empty($game['metacritic_platforms']))
        <div class="show-card">
            <h2 class="show-card-title">Metacritic by Platform</h2>
            <div class="mc-platform-list">
                @foreach($game['metacritic_platforms'] as $mc)
                <a href="{{ $mc['url'] }}" target="_blank" class="mc-platform-row">
                    <span>{{ $mc['platform']['name'] }}</span>
                    <span class="mc-score mc-{{ $mc['metascore'] >= 75 ? 'good' : ($mc['metascore'] >= 50 ? 'mid' : 'bad') }}">
                        {{ $mc['metascore'] }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Tags --}}
        @if(!empty($game['tags']))
        <div class="show-card">
            <h2 class="show-card-title">Tags</h2>
            <div class="tag-cloud">
                @foreach($game['tags'] as $tag)
                    <span class="tag-pill">{{ $tag['name'] }}</span>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="show-sidebar">

        {{-- Cover image --}}
        @if(!empty($game['background_image']))
        <div class="show-card show-cover-card">
            <img src="{{ $game['background_image'] }}" alt="{{ $game['name'] }}" class="show-cover-img">
        </div>
        @endif

        {{-- Key facts --}}
        <div class="show-card">
            <h2 class="show-card-title">Details</h2>
            <dl class="detail-list">

                @if(!empty($game['released']))
                <div class="detail-row">
                    <dt>Released</dt>
                    <dd>{{ $game['released'] }}{{ !empty($game['tba']) ? ' (TBA)' : '' }}</dd>
                </div>
                @endif

                @if(!empty($game['playtime']))
                <div class="detail-row">
                    <dt>Avg. Playtime</dt>
                    <dd>{{ $game['playtime'] }} hrs</dd>
                </div>
                @endif

                @if(!empty($game['developers']))
                <div class="detail-row">
                    <dt>Developer{{ count($game['developers']) > 1 ? 's' : '' }}</dt>
                    <dd>{{ implode(', ', array_column($game['developers'], 'name')) }}</dd>
                </div>
                @endif

                @if(!empty($game['publishers']))
                <div class="detail-row">
                    <dt>Publisher{{ count($game['publishers']) > 1 ? 's' : '' }}</dt>
                    <dd>{{ implode(', ', array_column($game['publishers'], 'name')) }}</dd>
                </div>
                @endif

                @if(!empty($game['esrb_rating']['name']))
                <div class="detail-row">
                    <dt>ESRB Rating</dt>
                    <dd>{{ $game['esrb_rating']['name'] }}</dd>
                </div>
                @endif

                @if(!empty($game['website']))
                <div class="detail-row">
                    <dt>Website</dt>
                    <dd><a href="{{ $game['website'] }}" target="_blank" class="detail-link">Official site ↗</a></dd>
                </div>
                @endif

                @if(!empty($game['added']))
                <div class="detail-row">
                    <dt>Added by</dt>
                    <dd>{{ number_format($game['added']) }} users</dd>
                </div>
                @endif

                @if(!empty($game['achievements_count']))
                <div class="detail-row">
                    <dt>Achievements</dt>
                    <dd>{{ number_format($game['achievements_count']) }}</dd>
                </div>
                @endif

                @if(!empty($game['reddit_url']))
                <div class="detail-row">
                    <dt>Subreddit</dt>
                    <dd><a href="{{ $game['reddit_url'] }}" target="_blank" class="detail-link">{{ $game['reddit_name'] ?? 'View ↗' }}</a></dd>
                </div>
                @endif

                @if(isset($game['updated']))
                <div class="detail-row">
                    <dt>Last updated</dt>
                    <dd>{{ \Carbon\Carbon::parse($game['updated'])->format('M j, Y') }}</dd>
                </div>
                @endif

            </dl>
        </div>

        {{-- Added by status breakdown --}}
        @if(!empty($game['added_by_status']))
        <div class="show-card">
            <h2 class="show-card-title">Library Status</h2>
            <dl class="detail-list">
                @foreach($game['added_by_status'] as $status => $count)
                <div class="detail-row">
                    <dt>{{ ucfirst(str_replace('-', ' ', $status)) }}</dt>
                    <dd>{{ number_format($count) }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
        @endif

        {{-- Stores --}}
        @if(!empty($game['stores']))
        <div class="show-card">
            <h2 class="show-card-title">Available On</h2>
            <div class="store-list">
                @foreach($game['stores'] as $s)
                <a href="{{ $s['url'] ?? '#' }}" target="_blank" class="store-btn">
                    {{ $s['store']['name'] }} ↗
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Reactions --}}
        @if(!empty($game['reactions']))
        <div class="show-card">
            <h2 class="show-card-title">Reactions</h2>
            <div class="reaction-grid">
                @php
                    $reactionMap = [
                        '1'=>'😍','2'=>'🤩','3'=>'😮','4'=>'😁','5'=>'😕',
                        '6'=>'😴','7'=>'😤','8'=>'🤔','9'=>'😢','10'=>'💩',
                        '11'=>'😂','12'=>'😎','13'=>'👍','14'=>'💯','15'=>'🤯',
                        '16'=>'🙀','17'=>'🙏','18'=>'❤️','19'=>'🤮','20'=>'👎',
                        '21'=>'🎮'
                    ];
                @endphp
                @foreach($game['reactions'] as $key => $count)
                    @if($count > 0)
                    <div class="reaction-item">
                        <span class="reaction-emoji">{{ $reactionMap[$key] ?? '?' }}</span>
                        <span class="reaction-count">{{ $count }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
