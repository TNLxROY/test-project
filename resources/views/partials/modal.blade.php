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
      <div class="form-group"><label>Email</label><input type="email" placeholder="you@example.com" id="login-email" name="email"></div>
      <div class="form-group"><label>Password</label><input type="password" placeholder="••••••••" id="login-pass" name="password"></div>
      <button type="button" class="btn btn-primary" onclick="doLogin()">Log in</button>
    </div>

    <div id="form-register" style="display:none">
      <div class="form-group"><label>Username</label><input type="text" placeholder="dev42" id="reg-user" name="name"></div>
      <div class="form-group"><label>Email</label><input type="email" placeholder="you@example.com" id="reg-email" name="email"></div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" placeholder="••••••••" id="reg-pass" name="password" oninput="checkPasswordLength(this)">
        <small id="reg-pass-hint" style="font-size:.75rem; margin-top:.3rem; display:none"></small>
      </div>
      <button type="button" class="btn btn-primary" onclick="doRegister()">Create account</button>
    </div>

    <div class="auth-msg" id="auth-msg"></div>
  </div>
</div>

<div class="notification" id="notif"></div>
