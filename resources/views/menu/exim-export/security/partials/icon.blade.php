{{-- Ikon garis 24x24, stroke 2px, currentColor. Pakai: @include('menu.exim-export.security.partials.icon', ['name' => 'clock']) --}}
<svg class="sg-icon {{ $class ?? '' }}" viewBox="0 0 24 24" aria-hidden="true">
@switch($name)
    @case('back')<path d="M15 18l-6-6 6-6"/>@break
    @case('next')<path d="M9 6l6 6-6 6"/>@break
    @case('play')<path d="M8 5v14l11-7z"/>@break
    @case('sound')<path d="M4 9v6h4l5 4V5L8 9z"/><path d="M16.5 8.5a5 5 0 0 1 0 7M19 6a8.5 8.5 0 0 1 0 12"/>@break
    @case('mute')<path d="M4 9v6h4l5 4V5L8 9z"/><path d="M17 9.5l5 5M22 9.5l-5 5"/>@break
    @case('search')<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>@break
    @case('camera')<path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/>@break
    @case('video')<rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10l5-3v10l-5-3z"/>@break
    @case('id')<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.6-1.5 1.8-2 3-2s2.4.5 3 2M14 10h4M14 13h3"/>@break
    @case('truck')<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>@break
    @case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
    @case('refresh')<path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 4v7h-7"/>@break
    @case('calendar')<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/>@break
    @case('check')<path d="M5 12.5l4.5 4.5L19 7"/>@break
    @case('warn')<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.01"/>@break
    @case('info')<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.01"/>@break
    @case('qr')<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4z"/><path d="M14 14h2v2h-2zM20 14v2M14 20h2M18 18h2v2h-2z"/>@break
    @case('close')<path d="M6 6l12 12M18 6L6 18"/>@break
    @case('edit')<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>@break
    @case('trash')<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>@break
    @case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>@break
    @case('moon')<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"/>@break
    @case('expand')<path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/>@break
    @case('shrink')<path d="M9 4v5H4M20 9h-5V4M15 20v-5h5M4 15h5v5"/>@break
    @case('logout')<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/>@break
    @case('wifi-off')<path d="M3 3l18 18M8.5 16.5a5 5 0 0 1 7 0M5 12.5a10 10 0 0 1 4-2.3M19 12.5a10 10 0 0 0-3-2M12 20v.01"/>@break
    @case('dock')<path d="M3 21V9l9-5 9 5v12"/><path d="M7 21v-8h10v8M7 17h10"/>@break
    @case('upload')<path d="M12 16V4M7 9l5-5 5 5M5 20h14"/>@break
    @case('download')<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>@break
    @case('sidebar')<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16"/>@break
@endswitch
</svg>
