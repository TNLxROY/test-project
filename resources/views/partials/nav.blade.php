<nav>
    <span class="logo">Fact<span>.</span>Speakers</span>
    <div class="nav-links">
        <a href="/">Home</a>
        <a href="/about">About</a>
        <a href="/games">Games</a>
        <a style="color:var(--muted)">News</a>
        <a style="color:var(--muted)">Users</a>
        <a style="color:var(--muted)">Membership</a>
    </div>
    {{-- JS owns this container entirely — do NOT use @auth here --}}
    <div id="mini-profile-container">
        {{-- Rendered by refreshAuthUI() in app.js --}}
    </div>
</nav>
