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
                    @php
                        $prefillId    = old('rawg_id',    request('game_id'));
                        $prefillName  = old('game_name',  request('game_name'));
                        $prefillImage = old('game_image', request('game_image'));
                    @endphp
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
                            value="{{ $prefillName }}"
                            style="{{ $prefillId ? 'display:none' : '' }}"
                        >
                        <div class="ruleset-search-spinner" id="search-spinner" style="display:none">
                            <div class="spinner-ring"></div>
                        </div>
                    </div>
                    <div class="ruleset-search-results" id="game-results" style="display:none"></div>

                    {{-- Hidden fields populated by JS or query string (when arriving from challenges page) --}}
                    <input type="hidden" name="rawg_id"    id="rawg-id"    value="{{ $prefillId }}"    required>
                    <input type="hidden" name="game_name"  id="game-name"  value="{{ $prefillName }}">
                    <input type="hidden" name="game_image" id="game-image" value="{{ $prefillImage }}">

                    {{-- Selected game preview --}}
                    <div class="ruleset-game-selected" id="game-selected" style="{{ $prefillId ? '' : 'display:none' }}">
                        <img id="game-selected-img" src="{{ $prefillImage }}" alt="" class="ruleset-game-thumb" style="{{ $prefillImage ? '' : 'display:none' }}">
                        <div class="ruleset-game-selected-info">
                            <span class="ruleset-game-selected-name" id="game-selected-name">{{ $prefillName }}</span>
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
@endsection
