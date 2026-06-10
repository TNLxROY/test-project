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
    if (!el) return;
    el.innerText      = msg;
    el.style.display  = msg ? 'block' : 'none';
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
            body: JSON.stringify({ name, email, password, password_confirmation: password })
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

        // Build star HTML for the rating
        const rating = data.review.rating || 0;
        let starsHtml = '';
        for (let s = 1; s <= 10; s++) {
            let cls = 'review-star';
            if (s <= Math.floor(rating)) cls += ' lit';
            else if (s === Math.ceil(rating) && rating % 1 > 0) cls += ' lit-partial';
            starsHtml += `<span class="${cls}"><i class="ti ti-star"></i></span>`;
        }

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
            ${rating ? `<div class="review-rating-row">
                <div class="review-stars">${starsHtml}</div>
                <span class="review-score">${rating}</span>
                ${data.review.is_detailed ? '<span class="review-detailed-badge">Detailed</span>' : ''}
            </div>` : ''}
            ${data.review.body ? `<p class="review-body">${data.review.body}</p>` : ''}
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

        // Restore "Write a Review" tab
        const reviewedBadge = document.querySelector('.reviewed-badge');
        if (reviewedBadge) {
            const btn = document.createElement('button');
            btn.className = 'show-tab show-tab-accent';
            btn.id = 'tab-write';
            btn.innerHTML = '<i class="ti ti-pencil" aria-hidden="true"></i> Write a Review';
            btn.addEventListener('click', () => switchShowTab('write'));
            reviewedBadge.replaceWith(btn);
        }

        // Show empty state if no reviews remain
        const list = document.querySelector('#panel-reviews .reviews-layout');
        if (list && !list.querySelector('.review-card')) {
            list.innerHTML = `
                <div class="reviews-empty">
                    <i class="ti ti-message-off reviews-empty-icon" aria-hidden="true"></i>
                    <h3>No reviews yet</h3>
                    <p>Be the first to share your thoughts.</p>
                </div>`;
        }
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

// -------------------------
// WRITE REVIEW WIDGET
// -------------------------
(function () {

    const WR = {
        mode: 'simple',
        simpleRating: 0,
        overallRating: 0,
        autoAvg: true,
        categories: [],
        nextCatId: 1,
    };

    const RATING_LABELS = ['','Terrible','Bad','Poor','Mediocre','Average','Decent','Good','Great','Excellent','Masterpiece'];
    const DEFAULT_CATS  = ['Story','Gameplay','Graphics','Soundtrack','Characters'];

    /* ── Init ── */
    window.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('panel-write')) return; // only on game show page
        DEFAULT_CATS.forEach(name => addCategory(name, 0));
        renderCategories();
        updateDetailedScore();
        initMainStarRows();
    });

    /* ── Mode toggle ── */
    window.wrToggleMode = function () {
        WR.mode = WR.mode === 'simple' ? 'detailed' : 'simple';
        document.getElementById('wr-simple').style.display   = WR.mode === 'simple'   ? '' : 'none';
        document.getElementById('wr-detailed').style.display = WR.mode === 'detailed' ? '' : 'none';
        const icon  = document.getElementById('wr-toggle-icon');
        const label = document.getElementById('wr-toggle-label');
        icon.className    = WR.mode === 'detailed' ? 'ti ti-star' : 'ti ti-list-details';
        label.textContent = WR.mode === 'detailed' ? 'Simple review' : 'Detailed review';
    };

    /* ── Shared star+input logic ── */

    // Calculate a decimal rating from a mousemove/click event within a star button.
    // Each star represents 1 integer. The cursor x-position within the star maps
    // to tenths: 0–10% = .1, 10–20% = .2 … 90–100% = 1.0 (the full integer).
    function ratingFromEvent(e, starBtn) {
        const rect   = starBtn.getBoundingClientRect();
        const pct    = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        const tenth  = Math.max(1, Math.round(pct * 10)) / 10;  // 0.1 – 1.0
        const whole  = parseInt(starBtn.dataset.val);
        return Math.round((whole - 1 + tenth) * 10) / 10;       // e.g. star 7 at 30% → 6.3
    }

    // Attach precision star listeners to a star row container.
    // onCommit(val) called on click, onPreview(val) on move, onReset() on leave.
    function initPrecisionStars(container, onCommit, onPreview, onReset) {
        container.querySelectorAll('.wr-star, .wr-cat-star').forEach(btn => {
            btn.addEventListener('mousemove', e => {
                const val = ratingFromEvent(e, btn);
                onPreview(val);
                paintStarsFill(container, val);
            });
            btn.addEventListener('mouseleave', () => {
                onReset();
            });
            btn.addEventListener('click', e => {
                const val = ratingFromEvent(e, btn);
                onCommit(val);
            });
        });
    }

    // Paint stars using lit/lit-partial classes — simple and bulletproof
    function paintStarsFill(container, val) {
        if (!container) return;
        container.querySelectorAll('.wr-star, .wr-cat-star').forEach(btn => {
            const v = parseInt(btn.dataset.val);
            btn.classList.remove('lit', 'lit-partial');
            if (v <= Math.floor(val)) {
                btn.classList.add('lit');
            } else if (v === Math.ceil(val) && val % 1 > 0) {
                btn.classList.add('lit-partial');
            }
        });
    }

    // Init precision stars for simple and overall rows on DOMContentLoaded
    function initMainStarRows() {
        ['simple', 'overall'].forEach(key => {
            const container = document.getElementById(starRowId(key));
            if (!container) return;
            initPrecisionStars(
                container,
                val => { // click → commit
                    setRatingRaw(key, val);
                    paintStarsFill(container, val);
                    syncInput(key, val);
                    syncLabel(key, val);
                    if (key === 'overall') updateDetailedScore();
                },
                val => { // mousemove → preview input box while hovering
                    syncInput(key, val);
                    syncLabel(key, val);
                },
                () => { // mouseleave → restore to committed rating
                    const saved = getRating(key);
                    paintStarsFill(container, saved);
                    syncInput(key, saved || '');
                    syncLabel(key, saved);
                }
            );
        });
    }

    // Typing in the input box — accepts 1 decimal, updates stars and label
    window.wrInputChange = function (key, input) {
        const val = parseFloat(input.value);
        if (isNaN(val) || input.value === '') {
            setRatingRaw(key, 0);
            const c = document.getElementById(starRowId(key));
            if (c) paintStarsFill(c, 0);
            syncLabel(key, 0);
            if (key === 'overall') updateDetailedScore();
            return;
        }
        const clamped = Math.min(10, Math.max(1, Math.round(val * 10) / 10));
        setRatingRaw(key, clamped);
        const c = document.getElementById(starRowId(key));
        if (c) paintStarsFill(c, clamped);
        syncLabel(key, clamped);
        if (key === 'overall') updateDetailedScore();
    };

    function setRatingRaw(key, val) {
        if      (key === 'simple')  WR.simpleRating  = val;
        else if (key === 'overall') WR.overallRating = val;
    }

    function getRating(key) {
        if (key === 'simple')  return WR.simpleRating;
        if (key === 'overall') return WR.overallRating;
        return 0;
    }

    function starRowId(key) {
        if (key === 'simple')  return 'wr-stars';
        if (key === 'overall') return 'wr-overall-stars';
        return null;
    }

    function syncInput(key, val) {
        const id = key === 'simple' ? 'wr-simple-input' : 'wr-overall-input';
        const el = document.getElementById(id);
        if (el) el.value = val || '';
    }

    function syncLabel(key, val) {
        if (key !== 'simple') return;
        const lbl = document.getElementById('wr-simple-label');
        if (lbl) lbl.textContent = RATING_LABELS[Math.round(val)] || '\u00a0';
    }

    /* ── Category helpers ── */
    function addCategory(name, rating) {
        WR.categories.push({ id: WR.nextCatId++, name: name || '', rating: rating || 0, note: '' });
    }

    window.wrAddCategory = function () {
        addCategory('', 0);
        renderCategories();
        updateDetailedScore();
        const inputs = document.querySelectorAll('.wr-category-name');
        if (inputs.length) inputs[inputs.length - 1].focus();
    };

    function removeCategory(id) {
        WR.categories = WR.categories.filter(c => c.id !== id);
        renderCategories();
        updateDetailedScore();
    }

    function setCatRating(id, val) {
        const cat = WR.categories.find(c => c.id === id);
        if (!cat) return;
        cat.rating = Math.round(val * 10) / 10;
        updateDetailedScore();
    }

    /* ── Render categories ── */
    function renderCategories() {
        const container = document.getElementById('wr-categories');
        if (!container) return;
        container.innerHTML = '';

        WR.categories.forEach(cat => {
            const row = document.createElement('div');
            row.className = 'wr-category-row';

            // Name input
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'wr-category-name';
            inp.placeholder = 'Category name…';
            inp.value = cat.name;
            inp.maxLength = 40;
            inp.addEventListener('input', e => {
                const c = WR.categories.find(c => c.id === cat.id);
                if (c) c.name = e.target.value;
            });

            // Stars + input wrapper
            const widget = document.createElement('div');
            widget.className = 'wr-cat-widget';

            const starsWrap = document.createElement('div');
            starsWrap.className = 'wr-cat-stars';

            // Score input (declared before stars so star click can reference it)
            const scoreInp = document.createElement('input');
            scoreInp.type = 'number';
            scoreInp.className = 'wr-cat-input';
            scoreInp.min = 1; scoreInp.max = 10; scoreInp.step = 0.1;
            scoreInp.placeholder = '—';
            scoreInp.value = cat.rating || '';
            scoreInp.addEventListener('input', () => {
                const v = parseFloat(scoreInp.value);
                if (isNaN(v) || scoreInp.value === '') {
                    setCatRating(cat.id, 0);
                    paintStarsFill(starsWrap, 0);
                    return;
                }
                const clamped = Math.min(10, Math.max(1, Math.round(v * 10) / 10));
                setCatRating(cat.id, clamped);
                paintStarsFill(starsWrap, clamped);
            });

            for (let i = 1; i <= 10; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'wr-cat-star';
                btn.dataset.val = i;
                btn.innerHTML = '<i class="ti ti-star" aria-hidden="true"></i>';
                starsWrap.appendChild(btn);
            }
            // attach precision listeners after all stars are in the DOM
            initPrecisionStars(
                starsWrap,
                val => { // click → commit
                    setCatRating(cat.id, val);
                    scoreInp.value = val;
                    paintStarsFill(starsWrap, val);
                },
                val => { // mousemove → preview
                    scoreInp.value = val;
                    paintStarsFill(starsWrap, val);
                },
                () => { // mouseleave → restore
                    paintStarsFill(starsWrap, cat.rating);
                    scoreInp.value = cat.rating || '';
                }
            );

            // Remove button
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'wr-cat-remove';
            rm.setAttribute('aria-label', 'Remove category');
            rm.innerHTML = '<i class="ti ti-x" aria-hidden="true"></i>';
            rm.addEventListener('click', () => removeCategory(cat.id));

            // Note toggle button
            const noteToggle = document.createElement('button');
            noteToggle.type = 'button';
            noteToggle.className = 'wr-cat-note-toggle';
            noteToggle.setAttribute('aria-label', 'Add a note');
            noteToggle.title = 'Add a note';
            noteToggle.innerHTML = '<i class="ti ti-notes" aria-hidden="true"></i>';

            // Note textarea (hidden by default, shown if already has text)
            const noteArea = document.createElement('textarea');
            noteArea.className = 'wr-cat-note-area';
            noteArea.placeholder = 'Optional note for this category…';
            noteArea.maxLength = 500;
            noteArea.value = cat.note || '';
            noteArea.style.display = cat.note ? 'block' : 'none';
            noteToggle.classList.toggle('active', !!cat.note);

            noteToggle.addEventListener('click', () => {
                const isOpen = noteArea.style.display === 'block';
                noteArea.style.display = isOpen ? 'none' : 'block';
                noteToggle.classList.toggle('active', !isOpen);
                if (!isOpen) noteArea.focus();
            });

            noteArea.addEventListener('input', () => {
                const c = WR.categories.find(c => c.id === cat.id);
                if (c) c.note = noteArea.value;
            });

            widget.appendChild(starsWrap);
            widget.appendChild(scoreInp);
            row.appendChild(inp);
            row.appendChild(widget);
            row.appendChild(noteToggle);
            row.appendChild(rm);
            container.appendChild(row);

            // Note area sits below the row, full width
            const noteWrap = document.createElement('div');
            noteWrap.className = 'wr-cat-note-wrap';
            noteWrap.appendChild(noteArea);
            container.appendChild(noteWrap);
        });
    }

    // paintCatStars replaced by paintStarsFill + initPrecisionStars

    /* ── Detailed overall ── */
    function updateDetailedScore() {
        const el   = document.getElementById('wr-detailed-score');
        const hint = document.getElementById('wr-avg-hint');
        if (!el) return;

        if (WR.autoAvg) {
            const rated = WR.categories.filter(c => c.rating > 0);
            if (!rated.length) { el.textContent = '—'; hint.textContent = 'Average of your categories'; return; }
            const avg     = rated.reduce((s, c) => s + c.rating, 0) / rated.length;
            const rounded = Math.round(avg * 100) / 100;
            el.textContent = parseFloat(rounded.toFixed(2));
            hint.textContent = `Average of ${rated.length} categor${rated.length === 1 ? 'y' : 'ies'}`;
        } else {
            el.textContent   = WR.overallRating || '—';
            hint.textContent = 'Manual overall score';
        }
    }

    window.wrToggleAutoAvg = function () {
        WR.autoAvg = document.getElementById('wr-auto-avg').checked;
        document.getElementById('wr-manual-overall').style.display = WR.autoAvg ? 'none' : '';
        if (WR.autoAvg) {
            WR.overallRating = 0;
            syncInput('overall', 0);
            const overallContainer = document.getElementById('wr-overall-stars');
        if (overallContainer) paintStarsFill(overallContainer, 0);
        }
        updateDetailedScore();
    };

    /* ── Char count ── */
    window.wrUpdateCharCount = function (textareaId, counterId) {
        const ta = document.getElementById(textareaId);
        const ct = document.getElementById(counterId);
        if (ta && ct) ct.textContent = ta.value.length;
    };

    /* ── Submit ── */
    window.wrSubmit = function (gameId, gameName, gameCover) {
        const msgEl = document.getElementById('review-msg');
        msgEl.textContent = '';
        msgEl.style.display = 'none';

        let rating, body, categories = null;

        if (WR.mode === 'simple') {
            if (!WR.simpleRating) { wrShowMsg('Please select a rating before posting.', true); return; }
            rating = Math.round(WR.simpleRating * 10) / 10;
            body   = document.getElementById('wr-simple-body').value.trim();
        } else {
            const rated = WR.categories.filter(c => c.rating > 0);
            if (WR.autoAvg) {
                if (!rated.length) { wrShowMsg('Please rate at least one category.', true); return; }
                const avg = rated.reduce((s, c) => s + c.rating, 0) / rated.length;
                rating = Math.round(avg * 100) / 100;
            } else {
                if (!WR.overallRating) { wrShowMsg('Please set an overall score.', true); return; }
                rating = Math.round(WR.overallRating * 10) / 10;
            }
            body       = document.getElementById('wr-detailed-body').value.trim();
            categories = WR.categories
                .filter(c => c.name.trim() && c.rating > 0)
                .map(c => ({ name: c.name.trim(), rating: Math.round(c.rating * 10) / 10, note: c.note?.trim() || null }));
        }

        if (body && body.length < 10) { wrShowMsg('Description must be at least 10 characters (or leave it empty).', true); return; }

        const btn = document.getElementById('wr-submit');
        btn.disabled = true;
        btn.textContent = 'Posting…';

        fetch(`/games/${gameId}/reviews`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ rating, body: body || null, categories, is_detailed: WR.mode === 'detailed', game_name: gameName, game_cover: gameCover ?? null }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.message === 'Review posted!' || data.id || data.success) {
                location.reload();
            } else {
                wrShowMsg(data.message || 'Something went wrong. Try again.', true);
                btn.disabled = false;
                btn.textContent = 'Post Review';
            }
        })
        .catch(() => {
            wrShowMsg('Network error. Please try again.', true);
            btn.disabled = false;
            btn.textContent = 'Post Review';
        });
    };

    function wrShowMsg(text, isError) {
        const el = document.getElementById('review-msg');
        if (!el) return;
        el.textContent  = text;
        el.style.color  = isError ? 'var(--accent)' : '#1d9e75';
        el.style.display = text ? 'block' : 'none';
    }

})();

// -------------------------
// REVIEW VOTES
// -------------------------
window.voteReview = async function(reviewId, vote, clickedBtn) {
    const card      = clickedBtn.closest('.review-vote-bar');
    const likeBtn   = card.querySelector('.review-vote-like');
    const dislikeBtn= card.querySelector('.review-vote-dislike');

    // Optimistic disable while request is in flight
    likeBtn.classList.add('loading');
    dislikeBtn.classList.add('loading');

    // Pop animation on the clicked button
    clickedBtn.classList.remove('pop');
    void clickedBtn.offsetWidth; // reflow to restart animation
    clickedBtn.classList.add('pop');
    setTimeout(() => clickedBtn.classList.remove('pop'), 400);

    try {
        const res  = await authFetch(`/reviews/${reviewId}/vote`, {
            method: 'POST',
            body: JSON.stringify({ vote }),
        });
        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            showNotif(data.message || 'Could not register vote.');
            return;
        }

        // Update counts
        likeBtn.querySelector('[data-type="likes"]').textContent     = data.likes;
        dislikeBtn.querySelector('[data-type="dislikes"]').textContent = data.dislikes;

        // Update active state
        likeBtn.classList.toggle('active',    data.user_vote === 'like');
        dislikeBtn.classList.toggle('active', data.user_vote === 'dislike');

    } catch (err) {
        showNotif('Network error. Please try again.');
    } finally {
        likeBtn.classList.remove('loading');
        dislikeBtn.classList.remove('loading');
    }
};


// =========================================================================
// PROFILE PAGE
// Only runs when the profile page elements are present in the DOM.
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('panel-about')) return; // not the profile page

    // ── XP bar charge-up ─────────────────────────────────────────────────
    setTimeout(() => {
        const bar = document.querySelector('.profile-xp-bar-fill');
        if (bar) bar.style.width = bar.dataset.xpTarget + '%';
    }, 800);

    // ── Tab switching ─────────────────────────────────────────────────────
    function switchProfileTab(tab) {
        ['about', 'reviews', 'titles', 'settings'].forEach(t => {
            document.getElementById('tab-' + t)?.classList.toggle('active', t === tab);
            const panel = document.getElementById('panel-' + t);
            if (panel) panel.style.display = t === tab ? '' : 'none';
        });
        // Tint cards when the reviews tab becomes visible
        if (tab === 'reviews') setTimeout(tintReviewCards, 50);
    }

    // ── Shared fetch helper ───────────────────────────────────────────────
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

    // ── Notifications ─────────────────────────────────────────────────────
    function showProfileNotif(msg) {
        const n = document.getElementById('profile-notif');
        if (!n) return;
        n.innerText = msg;
        n.classList.add('show');
        setTimeout(() => n.classList.remove('show'), 2800);
    }

    function showDeleteMsg(msg) {
        const el = document.getElementById('delete-msg');
        if (!el) return;
        el.innerText     = msg;
        el.style.display = 'block';
    }

    // ── Bio ───────────────────────────────────────────────────────────────
    const bioTextarea = document.getElementById('about-bio');
    const bioChars    = document.getElementById('bio-chars');
    if (bioTextarea && bioChars) {
        bioTextarea.addEventListener('input', () => {
            bioChars.textContent = bioTextarea.value.length;
        });
    }

    async function saveBio() {
        const bio = bioTextarea?.value.trim() ?? '';
        const res = await profileFetch('/profile/bio', 'POST', { bio });
        if (res.ok) {
            showProfileNotif('Bio saved!');
        } else {
            const data = await res.json().catch(() => ({}));
            showProfileNotif(data.message || 'Failed to save bio.');
        }
    }

    // ── Favourite game ────────────────────────────────────────────────────
    let favDebounceTimer = null;

    function favSearchDebounce() {
        clearTimeout(favDebounceTimer);
        favDebounceTimer = setTimeout(favSearchNow, 400);
    }

    async function favSearchNow() {
        const input      = document.getElementById('fav-search-input');
        const resultsBox = document.getElementById('fav-results');
        const inner      = document.getElementById('fav-results-inner');
        const noResults  = document.getElementById('fav-no-results');
        const q          = input?.value.trim() ?? '';
        if (!q) return;

        inner.innerHTML          = '<p style="color:var(--text-muted);font-size:.875rem">Searching…</p>';
        resultsBox.style.display = '';
        noResults.style.display  = 'none';

        try {
            const res   = await fetch(`/api/games/search?q=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data  = await res.json();
            const games = data.results ?? [];

            inner.innerHTML = '';

            if (!games.length) {
                noResults.style.display = '';
                return;
            }

            games.slice(0, 8).forEach(game => {
                const btn         = document.createElement('button');
                btn.type          = 'button';
                btn.className     = 'store-btn';
                btn.style.cssText = 'display:flex;align-items:center;gap:.75rem;text-align:left;width:100%';

                const thumb = game.background_image
                    ? `<img src="${game.background_image}" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:4px;flex-shrink:0">`
                    : `<div style="width:42px;height:42px;border-radius:4px;background:var(--surface2);flex-shrink:0;display:flex;align-items:center;justify-content:center"><i class="ti ti-device-gamepad-2"></i></div>`;

                const released = game.released
                    ? `<span style="font-size:.75rem;color:var(--text-muted)">${game.released.slice(0, 4)}</span>`
                    : '';

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

        const res = await profileFetch('/profile/favourite-game', 'POST', {
            game_id:    game.id,
            game_name:  game.name,
            game_cover: game.background_image ?? null,
        });

        if (res.ok) {
            document.getElementById('fav-cover').src            = game.background_image ?? '';
            document.getElementById('fav-name').textContent     = game.name;
            document.getElementById('fav-current').style.display = '';

            const sideCard = document.getElementById('fav-sidebar-card');
            const sideLink = document.getElementById('fav-sidebar-link');
            const sideName = document.getElementById('fav-sidebar-name');
            sideCard.style.display   = '';
            sideLink.href            = `/games/${game.id}`;
            sideName.textContent     = game.name;

            document.getElementById('fav-search-input').value   = '';
            document.getElementById('fav-results').style.display = 'none';

            showProfileNotif('Favourite game saved!');
        } else {
            const data        = await res.json().catch(() => ({}));
            msg.innerText     = data.message || 'Failed to save favourite game.';
            msg.style.display = 'block';
        }
    }

    async function removeFavGame() {
        const res = await profileFetch('/profile/favourite-game', 'DELETE', {});
        if (res.ok) {
            document.getElementById('fav-current').style.display      = 'none';
            document.getElementById('fav-sidebar-card').style.display = 'none';
            showProfileNotif('Favourite game removed.');
        }
    }

    function escHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ── Name & password ───────────────────────────────────────────────────
    async function saveName() {
        const name = document.getElementById('profile-name')?.value.trim();
        if (!name) return;
        const res  = await profileFetch('/profile/name', 'POST', { name });
        const data = await res.json().catch(() => ({}));
        if (res.ok) {
            showProfileNotif('Name updated!');
            const displayName = document.querySelector('.profile-name');
            if (displayName) displayName.innerText = name;
            const pubName = document.getElementById('pub-name');
            if (pubName) pubName.textContent = name;
        } else {
            showProfileNotif(data.message || 'Failed to update name.');
        }
    }

    async function savePassword() {
        const current_password      = document.getElementById('cur-pass')?.value;
        const password              = document.getElementById('new-pass')?.value;
        const password_confirmation = document.getElementById('new-pass-confirm')?.value;
        if (!current_password || !password || !password_confirmation) {
            showProfileNotif('Please fill in all password fields.');
            return;
        }
        const res  = await profileFetch('/profile/password', 'POST', { current_password, password, password_confirmation });
        const data = await res.json().catch(() => ({}));
        if (res.ok) {
            showProfileNotif('Password updated!');
            ['cur-pass', 'new-pass', 'new-pass-confirm'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
        } else {
            showProfileNotif(data.message || 'Failed to update password.');
        }
    }

    // ── Delete account ────────────────────────────────────────────────────
    function openDeleteModal()  { document.getElementById('delete-modal-overlay').style.display = 'flex'; }
    function closeDeleteModal() { document.getElementById('delete-modal-overlay').style.display = 'none'; }

    async function confirmDelete() {
        const password = document.getElementById('delete-pass')?.value;
        if (!password) { showDeleteMsg('Please enter your password.'); return; }
        const res  = await profileFetch('/profile/delete', 'DELETE', { password });
        const data = await res.json().catch(() => ({}));
        if (res.ok) {
            window.location.href = '/';
        } else {
            showDeleteMsg(data.message || 'Incorrect password.');
        }
    }

    // ── Avatar crop ───────────────────────────────────────────────────────
    const cropState = {
        x: 0, y: 0,
        scale: 1,
        dragging: false,
        startX: 0, startY: 0,
        naturalW: 0, naturalH: 0,
        file: null,
    };

    const ZONE     = 300;
    const cropZone = document.getElementById('crop-zone');

    function closeAvatarModal() {
        document.getElementById('avatar-modal').style.display = 'none';
    }

    function applyCrop() {
        const img = document.getElementById('crop-img');
        if (!img) return;
        img.style.width     = (cropState.naturalW * cropState.scale) + 'px';
        img.style.height    = (cropState.naturalH * cropState.scale) + 'px';
        img.style.transform = `translate(${cropState.x}px, ${cropState.y}px)`;
    }

    function startDrag(e) {
        cropState.dragging = true;
        const pt = e.touches ? e.touches[0] : e;
        cropState.startX = pt.clientX - cropState.x;
        cropState.startY = pt.clientY - cropState.y;
    }

    function onDrag(e) {
        if (!cropState.dragging) return;
        const pt = e.touches ? e.touches[0] : e;
        cropState.x = pt.clientX - cropState.startX;
        cropState.y = pt.clientY - cropState.startY;
        applyCrop();
    }

    document.addEventListener('mousemove', onDrag);
    document.addEventListener('touchmove', onDrag, { passive: true });
    document.addEventListener('mouseup',   () => { cropState.dragging = false; });
    document.addEventListener('touchend',  () => { cropState.dragging = false; });

    if (cropZone) {
        cropZone.addEventListener('mousedown',  startDrag);
        cropZone.addEventListener('touchstart', startDrag, { passive: true });
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
    }

    const avatarFileInput = document.getElementById('avatar-file-input');
    if (avatarFileInput) {
        avatarFileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            cropState.file = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.getElementById('crop-img');
                img.src   = e.target.result;
                img.onload = () => {
                    cropState.naturalW = img.naturalWidth;
                    cropState.naturalH = img.naturalHeight;
                    const fitScale     = ZONE / Math.min(cropState.naturalW, cropState.naturalH);
                    cropState.scale    = fitScale;
                    cropState.x        = (ZONE - cropState.naturalW * fitScale) / 2;
                    cropState.y        = (ZONE - cropState.naturalH * fitScale) / 2;
                    applyCrop();
                };
                document.getElementById('avatar-modal').style.display = 'flex';
            };
            reader.readAsDataURL(file);
        });
    }

    async function saveAvatar() {
        const msg = document.getElementById('avatar-msg');
        msg.style.display = 'none';
        if (!cropState.file) {
            msg.innerText     = 'Please choose an image first.';
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
                removeBtn.dataset.action = 'remove-avatar';
                document.querySelector('.profile-hero-inner > div:last-child').appendChild(removeBtn);
            }
            showProfileNotif('Profile photo updated!');
        } else {
            msg.innerText     = data.message ?? (data.errors ? JSON.stringify(data.errors) : 'Upload failed.');
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

    // ── Title equip ───────────────────────────────────────────────────────
    async function equipTitle(label) {
        const res = label
            ? await profileFetch('/profile/title', 'POST', { title: label })
            : await profileFetch('/profile/title', 'DELETE', {});
        const data = await res.json().catch(() => ({}));
        if (!res.ok) { showProfileNotif(data.message || 'Could not update title.'); return; }

        // Update all title cards
        document.querySelectorAll('.title-card--unlocked').forEach(card => {
            const equipped = card.dataset.title === label;
            card.classList.toggle('title-card--equipped', equipped);
            const chip = card.querySelector('.title-card-equipped-chip');
            if (equipped && !chip) {
                const c = document.createElement('span');
                c.className = 'title-card-equipped-chip';
                c.textContent = 'Equipped';
                card.appendChild(c);
            } else if (!equipped && chip) {
                chip.remove();
            }
        });

        // Update active display
        const display = document.getElementById('title-active-display');
        if (display) {
            if (label) {
                display.innerHTML = `<span class="title-badge title-badge--active">${label}</span>
                    <button class="btn btn-ghost btn-sm" data-action="clear-title" style="margin-left:.75rem;font-size:.75rem">Remove</button>`;
            } else {
                display.innerHTML = `<span style="color:var(--text-muted);font-size:.875rem">No title equipped — pick one below.</span>`;
            }
        }

        // Update sidebar stat
        const sidebarTitle = document.getElementById('sidebar-active-title');
        if (sidebarTitle) sidebarTitle.textContent = label || '—';

        showProfileNotif(label ? `Title equipped: "${label}"` : 'Title removed.');
    }

    // ── Event delegation — wires all data-action attributes ──────────────
    document.addEventListener('click', (e) => {
        const el     = e.target.closest('[data-action]');
        const action = el?.dataset.action;
        if (!action) return;

        switch (action) {
            // Tabs
            case 'profile-tab': break; // handled by data-profile-tab below

            // Bio
            case 'save-bio':           saveBio();           break;

            // Favourite game
            case 'fav-search':         favSearchNow();      break;
            case 'remove-fav-game':    removeFavGame();      break;

            // Settings
            case 'save-name':          saveName();          break;
            case 'save-password':      savePassword();      break;

            // Delete modal
            case 'open-delete-modal':  openDeleteModal();   break;
            case 'close-delete-modal': closeDeleteModal();  break;
            case 'confirm-delete':     confirmDelete();     break;
            case 'close-delete-modal-overlay':
                if (e.target.id === 'delete-modal-overlay') closeDeleteModal();
                break;

            // Avatar modal
            case 'save-avatar':        saveAvatar();        break;
            case 'remove-avatar':      removeAvatar();      break;
            case 'close-avatar-modal': closeAvatarModal();  break;
            case 'choose-avatar-file':
                document.getElementById('avatar-file-input')?.click();
                break;
            case 'close-avatar-modal-overlay':
                if (e.target.id === 'avatar-modal') closeAvatarModal();
                break;

            // Delete review from profile page
            case 'delete-profile-review':
                deleteProfileReview(
                    parseInt(el.dataset.reviewId),
                    parseInt(el.dataset.gameId)
                );
                break;

            // Titles
            case 'equip-title':
                equipTitle(el.dataset.title);
                break;
            case 'clear-title':
                equipTitle('');
                break;

            // Avatar edit button (opens modal)
            case 'open-avatar-modal':
                document.getElementById('avatar-modal').style.display = 'flex';
                if (!cropState.file) document.getElementById('avatar-file-input')?.click();
                break;
        }
    });

    // Tab buttons use data-profile-tab instead of data-action
    document.querySelectorAll('[data-profile-tab]').forEach(btn => {
        btn.addEventListener('click', () => switchProfileTab(btn.dataset.profileTab));
    });

    // Avatar edit button
    document.getElementById('avatar-edit-btn')?.addEventListener('click', () => {
        document.getElementById('avatar-modal').style.display = 'flex';
        if (!cropState.file) document.getElementById('avatar-file-input')?.click();
    });

    // Fav search input — debounce on typing
    document.getElementById('fav-search-input')?.addEventListener('input', favSearchDebounce);

    // ── Cover art dominant-colour tint via Canvas ─────────────────────────
    function tintReviewCards() {
        document.querySelectorAll('.review-profile-card[data-cover]').forEach(card => {
            if (card.dataset.tinted) return;
            const coverUrl = card.dataset.cover;
            if (!coverUrl) return;

            const img = new Image();
            img.crossOrigin = 'anonymous';

            img.onload = () => {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = canvas.height = 16;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, 16, 16);

                    const { data } = ctx.getImageData(0, 0, 16, 16);
                    let r = 0, g = 0, b = 0, count = 0;

                    for (let i = 0; i < data.length; i += 4) {
                        if (data[i + 3] < 128) continue;
                        const brightness = (data[i] + data[i + 1] + data[i + 2]) / 3;
                        if (brightness < 20 || brightness > 235) continue;
                        r += data[i]; g += data[i + 1]; b += data[i + 2];
                        count++;
                    }

                    if (!count) return;

                    r = Math.round(r / count);
                    g = Math.round(g / count);
                    b = Math.round(b / count);

                    card.style.setProperty('--card-tint', `rgb(${r},${g},${b})`);
                    card.style.borderLeftColor = `rgba(${r},${g},${b},0.45)`;
                    card.style.borderLeftWidth = '3px';
                    card.dataset.tinted = '1';
                } catch (_) { /* CORS or security error — skip silently */ }
            };

            img.src = coverUrl;
        });
    }

    // Run on load in case the reviews panel is already visible
    tintReviewCards();

    // ── Delete review from profile page ──────────────────────────────────
    async function deleteProfileReview(reviewId, gameId) {
        const res = await fetch(`/games/${gameId}/reviews/${reviewId}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
        });

        if (res.ok) {
            const card = document.getElementById('profile-review-' + reviewId);
            if (card) {
                card.style.transition = 'opacity .25s';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();

                    // Show empty state if no reviews remain
                    const main = document.querySelector('#panel-reviews .show-main');
                    if (main && !main.querySelector('.review-profile-card')) {
                        main.innerHTML = `
                            <div class="show-card" style="text-align:center;padding:2.5rem 1rem">
                                <i class="ti ti-message-off" style="font-size:2.5rem;color:var(--text-muted);display:block;margin-bottom:.75rem" aria-hidden="true"></i>
                                <p style="color:var(--text-muted);margin:0">You haven't written any reviews yet.</p>
                                <a href="/games" class="btn btn-primary btn-sm" style="margin-top:1rem;display:inline-block">Browse Games</a>
                            </div>`;
                    }

                    // Update the review count chip on the tab
                    const chip = document.querySelector('#tab-reviews .review-count-chip');
                    if (chip) {
                        const newCount = parseInt(chip.textContent) - 1;
                        if (newCount <= 0) chip.remove();
                        else chip.textContent = newCount;
                    }

                    // Update sidebar stat
                    const statEl = document.querySelector('#panel-reviews .detail-row dd');
                    if (statEl) {
                        const cur = parseInt(statEl.textContent) || 0;
                        statEl.textContent = Math.max(0, cur - 1);
                    }
                }, 250);
            }
            showProfileNotif('Review deleted.');
        } else {
            showProfileNotif('Could not delete review. Please try again.');
        }
    }
});
