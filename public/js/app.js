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
// STYLED USERNAME (random red character)
// -------------------------
function styledUsername(name) {
    if (!name) return '';
    return `<span style="color:var(--accent);font-weight:inherit;font-size:inherit;font-family:inherit;line-height:inherit">${name[0]}</span>${name.slice(1)}`;
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
        const dd = document.getElementById('user-dropdown');
        if (dd) dd.classList.remove('open');
        return;
    }

    const name     = user.name || '';
    const email    = user.email || '';
    const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

    const dName  = document.getElementById('dropdown-name');
    const dEmail = document.getElementById('dropdown-email');
    if (dName)  dName.innerText  = name;
    if (dEmail) dEmail.innerText = email;

    container.innerHTML = `
        <div class="user-chip" id="user-chip">
            <a href="/profile" class="user-chip-link">
                ${user.avatar_url
                    ? `<img src="${user.avatar_url}" class="avatar avatar-img-chip" alt="${name}">`
                    : `<div class="avatar">${initials}</div>`
                }
                <span class="username">${styledUsername(name)}</span>
            </a>
            <button class="kebab-btn" id="kebab-btn" aria-label="User menu">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </button>
        </div>
    `;

    document.getElementById('kebab-btn').addEventListener('click', (e) => {
        e.stopPropagation();
        const dd = document.getElementById('user-dropdown');
        if (dd) dd.classList.toggle('open');
    });

    // ── Hero greeting (home page only) ──────────────────
    const greetingEl = document.getElementById('hero-greeting');
    if (greetingEl) {
        const hour = new Date().getHours();
        let greeting;

        if (hour >= 5  && hour < 12) greeting = 'Good morning';
        else if (hour >= 12 && hour < 18) greeting = 'Good afternoon';
        else if (hour >= 18 && hour < 23) greeting = 'Good evening';
        else                              greeting = 'Good night';

        greetingEl.innerHTML = name
            ? `${greeting}, <em>${styledUsername(name)}</em>`
            : greeting;
    }
}

// close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const dd = document.getElementById('user-dropdown');
    if (dd && !dd.contains(e.target)) dd.classList.remove('open');
});

// Only used on initial page load to check if user is already logged in.
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
                renderUser(null);
                const protectedPaths = ['/friends', '/profile'];
                const isProtected = protectedPaths.some(p => window.location.pathname.startsWith(p));
                if (isProtected) {
                    window.location.href = '/';
                } else {
                    location.reload();
                }
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
            renderUser(data.user);
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
            renderUser(data.user);
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

window.switchShowTab = function(tab) {
    ['info','reviews','write'].forEach(t => {
        document.getElementById('panel-' + t).style.display = t === tab ? 'block' : 'none';
        const btn = document.getElementById('tab-' + t);
        if (btn) btn.classList.toggle('active', t === tab);
    });
}

// character counter
const textarea = document.getElementById('review-body');
if (textarea) {
    textarea.addEventListener('input', () => {
        document.getElementById('char-count').textContent = textarea.value.length;
    });
}

window.submitReview = async function(gameId, gameName) {
    const body = document.getElementById('review-body')?.value.trim();
    const msg  = document.getElementById('review-msg');

    if (!body || body.length < 10) {
        msg.innerText = 'Review must be at least 10 characters.';
        msg.style.display = 'block';
        return;
    }

    const res = await fetch(`/games/${gameId}/reviews`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify({ body, game_name: gameName }),
    });

    const data = await res.json().catch(() => ({}));

    if (res.ok) {
        const list  = document.querySelector('#panel-reviews .reviews-layout');
        const empty = list?.querySelector('.reviews-empty');
        if (empty) empty.remove();

        const card = document.createElement('div');
        card.className = 'review-card';
        card.id = 'review-' + data.review.id;
        card.innerHTML = `
            <div class="review-header">
                <div class="review-avatar">${data.review.user.name[0].toUpperCase()}</div>
                <div>
                    <div class="review-author">${data.review.user.name}</div>
                    <div class="review-date">${data.review.created_at}</div>
                </div>
                <button class="review-delete-btn" onclick="deleteReview(${data.review.id}, ${gameId})">
                    <i class="ti ti-trash" aria-hidden="true"></i>
                </button>
            </div>
            <p class="review-body">${data.review.body}</p>
        `;
        list.prepend(card);

        const badge = document.getElementById('review-count-badge');
        if (badge) badge.textContent = parseInt(badge.textContent || 0) + 1;

        const writeBtn = document.getElementById('tab-write');
        if (writeBtn) {
            writeBtn.outerHTML = `<span class="reviewed-badge"><i class="ti ti-circle-check"></i> You reviewed this</span>`;
        }

        switchShowTab('reviews');
    } else {
        msg.innerText = data.message || 'Failed to post review.';
        msg.style.display = 'block';
    }
}

window.deleteReview = async function(reviewId, gameId) {
    const res = await fetch(`/games/${gameId}/reviews/${reviewId}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
    });

    if (res.ok) {
        document.getElementById('review-' + reviewId)?.remove();
        const badge = document.getElementById('review-count-badge');
        if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent || 1) - 1);
    }
}

// -------------------------
// HAMBURGER NAV
// -------------------------
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('nav-links');

    if (!hamburger || !navLinks) return;

    hamburger.addEventListener('click', (e) => {
        e.stopPropagation();
        navLinks.classList.toggle('nav-open');
        hamburger.classList.toggle('hamburger-open');
    });

    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('nav-open');
            hamburger.classList.remove('hamburger-open');
        });
    });

    document.addEventListener('click', (e) => {
        if (!navLinks.contains(e.target) && !hamburger.contains(e.target)) {
            navLinks.classList.remove('nav-open');
            hamburger.classList.remove('hamburger-open');
        }
    });
});

// -------------------------
// STYLED USERNAMES ON USERS PAGE
// -------------------------
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.player-name').forEach(el => {
        el.innerHTML = styledUsername(el.innerText.trim());
    });
});

document.addEventListener('DOMContentLoaded', () => {

    const platformButtons = document.querySelectorAll(
        '.platform-expand-btn'
    );

    platformButtons.forEach(button => {

        button.addEventListener('click', (e) => {

            e.preventDefault();
            e.stopPropagation();

            const platformItem =
                button.closest('.platform-item');

            platformItem.classList.toggle('active');
        });

    });

});
