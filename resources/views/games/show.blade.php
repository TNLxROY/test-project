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
<div id="panel-write" style="display:none">
    <div class="reviews-layout">
        <div class="show-card write-review-card">
            <h2 class="show-card-title">Your Review</h2>
            <p class="write-review-sub">Share your thoughts on <strong>{{ $game['name'] }}</strong></p>
            <div class="form-group" style="margin-top:1rem">
                <label>Review</label>
                <textarea id="review-body" class="review-textarea"
                    placeholder="What did you think of this game? (min. 10 characters)"
                    maxlength="2000"></textarea>
                <div class="review-char-count"><span id="char-count">0</span> / 2000</div>
            </div>
            <button class="btn btn-primary" onclick="submitReview({{ $game['id'] }}, '{{ addslashes($game['name']) }}')">
                Post Review
            </button>
            <div class="auth-msg" id="review-msg" style="margin-top:.75rem"></div>
        </div>
    </div>
</div>

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
