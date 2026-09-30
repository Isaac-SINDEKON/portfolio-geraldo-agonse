@props([
    'eyebrow' => '',
    'title' => '',
    'text' => '',
])

<div class="container-x">
    <div class="mx-auto max-w-2xl text-center">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        <h2 class="mt-3 text-3xl font-extrabold sm:text-4xl">{{ $title }}</h2>
        @if ($text)
            <p class="mt-4 text-base text-slate-600">{{ $text }}</p>
        @endif
    </div>
</div>
