@extends('layouts.app')

@section('content')

<div class="profile-hero">
    <div class="profile-hero-inner">

        {{-- Avatar --}}
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
</div>

{{-- Avatar crop modal --}}
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

<div class="profile-layout">

    {{-- LEFT: Settings forms --}}
    <div class="profile-main">

        {{-- Change name --}}
        <div class="show-card">
            <h2 class="show-card-title">Display Name</h2>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="profile-name" value="{{ $user->name }}" placeholder="Your name">
            </div>
            <button class="btn btn-primary btn-sm" onclick="saveName()">Save changes</button>
        </div>

        {{-- Change password --}}
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

        {{-- Danger zone --}}
        <div class="show-card danger-card">
            <h2 class="show-card-title" style="color:var(--accent2)">Danger Zone</h2>
            <p class="danger-desc">Permanently delete your account and all associated data. This cannot be undone.</p>
            <button class="btn btn-ghost btn-sm danger-btn" onclick="openDeleteModal()">Delete account</button>
        </div>

    </div>

    {{-- RIGHT: Account info --}}
    <div class="profile-sidebar">

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

        <div class="show-card">
            <h2 class="show-card-title">Quick Links</h2>
            <div class="store-list">
                <a href="{{ route('games.index') }}" class="store-btn">Browse Games</a>
                <a href="{{ route('home') }}" class="store-btn">Home</a>
            </div>
        </div>

    </div>
</div>

{{-- Delete account confirmation modal --}}
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
// ── Profile utilities ────────────────────────────────
function profileFetch(url, method, body) {
    return fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
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
    el.innerText = msg;
    el.style.display = 'block';
}

// ── Name & password ──────────────────────────────────
async function saveName() {
    const name = document.getElementById('profile-name').value.trim();
    if (!name) return;
    const res  = await profileFetch('/profile/name', 'POST', { name });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
        showProfileNotif('Name updated!');
        document.querySelector('.profile-name').innerText = name;
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
        document.getElementById('cur-pass').value        = '';
        document.getElementById('new-pass').value        = '';
        document.getElementById('new-pass-confirm').value = '';
    } else {
        showProfileNotif(data.message || 'Failed to update password.');
    }
}

// ── Delete account ───────────────────────────────────
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

// ── Avatar crop ──────────────────────────────────────
let cropState = {
    x: 0, y: 0,        // offset of image top-left from crop zone top-left
    scale: 1,          // zoom multiplier
    dragging: false,
    startX: 0, startY: 0,
    naturalW: 0, naturalH: 0,  // actual image pixel dimensions
    file: null
};

const ZONE = 300; // crop zone size in px

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

            // fit the shortest side to the zone so image fills the circle
            const fitScale = ZONE / Math.min(cropState.naturalW, cropState.naturalH);
            cropState.scale = fitScale;

            // centre the image in the zone
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

// drag
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

// scroll to zoom
cropZone.addEventListener('wheel', (e) => {
    e.preventDefault();
    const delta      = e.deltaY > 0 ? 0.9 : 1.1;
    const minScale   = ZONE / Math.max(cropState.naturalW, cropState.naturalH);
    const newScale   = Math.max(minScale, Math.min(10, cropState.scale * delta));

    // zoom toward centre of zone
    const cx         = ZONE / 2;
    const cy         = ZONE / 2;
    cropState.x      = cx - (cx - cropState.x) * (newScale / cropState.scale);
    cropState.y      = cy - (cy - cropState.y) * (newScale / cropState.scale);
    cropState.scale  = newScale;

    applyCrop();
}, { passive: false });

function applyCrop() {
    const img    = document.getElementById('crop-img');
    const renderW = cropState.naturalW * cropState.scale;
    const renderH = cropState.naturalH * cropState.scale;
    img.style.width     = renderW + 'px';
    img.style.height    = renderH + 'px';
    img.style.transform = `translate(${cropState.x}px, ${cropState.y}px)`;
}

async function saveAvatar() {
    const msg = document.getElementById('avatar-msg');
    msg.style.display = 'none';

    if (!cropState.file) {
        msg.innerText     = 'Please choose an image first.';
        msg.style.display = 'block';
        return;
    }

    // convert screen coords back to actual image pixel coords
    const cropXNatural    = Math.round(-cropState.x / cropState.scale);
    const cropYNatural    = Math.round(-cropState.y / cropState.scale);
    const cropSizeNatural = Math.round(ZONE / cropState.scale);

    const formData = new FormData();
    formData.append('avatar',    cropState.file);
    formData.append('crop_x',    Math.max(0, cropXNatural));
    formData.append('crop_y',    Math.max(0, cropYNatural));
    formData.append('crop_size', cropSizeNatural);
    formData.append('_token',    csrf);

    const res  = await fetch('/profile/avatar', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
    });

    const data = await res.json().catch(() => ({}));

    if (res.ok) {
        closeAvatarModal();

        const wrap     = document.getElementById('avatar-wrap');
        const initials = document.getElementById('avatar-initials');
        let   imgEl    = document.getElementById('avatar-img');

        if (initials) initials.remove();
        if (!imgEl) {
            imgEl           = document.createElement('img');
            imgEl.id        = 'avatar-img';
            imgEl.alt       = 'Avatar';
            imgEl.className = 'profile-avatar-img';
            wrap.prepend(imgEl);
        }
        imgEl.src = data.avatar_url;

        if (!document.getElementById('remove-avatar-btn')) {
            const removeBtn         = document.createElement('button');
            removeBtn.id            = 'remove-avatar-btn';
            removeBtn.className     = 'btn btn-ghost btn-sm';
            removeBtn.style.cssText = 'margin-top:.5rem;font-size:.75rem';
            removeBtn.textContent   = 'Remove photo';
            removeBtn.onclick       = removeAvatar;
            document.querySelector('.profile-hero-inner > div:last-child').appendChild(removeBtn);
        }

        showProfileNotif('Profile photo updated!');
    } else {
        msg.innerText     = data.message
            ? data.message
            : data.errors
                ? JSON.stringify(data.errors)
                : 'Upload failed.';
        msg.style.display = 'block';
    }
}

async function removeAvatar() {
    const res = await fetch('/profile/avatar', {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });

    if (res.ok) {
        const imgEl = document.getElementById('avatar-img');
        const wrap  = document.getElementById('avatar-wrap');
        const name  = document.getElementById('profile-display-name')?.innerText || '?';

        if (imgEl) imgEl.remove();

        const initials       = document.createElement('div');
        initials.id          = 'avatar-initials';
        initials.className   = 'profile-avatar-lg';
        initials.textContent = name[0].toUpperCase();
        wrap.prepend(initials);

        document.getElementById('remove-avatar-btn')?.remove();
        cropState.file = null;

        showProfileNotif('Profile photo removed.');
    }
}
</script>

@endsection
