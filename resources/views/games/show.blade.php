@extends('layouts.app')

@section('content')

{{-- Hero Banner --}}
<div class="show-hero" style="background-image: url('{{ $game['background_image_additional'] ?? $game['background_image'] ?? '' }}')">
    <div class="show-hero-overlay"></div>
    <div class="show-hero-content">
        <a href="{{ route('games.index') }}" class="back-link">← Back to results</a>
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

        <div class="show-tabs">
            <button class="show-tab active" id="tab-info" onclick="switchShowTab('info')">
                <i class="ti ti-info-circle" aria-hidden="true"></i> Game Info
            </button>
            <button class="show-tab" id="tab-reviews" onclick="switchShowTab('reviews')">
                <i class="ti ti-message-circle" aria-hidden="true"></i> Reviews
                <span class="review-count-badge" id="review-count-badge">{{ count($reviews) }}</span>
            </button>
            @auth
                @if(!$userReview)
                    <button class="show-tab show-tab-accent" id="tab-write" onclick="switchShowTab('write')">
                        <i class="ti ti-pencil" aria-hidden="true"></i> Write a Review
                    </button>
                @else
                    <span class="reviewed-badge">
                        <i class="ti ti-circle-check" aria-hidden="true"></i> You reviewed this
                    </span>
                @endif
            @else
                <button class="show-tab" onclick="openModal('login')">
                    <i class="ti ti-pencil" aria-hidden="true"></i> Write a Review
                </button>
            @endauth
        </div>
    </div>
</div>

{{-- ── GAME INFO PANEL ── --}}
<div id="panel-info">
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

        {{-- Platforms --}}
        @if(!empty($game['platforms']))
        <div class="show-card">
            <h2 class="show-card-title">Platforms</h2>

            @php
                // ── Official brand SVG logos ──────────────────────────────────────────

                // PlayStation, Xbox, Switch — loaded from Simple Icons CDN and recolored via CSS filter
                // Filter values convert black SVGs to the target brand color
                $psLogo     = '<img src="https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/playstation.svg" width="32" height="32" style="filter: invert(14%) sepia(93%) saturate(2800%) hue-rotate(211deg) brightness(85%) contrast(110%)" alt="PlayStation">';
                $xboxLogo   = '<img src="https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/xbox.svg" width="32" height="32" style="filter: invert(33%) sepia(96%) saturate(500%) hue-rotate(82deg) brightness(85%) contrast(110%)" alt="Xbox">';
                $switchLogo = '<img src="https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/nintendoswitch.svg" width="32" height="32" style="filter: invert(10%) sepia(99%) saturate(7000%) hue-rotate(1deg) brightness(95%) contrast(110%)" alt="Nintendo Switch">';

                // PC / Windows
                $windowsLogo = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/></svg>';

                // Apple
                $appleLogo = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/></svg>';

                // Android
                $androidLogo = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M17.523 15.341A1.041 1.041 0 1 1 16.482 16.382 1.041 1.041 0 0 1 17.523 15.341M6.477 15.341A1.041 1.041 0 1 1 5.436 16.382 1.041 1.041 0 0 1 6.477 15.341M17.741 10.211L19.851 6.53A.408.408 0 0 0 19.143 6.12L17.011 9.839A11.645 11.645 0 0 0 12 8.678 11.645 11.645 0 0 0 6.989 9.839L4.857 6.12A.408.408 0 0 0 4.149 6.53L6.259 10.211A11.168 11.168 0 0 0 .818 17.818H23.182A11.168 11.168 0 0 0 17.741 10.211Z"/></svg>';

                // Linux / Tux
                $linuxLogo = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M12.503 0c-.155 0-.315.008-.48.024C10.64.154 9.664.55 9.003 1.398c-.663.851-.78 2.038-.507 3.331a9.43 9.43 0 0 0 .486 1.524c-.636.86-1.344 2.074-1.74 3.133-.482 1.303-.63 2.448-.285 3.21.275.607.805 1.048 1.443 1.312-.154.474-.271.949-.32 1.387-.124 1.125.046 2.016.635 2.615.432.438.93.612 1.418.666.453.05.898-.03 1.32-.135.148.47.38.912.661 1.31a4.79 4.79 0 0 0 1.17 1.145c1.009.673 2.285.992 3.428.68a3.35 3.35 0 0 0 2.156-1.767c.368-.768.44-1.614.338-2.373a4.948 4.948 0 0 0-.2-.853c.538-.246 1.015-.641 1.243-1.201.407-1.003.022-2.252-.65-3.36a9.68 9.68 0 0 0-1.28-1.616c.253-.72.437-1.527.537-2.257.127-.922.124-1.817-.075-2.49-.39-1.33-1.467-1.757-2.57-1.654a3.7 3.7 0 0 0-.648.118C15.346.336 14.18 0 12.503 0zm1.018 1.563c.903.012 1.727.316 2.166.942.416.594.527 1.51.34 2.514a8.25 8.25 0 0 1-.563 1.83 3.987 3.987 0 0 0-.595-.523c-.546-.394-1.193-.665-1.876-.814a8.68 8.68 0 0 0-.576-1.7c-.358-.81-.82-1.51-1.378-1.916.356-.213.838-.347 1.483-.347zm-2.97.38c.474.266.944.877 1.28 1.698a8.01 8.01 0 0 1 .49 1.571 5.117 5.117 0 0 0-1.01.493c-.466.317-.85.737-1.117 1.238a8.534 8.534 0 0 1-.35-1.43c-.198-1.058-.105-1.966.24-2.432.186-.25.283-.308.467-.334zM12 6.07c.272 0 .533.019.786.055.64.09 1.213.324 1.67.663.457.339.783.788.906 1.314.093.395.071.835-.063 1.295a4.95 4.95 0 0 1-.5.062C14.04 9.5 13.22 9.58 12 9.58c-1.22 0-2.04-.08-2.8-.122a4.95 4.95 0 0 1-.5-.062c-.134-.46-.156-.9-.063-1.295.123-.526.449-.975.906-1.314.457-.34 1.03-.574 1.67-.663A5.49 5.49 0 0 1 12 6.07zm-3.68 4.005c.912.07 2.052.133 3.68.133s2.768-.062 3.68-.133c.068.188.112.382.12.578.012.31-.065.638-.27.952C14.79 12.27 13.42 12.7 12 12.7c-1.42 0-2.79-.43-3.53-1.095-.205-.314-.282-.642-.27-.952.008-.196.052-.39.12-.578zM7.37 12.32c.979.784 2.36 1.258 3.795 1.332l-.009.033a1.7 1.7 0 0 1-.048.143c-.205.52-.682.886-1.255 1.148-.573.262-1.249.416-1.881.516-.632.1-1.21.143-1.62.119-.41-.024-.65-.106-.755-.215-.25-.254-.366-.797-.266-1.709.065-.6.24-1.26.468-1.877.162.122.37.25.57.34a2.99 2.99 0 0 0 1.001.17zm9.26 0a2.99 2.99 0 0 0 1-.17c.202-.09.41-.218.571-.34.228.617.403 1.277.468 1.877.1.912-.016 1.455-.266 1.709-.105.109-.345.191-.755.215-.41.024-.988-.019-1.62-.119-.632-.1-1.308-.254-1.881-.516-.573-.262-1.05-.628-1.255-1.148a1.7 1.7 0 0 1-.048-.143l-.009-.033c1.435-.074 2.816-.548 3.795-1.332zm-9.077 4.06c.318.007.668-.02 1.034-.073.737-.108 1.51-.285 2.167-.587.656-.302 1.213-.74 1.474-1.405.037-.094.065-.193.086-.295.29.093.593.16.911.195v.021c.001.786-.227 1.535-.67 2.127-.58.779-1.51 1.298-2.593 1.479-.15.025-.3.04-.45.04a3.353 3.353 0 0 1-1.93-.634 4.228 4.228 0 0 1-.9-.97c.32.065.605.098.87.102zm9.127 0c.266-.004.55-.037.871-.102a4.228 4.228 0 0 1-.9.97 3.353 3.353 0 0 1-1.93.634c-.15 0-.3-.015-.45-.04-1.083-.181-2.013-.7-2.593-1.479-.443-.592-.671-1.34-.67-2.127v-.02c.318-.036.621-.103.911-.196.021.102.05.201.086.295.261.665.818 1.103 1.474 1.405.657.302 1.43.48 2.167.587.366.053.716.08 1.034.073z"/></svg>';

                // Web / Globe
                $webLogo = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zM4.05 11h3.01c.072-1.54.323-2.994.719-4.234A8.025 8.025 0 0 0 4.05 11zm0 2a8.025 8.025 0 0 0 3.729 3.234C7.383 14.994 7.132 13.54 7.06 13H4.05zm15.9 0h-3.01c-.072 1.54-.323 2.994-.719 4.234A8.025 8.025 0 0 0 19.95 13zm0-2a8.025 8.025 0 0 0-3.729-3.234c.396 1.24.647 2.694.719 4.234h3.01zM9.062 11h5.876c-.076-1.562-.338-2.974-.721-4.068-.361-1.03-.78-1.682-1.217-1.682s-.856.652-1.217 1.682C11.4 8.026 11.138 9.438 11.062 11H9.062zm0 2c.076 1.562.338 2.974.721 4.068.361 1.03.78 1.682 1.217 1.682s.856-.652 1.217-1.682c.383-1.094.645-2.506.721-4.068H9.062z"/></svg>';

                // Generic gamepad fallback
                $defaultLogo = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M7.97 4C5.8 4 4 5.8 4 7.97v8.06C4 18.2 5.8 20 7.97 20h8.06C18.2 20 20 18.2 20 16.03V7.97C20 5.8 18.2 4 16.03 4H7.97zM9 8h2v2h2V8h2v2h-2v2H9v-2H7V8h2zm7 4a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm-2 2a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>';

                // ── Map: slug → [logo, color, label] ─────────────────────────────────
                $platforms = [
                    // PlayStation family
                    'playstation1'  => ['logo' => $psLogo,      'color' => '#003087'],
                    'playstation2'  => ['logo' => $psLogo,      'color' => '#003087'],
                    'playstation3'  => ['logo' => $psLogo,      'color' => '#003087'],
                    'playstation4'  => ['logo' => $psLogo,      'color' => '#003087'],
                    'playstation5'  => ['logo' => $psLogo,      'color' => '#003087'],
                    'ps-vita'       => ['logo' => $psLogo,      'color' => '#003087'],
                    'psp'           => ['logo' => $psLogo,      'color' => '#003087'],

                    // Xbox family
                    'xbox-series-x' => ['logo' => $xboxLogo,    'color' => '#107C10'],
                    'xbox-series-s' => ['logo' => $xboxLogo,    'color' => '#107C10'],
                    'xbox-one'      => ['logo' => $xboxLogo,    'color' => '#107C10'],
                    'xbox360'       => ['logo' => $xboxLogo,    'color' => '#107C10'],
                    'xbox-old'      => ['logo' => $xboxLogo,    'color' => '#107C10'],

                    // Nintendo
                    'nintendo-switch'     => ['logo' => $switchLogo,  'color' => '#E4000F'],
                    'nintendo-switch-2'   => ['logo' => $switchLogo,  'color' => '#E4000F'],
                    'wii-u'               => ['logo' => $defaultLogo,  'color' => '#009AC7'],
                    'wii'                 => ['logo' => $defaultLogo,  'color' => '#009AC7'],
                    'nintendo-3ds'        => ['logo' => $defaultLogo,  'color' => '#CC0000'],
                    'nintendo-ds'         => ['logo' => $defaultLogo,  'color' => '#CC0000'],
                    'game-boy-advance'    => ['logo' => $defaultLogo,  'color' => '#8B60ED'],
                    'game-boy-color'      => ['logo' => $defaultLogo,  'color' => '#B0D94A'],
                    'game-boy'            => ['logo' => $defaultLogo,  'color' => '#888899'],
                    'nintendo-64'         => ['logo' => $defaultLogo,  'color' => '#CC0000'],
                    'snes'                => ['logo' => $defaultLogo,  'color' => '#CC0000'],
                    'nes'                 => ['logo' => $defaultLogo,  'color' => '#CC0000'],
                    'gamecube'            => ['logo' => $defaultLogo,  'color' => '#6A0DAD'],
                    'nintendo-ds-lite'    => ['logo' => $defaultLogo,  'color' => '#CC0000'],

                    // PC & Desktop
                    'pc'            => ['logo' => $windowsLogo,  'color' => '#0078D4'],
                    'macos'         => ['logo' => $appleLogo,     'color' => '#555555'],
                    'ios'           => ['logo' => $appleLogo,     'color' => '#555555'],
                    'android'       => ['logo' => $androidLogo,   'color' => '#3DDC84'],
                    'linux'         => ['logo' => $linuxLogo,     'color' => '#F5A623'],
                    'web'           => ['logo' => $webLogo,       'color' => '#4fc3f7'],

                    // Legacy / Sega
                    'sega-genesis'  => ['logo' => $defaultLogo,  'color' => '#1a5276'],
                    'sega-saturn'   => ['logo' => $defaultLogo,  'color' => '#1a5276'],
                    'dreamcast'     => ['logo' => $defaultLogo,  'color' => '#FF6600'],
                    'sega-cd'       => ['logo' => $defaultLogo,  'color' => '#1a5276'],
                    'game-gear'     => ['logo' => $defaultLogo,  'color' => '#1a5276'],

                    // Atari / other
                    'atari-2600'    => ['logo' => $defaultLogo,  'color' => '#888899'],
                    'atari-5200'    => ['logo' => $defaultLogo,  'color' => '#888899'],
                    'atari-7800'    => ['logo' => $defaultLogo,  'color' => '#888899'],
                    'atari-lynx'    => ['logo' => $defaultLogo,  'color' => '#888899'],
                    'atari-jaguar'  => ['logo' => $defaultLogo,  'color' => '#888899'],
                    '3do'           => ['logo' => $defaultLogo,  'color' => '#888899'],
                    'neogeo'        => ['logo' => $defaultLogo,  'color' => '#C0A000'],
                ];

                $defaultPlatform = ['logo' => $defaultLogo, 'color' => '#888899'];
            @endphp

            <div class="platform-grid">
                @foreach($game['platforms'] as $p)
                    @php
                        $slug        = $p['platform']['slug'] ?? null;
                        $name        = $p['platform']['name'] ?? 'Unknown Platform';
                        $releaseDate = $p['released_at'] ?? null;
                        $meta        = $platforms[$slug] ?? $defaultPlatform;
                    @endphp

                    @php $hasSysreq = !empty($p['requirements']['minimum']) || !empty($p['requirements']['recommended']); @endphp
                    <div class="platform-item {{ $hasSysreq ? 'platform-item--expandable' : '' }}">
                        <div class="platform-item-header" @if($hasSysreq) onclick="toggleSysreq(this)" style="cursor:pointer" @endif>
                            <div class="platform-logo-wrap" style="color: {{ $meta['color'] }}">
                                {!! $meta['logo'] !!}
                            </div>
                            <div class="platform-info">
                                <span class="platform-name">{{ $name }}</span>
                                @if($releaseDate)
                                    <span class="platform-date">{{ $releaseDate }}</span>
                                @else
                                    <span class="platform-date platform-date--unknown">Date unknown</span>
                                @endif
                            </div>
                            @if($hasSysreq)
                                <button class="sysreq-toggle" aria-label="Toggle system requirements" onclick="event.stopPropagation(); toggleSysreq(this.closest('.platform-item-header'))">
                                    <i class="ti ti-chevron-down"></i>
                                </button>
                            @endif
                        </div>

                        @if($hasSysreq)
                            <div class="sysreq sysreq--collapsed">
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

        {{-- Cover image — IGDB first, fallback to RAWG --}}
        @php
            $coverUrl = $igdbCoverUrl ?? $game['background_image'] ?? null;
        @endphp
        @if($coverUrl)
        <div class="show-card show-cover-card">
            <img src="{{ $coverUrl }}" alt="{{ $game['name'] }}" class="show-cover-img">
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

        {{-- Game series --}}
        @if(!empty($gameSeries))
        <div class="show-card">
            <h2 class="show-card-title">Game Series</h2>
            <div class="store-list">
                @foreach($gameSeries as $s)
                <a href="{{ route('games.show', $s['id']) }}" class="store-btn">
                    <i class="ti ti-device-gamepad-2" aria-hidden="true"></i>
                    {{ $s['name'] }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
</div>

{{-- ── REVIEWS PANEL ── --}}
<div id="panel-reviews" style="display:none">
    <div class="reviews-layout">
        @if(count($reviews) === 0)
            <div class="reviews-empty">
                <i class="ti ti-message-off reviews-empty-icon" aria-hidden="true"></i>
                <h3>No reviews yet</h3>
                <p>Be the first to share your thoughts on {{ $game['name'] }}.</p>
            </div>
        @else
            @foreach($reviews as $review)
            <div class="review-card" id="review-{{ $review->id }}">
                <div class="review-header">
                    <div class="review-avatar">
                        {{ strtoupper(substr($review->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="review-author">{{ $review->user->name }}</div>
                        <div class="review-date">{{ $review->created_at->format('M j, Y') }}</div>
                    </div>
                    @auth
                        @if(Auth::id() === $review->user_id)
                        <button class="review-delete-btn"
                            onclick="deleteReview({{ $review->id }}, {{ $game['id'] }})">
                            <i class="ti ti-trash" aria-hidden="true"></i>
                        </button>
                        @endif
                    @endauth
                </div>
                <p class="review-body">{{ $review->body }}</p>
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- ── WRITE REVIEW PANEL ── --}}
{{--
    DROP-IN REPLACEMENT for the old #panel-write div.

    Features:
    - Mode A (Simple): 1–10 star rating + optional description
    - Mode B (Detailed): per-category ratings with auto-average OR manual overall score
    - Animated transitions between modes
    - submitReview() sends { rating, body, categories? } to your existing route
      ↳ Make sure your reviews table/model accepts: rating (int), body (text, nullable),
        categories (json, nullable), is_detailed (bool)
--}}

<div id="panel-write" style="display:none">
    <div class="reviews-layout">
        <div class="show-card write-review-card wr-card">

            {{-- ── Header with mode toggle ── --}}
            <div class="wr-header">
                <div>
                    <h2 class="show-card-title" style="margin:0">Your Review</h2>
                    <p class="write-review-sub" style="margin:.25rem 0 0">
                        Reviewing <strong>{{ $game['name'] }}</strong>
                    </p>
                </div>
                <button class="wr-mode-toggle" id="wr-mode-toggle" onclick="wrToggleMode()" title="Switch review type">
                    <i class="ti ti-list-details" aria-hidden="true" id="wr-toggle-icon"></i>
                    <span id="wr-toggle-label">Detailed review</span>
                </button>
            </div>

            {{-- ── MODE A: Simple ── --}}
            <div id="wr-simple" class="wr-panel">

                <div class="wr-section-label">Overall rating</div>
                <div class="wr-star-row" id="wr-stars" role="group" aria-label="Rating 1 to 10">
                    @for($i = 1; $i <= 10; $i++)
                        <button
                            class="wr-star"
                            data-val="{{ $i }}"
                            onclick="wrSetSimpleRating({{ $i }})"
                            onmouseenter="wrHoverRating({{ $i }})"
                            onmouseleave="wrHoverRating(0)"
                            aria-label="{{ $i }} out of 10"
                            type="button">
                            <i class="ti ti-star" aria-hidden="true"></i>
                        </button>
                    @endfor
                    <span class="wr-rating-display" id="wr-simple-score">—</span>
                </div>
                <p class="wr-rating-label" id="wr-simple-label">&nbsp;</p>

                <div class="wr-section-label" style="margin-top:1.25rem">
                    Description <span class="wr-optional">(optional)</span>
                </div>
                <textarea
                    id="wr-simple-body"
                    class="review-textarea wr-textarea"
                    placeholder="Share your thoughts… (max 2000 characters)"
                    maxlength="2000"
                    oninput="wrUpdateCharCount('wr-simple-body','wr-simple-chars')"></textarea>
                <div class="review-char-count">
                    <span id="wr-simple-chars">0</span> / 2000
                </div>
            </div>

            {{-- ── MODE B: Detailed ── --}}
            <div id="wr-detailed" class="wr-panel" style="display:none">

                <div class="wr-categories" id="wr-categories">
                    {{-- Category rows rendered by JS so they're easy to add/remove --}}
                </div>

                <button class="wr-add-category" onclick="wrAddCategory()" type="button">
                    <i class="ti ti-plus" aria-hidden="true"></i> Add category
                </button>

                <div class="wr-divider"></div>

                <div class="wr-overall-row">
                    <div class="wr-overall-left">
                        <div class="wr-section-label" style="margin:0">Overall score</div>
                        <div class="wr-overall-sub" id="wr-avg-hint">
                            Average of your categories
                        </div>
                    </div>
                    <div class="wr-overall-right">
                        <label class="wr-auto-toggle">
                            <input type="checkbox" id="wr-auto-avg" checked onchange="wrToggleAutoAvg()">
                            <span class="wr-auto-toggle-track"></span>
                            Auto
                        </label>
                        <span class="wr-rating-display wr-overall-score" id="wr-detailed-score">—</span>
                    </div>
                </div>

                {{-- Manual overall rating (hidden when auto is on) --}}
                <div id="wr-manual-overall" style="display:none; margin-top:.75rem">
                    <div class="wr-star-row" id="wr-overall-stars" role="group" aria-label="Overall rating 1 to 10">
                        @for($i = 1; $i <= 10; $i++)
                            <button
                                class="wr-star"
                                data-val="{{ $i }}"
                                onclick="wrSetOverallRating({{ $i }})"
                                onmouseenter="wrHoverOverall({{ $i }})"
                                onmouseleave="wrHoverOverall(0)"
                                aria-label="{{ $i }} out of 10"
                                type="button">
                                <i class="ti ti-star" aria-hidden="true"></i>
                            </button>
                        @endfor
                    </div>
                </div>

                <div class="wr-section-label" style="margin-top:1.25rem">
                    Description <span class="wr-optional">(optional)</span>
                </div>
                <textarea
                    id="wr-detailed-body"
                    class="review-textarea wr-textarea"
                    placeholder="Share your overall thoughts… (max 2000 characters)"
                    maxlength="2000"
                    oninput="wrUpdateCharCount('wr-detailed-body','wr-detailed-chars')"></textarea>
                <div class="review-char-count">
                    <span id="wr-detailed-chars">0</span> / 2000
                </div>
            </div>

            {{-- ── Submit ── --}}
            <div class="wr-footer">
                <button class="btn btn-primary wr-submit" id="wr-submit"
                    onclick="wrSubmit({{ $game['id'] }}, '{{ addslashes($game['name']) }}')">
                    Post Review
                </button>
                <div class="auth-msg" id="review-msg"></div>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════
     STYLES  (add to your main CSS file instead if preferred)
════════════════════════════════════════════════════════════ --}}
<style>
/* ── Card layout ── */
.wr-card { padding: 1.5rem 1.75rem; }

.wr-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.wr-mode-toggle {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .9rem;
    border: 1px solid var(--color-border, rgba(255,255,255,.18));
    border-radius: 2rem;
    background: transparent;
    color: var(--color-text-secondary, #aaa);
    font-size: .8rem;
    cursor: pointer;
    transition: background .2s, color .2s, border-color .2s;
    white-space: nowrap;
}
.wr-mode-toggle:hover {
    background: rgba(255,255,255,.06);
    color: var(--color-text-primary, #fff);
    border-color: rgba(255,255,255,.35);
}
.wr-mode-toggle i { font-size: 1rem; }

/* ── Panels ── */
.wr-panel { animation: wrFadeIn .22s ease; }
@keyframes wrFadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

.wr-section-label {
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--color-text-secondary, #888);
    margin-bottom: .6rem;
}
.wr-optional { font-weight: 400; text-transform: none; letter-spacing: 0; opacity: .7; }

/* ── Star row ── */
.wr-star-row {
    display: flex;
    align-items: center;
    gap: .15rem;
    flex-wrap: wrap;
}

.wr-star {
    background: none;
    border: none;
    padding: .2rem;
    cursor: pointer;
    color: var(--color-text-tertiary, #555);
    font-size: 1.45rem;
    line-height: 1;
    transition: color .12s, transform .1s;
}
.wr-star:hover, .wr-star.hovered, .wr-star.active {
    color: #f5a623;
    transform: scale(1.15);
}
.wr-star.active i::before { content: "\ea78"; } /* ti-star-filled */

.wr-rating-display {
    min-width: 2.5rem;
    margin-left: .6rem;
    font-size: 1.6rem;
    font-weight: 700;
    color: #f5a623;
    line-height: 1;
}
.wr-overall-score { font-size: 1.3rem; }

.wr-rating-label {
    font-size: .85rem;
    color: var(--color-text-secondary, #aaa);
    margin: .3rem 0 0;
    min-height: 1.2em;
    transition: opacity .2s;
}

/* ── Textarea ── */
.wr-textarea {
    width: 100%;
    min-height: 90px;
    resize: vertical;
    box-sizing: border-box;
}

/* ── Categories ── */
.wr-category-row {
    display: grid;
    grid-template-columns: 1fr auto auto;
    align-items: center;
    gap: .6rem;
    margin-bottom: .65rem;
    animation: wrFadeIn .18s ease;
}
.wr-category-name {
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--color-border, rgba(255,255,255,.15));
    color: var(--color-text-primary, #fff);
    font-size: .9rem;
    padding: .3rem .1rem;
    outline: none;
    width: 100%;
    transition: border-color .2s;
}
.wr-category-name:focus { border-bottom-color: #f5a623; }
.wr-category-name::placeholder { color: var(--color-text-tertiary, #555); }

.wr-cat-stars {
    display: flex;
    gap: .05rem;
}
.wr-cat-star {
    background: none;
    border: none;
    padding: .1rem;
    cursor: pointer;
    color: var(--color-text-tertiary, #555);
    font-size: 1rem;
    transition: color .1s, transform .08s;
}
.wr-cat-star:hover, .wr-cat-star.hovered, .wr-cat-star.active {
    color: #f5a623;
    transform: scale(1.15);
}

.wr-cat-score {
    min-width: 1.6rem;
    font-size: .95rem;
    font-weight: 600;
    color: #f5a623;
    text-align: right;
}
.wr-cat-remove {
    background: none;
    border: none;
    color: var(--color-text-tertiary, #555);
    cursor: pointer;
    font-size: 1rem;
    padding: .2rem;
    border-radius: 4px;
    transition: color .15s;
}
.wr-cat-remove:hover { color: #e24b4a; }

.wr-add-category {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: none;
    border: 1px dashed var(--color-border, rgba(255,255,255,.2));
    border-radius: 6px;
    color: var(--color-text-secondary, #888);
    font-size: .8rem;
    padding: .4rem .8rem;
    cursor: pointer;
    margin-top: .25rem;
    transition: border-color .2s, color .2s;
}
.wr-add-category:hover {
    border-color: rgba(255,255,255,.45);
    color: var(--color-text-primary, #fff);
}

/* ── Divider ── */
.wr-divider {
    border: none;
    border-top: 1px solid var(--color-border, rgba(255,255,255,.1));
    margin: 1.25rem 0;
}

/* ── Overall row ── */
.wr-overall-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.wr-overall-sub {
    font-size: .78rem;
    color: var(--color-text-secondary, #888);
    margin-top: .2rem;
}
.wr-overall-right {
    display: flex;
    align-items: center;
    gap: .75rem;
}

/* ── Auto-avg toggle ── */
.wr-auto-toggle {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-size: .78rem;
    color: var(--color-text-secondary, #888);
    cursor: pointer;
    user-select: none;
}
.wr-auto-toggle input { display: none; }
.wr-auto-toggle-track {
    width: 34px;
    height: 18px;
    border-radius: 99px;
    background: var(--color-border, rgba(255,255,255,.18));
    position: relative;
    transition: background .2s;
    flex-shrink: 0;
}
.wr-auto-toggle-track::after {
    content: '';
    position: absolute;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--color-text-secondary, #aaa);
    top: 3px; left: 3px;
    transition: transform .2s, background .2s;
}
.wr-auto-toggle input:checked + .wr-auto-toggle-track {
    background: #f5a623;
}
.wr-auto-toggle input:checked + .wr-auto-toggle-track::after {
    transform: translateX(16px);
    background: #fff;
}

/* ── Footer ── */
.wr-footer {
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.wr-submit { padding: .55rem 1.6rem; }
</style>


{{-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════ --}}
<script>
(function () {

    /* ── State ── */
    const WR = {
        mode: 'simple',          // 'simple' | 'detailed'
        simpleRating: 0,
        overallRating: 0,        // manual overall (detailed mode)
        autoAvg: true,
        categories: [],          // [{ id, name, rating }]
        nextCatId: 1,
    };

    const RATING_LABELS = ['','Terrible','Bad','Poor','Mediocre','Average','Decent','Good','Great','Excellent','Masterpiece'];
    const DEFAULT_CATS  = ['Story','Gameplay','Graphics','Soundtrack','Characters'];

    /* ─────────────────────────────── Init ── */
    window.addEventListener('DOMContentLoaded', () => {
        DEFAULT_CATS.forEach(name => addCategory(name, 0));
        renderCategories();
        updateDetailedScore();
    });

    /* ─────────────────────────────── Mode toggle ── */
    window.wrToggleMode = function () {
        WR.mode = WR.mode === 'simple' ? 'detailed' : 'simple';
        document.getElementById('wr-simple').style.display   = WR.mode === 'simple'   ? '' : 'none';
        document.getElementById('wr-detailed').style.display = WR.mode === 'detailed' ? '' : 'none';

        const icon  = document.getElementById('wr-toggle-icon');
        const label = document.getElementById('wr-toggle-label');
        if (WR.mode === 'detailed') {
            icon.className  = 'ti ti-star';
            label.textContent = 'Simple review';
        } else {
            icon.className  = 'ti ti-list-details';
            label.textContent = 'Detailed review';
        }
    };

    /* ─────────────────────────────── Simple rating ── */
    window.wrSetSimpleRating = function (val) {
        WR.simpleRating = val;
        paintStars('wr-stars', val, false);
        document.getElementById('wr-simple-score').textContent = val;
        document.getElementById('wr-simple-label').textContent = RATING_LABELS[val] || '';
    };

    window.wrHoverRating = function (val) {
        paintStars('wr-stars', val || WR.simpleRating, val > 0);
    };

    /* ─────────────────────────────── Category helpers ── */
    function addCategory(name, rating) {
        const id = WR.nextCatId++;
        WR.categories.push({ id, name: name || '', rating: rating || 0 });
        return id;
    }

    window.wrAddCategory = function () {
        addCategory('', 0);
        renderCategories();
        updateDetailedScore();
        // focus new name input
        const inputs = document.querySelectorAll('.wr-category-name');
        if (inputs.length) inputs[inputs.length - 1].focus();
    };

    function removeCategory(id) {
        WR.categories = WR.categories.filter(c => c.id !== id);
        renderCategories();
        updateDetailedScore();
    }

    function setCatRating(id, val) {
        const cat = WR.categories.find(c => c.id === id);
        if (cat) cat.rating = val;
        renderCategories();
        updateDetailedScore();
    }

    /* ─────────────────────────────── Render categories ── */
    function renderCategories() {
        const container = document.getElementById('wr-categories');
        container.innerHTML = '';

        WR.categories.forEach(cat => {
            const row = document.createElement('div');
            row.className = 'wr-category-row';
            row.dataset.id = cat.id;

            // Name input
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'wr-category-name';
            inp.placeholder = 'Category name…';
            inp.value = cat.name;
            inp.maxLength = 40;
            inp.addEventListener('input', e => {
                const c = WR.categories.find(c => c.id === cat.id);
                if (c) c.name = e.target.value;
            });

            // Stars
            const starsWrap = document.createElement('div');
            starsWrap.className = 'wr-cat-stars';
            for (let i = 1; i <= 10; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'wr-cat-star' + (i <= cat.rating ? ' active' : '');
                btn.dataset.val = i;
                btn.setAttribute('aria-label', `${i} out of 10`);
                btn.innerHTML = '<i class="ti ti-star" aria-hidden="true"></i>';
                btn.addEventListener('click', () => setCatRating(cat.id, i));
                btn.addEventListener('mouseenter', () => paintCatStars(starsWrap, i, true));
                btn.addEventListener('mouseleave', () => paintCatStars(starsWrap, cat.rating, false));
                starsWrap.appendChild(btn);
            }

            // Score label
            const score = document.createElement('span');
            score.className = 'wr-cat-score';
            score.textContent = cat.rating || '—';

            // Remove button
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'wr-cat-remove';
            rm.setAttribute('aria-label', 'Remove category');
            rm.innerHTML = '<i class="ti ti-x" aria-hidden="true"></i>';
            rm.addEventListener('click', () => removeCategory(cat.id));

            row.appendChild(inp);
            row.appendChild(starsWrap);
            row.appendChild(score);
            row.appendChild(rm);
            container.appendChild(row);
        });
    }

    function paintCatStars(wrap, val, isHover) {
        wrap.querySelectorAll('.wr-cat-star').forEach(btn => {
            const v = parseInt(btn.dataset.val);
            btn.classList.toggle('active',  !isHover && v <= val);
            btn.classList.toggle('hovered',  isHover && v <= val);
        });
    }

    /* ─────────────────────────────── Detailed overall ── */
    function updateDetailedScore() {
        const el = document.getElementById('wr-detailed-score');
        const hint = document.getElementById('wr-avg-hint');

        if (WR.autoAvg) {
            const rated = WR.categories.filter(c => c.rating > 0);
            if (!rated.length) {
                el.textContent = '—';
                hint.textContent = 'Average of your categories';
                return;
            }
            const avg = rated.reduce((s, c) => s + c.rating, 0) / rated.length;
            const rounded = Math.round(avg * 10) / 10;
            el.textContent = Number.isInteger(rounded) ? rounded : rounded.toFixed(1);
            hint.textContent = `Average of ${rated.length} categor${rated.length === 1 ? 'y' : 'ies'}`;
        } else {
            el.textContent = WR.overallRating || '—';
            hint.textContent = 'Manual overall score';
        }
    }

    window.wrToggleAutoAvg = function () {
        WR.autoAvg = document.getElementById('wr-auto-avg').checked;
        document.getElementById('wr-manual-overall').style.display = WR.autoAvg ? 'none' : '';
        if (WR.autoAvg) WR.overallRating = 0;
        updateDetailedScore();
    };

    window.wrSetOverallRating = function (val) {
        WR.overallRating = val;
        paintStars('wr-overall-stars', val, false);
        updateDetailedScore();
    };

    window.wrHoverOverall = function (val) {
        paintStars('wr-overall-stars', val || WR.overallRating, val > 0);
    };

    /* ─────────────────────────────── Generic star painter ── */
    function paintStars(containerId, val, isHover) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.querySelectorAll('.wr-star').forEach(btn => {
            const v = parseInt(btn.dataset.val);
            btn.classList.toggle('active',   !isHover && v <= val);
            btn.classList.toggle('hovered',   isHover && v <= val);
        });
    }

    /* ─────────────────────────────── Char count ── */
    window.wrUpdateCharCount = function (textareaId, counterId) {
        const ta = document.getElementById(textareaId);
        const ct = document.getElementById(counterId);
        if (ta && ct) ct.textContent = ta.value.length;
    };

    /* ─────────────────────────────── Submit ── */
    window.wrSubmit = function (gameId, gameName) {
        const msg = document.getElementById('review-msg');
        msg.textContent = '';

        let rating, body, categories = null;

        if (WR.mode === 'simple') {
            if (!WR.simpleRating) {
                showMsg('Please select a rating before posting.', true);
                return;
            }
            rating = WR.simpleRating;
            body   = document.getElementById('wr-simple-body').value.trim();

        } else {
            // Detailed mode
            const rated = WR.categories.filter(c => c.rating > 0);

            if (WR.autoAvg) {
                if (!rated.length) {
                    showMsg('Please rate at least one category.', true);
                    return;
                }
                const avg = rated.reduce((s, c) => s + c.rating, 0) / rated.length;
                rating = Math.round(avg);
            } else {
                if (!WR.overallRating) {
                    showMsg('Please set an overall score.', true);
                    return;
                }
                rating = WR.overallRating;
            }

            body       = document.getElementById('wr-detailed-body').value.trim();
            categories = WR.categories
                .filter(c => c.name.trim() && c.rating > 0)
                .map(c => ({ name: c.name.trim(), rating: c.rating }));
        }

        if (body && body.length < 10) {
            showMsg('Description must be at least 10 characters (or leave it empty).', true);
            return;
        }

        const btn = document.getElementById('wr-submit');
        btn.disabled = true;
        btn.textContent = 'Posting…';

        fetch(`/games/${gameId}/reviews`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                rating,
                body: body || null,
                categories: categories,
                is_detailed: WR.mode === 'detailed',
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success || data.id) {
                location.reload();
            } else {
                showMsg(data.message || 'Something went wrong. Try again.', true);
                btn.disabled = false;
                btn.textContent = 'Post Review';
            }
        })
        .catch(() => {
            showMsg('Network error. Please try again.', true);
            btn.disabled = false;
            btn.textContent = 'Post Review';
        });
    };

    function showMsg(text, isError) {
        const el = document.getElementById('review-msg');
        el.textContent = text;
        el.style.color = isError ? 'var(--color-danger, #e24b4a)' : 'var(--color-success, #1d9e75)';
    }

})();
</script>

<script>
function toggleSysreq(header) {
    const item   = header.closest('.platform-item');
    const sysreq = item.querySelector('.sysreq');
    const btn    = item.querySelector('.sysreq-toggle');
    if (!sysreq) return;
    const isOpen = !sysreq.classList.contains('sysreq--collapsed');
    sysreq.classList.toggle('sysreq--collapsed', isOpen);
    btn.classList.toggle('open', !isOpen);
}
</script>

@endsection
