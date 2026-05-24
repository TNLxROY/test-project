<nav>
    <span class="logo">Fact<span>.</span>Speakers</span>
    <div class="nav-links" id="nav-links">
        <a href="/">Home</a>
        <a href="/about">About</a>
        <a href="/games">Games</a>
        <a style="color:var(--muted)">News</a>
        <a href="{{ route('users.index') }}">Users</a>
        <a style="color:var(--muted)">Membership</a>
    </div>
    <div class="nav-right">
        <button class="hamburger" id="hamburger" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div id="mini-profile-container"></div>
    </div>
    <div class="user-dropdown" id="user-dropdown">
        <div class="dropdown-header">
            <div class="d-name" id="dropdown-name"></div>
            <div class="d-email" id="dropdown-email"></div>
        </div>
        <a href="{{ route('profile') }}" class="dropdown-item">
            <i class="ti ti-user" aria-hidden="true"></i> Profile
        </a>
        <a href="{{ route('friends.index') }}" class="dropdown-item">
            <i class="ti ti-users" aria-hidden="true"></i> Friends
        </a>
        <a href="{{ route('games.index') }}" class="dropdown-item">
            <i class="ti ti-device-gamepad-2" aria-hidden="true"></i> Browse Games
        </a>
        <hr class="dropdown-divider">
        <button class="dropdown-item danger" data-action="logout">
            <i class="ti ti-logout" aria-hidden="true"></i> Log out
        </button>
    </div>
</nav>
