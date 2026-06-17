@extends('layouts.app')

@section('title', $ruleset->title . ' — Ruleset')

@section('content')

{{-- ── Hero banner (game art as background) ──────────────────── --}}
<div class="show-hero" style="background-image: url('{{ $ruleset->game_image }}')">
    <div class="show-hero-overlay"></div>
    <div class="show-hero-content">

        <a href="{{ url()->previous() }}" class="ruleset-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back
        </a>

        <div class="show-hero-meta" style="margin-top:.75rem">
            <span class="hero-tag">Challenge Ruleset</span>
            @if($ruleset->is_public)
                <span class="esrb-badge">Public</span>
            @else
                <span class="esrb-badge" style="background:rgba(136,136,153,.15);border-color:rgba(136,136,153,.35);color:var(--muted)">Private</span>
            @endif
        </div>

        <h1 class="show-title">{{ $ruleset->title }}</h1>
        <p class="show-original-title">{{ $ruleset->game_name }}</p>

        <div class="show-hero-tags">
            <span class="tag-pill">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                {{ count($ruleset->rules) }} {{ Str::plural('rule', count($ruleset->rules)) }}
            </span>
            @if($ruleset->mod_url)
                <span class="tag-pill">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    Mod required
                </span>
            @endif
            <span class="tag-pill">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ $ruleset->created_at->diffForHumans() }}
            </span>
        </div>

    </div>
</div>

{{-- ── Body layout ──────────────────────────────────────────────── --}}
<div class="show-layout">

    {{-- ── LEFT / MAIN ─────────────────────────────────────────── --}}
    <div class="show-main">

        {{-- Description --}}
        <div class="show-card">
            <div class="show-card-title">About this ruleset</div>
            <p class="ruleset-show-description">{{ $ruleset->description }}</p>
        </div>

        {{-- Rules list --}}
        <div class="show-card">
            <div class="show-card-title">
                Rules
                <span class="ruleset-show-rule-count">{{ count($ruleset->rules) }}</span>
            </div>

            <ol class="ruleset-show-rules">
                @foreach($ruleset->rules as $index => $rule)
                    <li class="ruleset-show-rule-item">
                        <span class="ruleset-show-rule-num">{{ $index + 1 }}</span>
                        <span class="ruleset-show-rule-text">{{ $rule }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- Mod link --}}
        @if($ruleset->mod_url)
            <div class="show-card">
                <div class="show-card-title">Required Mod</div>
                <div class="ruleset-show-mod">
                    <div class="ruleset-show-mod-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </div>
                    <div class="ruleset-show-mod-body">
                        <p class="ruleset-show-mod-label">This challenge requires an external mod or patch.</p>
                        <a href="{{ $ruleset->mod_url }}" target="_blank" rel="noopener noreferrer" class="ruleset-show-mod-link">
                            {{ $ruleset->mod_url }}
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- ── RIGHT / SIDEBAR ─────────────────────────────────────── --}}
    <div class="show-sidebar">

        {{-- Game info card --}}
        <div class="show-card" style="padding:0;overflow:hidden">
            @if($ruleset->game_image)
                <img src="{{ $ruleset->game_image }}" alt="{{ $ruleset->game_name }}" class="show-cover-img">
            @endif
            <div style="padding:1.25rem 1.5rem">
                <div class="show-card-title" style="margin-bottom:.5rem">Game</div>
                <p style="font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;line-height:1.3;margin-bottom:.85rem">{{ $ruleset->game_name }}</p>
                <a href="{{ route('games.show', $ruleset->rawg_id) }}" class="btn btn-ghost btn-sm" style="width:100%;text-align:center;display:block">
                    View game page
                </a>
            </div>
        </div>

        {{-- Creator card --}}
        <div class="show-card">
            <div class="show-card-title">Created by</div>
            <div class="ruleset-show-creator">
                <div class="avatar" style="width:36px;height:36px;font-size:.8rem">
                    {{ strtoupper(substr($ruleset->user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="ruleset-show-creator-name">{{ $ruleset->user->name }}</div>
                    <div class="ruleset-show-creator-date">{{ $ruleset->created_at->format('M j, Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Actions (only visible to the owner) --}}
        @auth
            @if(auth()->id() === $ruleset->user_id)
                <div class="show-card">
                    <div class="show-card-title">Manage</div>
                    <div class="ruleset-show-actions">
                        <a href="{{ route('rulesets.edit', $ruleset) }}" class="btn btn-ghost btn-sm ruleset-show-action-btn">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit ruleset
                        </a>
                        <form method="POST" action="{{ route('rulesets.destroy', $ruleset) }}" id="delete-ruleset-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-ghost btn-sm ruleset-show-action-btn ruleset-show-delete-btn" id="delete-ruleset-btn">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Delete ruleset
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endauth

    </div>
</div>

{{-- ── Delete confirmation modal ────────────────────────────────── --}}
@auth
    @if(auth()->id() === $ruleset->user_id)
        <div class="ruleset-modal-backdrop" id="delete-modal" style="display:none">
            <div class="ruleset-modal">
                <div class="ruleset-modal-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </div>
                <h3 class="ruleset-modal-title">Delete this ruleset?</h3>
                <p class="ruleset-modal-body">This will permanently remove <strong>{{ $ruleset->title }}</strong>. This action cannot be undone.</p>
                <div class="ruleset-modal-btns">
                    <button type="button" class="btn btn-ghost btn-sm" id="delete-cancel-btn">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm ruleset-modal-confirm-btn" id="delete-confirm-btn">Yes, delete it</button>
                </div>
            </div>
        </div>
    @endif
@endauth

<style>
/* ── Show-page specific styles ───────────────────────────────── */
.ruleset-back {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    color: rgba(255,255,255,.6);
    font-size: .83rem;
    text-decoration: none;
    transition: color .15s;
}
.ruleset-back:hover { color: #fff; }

.ruleset-show-description {
    font-size: .93rem;
    color: var(--muted);
    line-height: 1.75;
}

/* Rules list */
.show-card-title {
    display: flex;
    align-items: center;
    gap: .5rem;
}
.ruleset-show-rule-count {
    font-family: 'DM Sans', sans-serif;
    font-size: .72rem;
    font-weight: 600;
    background: rgba(232,25,44,.12);
    border: 1px solid rgba(232,25,44,.25);
    color: var(--accent2);
    border-radius: 5px;
    padding: .1rem .4rem;
    letter-spacing: 0;
    text-transform: none;
}
.ruleset-show-rules {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0;
    counter-reset: none;
}
.ruleset-show-rule-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: .85rem 0;
    border-bottom: 1px solid var(--border);
}
.ruleset-show-rule-item:last-child { border-bottom: none; }

.ruleset-show-rule-num {
    font-family: 'Syne', sans-serif;
    font-size: .75rem;
    font-weight: 800;
    color: var(--accent);
    min-width: 1.6rem;
    padding-top: .1rem;
    flex-shrink: 0;
}
.ruleset-show-rule-text {
    font-size: .9rem;
    color: var(--text);
    line-height: 1.6;
}

/* Mod card */
.ruleset-show-mod {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: .9rem 1rem;
}
.ruleset-show-mod-icon {
    color: var(--accent2);
    flex-shrink: 0;
    margin-top: .1rem;
}
.ruleset-show-mod-label {
    font-size: .82rem;
    color: var(--muted);
    margin-bottom: .4rem;
}
.ruleset-show-mod-link {
    font-size: .82rem;
    color: var(--accent2);
    text-decoration: none;
    word-break: break-all;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}
.ruleset-show-mod-link:hover { text-decoration: underline; }

/* Creator card */
.ruleset-show-creator {
    display: flex;
    align-items: center;
    gap: .75rem;
}
.ruleset-show-creator-name {
    font-size: .9rem;
    font-weight: 600;
    color: var(--text);
}
.ruleset-show-creator-date {
    font-size: .76rem;
    color: var(--muted);
    margin-top: .15rem;
}

/* Actions */
.ruleset-show-actions {
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.ruleset-show-action-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
}
.ruleset-show-delete-btn {
    color: var(--accent2) !important;
    border-color: rgba(232,25,44,.3) !important;
}
.ruleset-show-delete-btn:hover {
    background: rgba(232,25,44,.1) !important;
    border-color: var(--accent) !important;
}

/* Delete modal */
.ruleset-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.7);
    backdrop-filter: blur(4px);
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.ruleset-modal {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 2rem;
    max-width: 420px;
    width: 100%;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .75rem;
}
.ruleset-modal-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(232,25,44,.12);
    border: 1px solid rgba(232,25,44,.3);
    color: var(--accent2);
    display: flex;
    align-items: center;
    justify-content: center;
}
.ruleset-modal-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.15rem;
    font-weight: 700;
}
.ruleset-modal-body {
    font-size: .87rem;
    color: var(--muted);
    line-height: 1.6;
}
.ruleset-modal-btns {
    display: flex;
    gap: .6rem;
    margin-top: .5rem;
}
.ruleset-modal-confirm-btn {
    background: var(--accent) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const deleteBtn    = document.getElementById('delete-ruleset-btn');
    const modal        = document.getElementById('delete-modal');
    const cancelBtn    = document.getElementById('delete-cancel-btn');
    const confirmBtn   = document.getElementById('delete-confirm-btn');
    const deleteForm   = document.getElementById('delete-ruleset-form');

    if (deleteBtn && modal) {
        deleteBtn.addEventListener('click', function () {
            modal.style.display = 'flex';
        });
        cancelBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.style.display = 'none';
        });
        confirmBtn.addEventListener('click', function () {
            deleteForm.submit();
        });
    }

});
</script>

@endsection
