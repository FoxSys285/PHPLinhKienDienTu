@props(['slug', 'size' => 'size-4'])

<svg viewBox="0 0 24 24" class="{{ $size }}" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($slug)
        @case('dien-tro')
            <path d="M2 12h4l2.25-4 3.5 8 3.5-8 2.25 4h4"/>
            @break
        @case('tu-dien')
            <path d="M3 12h7m4 0h7M10 6v12m4-12v12"/>
            @break
        @case('ban-dan-ic')
            <path d="M3 12h5m0-6v12m0-6 8-5m-8 5 8 5m0-10v10m0-5h5"/>
            @break
        @case('vi-dieu-khien')
            <rect x="7" y="7" width="10" height="10" rx="2"/>
            <path d="M9 2v5m6-5v5m-6 10v5m6-5v5M2 9h5m-5 6h5m10-6h5m-5 6h5"/>
            <path d="M10 10h4v4h-4z"/>
            @break
        @case('module-cam-bien')
            <rect x="4" y="5" width="16" height="14" rx="2"/>
            <circle cx="12" cy="12" r="3"/>
            <path d="M8 2v3m8-3v3M8 19v3m8-3v3"/>
            @break
        @case('board-phat-trien')
            <rect x="4" y="4" width="16" height="16" rx="2"/>
            <circle cx="7.5" cy="7.5" r="1"/>
            <circle cx="16.5" cy="16.5" r="1"/>
            <path d="M10 8h5v4h-5zm0 7h3m-3-4H7m10 0h-1M8 2v2m4-2v2m4-2v2M8 20v2m4-2v2m4-2v2"/>
            @break
        @case('dung-cu-phu-kien')
            <path d="M14.5 6.5a4 4 0 0 0-5 5L4 17l3 3 5.5-5.5a4 4 0 0 0 5-5L15 12l-3-3z"/>
            @break
        @default
            <rect x="5" y="5" width="14" height="14" rx="2"/>
            <path d="M9 9h6v6H9z"/>
    @endswitch
</svg>
