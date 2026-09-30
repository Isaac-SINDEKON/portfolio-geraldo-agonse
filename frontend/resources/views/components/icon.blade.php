@props(['name' => ''])

{{-- La taille par defaut ne s'applique que si l'appelant n'en fournit pas :
     merge() concatene les deux classes, et c'est alors la regle CSS la plus
     tardive qui l'emporte, ce qui ecraserait la taille demandee. --}}
<svg {{ $attributes->class(['h-6 w-6' => ! $attributes->has('class')]) }} fill="none" viewBox="0 0 24 24"
     stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        @case('clock')
            <path d="M12 7v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            @break
        @case('chart')
            <path d="M3 3v18h18M7 15l4-5 3 3 5-7" />
            @break
        @case('rocket')
            <path d="M5 15l-2 6 6-2m-4-4c0-6 5-12 14-13 1 9-4 15-11 16m0 0l4-4" />
            @break
        @case('heart')
            <path d="M19 14a2 2 0 01-2 2h-3l-2 3-2-3H7a2 2 0 01-2-2V7a2 2 0 012-2h3l2-3 2 3h3a2 2 0 012 2v7z" />
            @break
        @case('sparkles')
            <path d="M12 3l1.8 4.7L18.5 9.5 13.8 11.3 12 16l-1.8-4.7L5.5 9.5l4.7-1.8L12 3zM19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9L19 15z" />
            @break
        @case('briefcase')
            <path d="M4 8h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V9a1 1 0 011-1zM9 8V6a1 1 0 011-1h4a1 1 0 011 1v2" />
            @break
        @case('cog')
            <path d="M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.6 1.6 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.6 1.6 0 00-1.8-.3 1.6 1.6 0 00-1 1.5V21a2 2 0 11-4 0v-.1A1.6 1.6 0 008 19.4a1.6 1.6 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.6 1.6 0 00.3-1.8 1.6 1.6 0 00-1.5-1H2a2 2 0 110-4h.1A1.6 1.6 0 004.6 8a1.6 1.6 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.6 1.6 0 001.8.3H9a1.6 1.6 0 001-1.5V2a2 2 0 114 0v.1a1.6 1.6 0 001 1.5 1.6 1.6 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.6 1.6 0 00-.3 1.8V9a1.6 1.6 0 001.5 1H22a2 2 0 110 4h-.1a1.6 1.6 0 00-1.5 1z" />
            @break
        @case('target')
            <path d="M12 12m-9 0a9 9 0 1018 0 9 9 0 10-18 0M12 12m-5 0a5 5 0 1010 0 5 5 0 10-10 0M12 12m-1.5 0a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0" />
            @break
        @case('building')
            <path d="M4 21V5a1 1 0 011-1h9a1 1 0 011 1v16M15 9h4a1 1 0 011 1v11M8 8h3M8 12h3M8 16h3M3 21h18" />
            @break
        @case('tools')
            <path d="M14.7 6.3a4 4 0 105.6 5.6l-8.5 8.5a2.1 2.1 0 01-3-3l8.5-8.5a4 4 0 01-2.6-2.6z" />
            @break
        @case('pencil')
            <path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
            @break
        @case('users')
            <path d="M17 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9.5 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.9M16 3.1a4 4 0 010 7.8" />
            @break
        @case('map')
            <path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14" />
            @break
        @case('graduation')
            <path d="M22 9L12 4 2 9l10 5 10-5zM6 11.5V17c0 1.7 2.7 3 6 3s6-1.3 6-3v-5.5" />
            @break
        @case('mail')
            <path d="M4 5h16a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1zM3 7l9 6 9-6" />
            @break
        @case('phone')
            <path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012 4.2 2 2 0 014 2h3a2 2 0 012 1.7c.1 1 .4 1.9.7 2.8a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.3-1.1a2 2 0 012.1-.5c.9.3 1.8.6 2.8.7a2 2 0 011.7 2z" />
            @break
        @case('whatsapp')
            <path d="M12 3a9 9 0 00-7.7 13.6L3 21l4.5-1.2A9 9 0 1012 3zm0 2a7 7 0 016.3 10.6l-.3.4.7 2.6-2.7-.7-.4.3A7 7 0 115 12a7 7 0 017-7zm-3 3c-.2 0-.5.1-.7.4-.3.3-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.7 4.3 3.7 2.1.8 2.5.6 3 .6.4 0 1.4-.6 1.6-1.2.2-.6.2-1.1.1-1.2l-.6-.3-1.4-.7c-.2-.1-.4-.1-.5.1l-.7.8c-.1.2-.3.2-.5.1a5.6 5.6 0 01-2.8-2.4c-.1-.2 0-.4.1-.5l.4-.5.3-.5c.1-.2 0-.3 0-.4l-.6-1.5c-.2-.4-.3-.4-.5-.4H9z" />
            @break
        @case('check')
            <path d="M20 6L9 17l-5-5" />
            @break
        @case('quote')
            <path d="M7.5 5C5 5 3 7 3 9.5S5 14 7.5 14c.3 0 .6 0 .9-.1-.6 2-2.2 3.4-4.4 3.9v2C8.7 19 12 14.8 12 9.5 12 7 10 5 7.5 5zm9 0C14 5 12 7 12 9.5s2 4.5 4.5 4.5c.3 0 .6 0 .9-.1-.6 2-2.2 3.4-4.4 3.9v2c4.7-1.4 8-5.6 8-10.8C21 7 19 5 16.5 5z" />
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break
        @case('close')
            <path d="M6 6l12 12M18 6L6 18" />
            @break
        @case('arrow-right')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break
        @case('arrow-left')
            <path d="M19 12H5M11 18l-6-6 6-6" />
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('pencil')
            <path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
            @break
        @case('trash')
            <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14M10 11v6M14 11v6" />
            @break
        @case('upload')
            <path d="M12 16V4M7 9l5-5 5 5M4 17v2a1 1 0 001 1h14a1 1 0 001-1v-2" />
            @break
        @case('download')
            <path d="M12 4v12M7 11l5 5 5-5M4 17v2a1 1 0 001 1h14a1 1 0 001-1v-2" />
            @break
        @case('logout')
            <path d="M9 21H5a1 1 0 01-1-1V4a1 1 0 011-1h4M16 17l5-5-5-5M21 12H9" />
            @break
        @case('dashboard')
            <path d="M4 13h6V4H4v9zm0 7h6v-5H4v5zm10 0h6v-9h-6v9zm0-16v5h6V4h-6z" />
            @break
        @case('settings')
            <path d="M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.6 1.6 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.6 1.6 0 00-1.8-.3 1.6 1.6 0 00-1 1.5V21a2 2 0 11-4 0v-.1A1.6 1.6 0 009 19.4a1.6 1.6 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.6 1.6 0 00.3-1.8 1.6 1.6 0 00-1.5-1H3a2 2 0 110-4h.1A1.6 1.6 0 004.6 8a1.6 1.6 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.6 1.6 0 001.8.3H9a1.6 1.6 0 001-1.5V2a2 2 0 114 0v.1a1.6 1.6 0 001 1.5 1.6 1.6 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.6 1.6 0 00-.3 1.8V9a1.6 1.6 0 001.5 1H22a2 2 0 110 4h-.1a1.6 1.6 0 00-1.5 1z" />
            @break
        @case('gallery')
            <path d="M3 5h18v14H3V5zm0 10l5-5 4 4 3-3 6 6" />
            <circle cx="8.5" cy="9.5" r="1.5" />
            @break
        @case('inbox')
            <path d="M3 12h5l1 3h6l1-3h5M3 12l3-8h12l3 8v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6z" />
            @break
        @case('layers')
            <path d="M12 3l9 5-9 5-9-5 9-5zM3 13l9 5 9-5M3 17l9 5 9-5" />
            @break
        @case('alert')
            <path d="M12 8v5M12 16.5v.5M10.3 3.9L2.4 18a1.6 1.6 0 001.4 2.4h16.4A1.6 1.6 0 0021.6 18L13.7 3.9a1.6 1.6 0 00-2.8 0z" />
            @break
        @case('info')
            <path d="M12 16v-5M12 8.5v.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            @break
        @case('eye')
            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7zm10 3a3 3 0 100-6 3 3 0 000 6z" />
            @break
        @case('eye-off')
            <path d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.4 5.3A9.7 9.7 0 0112 5c6 0 10 7 10 7a17.6 17.6 0 01-3.4 4.3M6.3 6.4A17.3 17.3 0 002 12s4 7 10 7a9.6 9.6 0 004.2-.9" />
            @break
        @case('send')
            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" />
            @break
        @case('facebook')
            <path d="M15 3h-2.5A4.5 4.5 0 008 7.5V10H5v4h3v7h4v-7h3l1-4h-4V7.5A.5.5 0 0112.5 7H15V3z" fill="currentColor" stroke="none" />
            @break
        @case('linkedin')
            <path d="M6.5 9.5v9M6.5 6v.01M10.5 18.5v-9M10.5 13c0-2 1.5-3.5 3.5-3.5s3.5 1.5 3.5 3.5v5.5" />
            @break
        @case('x')
            <path d="M4 4l7 9-7 7M20 4l-7 9 7 7" />
            @break
        @case('link')
            <path d="M10 13a5 5 0 007.5.5l3-3a5 5 0 00-7-7l-1.7 1.7M14 11a5 5 0 00-7.5-.5l-3 3a5 5 0 007 7l1.7-1.7" />
            @break
        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
