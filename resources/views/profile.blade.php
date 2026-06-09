@extends('layouts.app')

@section('content')

{{-- ── PROFILE HERO (unchanged) ─────────────────────────────────────────── --}}
<div class="profile-hero">
    <div class="profile-hero-inner">

        <div class="profile-hero-identity">
            <div class="profile-avatar-wrap" id="avatar-wrap">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="profile-avatar-img" id="avatar-img">
                @else
                    <div class="profile-avatar-lg" id="avatar-initials">
                        {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name.' ', ' '), 1, 1)) }}
                    </div>
                @endif
                <button class="avatar-edit-btn" id="avatar-edit-btn" title="Change profile picture">
                    <i class="ti ti-camera" aria-hidden="true"></i>
                </button>
            </div>

            <div>
                <h1 class="profile-name" id="profile-display-name">{{ $user->name }}</h1>
                <p class="profile-email">{{ $user->email }}</p>
                <p class="profile-joined">Member since {{ $user->created_at->format('F Y') }}</p>
                @if($user->avatar)
                    <button id="remove-avatar-btn" class="btn btn-ghost btn-sm" style="margin-top:.5rem;font-size:.75rem" onclick="removeAvatar()">
                        Remove photo
                    </button>
                @endif
            </div>
        </div>

        <div class="hero-right">
            <a href="{{ route('home') }}" class="back-link">
                <i class="ti ti-home" aria-hidden="true"></i> Home
            </a>
            <div class="profile-xp-panel">
                <div class="profile-xp-top">
                    <div class="profile-xp-badge">
                        <span class="profile-xp-num">{{ $userLevel->level }}</span>
                        <span class="profile-xp-lvl">LVL</span>
                    </div>
                    <div class="profile-xp-top-right">
                        <span class="profile-xp-sub">{{ $userLevel->xp }} / {{ $userLevel->xpForCurrentLevel() }} XP &mdash; {{ $userLevel->progressPercent() }}%</span>
                        <div class="profile-xp-bar-track" role="progressbar"
                             aria-valuenow="{{ $userLevel->xp }}"
                             aria-valuemin="0"
                             aria-valuemax="{{ $userLevel->xpForCurrentLevel() }}">
                            <div class="profile-xp-bar-fill" style="width: 0%" data-xp-target="{{ $userLevel->progressPercent() }}"></div>
                        </div>
                        <span class="profile-xp-hint">{{ $userLevel->xpToNextLevel() }} xp needed to level up</span>
                    </div>
                </div>
                <div class="profile-xp-stats">
                    <div class="profile-xp-stat">
                        <i class="ti ti-file-text" aria-hidden="true"></i>
                        <div class="profile-xp-stat-text">
                            <span class="profile-xp-stat-val">{{ $userLevel->review_count }}</span>
                            <span class="profile-xp-stat-label">Reviews</span>
                        </div>
                    </div>
                    <div class="profile-xp-stat">
                        <i class="ti ti-star" aria-hidden="true"></i>
                        <div class="profile-xp-stat-text">
                            <span class="profile-xp-stat-val">{{ number_format($userLevel->total_xp) }}</span>
                            <span class="profile-xp-stat-label">Total XP</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── TAB BAR (mirrors show blade style) ──────────────────────────────── --}}
<div class="show-hero" style="background:none;min-height:unset;padding:0">
    <div class="show-hero-overlay" style="display:none"></div>
    <div class="show-hero-content" style="padding:1rem 1.5rem 0">
        <div class="show-tabs">
            <button class="show-tab active" id="tab-about"    onclick="switchProfileTab('about')">
                <i class="ti ti-user" aria-hidden="true"></i> About Me
            </button>
            <button class="show-tab"        id="tab-settings" onclick="switchProfileTab('settings')">
                <i class="ti ti-settings" aria-hidden="true"></i> Settings
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- PANEL: ABOUT ME                                                        --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="panel-about">
<div class="show-layout">

    {{-- LEFT: bio + favourite game --}}
    <div class="show-main">

        {{-- Bio --}}
        <div class="show-card">
            <h2 class="show-card-title">Bio</h2>
            <textarea id="about-bio" class="review-textarea wr-textarea"
                placeholder="Tell others a bit about yourself… (max 500 characters)"
                maxlength="500"
                oninput="document.getElementById('bio-chars').textContent = this.value.length"
                style="min-height:120px">{{ $user->bio ?? '' }}</textarea>
            <div class="review-char-count">
                <span id="bio-chars">{{ strlen($user->bio ?? '') }}</span> / 500
            </div>
            <button class="btn btn-primary btn-sm" style="margin-top:.75rem" onclick="saveBio()">
                Save bio
            </button>
        </div>

        {{-- Favourite game search --}}
        <div class="show-card">
            <h2 class="show-card-title">Favourite Game</h2>

            {{-- Current favourite --}}
            <div id="fav-current" style="{{ $user->favourite_game_id ? '' : 'display:none' }}">
                <div class="platform-item" style="margin-bottom:1rem">
                    <div class="platform-item-header">
                        <div class="platform-logo-wrap" style="width:52px;height:52px;overflow:hidden;border-radius:6px;flex-shrink:0">
                            <img id="fav-cover" src="{{ $user->favourite_game_cover ?? '' }}"
                                 alt="" style="width:100%;height:100%;object-fit:cover">
                        </div>
                        <div class="platform-info">
                            <span class="platform-name" id="fav-name">{{ $user->favourite_game_name ?? '' }}</span>
                            <span class="platform-date" id="fav-meta"></span>
                        </div>
                        <button class="sysreq-toggle" title="Remove" onclick="removeFavGame()" aria-label="Remove favourite game">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search box --}}
            <div class="form-group" style="margin-bottom:.5rem">
                <label>Search for a game</label>
                <div style="display:flex;gap:.5rem">
                    <input type="text" id="fav-search-input"
                           placeholder="e.g. The Witcher 3…"
                           oninput="favSearchDebounce()"
                           autocomplete="off"
                           style="flex:1">
                    <button class="btn btn-ghost btn-sm" onclick="favSearchNow()" style="white-space:nowrap">
                        <i class="ti ti-search" aria-hidden="true"></i> Search
                    </button>
                </div>
            </div>

            {{-- Results list --}}
            <div id="fav-results" style="display:none">
                <div id="fav-results-inner" class="store-list" style="flex-direction:column;gap:.5rem"></div>
                <p id="fav-no-results" style="display:none;color:var(--text-muted);font-size:.875rem;margin-top:.5rem">
                    No games found.
                </p>
            </div>

            <div class="auth-msg" id="fav-msg" style="margin-top:.5rem"></div>
        </div>

    </div>

    {{-- RIGHT: public profile summary --}}
    <div class="show-sidebar">

        <div class="show-card">
            <h2 class="show-card-title">Public Profile</h2>
            <dl class="detail-list">
                <div class="detail-row">
                    <dt>Username</dt>
                    <dd id="pub-name">{{ $user->name }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Member since</dt>
                    <dd>{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Reviews written</dt>
                    <dd>{{ $userLevel->review_count }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Level</dt>
                    <dd>{{ $userLevel->level }}</dd>
                </div>
            </dl>
        </div>

        @if($user->favourite_game_id)
        <div class="show-card" id="fav-sidebar-card">
            <h2 class="show-card-title">Favourite Game</h2>
            <a href="{{ route('games.show', $user->favourite_game_id) }}" class="store-btn" id="fav-sidebar-link">
                <i class="ti ti-device-gamepad-2" aria-hidden="true"></i>
                <span id="fav-sidebar-name">{{ $user->favourite_game_name ?? '' }}</span>
            </a>
        </div>
        @else
        <div class="show-card" id="fav-sidebar-card" style="display:none">
            <h2 class="show-card-title">Favourite Game</h2>
            <a href="#" class="store-btn" id="fav-sidebar-link">
                <i class="ti ti-device-gamepad-2" aria-hidden="true"></i>
                <span id="fav-sidebar-name"></span>
            </a>
        </div>
        @endif

        <div class="show-card">
            <h2 class="show-card-title">Quick Links</h2>
            <div class="store-list">
                <a href="{{ route('games.index') }}" class="store-btn">Browse Games</a>
                <a href="{{ route('home') }}" class="store-btn">Home</a>
            </div>
        </div>

    </div>
</div>
</div>{{-- /#panel-about --}}


{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- PANEL: SETTINGS                                                        --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="panel-settings" style="display:none">
<div class="show-layout">

    <div class="show-main">

        <div class="show-card">
            <h2 class="show-card-title">Display Name</h2>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="profile-name" value="{{ $user->name }}" placeholder="Your name">
            </div>
            <button class="btn btn-primary btn-sm" onclick="saveName()">Save changes</button>
        </div>

        <div class="show-card">
            <h2 class="show-card-title">Change Password</h2>
            <div class="form-group">
                <label>Current password</label>
                <input type="password" id="cur-pass" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>New password</label>
                <input type="password" id="new-pass" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>Confirm new password</label>
                <input type="password" id="new-pass-confirm" placeholder="••••••••">
            </div>
            <button class="btn btn-primary btn-sm" onclick="savePassword()">Update password</button>
        </div>

        <div class="show-card danger-card">
            <h2 class="show-card-title" style="color:var(--accent2)">Danger Zone</h2>
            <p class="danger-desc">Permanently delete your account and all associated data. This cannot be undone.</p>
            <button class="btn btn-ghost btn-sm danger-btn" onclick="openDeleteModal()">Delete account</button>
        </div>

    </div>

    <div class="show-sidebar">
        <div class="show-card">
            <h2 class="show-card-title">Account Info</h2>
            <dl class="detail-list">
                <div class="detail-row">
                    <dt>User ID</dt>
                    <dd>#{{ $user->id }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Email</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Joined</dt>
                    <dd>{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div class="detail-row">
                    <dt>Last updated</dt>
                    <dd>{{ $user->updated_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

</div>
</div>{{-- /#panel-settings --}}


{{-- ── AVATAR CROP MODAL (unchanged) ───────────────────────────────────── --}}
<div class="modal-overlay" id="avatar-modal" style="display:none" onclick="if(event.target.id==='avatar-modal') closeAvatarModal()">
    <div class="modal" style="max-width:380px">
        <button class="modal-close" onclick="closeAvatarModal()">✕</button>
        <h2>Set Profile Photo</h2>
        <p class="modal-sub">Drag the image to reposition, then crop.</p>
        <div class="crop-zone" id="crop-zone">
            <img id="crop-img" src="" alt="" draggable="false">
            <div class="crop-circle-overlay"></div>
        </div>
        <input type="file" id="avatar-file-input" accept="image/png,image/jpeg,image/webp" style="display:none">
        <div style="display:flex;gap:.5rem;margin-top:1rem">
            <button class="btn btn-ghost btn-sm" style="flex:1" onclick="document.getElementById('avatar-file-input').click()">
                Choose image
            </button>
            <button class="btn btn-primary btn-sm" style="flex:1" onclick="saveAvatar()">
                Save photo
            </button>
        </div>
        <div class="auth-msg" id="avatar-msg" style="margin-top:.75rem"></div>
    </div>
</div>

{{-- ── DELETE ACCOUNT MODAL (unchanged) ────────────────────────────────── --}}
<div class="modal-overlay" id="delete-modal-overlay" onclick="if(event.target.id==='delete-modal-overlay') closeDeleteModal()" style="display:none">
    <div class="modal" id="delete-modal-box">
        <button class="modal-close" onclick="closeDeleteModal()">✕</button>
        <h2>Delete account?</h2>
        <p class="modal-sub">Enter your password to confirm. This action is permanent and cannot be reversed.</p>
        <div class="form-group">
            <label>Password</label>
            <input type="password" id="delete-pass" placeholder="••••••••">
        </div>
        <button class="btn btn-primary btn-sm" style="background:var(--accent);width:100%;padding:.75rem" onclick="confirmDelete()">
            Yes, delete my account
        </button>
        <div class="auth-msg" id="delete-msg" style="margin-top:.75rem"></div>
    </div>
</div>

<div class="notification" id="profile-notif"></div>

<script>
// ── XP bar ────────────────────────────────────────────────────────────────
setTimeout(() => {
    const bar = document.querySelector('.profile-xp-bar-fill');
    if (bar) bar.style.width = bar.dataset.xpTarget + '%';
}, 800);

// ── Tab switching (same pattern as show blade) ────────────────────────────
window.switchProfileTab = function (tab) {
    ['about', 'settings'].forEach(t => {
        document.getElementById('tab-' + t)?.classList.toggle('active', t === tab);
        const panel = document.getElementById('panel-' + t);
        if (panel) panel.style.display = t === tab ? '' : 'none';
    });
};

// ── Shared fetch helper ───────────────────────────────────────────────────
function profileFetch(url, method, body) {
    return fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  csrf,
        },
        body: JSON.stringify(body),
    });
}

function showProfileNotif(msg) {
    const n = document.getElementById('profile-notif');
    n.innerText = msg;
    n.classList.add('show');
    setTimeout(() => n.classList.remove('show'), 2800);
}

function showDeleteMsg(msg) {
    const el = document.getElementById('delete-msg');
    el.innerText     = msg;
    el.style.display = 'block';
}

// ── Bio ───────────────────────────────────────────────────────────────────
async function saveBio() {
    const bio = document.getElementById('about-bio').value.trim();
    const res = await profileFetch('/profile/bio', 'POST', { bio });
    if (res.ok) {
        showProfileNotif('Bio saved!');
    } else {
        const data = await res.json().catch(() => ({}));
        showProfileNotif(data.message || 'Failed to save bio.');
    }
}

// ── Favourite game ────────────────────────────────────────────────────────
let favDebounceTimer = null;

function favSearchDebounce() {
    clearTimeout(favDebounceTimer);
    favDebounceTimer = setTimeout(favSearchNow, 400);
}

async function favSearchNow() {
    const q = document.getElementById('fav-search-input').value.trim();
    if (!q) return;

    const resultsBox   = document.getElementById('fav-results');
    const inner        = document.getElementById('fav-results-inner');
    const noResults    = document.getElementById('fav-no-results');

    inner.innerHTML    = '<p style="color:var(--text-muted);font-size:.875rem">Searching…</p>';
    resultsBox.style.display = '';
    noResults.style.display  = 'none';

    try {
        const res  = await fetch(`/api/games/search?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        const data = await res.json();
        const games = data.results ?? [];

        inner.innerHTML = '';

        if (!games.length) {
            noResults.style.display = '';
            return;
        }

        games.slice(0, 8).forEach(game => {
            const btn = document.createElement('button');
            btn.type      = 'button';
            btn.className = 'store-btn';
            btn.style.cssText = 'display:flex;align-items:center;gap:.75rem;text-align:left;width:100%';

            const thumb = game.background_image
                ? `<img src="${game.background_image}" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:4px;flex-shrink:0">`
                : `<div style="width:42px;height:42px;border-radius:4px;background:var(--surface2);flex-shrink:0;display:flex;align-items:center;justify-content:center"><i class="ti ti-device-gamepad-2"></i></div>`;

            const released = game.released ? `<span style="font-size:.75rem;color:var(--text-muted)">${game.released.slice(0,4)}</span>` : '';

            btn.innerHTML = `${thumb}<span style="flex:1;min-width:0"><span style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(game.name)}</span>${released}</span>`;

            btn.addEventListener('click', () => selectFavGame(game));
            inner.appendChild(btn);
        });

    } catch {
        inner.innerHTML = '<p style="color:var(--text-muted);font-size:.875rem">Search failed. Please try again.</p>';
    }
}

async function selectFavGame(game) {
    const msg = document.getElementById('fav-msg');
    msg.style.display = 'none';

    const res  = await profileFetch('/profile/favourite-game', 'POST', {
        game_id:   game.id,
        game_name: game.name,
        game_cover: game.background_image ?? null,
    });

    if (res.ok) {
        // Update inline current-favourite display
        document.getElementById('fav-cover').src = game.background_image ?? '';
        document.getElementById('fav-name').textContent  = game.name;
        document.getElementById('fav-current').style.display = '';

        // Update sidebar card
        const sideCard  = document.getElementById('fav-sidebar-card');
        const sideLink  = document.getElementById('fav-sidebar-link');
        const sideName  = document.getElementById('fav-sidebar-name');
        sideCard.style.display = '';
        sideLink.href   = `/games/${game.id}`;
        sideName.textContent = game.name;

        // Clear search UI
        document.getElementById('fav-search-input').value = '';
        document.getElementById('fav-results').style.display = 'none';

        showProfileNotif('Favourite game saved!');
    } else {
        const data = await res.json().catch(() => ({}));
        msg.innerText     = data.message || 'Failed to save favourite game.';
        msg.style.display = 'block';
    }
}

async function removeFavGame() {
    const res = await profileFetch('/profile/favourite-game', 'DELETE', {});
    if (res.ok) {
        document.getElementById('fav-current').style.display       = 'none';
        document.getElementById('fav-sidebar-card').style.display  = 'none';
        showProfileNotif('Favourite game removed.');
    }
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Name & password ───────────────────────────────────────────────────────
async function saveName() {
    const name = document.getElementById('profile-name').value.trim();
    if (!name) return;
    const res  = await profileFetch('/profile/name', 'POST', { name });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
        showProfileNotif('Name updated!');
        document.querySelector('.profile-name').innerText     = name;
        document.getElementById('pub-name').textContent       = name;
    } else {
        showProfileNotif(data.message || 'Failed to update name.');
    }
}

async function savePassword() {
    const current_password      = document.getElementById('cur-pass').value;
    const password              = document.getElementById('new-pass').value;
    const password_confirmation = document.getElementById('new-pass-confirm').value;
    if (!current_password || !password || !password_confirmation) {
        showProfileNotif('Please fill in all password fields.');
        return;
    }
    const res  = await profileFetch('/profile/password', 'POST', { current_password, password, password_confirmation });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
        showProfileNotif('Password updated!');
        ['cur-pass','new-pass','new-pass-confirm'].forEach(id => document.getElementById(id).value = '');
    } else {
        showProfileNotif(data.message || 'Failed to update password.');
    }
}

// ── Delete account ────────────────────────────────────────────────────────
function openDeleteModal()  { document.getElementById('delete-modal-overlay').style.display = 'flex'; }
function closeDeleteModal() { document.getElementById('delete-modal-overlay').style.display = 'none'; }

async function confirmDelete() {
    const password = document.getElementById('delete-pass').value;
    if (!password) { showDeleteMsg('Please enter your password.'); return; }
    const res  = await profileFetch('/profile/delete', 'DELETE', { password });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
        window.location.href = '/';
    } else {
        showDeleteMsg(data.message || 'Incorrect password.');
    }
}

// ── Avatar crop ───────────────────────────────────────────────────────────
let cropState = {
    x: 0, y: 0,
    scale: 1,
    dragging: false,
    startX: 0, startY: 0,
    naturalW: 0, naturalH: 0,
    file: null
};

const ZONE = 300;

document.getElementById('avatar-edit-btn').addEventListener('click', () => {
    document.getElementById('avatar-modal').style.display = 'flex';
    if (!cropState.file) document.getElementById('avatar-file-input').click();
});

function closeAvatarModal() {
    document.getElementById('avatar-modal').style.display = 'none';
}

document.getElementById('avatar-file-input').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    cropState.file = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        const img = document.getElementById('crop-img');
        img.src = e.target.result;
        img.onload = () => {
            cropState.naturalW = img.naturalWidth;
            cropState.naturalH = img.naturalHeight;
            const fitScale = ZONE / Math.min(cropState.naturalW, cropState.naturalH);
            cropState.scale = fitScale;
            const renderedW = cropState.naturalW * fitScale;
            const renderedH = cropState.naturalH * fitScale;
            cropState.x = (ZONE - renderedW) / 2;
            cropState.y = (ZONE - renderedH) / 2;
            applyCrop();
        };
        document.getElementById('avatar-modal').style.display = 'flex';
    };
    reader.readAsDataURL(file);
});

const cropZone = document.getElementById('crop-zone');
cropZone.addEventListener('mousedown',  startDrag);
cropZone.addEventListener('touchstart', startDrag, { passive: true });

function startDrag(e) {
    cropState.dragging = true;
    const pt = e.touches ? e.touches[0] : e;
    cropState.startX = pt.clientX - cropState.x;
    cropState.startY = pt.clientY - cropState.y;
}

document.addEventListener('mousemove', onDrag);
document.addEventListener('touchmove', onDrag, { passive: true });
document.addEventListener('mouseup',   () => cropState.dragging = false);
document.addEventListener('touchend',  () => cropState.dragging = false);

function onDrag(e) {
    if (!cropState.dragging) return;
    const pt = e.touches ? e.touches[0] : e;
    cropState.x = pt.clientX - cropState.startX;
    cropState.y = pt.clientY - cropState.startY;
    applyCrop();
}

cropZone.addEventListener('wheel', (e) => {
    e.preventDefault();
    const delta    = e.deltaY > 0 ? 0.9 : 1.1;
    const minScale = ZONE / Math.max(cropState.naturalW, cropState.naturalH);
    const newScale = Math.max(minScale, Math.min(10, cropState.scale * delta));
    const cx = ZONE / 2, cy = ZONE / 2;
    cropState.x     = cx - (cx - cropState.x) * (newScale / cropState.scale);
    cropState.y     = cy - (cy - cropState.y) * (newScale / cropState.scale);
    cropState.scale = newScale;
    applyCrop();
}, { passive: false });

function applyCrop() {
    const img = document.getElementById('crop-img');
    img.style.width     = (cropState.naturalW * cropState.scale) + 'px';
    img.style.height    = (cropState.naturalH * cropState.scale) + 'px';
    img.style.transform = `translate(${cropState.x}px, ${cropState.y}px)`;
}

async function saveAvatar() {
    const msg = document.getElementById('avatar-msg');
    msg.style.display = 'none';
    if (!cropState.file) {
        msg.innerText = 'Please choose an image first.';
        msg.style.display = 'block';
        return;
    }
    const cropXNatural    = Math.round(-cropState.x / cropState.scale);
    const cropYNatural    = Math.round(-cropState.y / cropState.scale);
    const cropSizeNatural = Math.round(ZONE / cropState.scale);
    const formData = new FormData();
    formData.append('avatar',    cropState.file);
    formData.append('crop_x',    Math.max(0, cropXNatural));
    formData.append('crop_y',    Math.max(0, cropYNatural));
    formData.append('crop_size', cropSizeNatural);
    formData.append('_token',    csrf);
    const res  = await fetch('/profile/avatar', { method: 'POST', credentials: 'same-origin', body: formData });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
        closeAvatarModal();
        const wrap     = document.getElementById('avatar-wrap');
        const initials = document.getElementById('avatar-initials');
        let   imgEl    = document.getElementById('avatar-img');
        if (initials) initials.remove();
        if (!imgEl) {
            imgEl = document.createElement('img');
            imgEl.id = 'avatar-img'; imgEl.alt = 'Avatar'; imgEl.className = 'profile-avatar-img';
            wrap.prepend(imgEl);
        }
        imgEl.src = data.avatar_url;
        if (!document.getElementById('remove-avatar-btn')) {
            const removeBtn = document.createElement('button');
            removeBtn.id = 'remove-avatar-btn'; removeBtn.className = 'btn btn-ghost btn-sm';
            removeBtn.style.cssText = 'margin-top:.5rem;font-size:.75rem';
            removeBtn.textContent = 'Remove photo'; removeBtn.onclick = removeAvatar;
            document.querySelector('.profile-hero-inner > div:last-child').appendChild(removeBtn);
        }
        showProfileNotif('Profile photo updated!');
    } else {
        msg.innerText = data.message ? data.message : data.errors ? JSON.stringify(data.errors) : 'Upload failed.';
        msg.style.display = 'block';
    }
}

async function removeAvatar() {
    const res = await fetch('/profile/avatar', {
        method: 'DELETE', credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });
    if (res.ok) {
        const imgEl = document.getElementById('avatar-img');
        const wrap  = document.getElementById('avatar-wrap');
        const name  = document.getElementById('profile-display-name')?.innerText || '?';
        if (imgEl) imgEl.remove();
        const initials = document.createElement('div');
        initials.id = 'avatar-initials'; initials.className = 'profile-avatar-lg';
        initials.textContent = name[0].toUpperCase();
        wrap.prepend(initials);
        document.getElementById('remove-avatar-btn')?.remove();
        cropState.file = null;
        showProfileNotif('Profile photo removed.');
    }
}
</script>

@endsection
