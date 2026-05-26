{{--
    XP Level Card — drop inside profile-sidebar
    Requires: $userLevel (App\Models\UserLevel instance, eager-loaded on the User)

    In your ProfileController:
        $userLevel = app(\App\Services\XpService::class)->getOrCreate($user);
        return view('profile', compact('user', 'userLevel'));
--}}

<div class="show-card xp-level-card">
    <h2 class="show-card-title">Player Level</h2>

    {{-- Level badge + rank --}}
    <div class="xp-header">
        <div class="xp-badge">
            <span class="xp-badge-num">{{ $userLevel->level }}</span>
            <span class="xp-badge-label">LVL</span>
        </div>
        <div>
            <p class="xp-rank-title">{{ $userLevel->rankTitle() }}</p>
            <p class="xp-rank-sub">Rank up at level {{ $userLevel->level + 1 }}</p>
        </div>
    </div>

    {{-- XP progress bar --}}
    <div class="xp-bar-wrap">
        <div class="xp-bar-track" role="progressbar"
             aria-valuenow="{{ $userLevel->xp }}"
             aria-valuemin="0"
             aria-valuemax="{{ $userLevel->xpForCurrentLevel() }}"
             aria-label="XP progress">
            <div class="xp-bar-fill" style="width: {{ $userLevel->progressPercent() }}%"></div>
        </div>
        <div class="xp-bar-meta">
            <span>{{ $userLevel->xp }} / {{ $userLevel->xpForCurrentLevel() }} XP</span>
            <span>{{ $userLevel->progressPercent() }}%</span>
        </div>
    </div>

    {{-- Stats grid --}}
    <div class="xp-stats-grid">
        <div class="xp-stat">
            <span class="xp-stat-val">{{ $userLevel->review_count }}</span>
            <span class="xp-stat-label">Reviews</span>
        </div>
        <div class="xp-stat">
            <span class="xp-stat-val">{{ number_format($userLevel->total_xp) }}</span>
            <span class="xp-stat-label">Total XP</span>
        </div>
    </div>

    {{-- Next level hint --}}
    @if($userLevel->reviewsToNextLevel() > 0)
    <p class="xp-next-hint">
        <i class="ti ti-arrow-up-right" aria-hidden="true"></i>
        {{ $userLevel->reviewsToNextLevel() }} more review{{ $userLevel->reviewsToNextLevel() === 1 ? '' : 's' }} to reach level {{ $userLevel->level + 1 }}
    </p>
    @endif
</div>
