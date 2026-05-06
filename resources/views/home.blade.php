@extends('layouts.app')

@section('content')
<div id="page-home" class="page active">
  <div class="hero">
    <h1>Write. Share.<br><em>Connect.</em></h1>
    <p>Are you also sick of those terrible reviews of you favourite games created by the so called 'proffesionals'? let's create a platform together where we can share our own thoughts and experiences.</p>
    <div class="hero-btns">
      <button class="btn btn-primary btn-lg" onclick="openModal('register')">Become a Fact Speaker</button>
      <button class="btn btn-ghost btn-lg" onclick="showPage('about')">Learn more</button>
    </div>
  </div>

  <div class="section">
    <div class="stats-row">
      <div class="stat"><div class="stat-num">12.4k</div><div class="stat-label">Users</div></div>
      <div class="stat"><div class="stat-num">3.2k</div><div class="stat-label">Reviews</div></div>
      <div class="stat"><div class="stat-num">98k</div><div class="stat-label">Monthly reads</div></div>
      <div class="stat"><div class="stat-num">42+</div><div class="stat-label">Games</div></div>
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
</div>
@endsection
