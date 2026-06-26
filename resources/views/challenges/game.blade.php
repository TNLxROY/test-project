@extends('layouts.app')

@section('title', $game['name'] . ' — Challenges')

@section('content')

    {{-- ── Hero ───────────────────────────────────────────────────── --}}
    <div class="show-hero" style="background-image: url('{{ $game['background_image'] ?? '' }}')">
        <div class="show-hero-overlay"></div>
        <div class="show-hero-content">

            <a href="{{ route('challenges.index') }}" class="ruleset-back">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Back to search
            </a>

            <div class="show-hero-meta" style="margin-top:.75rem">
                <span class="hero-tag">Challenges</span>
                @foreach (array_slice($game['genres'] ?? [], 0, 3) as $genre)
                    <span class="esrb-badge"
                        style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.7)">
                        {{ $genre['name'] }}
                    </span>
                @endforeach
            </div>

            <h1 class="show-title">{{ $game['name'] }}</h1>

            <div class="show-hero-tags">
                <span class="tag-pill">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                    {{ $rulesets->total() }} {{ Str::plural('ruleset', $rulesets->total()) }}
                </span>
                @if (!empty($game['rating']))
                    <span class="tag-pill">★ {{ number_format($game['rating'], 1) }} RAWG rating</span>
                @endif
                @if (!empty($game['released']))
                    <span class="tag-pill">{{ \Carbon\Carbon::parse($game['released'])->format('Y') }}</span>
                @endif
            </div>

        </div>
    </div>

    {{-- ── Body ─────────────────────────────────────── --}}
    <div class="show-layout">

        {{-- ── LEFT / MAIN ─────────────────────────────── --}}
        <div class="show-main">

            {{-- Toolbar --}}
            <div class="cgame-toolbar">
                <div class="cgame-toolbar-left">
                    <span class="cgame-count">
                        @if ($rulesets->total() > 0)
                            <strong style="color:var(--text)">{{ $rulesets->total() }}</strong>
                            {{ Str::plural('ruleset', $rulesets->total()) }} found
                        @else
                            No rulesets yet — be the first!
                        @endif
                    </span>
                </div>
                @auth
                    <a href="{{ route('rulesets.create', ['game_id' => $game['id'], 'game_name' => $game['name'], 'game_image' => $game['background_image'] ?? '']) }}"
                        class="btn btn-primary btn-sm cgame-create-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Create ruleset
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                        Sign in to create a ruleset
                    </a>
                @endauth
            </div>

            {{-- Rulesets list --}}
            @if ($rulesets->count() > 0)
                <div class="cgame-rulesets-list">
                    @foreach ($rulesets as $ruleset)
                        <a href="{{ route('rulesets.show', $ruleset) }}" class="cgame-ruleset-card">

                            <div class="cgame-ruleset-main">
                                <div class="cgame-ruleset-header">
                                    <h3 class="cgame-ruleset-title">{{ $ruleset->title }}</h3>
                                    <span class="cgame-ruleset-count">
                                        {{ count($ruleset->rules) }} {{ Str::plural('rule', count($ruleset->rules)) }}
                                    </span>
                                </div>

                                <p class="cgame-ruleset-desc">{{ Str::limit($ruleset->description, 120) }}</p>

                                <div class="cgame-ruleset-rules-preview">
                                    @foreach (array_slice($ruleset->rules, 0, 3) as $i => $rule)
                                        <div class="cgame-ruleset-rule-snippet">
                                            <span class="cgame-ruleset-rule-num">{{ $i + 1 }}</span>
                                            <span>{{ Str::limit($rule, 80) }}</span>
                                        </div>
                                    @endforeach
                                    @if (count($ruleset->rules) > 3)
                                        <div class="cgame-ruleset-rule-more">
                                            +{{ count($ruleset->rules) - 3 }} more
                                            {{ Str::plural('rule', count($ruleset->rules) - 3) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="cgame-ruleset-footer">
                                <div class="cgame-ruleset-author">
                                    <div class="avatar" style="width:24px;height:24px;font-size:.6rem;flex-shrink:0">
                                        {{ strtoupper(substr($ruleset->user->name, 0, 2)) }}
                                    </div>
                                    <span>{{ $ruleset->user->name }}</span>
                                </div>
                                <div class="cgame-ruleset-meta-right">
                                    @if ($ruleset->mod_url)
                                        <span class="cgame-ruleset-badge">
                                            <svg width="10" height="10" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                            </svg>
                                            Mod
                                        </span>
                                    @endif
                                    <span class="cgame-ruleset-date">{{ $ruleset->created_at->diffForHumans() }}</span>
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24" style="color:var(--muted)">
                                        <path d="M9 18l6-6-6-6" />
                                    </svg>
                                </div>
                            </div>

                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($rulesets->hasPages())
                    <div class="cgame-pagination">
                        {{ $rulesets->links() }}
                    </div>
                @endif
            @else
                {{-- Empty state --}}
                <div class="cgame-empty">
                    <div class="cgame-empty-icon">🎮</div>
                    <h3 class="cgame-empty-title">No rulesets yet</h3>
                    <p class="cgame-empty-sub">Be the first to create a challenge ruleset for
                        <strong>{{ $game['name'] }}</strong>.</p>
                    @auth
                        <a href="{{ route('rulesets.create', ['game_id' => $game['id'], 'game_name' => $game['name'], 'game_image' => $game['background_image'] ?? '']) }}"
                            class="btn btn-primary" style="margin-top:1.5rem">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            Create first ruleset
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost" style="margin-top:1.5rem">Sign in to create a
                            ruleset</a>
                    @endauth
                </div>
            @endif

        </div>

        {{-- ── RIGHT / SIDEBAR ─────────────────────────────────────── --}}
        <div class="show-sidebar">

            {{-- Game card --}}
            <div class="show-card" style="padding:0;overflow:hidden">
                @if (!empty($game['background_image']))
                    <img src="{{ $game['background_image'] }}" alt="{{ $game['name'] }}" class="show-cover-img">
                @endif
                <div style="padding:1.25rem 1.5rem">
                    <div class="show-card-title" style="margin-bottom:.4rem">Game Info</div>
                    <p
                        style="font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;line-height:1.3;margin-bottom:1rem">
                        {{ $game['name'] }}</p>
                    <dl class="detail-list">
                        @if (!empty($game['released']))
                            <div class="detail-row">
                                <dt>Released</dt>
                                <dd>{{ \Carbon\Carbon::parse($game['released'])->format('M j, Y') }}</dd>
                            </div>
                        @endif
                        @if (!empty($game['rating']))
                            <div class="detail-row">
                                <dt>RAWG Rating</dt>
                                <dd>★ {{ number_format($game['rating'], 2) }}</dd>
                            </div>
                        @endif
                        @if (!empty($game['developers']))
                            <div class="detail-row">
                                <dt>Developer</dt>
                                <dd>{{ implode(', ', array_slice($game['developers'], 0, 2)) }}</dd>
                            </div>
                        @endif
                        @if (!empty($game['publishers']))
                            <div class="detail-row">
                                <dt>Publisher</dt>
                                <dd>{{ implode(', ', array_slice($game['publishers'], 0, 2)) }}</dd>
                            </div>
                        @endif
                    </dl>
                    <a href="{{ route('games.show', $game['id']) }}" class="btn btn-ghost btn-sm"
                        style="width:100%;text-align:center;display:block;margin-top:1rem">
                        View full game page
                    </a>
                </div>
            </div>

            {{-- Create CTA (sidebar) --}}
            @auth
                <div class="show-card cgame-cta-card">
                    <div class="cgame-cta-icon">🏆</div>
                    <h3 class="cgame-cta-title">Got a challenge idea?</h3>
                    <p class="cgame-cta-sub">Share your ruleset with the community and let others take on your challenge.</p>
                    <a href="{{ route('rulesets.create', ['game_id' => $game['id'], 'game_name' => $game['name'], 'game_image' => $game['background_image'] ?? '']) }}"
                        class="btn btn-primary btn-sm cgame-cta-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Create a ruleset
                    </a>
                </div>
            @endauth

        </div>
    </div>
@endsection
