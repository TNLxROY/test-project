@extends('layouts.app')

@section('content')

<div class="users-hero">
    <div class="users-hero-inner">
        <h1 class="users-hero-title">Achievements</h1>
        <p class="users-hero-sub">{{ $earned }} / {{ $total }} unlocked</p>
        <div class="achievement-progress-bar-wrap">
            <div class="achievement-progress-bar" style="width: {{ $total > 0 ? round(($earned / $total) * 100) : 0 }}%"></div>
        </div>
    </div>
</div>

<div class="achievements-page-layout">

    {{-- Earned --}}
    @php $earnedList = collect($achievements)->where('earned', true); @endphp
    @if($earnedList->count() > 0)
    <div class="friends-section">
        <h2 class="friends-section-title">
            <i class="ti ti-trophy"></i>
            Unlocked
            <span class="review-count-badge">{{ $earnedList->count() }}</span>
        </h2>
        <div class="achievements-page-grid">
            @foreach($earnedList as $a)
            <div class="achievement-page-card achievement-page-card--earned">
                <div class="achievement-page-icon" style="background: {{ $a['color'] }}22; border-color: {{ $a['color'] }}44">
                    <i class="ti {{ $a['icon'] }}" style="color: {{ $a['color'] }}"></i>
                </div>
                <div class="achievement-page-info">
                    <span class="achievement-page-title">{{ $a['title'] }}</span>
                    <span class="achievement-page-desc">{{ $a['desc'] }}</span>
                    @if($a['earned_at'])
                        <span class="achievement-page-date">Earned {{ $a['earned_at']->format('M j, Y') }}</span>
                    @endif
                </div>
                @if(!empty($titleMap[$a['key']]))
                <div class="achievement-page-reward">
                    <span class="achievement-page-reward-label">Title unlocked</span>
                    <span class="achievement-page-reward-title">
                        <i class="ti ti-tag"></i>
                        {{ $titleMap[$a['key']] }}
                    </span>
                </div>
                @endif
                <i class="ti ti-circle-check achievement-page-check"></i>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Locked --}}
    @php $lockedList = collect($achievements)->where('earned', false); @endphp
    @if($lockedList->count() > 0)
    <div class="friends-section">
        <h2 class="friends-section-title">
            <i class="ti ti-lock"></i>
            Locked
            <span class="review-count-badge">{{ $lockedList->count() }}</span>
        </h2>
        <div class="achievements-page-grid">
            @foreach($lockedList as $a)
            <div class="achievement-page-card achievement-page-card--locked">
                <div class="achievement-page-icon achievement-page-icon--locked">
                    @if($a['secret'])
                        <i class="ti ti-help" style="color:var(--muted)"></i>
                    @else
                        <i class="ti {{ $a['icon'] }}" style="color:var(--muted)"></i>
                    @endif
                </div>
                <div class="achievement-page-info">
                    @if($a['secret'])
                        <span class="achievement-page-title" style="color:var(--muted)">???</span>
                        <span class="achievement-page-desc">Keep playing to unlock this secret achievement.</span>
                    @else
                        <span class="achievement-page-title" style="color:var(--muted)">{{ $a['title'] }}</span>
                        <span class="achievement-page-desc">{{ $a['desc'] }}</span>
                    @endif
                </div>
                @if(!$a['secret'] && !empty($titleMap[$a['key']]))
                <div class="achievement-page-reward achievement-page-reward--locked">
                    <span class="achievement-page-reward-label">Title reward</span>
                    <span class="achievement-page-reward-title achievement-page-reward-title--locked">
                        <i class="ti ti-tag"></i>
                        {{ $titleMap[$a['key']] }}
                    </span>
                </div>
                @endif
                <i class="ti ti-lock" style="color:var(--muted);font-size:1rem;flex-shrink:0"></i>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

@endsection
