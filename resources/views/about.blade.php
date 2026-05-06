@extends('layouts.app')

@section('content')
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
@endsection
