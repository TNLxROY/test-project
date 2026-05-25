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

                // PlayStation (used by PS1–PS5, PSP, PS Vita)
                $psLogo = '<svg viewBox="0 0 180 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M64.547 23.728V.272h9.642c1.88 0 3.433.188 4.66.563 1.226.375 2.194.894 2.902 1.557.708.663 1.208 1.444 1.5 2.344.291.9.437 1.875.437 2.927 0 1.126-.162 2.15-.487 3.075-.324.925-.852 1.72-1.583 2.388-.732.668-1.71 1.188-2.934 1.557-1.225.37-2.731.554-4.518.554h-4.897v8.491h-4.722zm4.722-12.41h4.565c1.087 0 1.965-.11 2.633-.33.668-.22 1.193-.518 1.574-.896.381-.377.645-.82.793-1.328.147-.508.22-1.061.22-1.658 0-.573-.072-1.108-.217-1.604a3.129 3.129 0 0 0-.72-1.28c-.334-.362-.806-.645-1.415-.849-.61-.203-1.392-.305-2.346-.305h-5.087v8.25zM83.37 18.4c0-.897.15-1.698.45-2.404.3-.706.735-1.316 1.305-1.828.57-.513 1.265-.906 2.083-1.18.82-.273 1.751-.41 2.795-.41.6 0 1.165.04 1.696.12.531.079 1.028.188 1.489.327v-.782c0-.988-.293-1.75-.878-2.287-.585-.537-1.432-.806-2.54-.806-.728 0-1.43.12-2.106.361-.677.24-1.372.591-2.086 1.051l-1.43-2.894c.896-.581 1.874-1.033 2.934-1.358 1.06-.324 2.2-.487 3.42-.487 2.27 0 4.023.573 5.26 1.72 1.237 1.146 1.855 2.787 1.855 4.922v8.543h-4.288v-1.578c-.52.6-1.15 1.063-1.893 1.39-.742.327-1.566.49-2.472.49-.82 0-1.57-.133-2.252-.399a5.336 5.336 0 0 1-1.76-1.105 4.932 4.932 0 0 1-1.14-1.657A5.396 5.396 0 0 1 83.37 18.4zm4.18-.24c0 .72.243 1.313.73 1.779.487.466 1.12.699 1.898.699.506 0 .974-.093 1.404-.278.43-.186.803-.44 1.119-.764.316-.324.563-.706.742-1.146.179-.44.268-.915.268-1.428v-.602a8.617 8.617 0 0 0-1.322-.293 9.68 9.68 0 0 0-1.369-.096c-.946 0-1.678.223-2.196.67-.518.446-.776 1.037-.774 1.659zM100.41 6.727h4.553v2.286c.49-.796 1.12-1.42 1.892-1.872.771-.452 1.66-.678 2.667-.678.266 0 .524.018.773.054.249.036.484.086.703.149l-.553 4.108a5.128 5.128 0 0 0-.72-.19 4.302 4.302 0 0 0-.793-.07c-1.105 0-1.982.358-2.633 1.074-.65.716-.975 1.726-.975 3.03v9.11h-4.914V6.727zM111.45 15.136c0-1.3.215-2.49.645-3.574.43-1.083 1.028-2.016 1.793-2.797a8.07 8.07 0 0 1 2.73-1.818c1.057-.433 2.211-.65 3.463-.65 1.252 0 2.406.217 3.463.65a8.07 8.07 0 0 1 2.73 1.818c.765.781 1.362 1.714 1.793 2.797.43 1.083.645 2.275.645 3.574 0 1.3-.215 2.49-.645 3.574-.43 1.083-1.028 2.016-1.793 2.797a8.07 8.07 0 0 1-2.73 1.818c-1.057.433-2.211.65-3.463.65-1.252 0-2.406-.217-3.463-.65a8.07 8.07 0 0 1-2.73-1.818c-.765-.781-1.362-1.714-1.793-2.797-.43-1.083-.645-2.275-.645-3.574zm4.914 0c0 .684.093 1.313.278 1.888.185.575.449 1.073.79 1.494.342.42.754.748 1.237.982.483.234 1.024.351 1.622.351.597 0 1.138-.117 1.622-.351a3.74 3.74 0 0 0 1.237-.982c.341-.421.605-.919.79-1.494.185-.575.278-1.204.278-1.888 0-.684-.093-1.313-.278-1.888a4.489 4.489 0 0 0-.79-1.494 3.74 3.74 0 0 0-1.237-.982 3.665 3.665 0 0 0-1.622-.351c-.598 0-1.139.117-1.622.351a3.74 3.74 0 0 0-1.237.982 4.489 4.489 0 0 0-.79 1.494c-.185.575-.278 1.204-.278 1.888zM128.42 22.033l1.7-3.32c.63.42 1.33.758 2.1 1.014.77.256 1.548.384 2.335.384.67 0 1.187-.108 1.549-.324.362-.216.543-.516.543-.9 0-.384-.176-.674-.527-.87-.352-.197-.942-.39-1.77-.578l-1.214-.277c-1.362-.325-2.38-.849-3.054-1.573-.673-.724-1.01-1.64-1.01-2.748 0-.72.143-1.375.43-1.964.287-.589.693-1.09 1.22-1.506.527-.416 1.165-.737 1.915-.963.75-.226 1.588-.34 2.515-.34.885 0 1.76.108 2.623.324.862.216 1.657.528 2.384.936l-1.6 3.115a6.81 6.81 0 0 0-1.724-.699 7.048 7.048 0 0 0-1.82-.24c-.573 0-1.026.099-1.358.297-.333.198-.499.468-.499.81 0 .337.15.601.45.793.3.192.79.374 1.47.546l1.262.313c1.458.361 2.534.9 3.228 1.614.693.715 1.04 1.624 1.04 2.728 0 .72-.148 1.38-.444 1.98a4.518 4.518 0 0 1-1.258 1.536c-.543.427-1.2.757-1.97.988-.77.232-1.631.348-2.58.348-1.044 0-2.059-.138-3.042-.415-.984-.277-1.874-.676-2.668-1.194zM140.88 6.727h2.468V2.643h4.914v4.084h3.512v3.729h-3.512v6.216c0 .72.17 1.25.51 1.591.341.342.834.513 1.48.513.34 0 .66-.033.957-.1.297-.066.578-.163.843-.29l.625 3.488a9.32 9.32 0 0 1-1.597.49c-.588.122-1.222.183-1.903.183-1.699 0-3.002-.43-3.908-1.29-.906-.861-1.36-2.114-1.36-3.76V10.456h-2.03V6.727zM152.92 23.728V.272h4.914v8.7c.537-.747 1.19-1.33 1.96-1.75.769-.42 1.638-.63 2.607-.63 1.8 0 3.18.55 4.14 1.648.959 1.1 1.439 2.65 1.439 4.651v10.837h-4.914V13.896c0-.988-.218-1.74-.655-2.257-.436-.518-1.063-.776-1.878-.776-.585 0-1.108.13-1.566.39a3.41 3.41 0 0 0-1.12 1.073 5.034 5.034 0 0 0-.674 1.567 7.658 7.658 0 0 0-.224 1.9v7.935h-4.029zM8.684 23.6V2.247L12.3 3.414V22.44L8.684 23.6zm5.428-1.746V3.79l9.03 3.047c1.028.347 1.838.83 2.429 1.45.59.62.886 1.44.886 2.46 0 .76-.172 1.43-.516 2.01-.344.58-.847 1.04-1.508 1.38-.66.34-1.47.51-2.427.51-.548 0-1.087-.06-1.617-.18V11.17c.346.12.694.18 1.043.18.47 0 .86-.1 1.17-.3.31-.2.465-.5.465-.9 0-.35-.12-.645-.36-.885-.24-.24-.61-.44-1.11-.6l-3.477-1.17v14.16h-3.998zm9.73-2.93l-3.73 1.285V17.47l3.73-1.32v2.774zM42.12 22.44l-3.61 1.16V3.408l3.61-1.16V22.44zM30.24 6.73c0-1.02.177-1.93.53-2.73.353-.8.843-1.47 1.47-2.01A6.56 6.56 0 0 1 34.42.7c.823-.297 1.713-.446 2.67-.446.657 0 1.29.065 1.898.194.608.13 1.17.32 1.686.572l-1.201 3.032a5.12 5.12 0 0 0-1.145-.41 5.43 5.43 0 0 0-1.238-.144c-.814 0-1.466.213-1.957.64-.49.426-.736 1.023-.736 1.79v.866l4.277 1.444v2.893l-4.277-1.444v14.26H30.24V6.73z"/></svg>';

                // Xbox (used for all Xbox variants)
                $xboxLogo = '<svg viewBox="0 0 98 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M7.975 24C3.569 24 0 20.43 0 16.025V7.975C0 3.569 3.569 0 7.975 0h8.05C20.431 0 24 3.569 24 7.975v8.05C24 20.431 20.431 24 16.025 24H7.975zm4.012-20.348c-2.47 0-4.717 1.02-6.336 2.662 1.074.9 3.374 3.044 6.336 6.56 2.961-3.516 5.262-5.66 6.336-6.56a8.936 8.936 0 0 0-6.336-2.662zm0 16.696c2.47 0 4.717-1.02 6.336-2.662-1.087-.913-3.4-3.068-6.336-6.572-2.937 3.504-5.25 5.659-6.336 6.572A8.936 8.936 0 0 0 11.987 20.348zm7.64-3.946c.845-1.232 1.338-2.724 1.338-4.327 0-1.64-.514-3.162-1.392-4.41-.946.869-2.604 2.501-4.588 5.016 1.847 2.365 3.476 3.873 4.643 4.72zm-15.279 0c1.166-.847 2.795-2.355 4.642-4.72C7.007 9.165 5.349 7.533 4.402 6.664A8.967 8.967 0 0 0 3.01 11.075c0 1.603.493 3.095 1.338 4.327zM36.104 7.2h2.52L41.4 11.784 44.208 7.2h2.448L42.6 13.104l4.296 6.696h-2.568l-3.024-4.92-2.976 4.92h-2.496l4.248-6.648L36.104 7.2zm13.464 0v12.6h-2.232V7.2h2.232zm3.48 0h3.696c.904 0 1.716.132 2.436.396a5.18 5.18 0 0 1 1.836 1.104c.504.472.888 1.032 1.152 1.68.272.648.408 1.36.408 2.136 0 .784-.136 1.5-.408 2.148a4.813 4.813 0 0 1-1.152 1.668 5.18 5.18 0 0 1-1.836 1.092c-.72.256-1.532.384-2.436.384h-1.464V19.8h-2.232V7.2zm2.232 8.616h1.392c.544 0 1.024-.08 1.44-.24.416-.168.764-.392 1.044-.672.288-.288.504-.624.648-1.008.152-.384.228-.8.228-1.248 0-.448-.076-.864-.228-1.248a2.777 2.777 0 0 0-.648-1.008 2.886 2.886 0 0 0-1.044-.672 3.766 3.766 0 0 0-1.44-.252h-1.392v6.348zm10.104-8.616h2.232v10.536h5.736V19.8H65.384V7.2zm9.048 0h7.8v2.016h-5.568v3.252h5.208v2.016h-5.208v3.3h5.736V19.8h-7.968V7.2zm15.408 0h3.072l3.744 8.484L100.4 7.2h3.024L97.4 19.8h-2.4L88.84 7.2z"/></svg>';

                // Nintendo Switch
                $switchLogo = '<svg viewBox="0 0 110 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M0 4.8A4.8 4.8 0 0 1 4.8 0h5.752v24H4.8A4.8 4.8 0 0 1 0 19.2V4.8zm4.8 2.952a2.448 2.448 0 1 0 4.896 0 2.448 2.448 0 0 0-4.896 0zm17.952 9.696a2.448 2.448 0 1 0 4.896 0 2.448 2.448 0 0 0-4.896 0zM13.448 0H19.2A4.8 4.8 0 0 1 24 4.8v14.4A4.8 4.8 0 0 1 19.2 24h-5.752V0zm16.96 5.04h2.568l7.248 11.04V5.04h2.472V18.96h-2.544l-7.272-11.04v11.04H29.44l-.032-13.92zm16.344 0h2.472v13.92h-2.472V5.04zm4.848 0h9.576v2.256H58.44v11.664h-2.472V7.296h-3.36V5.04zm12.456 0h2.472v5.448l5.232-5.448h3.12L69.8 10.608l5.304 8.352H71.96l-3.888-6.12-1.512 1.584v4.536H64.08l-.024-13.92zm13.296 0h2.472V15.6c0 .728.2 1.296.6 1.704.408.4.952.6 1.632.6s1.224-.2 1.632-.6c.4-.408.6-.976.6-1.704V5.04h2.472v10.704c0 .816-.152 1.528-.456 2.136a4.386 4.386 0 0 1-1.224 1.512 5.136 5.136 0 0 1-1.776.888 7.022 7.022 0 0 1-2.112.312 7.022 7.022 0 0 1-2.112-.312 5.136 5.136 0 0 1-1.776-.888 4.386 4.386 0 0 1-1.224-1.512c-.304-.608-.456-1.32-.456-2.136V5.04h.128zm14.52 5.4c0-.312-.056-.584-.168-.816a1.512 1.512 0 0 0-.456-.576 2.08 2.08 0 0 0-.672-.336 2.697 2.697 0 0 0-.816-.12c-.552 0-1.008.14-1.368.42-.36.272-.54.628-.54 1.068 0 .272.064.504.192.696.136.184.308.344.516.48.216.128.456.24.72.336l.792.288 1.056.384c.392.144.784.324 1.176.54.4.208.76.464 1.08.768.328.304.592.668.792 1.092.2.424.3.924.3 1.5 0 .68-.128 1.284-.384 1.812a3.862 3.862 0 0 1-1.056 1.332c-.448.36-.976.636-1.584.828a6.464 6.464 0 0 1-1.992.288c-.728 0-1.408-.1-2.04-.3a5.11 5.11 0 0 1-1.644-.876 4.13 4.13 0 0 1-1.092-1.404c-.264-.552-.396-1.18-.396-1.884h2.424c0 .352.06.672.18.96.128.288.304.536.528.744.232.2.504.356.816.468.32.112.672.168 1.056.168.616 0 1.112-.152 1.488-.456.376-.312.564-.716.564-1.212 0-.304-.072-.56-.216-.768a2.007 2.007 0 0 0-.564-.54 4.174 4.174 0 0 0-.792-.384l-.888-.312-1.008-.384a7.054 7.054 0 0 1-1.08-.528 3.935 3.935 0 0 1-.888-.756 3.217 3.217 0 0 1-.588-1.044 4.205 4.205 0 0 1-.204-1.38c0-.624.128-1.18.384-1.668a3.835 3.835 0 0 1 1.02-1.248c.424-.344.916-.604 1.476-.78a5.89 5.89 0 0 1 1.776-.264c.672 0 1.296.096 1.872.288.576.192 1.08.468 1.512.828.432.352.768.78 1.008 1.284.248.496.372 1.056.372 1.68h-2.396v-.024z"/></svg>';

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

                    <div class="platform-item">
                        <div class="platform-item-header">
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
                        </div>

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

        {{-- Cover image — IGDB first, fallback to RAWG --}}
        @php

            // Prefer IGDB cover first
            $coverUrl = $igdbCoverUrl ?? $game['background_image'] ?? null;

            // RAWG image quality fix
            if ($coverUrl && str_contains($coverUrl, 'media.rawg.io')) {

                // Remove RAWG crop/compression segment
                $coverUrl = preg_replace(
                    '#/crop/\d+/\d+/#',
                    '/',
                    $coverUrl
                );

                // Optional: remove additional resize filters
                $coverUrl = str_replace('/resize/', '/', $coverUrl);
            }

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

@endsection
