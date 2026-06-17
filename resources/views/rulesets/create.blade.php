@extends('layouts.app')

@section('title', 'Create Challenge Ruleset')

@section('content')

{{-- ── Page header ──────────────────────────────────────────────── --}}
<div class="ruleset-header">
    <div class="ruleset-header-inner">
        <a href="{{ url()->previous() }}" class="ruleset-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back
        </a>
        <div>
            <div class="hero-tag">Challenge Mode</div>
            <h1 class="ruleset-page-title">Create a <em>Ruleset</em></h1>
            <p class="ruleset-page-sub">Define the rules for your challenge run. Others can discover and follow your ruleset.</p>
        </div>
    </div>
</div>

{{-- ── Errors ────────────────────────────────────────────────────── --}}
@if ($errors->any())
    <div class="ruleset-errors">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Form ──────────────────────────────────────────────────────── --}}
<form method="POST" action="{{ route('rulesets.store') }}" class="ruleset-form" id="ruleset-form">
    @csrf

    <div class="ruleset-layout">

        {{-- ── LEFT COLUMN ──────────────────────────────────────── --}}
        <div class="ruleset-main">

            {{-- Game picker --}}
            <div class="ruleset-card">
                <div class="ruleset-card-label">
                    <span class="ruleset-step">01</span> Game
                </div>

                <div class="ruleset-game-search-wrap">
                    <div class="ruleset-search-row">
                        <div class="ruleset-search-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                        <input
                            type="text"
                            id="game-search-input"
                            class="ruleset-input ruleset-search-input"
                            placeholder="Search for a game…"
                            autocomplete="off"
                            value="{{ old('game_name') }}"
                        >
                        <div class="ruleset-search-spinner" id="search-spinner" style="display:none">
                            <div class="spinner-ring"></div>
                        </div>
                    </div>
                    <div class="ruleset-search-results" id="game-results" style="display:none"></div>

                    {{-- Hidden fields populated by JS --}}
                    <input type="hidden" name="rawg_id"   id="rawg-id"   value="{{ old('rawg_id') }}" required>
                    <input type="hidden" name="game_name" id="game-name" value="{{ old('game_name') }}">
                    <input type="hidden" name="game_image" id="game-image" value="{{ old('game_image') }}">

                    {{-- Selected game preview --}}
                    <div class="ruleset-game-selected" id="game-selected" style="{{ old('rawg_id') ? '' : 'display:none' }}">
                        <img id="game-selected-img" src="{{ old('game_image') }}" alt="" class="ruleset-game-thumb">
                        <div class="ruleset-game-selected-info">
                            <span class="ruleset-game-selected-name" id="game-selected-name">{{ old('game_name') }}</span>
                            <button type="button" class="ruleset-game-clear" id="game-clear">Change game</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Title + description --}}
            <div class="ruleset-card">
                <div class="ruleset-card-label">
                    <span class="ruleset-step">02</span> Ruleset Info
                </div>

                <div class="ruleset-field">
                    <label class="ruleset-label" for="title">Title <span class="ruleset-required">*</span></label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="ruleset-input @error('title') is-error @enderror"
                        placeholder="e.g. Nuzlocke Challenge, No-Hit Run, Pacifist Route…"
                        value="{{ old('title') }}"
                        maxlength="120"
                        required
                    >
                    @error('title') <p class="ruleset-field-error">{{ $message }}</p> @enderror
                </div>

                <div class="ruleset-field">
                    <label class="ruleset-label" for="description">Description <span class="ruleset-required">*</span></label>
                    <textarea
                        name="description"
                        id="description"
                        class="ruleset-textarea @error('description') is-error @enderror"
                        placeholder="Briefly explain the goal and spirit of this challenge run…"
                        rows="4"
                        maxlength="1000"
                        required
                    >{{ old('description') }}</textarea>
                    <div class="ruleset-char-count"><span id="desc-count">0</span> / 1000</div>
                    @error('description') <p class="ruleset-field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Rules --}}
            <div class="ruleset-card">
                <div class="ruleset-card-label">
                    <span class="ruleset-step">03</span> Rules
                    <span class="ruleset-card-label-sub">Add every rule the runner must follow</span>
                </div>

                <div class="ruleset-rules-list" id="rules-list">
                    {{-- Existing rules (on validation fail) --}}
                    @if (old('rules'))
                        @foreach (old('rules') as $i => $rule)
                            <div class="ruleset-rule-row" data-index="{{ $i }}">
                                <span class="ruleset-rule-num">{{ $i + 1 }}</span>
                                <input
                                    type="text"
                                    name="rules[]"
                                    class="ruleset-input ruleset-rule-input"
                                    placeholder="Describe this rule…"
                                    value="{{ $rule }}"
                                    maxlength="300"
                                >
                                <button type="button" class="ruleset-rule-remove" title="Remove rule">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        @endforeach
                    @else
                        {{-- Default first rule row --}}
                        <div class="ruleset-rule-row" data-index="0">
                            <span class="ruleset-rule-num">1</span>
                            <input
                                type="text"
                                name="rules[]"
                                class="ruleset-input ruleset-rule-input"
                                placeholder="Describe this rule…"
                                maxlength="300"
                            >
                            <button type="button" class="ruleset-rule-remove" title="Remove rule">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    @endif
                </div>

                <button type="button" class="ruleset-add-rule" id="add-rule-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add rule
                </button>
            </div>

        </div>

        {{-- ── RIGHT COLUMN ─────────────────────────────────────── --}}
        <div class="ruleset-sidebar">

            {{-- Mod link (optional) --}}
            <div class="ruleset-card">
                <div class="ruleset-card-label">
                    <span class="ruleset-step">04</span> Required Mod
                    <span class="ruleset-label-optional">optional</span>
                </div>
                <p class="ruleset-field-hint">If this challenge requires a mod, patch, or external tool, paste the link here.</p>
                <div class="ruleset-field">
                    <div class="ruleset-url-wrap">
                        <div class="ruleset-url-icon">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </div>
                        <input
                            type="url"
                            name="mod_url"
                            id="mod_url"
                            class="ruleset-input ruleset-url-input @error('mod_url') is-error @enderror"
                            placeholder="https://www.nexusmods.com/…"
                            value="{{ old('mod_url') }}"
                        >
                    </div>
                    @error('mod_url') <p class="ruleset-field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Visibility --}}
            <div class="ruleset-card">
                <div class="ruleset-card-label">Visibility</div>
                <div class="ruleset-visibility-group">
                    <label class="ruleset-radio-card @if(old('is_public', '1') == '1') is-selected @endif" id="vis-public-card">
                        <input type="radio" name="is_public" value="1" class="ruleset-radio-hidden" {{ old('is_public', '1') == '1' ? 'checked' : '' }}>
                        <div class="ruleset-radio-icon">🌐</div>
                        <div>
                            <div class="ruleset-radio-label">Public</div>
                            <div class="ruleset-radio-sub">Anyone can discover and use this ruleset</div>
                        </div>
                    </label>
                    <label class="ruleset-radio-card @if(old('is_public') == '0') is-selected @endif" id="vis-private-card">
                        <input type="radio" name="is_public" value="0" class="ruleset-radio-hidden" {{ old('is_public') == '0' ? 'checked' : '' }}>
                        <div class="ruleset-radio-icon">🔒</div>
                        <div>
                            <div class="ruleset-radio-label">Private</div>
                            <div class="ruleset-radio-sub">Only you can see this ruleset</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primary ruleset-submit-btn" id="submit-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Ruleset
            </button>

            <p class="ruleset-submit-note">You can edit or delete this ruleset at any time from your profile.</p>
        </div>

    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const csrfToken = '{{ csrf_token() }}';

    /* ── Game search ──────────────────────────────────────────── */
    const searchInput   = document.getElementById('game-search-input');
    const resultsBox    = document.getElementById('game-results');
    const spinner       = document.getElementById('search-spinner');
    const rawgIdInput   = document.getElementById('rawg-id');
    const gameNameInput = document.getElementById('game-name');
    const gameImageInput= document.getElementById('game-image');
    const gameSelected  = document.getElementById('game-selected');
    const selectedImg   = document.getElementById('game-selected-img');
    const selectedName  = document.getElementById('game-selected-name');
    const gameClear     = document.getElementById('game-clear');

    let searchTimeout;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const q = searchInput.value.trim();

        if (q.length < 2) { resultsBox.style.display = 'none'; return; }

        searchTimeout = setTimeout(function () {
            spinner.style.display = 'flex';
            fetch('/api/games/search?q=' + encodeURIComponent(q), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                spinner.style.display = 'none';
                renderResults(data.results || []);
            })
            .catch(function (err) {
                spinner.style.display = 'none';
                resultsBox.innerHTML = '<div class="ruleset-no-results">Something went wrong — try again.</div>';
                resultsBox.style.display = 'block';
                console.error('Game search error:', err);
            });
        }, 350);
    });

    function renderResults(games) {
        if (!games.length) {
            resultsBox.innerHTML = '<div class="ruleset-no-results">No games found</div>';
            resultsBox.style.display = 'block';
            return;
        }

        resultsBox.innerHTML = games.slice(0, 8).map(function (g) {
            const img = g.background_image
                ? '<img src="' + escHtml(g.background_image) + '" alt="">'
                : '<div class="ruleset-result-thumb-placeholder">🎮</div>';
            const year = g.released
                ? '<span class="ruleset-result-year">' + escHtml(g.released.slice(0, 4)) + '</span>'
                : '';
            return '<button type="button" class="ruleset-result-item"'
                + ' data-id="' + escHtml(String(g.id)) + '"'
                + ' data-name="' + escHtml(g.name) + '"'
                + ' data-img="' + escHtml(g.background_image || '') + '">'
                + '<div class="ruleset-result-thumb">' + img + '</div>'
                + '<div class="ruleset-result-info">'
                + '<span class="ruleset-result-name">' + escHtml(g.name) + '</span>'
                + year
                + '</div>'
                + '</button>';
        }).join('');

        resultsBox.style.display = 'block';

        resultsBox.querySelectorAll('.ruleset-result-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
                selectGame(btn.dataset.id, btn.dataset.name, btn.dataset.img);
            });
        });
    }

    function selectGame(id, name, img) {
        rawgIdInput.value    = id;
        gameNameInput.value  = name;
        gameImageInput.value = img;

        if (img) {
            selectedImg.src          = img;
            selectedImg.style.display = 'block';
        } else {
            selectedImg.style.display = 'none';
        }
        selectedName.textContent = name;

        resultsBox.style.display  = 'none';
        searchInput.style.display = 'none';
        gameSelected.style.display = 'flex';
    }

    gameClear.addEventListener('click', function () {
        rawgIdInput.value = gameNameInput.value = gameImageInput.value = '';
        searchInput.value = '';
        searchInput.style.display  = '';
        gameSelected.style.display = 'none';
        searchInput.focus();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.ruleset-game-search-wrap')) {
            resultsBox.style.display = 'none';
        }
    });

    /* ── Rules builder ────────────────────────────────────────── */
    const rulesList  = document.getElementById('rules-list');
    const addRuleBtn = document.getElementById('add-rule-btn');

    function getRuleRows() {
        return rulesList.querySelectorAll('.ruleset-rule-row');
    }

    function renumberRules() {
        const rows = getRuleRows();
        rows.forEach(function (row, i) {
            row.querySelector('.ruleset-rule-num').textContent = i + 1;
            row.querySelector('.ruleset-rule-remove').style.display =
                rows.length > 1 ? 'flex' : 'none';
        });
    }

    function bindRemoveBtn(row) {
        row.querySelector('.ruleset-rule-remove').addEventListener('click', function () {
            row.remove();
            renumberRules();
        });
    }

    function createRuleRow() {
        const div = document.createElement('div');
        div.className = 'ruleset-rule-row';
        div.innerHTML =
            '<span class="ruleset-rule-num"></span>'
            + '<input type="text" name="rules[]" class="ruleset-input ruleset-rule-input"'
            + ' placeholder="Describe this rule…" maxlength="300">'
            + '<button type="button" class="ruleset-rule-remove" title="Remove rule">'
            + '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">'
            + '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>'
            + '</svg></button>';
        bindRemoveBtn(div);
        return div;
    }

    // Bind remove buttons on rows that exist in the HTML (Blade-rendered)
    getRuleRows().forEach(function (row) { bindRemoveBtn(row); });

    addRuleBtn.addEventListener('click', function () {
        const newRow = createRuleRow();
        rulesList.appendChild(newRow);
        renumberRules();
        newRow.querySelector('input').focus();
    });

    renumberRules();

    /* ── Description char counter ─────────────────────────────── */
    const descTextarea = document.getElementById('description');
    const descCount    = document.getElementById('desc-count');

    function updateDescCount() {
        descCount.textContent = descTextarea.value.length;
    }
    descTextarea.addEventListener('input', updateDescCount);
    updateDescCount();

    /* ── Visibility radio cards ───────────────────────────────── */
    // Listen on the label (click) rather than the hidden radio input (change),
    // because display:none inputs don't fire reliable change events in all browsers.
    document.querySelectorAll('.ruleset-radio-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.ruleset-radio-card').forEach(function (c) {
                c.classList.remove('is-selected');
            });
            card.classList.add('is-selected');
            // Manually check the radio inside this card since the label click
            // may be intercepted before the browser updates it
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    /* ── Submit guard ─────────────────────────────────────────── */
    document.getElementById('ruleset-form').addEventListener('submit', function (e) {
        if (!rawgIdInput.value) {
            e.preventDefault();
            searchInput.classList.add('is-error');
            searchInput.style.display = '';
            searchInput.focus();
            searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    /* ── Utility ──────────────────────────────────────────────── */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});
</script>

@endsection
