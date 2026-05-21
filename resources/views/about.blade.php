@extends('layouts.app')

@section('content')
<div id="page-about" class="page">
  <div class="about-hero">
    <h1>We believe in<br><em style="color:var(--accent);font-style:normal">honest writing</em></h1>
    <p>Fact.Speakers is a simple website where people can share their honest opinions about games. This project was created using Laravel and Node.js</p>
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
    <div class="section-sub">Project created by:</div>
    <div class="team-grid">
      <div class="team-card"><div class="team-avatar">TNL</div><h3>TNiaL</h3><p>Creator and Maintainer</p></div>
    </div>
  </div>
@endsection
