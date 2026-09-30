@php
    $navigation = [
        ['route' => 'home', 'label' => 'Accueil', 'url' => route('home')],
        ['route' => 'about', 'label' => 'À propos', 'url' => route('about')],
        ['route' => 'formations', 'label' => 'Formations', 'url' => route('formations')],
        ['route' => 'services', 'label' => 'Services entreprises', 'url' => route('services')],
        ['route' => 'experience', 'label' => 'Expérience', 'url' => route('experience')],
        ['route' => 'testimonials', 'label' => 'Témoignages', 'url' => route('testimonials')],
        ['route' => 'gallery', 'label' => 'Galerie', 'url' => route('gallery')],
        ['route' => 'contact', 'label' => 'Contact', 'url' => route('contact')],
    ];
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="container-x">
        <div class="flex h-16 items-center justify-between sm:h-20">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-marque variante="entete" />
                <span class="hidden leading-tight sm:block">
                    <span class="block text-sm font-bold text-slate-900">{{ $site['settings']['name'] ?? 'Géraldo Perridys AGONSE' }}</span>
                    <span class="block text-xs text-slate-500">{{ $site['settings']['role'] ?? 'Formateur' }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigation principale">
                @foreach ($navigation as $item)
                    <a href="{{ $item['url'] }}"
                       @class([
                           'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                           'text-primary-700' => request()->routeIs($item['route']),
                           'text-slate-600 hover:text-primary-700' => ! request()->routeIs($item['route']),
                       ])
                       @if (request()->routeIs($item['route'])) aria-current="page" @endif>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden lg:block">
                <a href="{{ route('contact', ['form' => 'formation']) }}#formulaire-demande" class="btn-primary">Demander une formation</a>
            </div>

            <button type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 lg:hidden"
                    x-on:click="open = !open"
                    :aria-expanded="open"
                    aria-controls="menu-mobile"
                    aria-label="Ouvrir le menu">
                <x-icon name="menu" x-show="!open" />
                <x-icon name="close" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    <div id="menu-mobile" x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="border-t border-slate-200 bg-white lg:hidden">
        <nav class="container-x flex flex-col py-3" aria-label="Navigation mobile">
            @foreach ($navigation as $item)
                <a href="{{ $item['url'] }}"
                   @class([
                       'rounded-lg px-3 py-3 text-sm font-medium',
                       'bg-primary-50 text-primary-700' => request()->routeIs($item['route']),
                       'text-slate-700 hover:bg-primary-50' => ! request()->routeIs($item['route']),
                   ])
                   x-on:click="open = false">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ route('contact', ['form' => 'formation']) }}#formulaire-demande" class="btn-primary mt-3" x-on:click="open = false">
                Demander une formation
            </a>
        </nav>
    </div>
</header>
