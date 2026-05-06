<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0d0d0f;
  --bg2:#131316;
  --bg3:#1a1a1f;
  --bg4:#222228;
  --border:#2a2a33;
  --accent:#e8192c;
  --accent2:#ff4455;
  --text:#fff0f0;
  --muted:#888899;
  --card:#16161c;
}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden}
h1,h2,h3,h4,nav .logo{font-family:'Syne',sans-serif}

nav{
  position:sticky;top:0;z-index:100;
  background:rgba(13,13,15,0.85);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 2rem;height:64px;
}
.logo{font-size:1.4rem;font-weight:800;letter-spacing:-0.5px;color:var(--text)}
.logo span{color:var(--accent)}
.nav-links{display:flex;align-items:center;gap:1.5rem}
.nav-links a{color:var(--muted);text-decoration:none;font-size:0.9rem;font-weight:500;transition:color .2s;cursor:pointer}
.nav-links a:hover,.nav-links a.active{color:var(--text)}
.nav-cta{display:flex;align-items:center;gap:.75rem}
.btn{padding:.5rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:500;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;transition:all .2s}
.btn-ghost{background:transparent;color:var(--muted);border:1px solid var(--border)}
.btn-ghost:hover{color:var(--text);border-color:var(--accent)}
.btn-primary{background:var(--accent);color:#fff}
.btn-primary:hover{background:var(--accent2);transform:translateY(-1px)}
.btn-sm{padding:.35rem .9rem;font-size:.82rem}

.page{display:none;animation:fadeIn .3s ease}
.page.active{display:block}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}

.hero{padding:6rem 2rem 5rem;max-width:900px;margin:0 auto;text-align:center}
.hero-tag{display:inline-block;background:rgba(232,25,44,.15);color:var(--accent2);border:1px solid rgba(232,25,44,.3);padding:.3rem .9rem;border-radius:100px;font-size:.8rem;font-weight:500;margin-bottom:1.5rem;letter-spacing:.5px}
.hero h1{font-size:clamp(2.5rem,6vw,4rem);font-weight:800;line-height:1.1;letter-spacing:-1.5px;margin-bottom:1.2rem}
.hero h1 em{color:var(--accent);font-style:normal}
.hero p{font-size:1.1rem;color:var(--muted);max-width:540px;margin:0 auto 2.5rem;line-height:1.7}
.hero-btns{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap}
.btn-lg{padding:.75rem 2rem;font-size:1rem}

.section{padding:4rem 2rem;max-width:1100px;margin:0 auto}
.section-title{font-size:1.7rem;font-weight:700;letter-spacing:-0.5px;margin-bottom:.5rem}
.section-sub{color:var(--muted);margin-bottom:2rem;font-size:.95rem}

.posts-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem}
.post-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.5rem;cursor:pointer;transition:all .2s}
.post-card:hover{border-color:var(--accent);transform:translateY(-3px)}
.post-tag{display:inline-block;background:rgba(232,25,44,.12);color:var(--accent2);border-radius:6px;padding:.2rem .65rem;font-size:.75rem;font-weight:500;margin-bottom:1rem}
.post-card h3{font-size:1.05rem;font-weight:600;line-height:1.4;margin-bottom:.7rem}
.post-card p{font-size:.87rem;color:var(--muted);line-height:1.6}
.post-meta{display:flex;align-items:center;gap:.75rem;margin-top:1.2rem;font-size:.8rem;color:var(--muted)}
.avatar{width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff;flex-shrink:0}

.divider{border:none;border-top:1px solid var(--border);margin:0}

.stats-row{display:flex;gap:1px;background:var(--border);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:4rem}
.stat{flex:1;background:var(--card);padding:1.8rem 1.5rem;text-align:center}
.stat-num{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:var(--accent)}
.stat-label{font-size:.8rem;color:var(--muted);margin-top:.3rem}

footer{border-top:1px solid var(--border);padding:3rem 2rem;max-width:1100px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr;gap:3rem}
.footer-brand .logo{font-size:1.2rem;margin-bottom:.75rem;display:block}
.footer-brand p{font-size:.85rem;color:var(--muted);line-height:1.6}
.footer-col h4{font-size:.85rem;font-weight:600;margin-bottom:.85rem;color:var(--text)}
.footer-col a{display:block;color:var(--muted);font-size:.83rem;text-decoration:none;margin-bottom:.55rem;cursor:pointer;transition:color .2s}
.footer-col a:hover{color:var(--accent2)}
.footer-bottom{border-top:1px solid var(--border);padding:1.2rem 2rem;text-align:center;color:var(--muted);font-size:.8rem;max-width:1100px;margin:0 auto}

.about-hero{padding:5rem 2rem 3rem;max-width:800px;margin:0 auto;text-align:center}
.about-hero h1{font-size:clamp(2rem,5vw,3.2rem);font-weight:800;letter-spacing:-1px;margin-bottom:1rem}
.about-hero p{font-size:1rem;color:var(--muted);line-height:1.75;max-width:560px;margin:0 auto}
.team-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem}
.team-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.5rem;text-align:center}
.team-avatar{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#b91c1c);display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:#fff;margin:0 auto 1rem}
.team-card h3{font-size:1rem;font-weight:600;margin-bottom:.25rem}
.team-card p{font-size:.8rem;color:var(--muted)}
.values-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1rem;margin-top:1.5rem}
.value-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.5rem}
.value-icon{width:40px;height:40px;border-radius:10px;background:rgba(232,25,44,.15);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;font-size:1.1rem}
.value-card h3{font-size:.95rem;font-weight:600;margin-bottom:.4rem}
.value-card p{font-size:.83rem;color:var(--muted);line-height:1.6}

.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:none;align-items:center;justify-content:center;z-index:999;backdrop-filter:blur(4px)}
.modal-overlay.open{display:flex}
.modal{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:2rem;width:100%;max-width:420px;position:relative;animation:slideUp .25s ease}
@keyframes slideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.modal-close{position:absolute;top:1rem;right:1rem;background:var(--bg3);border:1px solid var(--border);color:var(--muted);border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center}
.modal-close:hover{color:var(--text)}
.modal h2{font-size:1.5rem;font-weight:700;margin-bottom:.4rem}
.modal-sub{font-size:.87rem;color:var(--muted);margin-bottom:1.75rem}
.modal-tabs{display:flex;gap:0;background:var(--bg3);border-radius:10px;padding:4px;margin-bottom:1.75rem}
.modal-tab{flex:1;text-align:center;padding:.45rem;border-radius:7px;font-size:.87rem;font-weight:500;cursor:pointer;color:var(--muted);transition:all .2s}
.modal-tab.active{background:var(--bg2);color:var(--text);border:1px solid var(--border)}
.form-group{margin-bottom:1rem}
.form-group label{display:block;font-size:.82rem;color:var(--muted);margin-bottom:.4rem;font-weight:500}
.form-group input{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:.65rem .9rem;color:var(--text);font-size:.9rem;font-family:'DM Sans',sans-serif;outline:none;transition:border .2s}
.form-group input:focus{border-color:var(--accent)}
.form-group input::placeholder{color:var(--muted)}
.modal .btn-primary{width:100%;padding:.75rem;margin-top:.5rem;font-size:.95rem}
.auth-msg{text-align:center;padding:1rem;border-radius:10px;background:rgba(232,25,44,.12);border:1px solid rgba(232,25,44,.25);color:var(--accent2);font-size:.88rem;margin-top:1rem;display:none}

.notification{position:fixed;top:80px;right:1.5rem;background:var(--bg2);border:1px solid var(--accent);border-radius:12px;padding:.85rem 1.25rem;font-size:.87rem;color:var(--text);z-index:9999;transform:translateX(120%);transition:transform .35s cubic-bezier(.34,1.56,.64,1);max-width:280px}
.notification.show{transform:translateX(0)}

.user-chip{display:flex;align-items:center;gap:.5rem;background:var(--bg3);border:1px solid var(--border);border-radius:100px;padding:.3rem .75rem .3rem .3rem;cursor:pointer;font-size:.85rem}
.user-chip .avatar{width:28px;height:28px;font-size:.7rem}
</style>
</head>
<body>

<nav>
  <span class="logo">Fact<span>.</span>Speakers</span>
  <div class="nav-links">
    <a href="/">Home</a>
    <a href="/about">About</a>
    <a style="color:var(--muted)">Blog</a>
    <a style="color:var(--muted)">Members</a>
  </div>
  <div class="nav-cta" id="nav-auth-area">
    <button class="btn btn-ghost btn-sm" onclick="openModal('login')">Log in</button>
    <button class="btn btn-primary btn-sm" onclick="openModal('register')">Sign up</button>
  </div>
</nav>

<div id="page-home" class="page active">
  <div class="hero">
    <span class="hero-tag">✦ A place for curious minds</span>
    <h1>Write. Share.<br><em>Connect.</em></h1>
    <p>A dark-mode community for developers, designers, and thinkers. No noise — just signal.</p>
    <div class="hero-btns">
      <button class="btn btn-primary btn-lg" onclick="openModal('register')">Join the community</button>
      <button class="btn btn-ghost btn-lg" onclick="showPage('about')">Learn more</button>
    </div>
  </div>

  <div class="section">
    <div class="stats-row">
      <div class="stat"><div class="stat-num">12.4k</div><div class="stat-label">Members</div></div>
      <div class="stat"><div class="stat-num">3.2k</div><div class="stat-label">Posts</div></div>
      <div class="stat"><div class="stat-num">98k</div><div class="stat-label">Monthly reads</div></div>
      <div class="stat"><div class="stat-num">42+</div><div class="stat-label">Topics</div></div>
    </div>

    <div style="margin-bottom:2rem">
      <div class="section-title">Latest from the community</div>
      <div class="section-sub">Fresh posts from members like you</div>
    </div>

    <div class="posts-grid">
      <div class="post-card">
        <span class="post-tag">Design Systems</span>
        <h3>Why I ditched Figma tokens and went fully CSS-first</h3>
        <p>After three years of wrestling with token sync tools, I found a simpler path that doesn't fight the browser.</p>
        <div class="post-meta"><div class="avatar">AK</div><span>Ana K. · 5 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Backend</span>
        <h3>Building a zero-downtime deploy pipeline with Laravel Octane</h3>
        <p>A practical walkthrough of how we moved to Octane on production and cut our p99 latency in half.</p>
        <div class="post-meta"><div class="avatar">TR</div><span>Tom R. · 8 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Philosophy</span>
        <h3>The quiet cost of always-on developer culture</h3>
        <p>We celebrate velocity, but at what price? A meditation on sustainable pace and the long game.</p>
        <div class="post-meta"><div class="avatar">MJ</div><span>Maya J. · 6 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Node.js</span>
        <h3>From callbacks to async context tracking in Node 22</h3>
        <p>AsyncLocalStorage changed how we think about request-scoped state. Here's what that means in practice.</p>
        <div class="post-meta"><div class="avatar">LW</div><span>Liam W. · 10 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Open Source</span>
        <h3>How I got my first OSS contribution merged into a 50k-star repo</h3>
        <p>It took three rejections and six months. Here's what I learned along the way.</p>
        <div class="post-meta"><div class="avatar">SC</div><span>Sara C. · 7 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Career</span>
        <h3>Negotiating a senior role without a CS degree: my story</h3>
        <p>Portfolio work, confidence, and a few uncomfortable conversations that changed my trajectory.</p>
        <div class="post-meta"><div class="avatar">BD</div><span>Ben D. · 9 min read</span></div>
      </div>
    </div>
  </div>

  <hr class="divider">
  <div style="max-width:1100px;margin:0 auto">
    <footer>
      <div class="footer-brand">
        <span class="logo">Fact<span>.</span>Speakers</span>
        <p>A modern community platform for developers and creators. Built with Laravel, Node.js, and a lot of dark mode.</p>
      </div>
      <div class="footer-col">
        <h4>Platform</h4>
        <a>Blog</a><a>Members</a><a>Topics</a><a>Newsletter</a>
      </div>
      <div class="footer-col">
        <h4>Company</h4>
        <a onclick="showPage('about')">About</a><a>Privacy</a><a>Terms</a><a>Contact</a>
      </div>
    </footer>
    <div class="footer-bottom">© 2026 void.community — Built with Laravel & Node.js</div>
  </div>
</div>

<div id="page-about" class="page">
  <div class="about-hero">
    <h1>We believe in<br><em style="color:var(--accent);font-style:normal">honest writing</em></h1>
    <p>void.community started as a Discord server in 2021. Today it's home to thousands of developers, designers, and thinkers who share ideas without the algorithm getting in the way.</p>
  </div>

  <div class="section" style="padding-top:1rem">
    <div class="section-title">Our values</div>
    <div class="section-sub">What guides every decision we make</div>
    <div class="values-grid">
      <div class="value-card"><div class="value-icon">◈</div><h3>Quality over quantity</h3><p>We don't chase engagement metrics. A single thoughtful post beats ten hot takes.</p></div>
      <div class="value-card"><div class="value-icon">⬡</div><h3>Open by default</h3><p>All content is free to read. We sustain the platform through optional memberships.</p></div>
      <div class="value-card"><div class="value-icon">◉</div><h3>No dark patterns</h3><p>No infinite scroll, no push-notification spam. Your attention is yours to spend.</p></div>
      <div class="value-card"><div class="value-icon">⬢</div><h3>Community-shaped</h3><p>Features come from members. Our roadmap is literally a public forum thread.</p></div>
    </div>
  </div>

  <div class="section">
    <div class="section-title">The team</div>
    <div class="section-sub">Small, distributed, opinionated</div>
    <div class="team-grid">
      <div class="team-card"><div class="team-avatar">SL</div><h3>Sofia L.</h3><p>Founder & Editor</p></div>
      <div class="team-card"><div class="team-avatar" style="background:linear-gradient(135deg,#b91c1c,#e8192c)">JK</div><h3>James K.</h3><p>Lead Engineer</p></div>
      <div class="team-card"><div class="team-avatar" style="background:linear-gradient(135deg,#7f1d1d,#dc2626)">PR</div><h3>Priya R.</h3><p>Community Lead</p></div>
      <div class="team-card"><div class="team-avatar" style="background:linear-gradient(135deg,#450a0a,#b91c1c)">OM</div><h3>Omar M.</h3><p>Product Design</p></div>
    </div>
  </div>

  <hr class="divider">
  <div style="max-width:1100px;margin:0 auto">
    <footer>
      <div class="footer-brand">
        <span class="logo">Fact<span>.</span>Speakers</span>
        <p>A modern community platform for developers and creators. Built with Laravel, Node.js, and a lot of dark mode.</p>
      </div>
      <div class="footer-col">
        <h4>Platform</h4>
        <a>Blog</a><a>Members</a><a>Topics</a><a>Newsletter</a>
      </div>
      <div class="footer-col">
        <h4>Company</h4>
        <a onclick="showPage('about')">About</a><a>Privacy</a><a>Terms</a><a>Contact</a>
      </div>
    </footer>
    <div class="footer-bottom">© 2026 Fact.Speakers — Built with Laravel & Node.js</div>
  </div>
</div>

<div class="modal-overlay" id="modal-overlay" onclick="handleOverlayClick(event)">
  <div class="modal" id="modal-box">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <h2 id="modal-title">Welcome back</h2>
    <p class="modal-sub" id="modal-sub">Sign in to your account</p>
    <div class="modal-tabs">
      <div class="modal-tab active" id="tab-login" onclick="switchTab('login')">Log in</div>
      <div class="modal-tab" id="tab-register" onclick="switchTab('register')">Sign up</div>
    </div>

    <div id="form-login">
      <div class="form-group"><label>Email</label><input type="email" placeholder="you@example.com" id="login-email"></div>
      <div class="form-group"><label>Password</label><input type="password" placeholder="••••••••" id="login-pass"></div>
      <button class="btn btn-primary" onclick="doLogin()">Log in</button>
    </div>

    <div id="form-register" style="display:none">
      <div class="form-group"><label>Username</label><input type="text" placeholder="cooldev42" id="reg-user"></div>
      <div class="form-group"><label>Email</label><input type="email" placeholder="you@example.com" id="reg-email"></div>
      <div class="form-group"><label>Password</label><input type="password" placeholder="••••••••" id="reg-pass"></div>
      <button class="btn btn-primary" onclick="doRegister()">Create account</button>
    </div>

    <div class="auth-msg" id="auth-msg"></div>
  </div>
</div>

<div class="notification" id="notif"></div>

<script>
let currentUser = null;

function showPage(name) {
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  document.getElementById('page-'+name).classList.add('active');
  document.querySelectorAll('.nav-links a').forEach(a=>a.classList.remove('active'));
  const el = document.getElementById('nav-'+name);
  if(el) el.classList.add('active');
  window.scrollTo(0,0);
}

function openModal(tab) {
  document.getElementById('modal-overlay').classList.add('open');
  switchTab(tab);
}
function closeModal() {
  document.getElementById('modal-overlay').classList.remove('open');
  document.getElementById('auth-msg').style.display='none';
}
function handleOverlayClick(e) {
  if(e.target===document.getElementById('modal-overlay')) closeModal();
}
function switchTab(tab) {
  const isLogin = tab==='login';
  document.getElementById('tab-login').classList.toggle('active',isLogin);
  document.getElementById('tab-register').classList.toggle('active',!isLogin);
  document.getElementById('form-login').style.display=isLogin?'block':'none';
  document.getElementById('form-register').style.display=isLogin?'none':'block';
  document.getElementById('modal-title').textContent=isLogin?'Welcome back':'Join the void';
  document.getElementById('modal-sub').textContent=isLogin?'Sign in to your account':'Create your free account';
  document.getElementById('auth-msg').style.display='none';
}

function showNotif(msg) {
  const n = document.getElementById('notif');
  n.textContent = msg;
  n.classList.add('show');
  setTimeout(()=>n.classList.remove('show'), 3200);
}

function doLogin() {
  const email = document.getElementById('login-email').value.trim();
  const pass = document.getElementById('login-pass').value;
  if(!email||!pass){showAuthMsg('Please fill in all fields.');return;}
  currentUser = email.split('@')[0];
  closeModal();
  updateNavForUser();
  showNotif('✦ Welcome back, '+currentUser+'!');
}

function doRegister() {
  const user = document.getElementById('reg-user').value.trim();
  const email = document.getElementById('reg-email').value.trim();
  const pass = document.getElementById('reg-pass').value;
  if(!user||!email||!pass){showAuthMsg('Please fill in all fields.');return;}
  currentUser = user;
  closeModal();
  updateNavForUser();
  showNotif('✦ Account created! Welcome, '+currentUser+'!');
}

function showAuthMsg(msg) {
  const el = document.getElementById('auth-msg');
  el.textContent = msg;
  el.style.display='block';
}

function updateNavForUser() {
  const area = document.getElementById('nav-auth-area');
  const initials = currentUser.slice(0,2).toUpperCase();
  area.innerHTML = `<div class="user-chip"><div class="avatar">${initials}</div>${currentUser}</div>`;
}
</script>
</body>
</html>
