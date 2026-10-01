@props([
    'eyebrow' => '',
    'title' => '',
    'text' => '',
])

<div class="container-x">
    <div class="mx-auto max-w-2xl text-center">
        @if ($eyebrow)
            <p class="eyebrow inline-flex items-center gap-2">
                <span class="h-px w-6 bg-primary-300"></span>
                {{ $eyebrow }}
                <span class="h-px w-6 bg-primary-300"></span>
            </p>
        @endif
        <h2 class="mt-4 text-3xl leading-tight font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $title }}</h2>
        @if ($text)
            <p class="mt-4 text-base leading-relaxed text-slate-600">{{ $text }}</p>
        @endif
    </div>
</div>
