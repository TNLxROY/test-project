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
      <div class="section-title">Latest Reviews from the community</div>
      <div class="section-sub">Fresh posts from members like you</div>
    </div>

    <div class="posts-grid">
      <div class="post-card">
        <span class="post-tag">Review</span>
        <h3>Lorem ipsum dolor sit amet</h3>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</p>
        <div class="post-meta"><div class="avatar">AK</div><span>Ana K. · 5 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Review</span>
        <h3>Lorem ipsum dolor sit amet</h3>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</p>
        <div class="post-meta"><div class="avatar">TR</div><span>Tom R. · 8 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Review</span>
        <h3>Lorem ipsum dolor sit amet</h3>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</p>
        <div class="post-meta"><div class="avatar">MJ</div><span>Maya J. · 6 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Review</span>
        <h3>Lorem ipsum dolor sit amet</h3>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor   .</p>
        <div class="post-meta"><div class="avatar">LW</div><span>Liam W. · 10 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Review</span>
        <h3>Lorem ipsum dolor sit amet</h3>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</p>
        <div class="post-meta"><div class="avatar">SC</div><span>Sara C. · 7 min read</span></div>
      </div>
      <div class="post-card">
        <span class="post-tag">Review</span>
        <h3>Lorem ipsum dolor sit amet</h3>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</p>
        <div class="post-meta"><div class="avatar">BD</div><span>Ben D. · 9 min read</span></div>
      </div>
    </div>
  </div>
</div>
@endsection
