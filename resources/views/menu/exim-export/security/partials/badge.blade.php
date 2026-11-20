@php
    $badges = [
        'late'       => ['sg-badge--late', 'warn', 'Terlambat'],
        'today'      => ['sg-badge--today', 'clock', 'Hari ini'],
        'upcoming'   => ['', 'calendar', 'Dijadwalkan'],
        'incomplete' => ['sg-badge--incomplete', 'info', 'Belum lengkap'],
        'arrived'    => ['sg-badge--arrived', 'check', 'Tiba'],
        'rejected'   => ['sg-badge--rejected', 'warn', 'Ditolak'],
        'departed'   => ['', 'truck', 'Keluar'],
    ];
    [$badgeClass, $badgeIcon, $badgeText] = $badges[$status];
@endphp
<span class="sg-badge {{ $badgeClass }}">@include('menu.exim-export.security.partials.icon', ['name' => $badgeIcon]){{ $badgeText }}</span>
