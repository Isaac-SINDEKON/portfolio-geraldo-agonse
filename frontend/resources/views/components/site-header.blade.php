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

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-100 bg-white/90 backdrop-blur-md">
    <div class="container-x">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <x-marque variante="entete" />
                <span class="hidden leading-tight sm:block">
                    <span class="block text-sm font-bold tracking-tight text-slate-900">{{ $site['settings']['name'] ?? 'Géraldo Perridys AGONSE' }}</span>
                    <span class="block text-xs text-slate-500">{{ $site['settings']['role'] ?? 'Formateur' }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Navigation principale">
                @foreach ($navigation as $item)
                    <a href="{{ $item['url'] }}"
                       @class([
                           'nav-link',
                           'nav-link-active' => request()->routeIs($item['route']),
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
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-700 transition-colors hover:bg-slate-100 lg:hidden"
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
         class="border-t border-slate-100 bg-white lg:hidden">
        <nav class="container-x flex flex-col py-3" aria-label="Navigation mobile">
            @foreach ($navigation as $item)
                <a href="{{ $item['url'] }}"
                   @class([
                       'nav-link rounded-lg py-2.5',
                       'nav-link-active' => request()->routeIs($item['route']),
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
