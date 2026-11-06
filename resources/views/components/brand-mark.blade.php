@props(['size' => 'size-11'])

<span {{ $attributes->class(['brand-mark grid shrink-0 place-items-center', $size]) }} aria-hidden="true">
    <svg viewBox="0 0 48 48" class="size-[78%]" fill="none" aria-hidden="true">
        <path d="M24 3.5 42 13.8v20.4L24 44.5 6 34.2V13.8L24 3.5Z" fill="rgba(255,255,255,.12)" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/>
        <rect x="16" y="16" width="16" height="16" rx="3" fill="rgba(255,255,255,.1)" stroke="currentColor" stroke-width="2"/>
        <rect x="20" y="20" width="8" height="8" rx="1.5" fill="currentColor"/>
        <path d="M19 11v5m10-5v5M19 32v5m10-5v5M11 19h5m-5 10h5m16-10h5m-5 10h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
</span>
