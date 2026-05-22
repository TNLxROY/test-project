// app.js

// -------------------------
// CSRF
// -------------------------
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

// -------------------------
// FETCH WRAPPER
// -------------------------
async function authFetch(url, options = {}) {
    return fetch(url, {
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            ...(options.headers || {})
        },
        ...options
    });
}

// -------------------------
// MODAL
// -------------------------
window.openModal = (tab = 'login') => {
    document.getElementById('modal-overlay').style.display = 'flex';
    switchTab(tab);
    clearMsg();
};

window.closeModal = () => {
    document.getElementById('modal-overlay').style.display = 'none';
};

window.handleOverlayClick = (e) => {
    if (e.target.id === 'modal-overlay') closeModal();
};

window.switchTab = (tab) => {
    const login       = document.getElementById('form-login');
    const register    = document.getElementById('form-register');
    const loginTab    = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');

    loginTab.classList.toggle('active', tab === 'login');
    registerTab.classList.toggle('active', tab === 'register');
    login.style.display    = tab === 'login'    ? 'block' : 'none';
    register.style.display = tab === 'register' ? 'block' : 'none';
};

// -------------------------
// NOTIFICATIONS
// -------------------------
window.showNotif = (msg) => {
    const n = document.getElementById('notif');
    if (!n) return;
    n.innerText = msg;
    n.style.display = 'block';
    setTimeout(() => { n.style.display = 'none'; }, 2500);
};

function showMsg(msg) {
    const el = document.getElementById('auth-msg');
    if (el) el.innerText = msg;
}

function clearMsg() {
    showMsg('');
}

// -------------------------
// RENDER UI (single function that updates the nav)
// -------------------------
function renderUser(user) {
    const container = document.getElementById('mini-profile-container');
    if (!container) return;

    if (!user) {
        container.innerHTML = `
            <div class="nav-cta">
                <button class="btn btn-ghost btn-sm" data-action="open-login">Log in</button>
                <button class="btn btn-primary btn-sm" data-action="open-register">Sign up</button>
            </div>
        `;
        // hide dropdown if visible
        const dd = document.getElementById('user-dropdown');
        if (dd) dd.classList.remove('open');
        return;
    }

    const name = user.name || '';
    const email = user.email || '';
    const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

    // populate dropdown header
    const dName = document.getElementById('dropdown-name');
    const dEmail = document.getElementById('dropdown-email');
    if (dName)  dName.innerText  = name;
    if (dEmail) dEmail.innerText = email;

    container.innerHTML = `
        <div class="user-chip" id="user-chip">
            <div class="avatar">${initials}</div>
            <span class="username">${name}</span>
            <button class="kebab-btn" id="kebab-btn" aria-label="User menu">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </button>
        </div>
    `;

    // toggle dropdown on kebab click
    document.getElementById('kebab-btn').addEventListener('click', (e) => {
        e.stopPropagation();
        const chip = document.getElementById('user-chip');
        const dd   = document.getElementById('user-dropdown');
        if (!dd || !chip) return;

        const open = dd.classList.toggle('open');

        if (open) {
            // position under the chip
            const rect = chip.getBoundingClientRect();
            dd.style.top   = (rect.bottom + window.scrollY + 8) + 'px';
            dd.style.right = (window.innerWidth - rect.right) + 'px';
        }
    });
}

// close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const dd = document.getElementById('user-dropdown');
    if (dd && !dd.contains(e.target)) dd.classList.remove('open');
});

// Only used on initial page load to check if user is already logged in.
// After login/logout we use renderUser() directly instead of asking the server again.
async function refreshAuthUI() {
    try {
        const res  = await authFetch('/api/user?_=' + Date.now());
        const data = await res.json().catch(() => ({}));
        renderUser(data?.user ?? null);
    } catch (err) {
        console.error('refreshAuthUI error', err);
    }
}

// -------------------------
// EVENT DELEGATION (login/register buttons + logout)
// -------------------------
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;

    if (action === 'open-login')    return openModal('login');
    if (action === 'open-register') return openModal('register');

    if (action === 'logout') {
        try {
            const res = await authFetch('/logout', { method: 'POST' });
            if (res.ok) {
                showNotif('Logged out!');
                renderUser(null); // we know the user is gone — no need to ask the server
                location.reload();
            }
        } catch (err) {
            console.error('Logout error', err);
        }
    }
});

// -------------------------
// LOGIN
// -------------------------
window.doLogin = async () => {
    const email    = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-pass').value;

    if (!email || !password) {
        showMsg('Please enter email and password.');
        return;
    }

    try {
        const res  = await authFetch('/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            showNotif('Logged in!');
            closeModal();
            renderUser(data.user); // use the user data already in the login response
            location.reload();
        } else {
            showMsg(data.message || 'Login failed.');
        }

    } catch (err) {
        console.error('Login error', err);
        showMsg('Something went wrong. Please try again.');
    }
};

// -------------------------
// REGISTER
// -------------------------
window.doRegister = async () => {
    const name     = document.getElementById('reg-user').value.trim();
    const email    = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-pass').value;

    if (!name || !email || !password) {
        showMsg('Please fill in all fields.');
        return;
    }

    try {
        const res  = await authFetch('/register', {
            method: 'POST',
            body: JSON.stringify({ name, email, password })
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            showNotif('Account created!');
            closeModal();
            renderUser(data.user); // use the user data already in the register response
            location.reload();
        } else {
            const firstError = data.errors
                ? Object.values(data.errors)[0]?.[0]
                : null;
            showMsg(firstError || data.message || 'Registration failed.');
        }

    } catch (err) {
        console.error('Register error', err);
        showMsg('Something went wrong. Please try again.');
    }
};

// -------------------------
// INIT — check login state on page load
// -------------------------
document.addEventListener('DOMContentLoaded', refreshAuthUI);
