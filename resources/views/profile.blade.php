@extends('layouts.app')

@section('content')

<div class="profile-hero">
    <div class="profile-hero-inner">
        <div class="profile-avatar-lg">
            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' '), 1, 1)) }}
        </div>
        <div>
            <h1 class="profile-name">{{ $user->name }}</h1>
            <p class="profile-email">{{ $user->email }}</p>
            <p class="profile-joined">Member since {{ $user->created_at->format('F Y') }}</p>
        </div>
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
const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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
    const current_password  = document.getElementById('cur-pass').value;
    const password          = document.getElementById('new-pass').value;
    const password_confirmation = document.getElementById('new-pass-confirm').value;
    if (!current_password || !password || !password_confirmation) {
        showProfileNotif('Please fill in all password fields.');
        return;
    }
    const res  = await profileFetch('/profile/password', 'POST', { current_password, password, password_confirmation });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
        showProfileNotif('Password updated!');
        document.getElementById('cur-pass').value = '';
        document.getElementById('new-pass').value = '';
        document.getElementById('new-pass-confirm').value = '';
    } else {
        showProfileNotif(data.message || 'Failed to update password.');
    }
}

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
</script>

@endsection
