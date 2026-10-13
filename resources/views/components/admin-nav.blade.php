@php
    // Menu de l'administration. Les libellés reprennent ceux du site public
    // (CC §6) : le propriétaire retrouve le même vocabulaire des deux côtés.
    //
    // Chaque entrée porte son nom de route et son URL. Les entrées qui
    // partagent une même page (les quatre rubriques d'admin.resources.index)
    // sont distinguées par le segment {resource} de l'URL.
    $menu = [
        ['route' => 'admin.dashboard', 'label' => 'Tableau de bord', 'icon' => 'dashboard'],
        ['route' => 'admin.leads.index', 'label' => 'Demandes reçues', 'icon' => 'inbox'],

        ['route' => 'admin.resources.index', 'resource' => 'domains', 'label' => 'Accueil', 'icon' => 'target'],
        ['route' => 'admin.resources.index', 'resource' => 'reasons', 'label' => 'Raisons de solliciter', 'icon' => 'sparkles'],

        ['route' => 'admin.settings.edit', 'label' => 'Identité & coordonnées', 'icon' => 'settings'],
        ['route' => 'admin.formations.index', 'label' => 'Formations', 'icon' => 'graduation'],
        ['route' => 'admin.resources.index', 'resource' => 'services', 'label' => 'Services entreprises', 'icon' => 'building'],
        ['route' => 'admin.resources.index', 'resource' => 'experiences', 'label' => 'Expérience', 'icon' => 'briefcase'],
        ['route' => 'admin.testimonials.index', 'label' => 'Témoignages', 'icon' => 'quote'],
        ['route' => 'admin.gallery.index', 'label' => 'Galerie', 'icon' => 'gallery'],
    ];

    foreach ($menu as &$entree) {
        $entree['url'] = route($entree['route'], isset($entree['resource']) ? ['resource' => $entree['resource']] : []);
    }
    unset($entree);

    // Une entrée est active si l'URL visitée correspond à sa route, ou à l'une
    // de ses sous-pages (édition d'une formation, détail d'une demande).
    $estActif = function (array $entree): bool {
        $motifs = [$entree['route']];

        // Les sous-pages heredent de l'etat de leur page mere.
        if ($entree['route'] === 'admin.formations.index') {
            $motifs[] = 'admin.formations.*';
        }

        if ($entree['route'] === 'admin.leads.index') {
            $motifs[] = 'admin.leads.*';
        }

        foreach ($motifs as $motif) {
            if (request()->routeIs($motif)) {
                // Quatre rubriques partagent admin.resources.index : on exige
                // aussi que le segment {resource} de l'URL soit le bon.
                if (isset($entree['resource'])) {
                    return request()->route('resource') === $entree['resource'];
                }

                return true;
            }
        }

        // La page « Contenu du site » est un point d'entree vers la meme
        // edition que l'entree « Identité & coordonnées ».
        return $entree['route'] === 'admin.settings.edit'
            && request()->routeIs('admin.content.index');
    };
@endphp

{{-- ============================= GRAND ÉCRAN =============================
     Barre latérale verticale, fixe, reprenant la structure du site. --}}
<aside class="sticky top-0 hidden h-screen w-64 shrink-0 flex-col border-r border-line bg-surface lg:flex">
    <a href="{{ route('admin.dashboard') }}"
       class="flex h-16 shrink-0 items-center gap-3 border-b border-line px-5 transition hover:bg-canvas">
        <x-marque variante="navigation" />
        <span class="min-w-0 leading-tight">
            <span class="block truncate text-sm font-bold text-ink">Administration</span>
            <span class="block truncate text-xs text-muted">{{ config('app.name') }}</span>
        </span>
    </a>

    <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4" aria-label="Navigation de l'administration">
        @foreach ($menu as $entree)
            @php $actif = $estActif($entree); @endphp
            <a href="{{ $entree['url'] }}"
               @class([
                   'relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                   'bg-primary-soft text-primary-soft-ink' => $actif,
                   'text-muted hover:bg-canvas hover:text-ink' => ! $actif,
               ])
               @if ($actif) aria-current="page" @endif>
                @if ($actif)
                    <span class="absolute inset-y-1.5 -left-3 w-1 rounded-r-full bg-primary-600" aria-hidden="true"></span>
                @endif

                <x-icon :name="$entree['icon']" @class(['h-5 w-5 shrink-0', 'text-primary-soft-ink' => $actif, 'text-muted' => ! $actif]) />
                <span class="min-w-0 truncate">{{ $entree['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="shrink-0 space-y-0.5 border-t border-line p-3">
        <a href="{{ route('home') }}" target="_blank" rel="noopener"
           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-muted transition hover:bg-canvas hover:text-ink">
            <x-icon name="eye" class="h-5 w-5 text-muted" />
            Voir le site
        </a>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-alert-fort transition hover:bg-alert-bg">
                <x-icon name="logout" class="h-5 w-5" />
                Se déconnecter
            </button>
        </form>
    </div>
</aside>

{{-- ============================= PETIT ÉCRAN =============================
     Même structure, dans un tiroir piloté par le bouton « trois traits ». --}}
{{-- Le sticky porte sur CE conteneur et non sur le <header> : le sticky est
     borne par la hauteur de son parent, or ce wrapper ne contient que la
     barre de 4rem (le tiroir etant en position fixed). En le collant au
     wrapper, le parent est le conteneur min-h-screen du layout, qui est
     aussi haut que la page : la barre suit donc vraiment le defilement. --}}
<div x-data="{ open: false }" class="sticky top-0 z-40 lg:hidden">
    <header class="border-b border-line bg-surface">
        <div class="container-x flex h-16 items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
                <x-marque variante="navigation" />
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-sm font-bold text-ink">Administration</span>
                    <span class="block truncate text-xs text-muted">{{ config('app.name') }}</span>
                </span>
            </a>

            <button type="button"
                    x-on:click="open = ! open"
                    :aria-expanded="open"
                    aria-controls="menu-admin"
                    aria-label="Ouvrir le menu"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-line text-ink transition hover:bg-canvas">
                <x-icon name="menu" x-show="! open" class="h-5 w-5" />
                <x-icon name="close" x-show="open" x-cloak class="h-5 w-5" />
            </button>
        </div>
    </header>

    <div x-show="open" x-cloak x-transition.opacity @click="open = false"
         class="fixed inset-0 z-40 bg-nuit/60 backdrop-blur-[2px] lg:hidden" aria-hidden="true"></div>

{{-- Panneau compact ancre en haut a droite : la pleine largeur de l'ancienne
         version couvrait l'ecran et repoussait la page vers le bas. La largeur est
         bornee, les rangees sont plus basses, et la liste defile seule. La
         geometrie reprend celle du menu public, pour que les deux se ressemblent.
         `dvh` evite le decalage quand la barre d'adresse mobile se replie. --}}
    {{-- .menu-modal impose opacity: 0 (animation d'entree) : sans la classe
         .menu-ouvert le panneau reste invisible tout en gardant son ombre,
         on ne voit donc que le voile et l'ombre derriere lui. --}}
    <nav id="menu-admin"
         x-show="open"
         x-cloak
         :class="open ? 'menu-ouvert' : ''"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-y-1 scale-95 opacity-0"
         x-transition:enter-end="translate-y-0 scale-100 opacity-100"
         class="menu-modal fixed top-[4.5rem] right-3 z-50 w-[min(20rem,calc(100vw-1.5rem))]
                 max-h-[calc(100dvh-6rem)] origin-top-right overflow-x-hidden overflow-y-auto overscroll-contain
                rounded-2xl border border-line p-2 shadow-lift lg:hidden"
         aria-label="Navigation de l'administration">
        <div class="flex flex-col">
            @foreach ($menu as $entree)
                @php $actif = $estActif($entree); @endphp
                <a href="{{ $entree['url'] }}"
                   @class([
                       'flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[0.8125rem] transition-colors',
                       'bg-primary-soft font-semibold text-primary-soft-ink' => $actif,
                       'font-medium text-ink hover:bg-canvas' => ! $actif,
                   ])
                   x-on:click="open = false"
                   @if ($actif) aria-current="page" @endif>
                    <span class="min-w-0 flex-1 truncate">{{ $entree['label'] }}</span>

                    @if ($actif)
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary-600" aria-hidden="true"></span>
                    @else
                        <x-icon :name="$entree['icon']" class="h-3.5 w-3.5 shrink-0 text-muted" />
                    @endif
                </a>
            @endforeach

            <div class="my-1.5 border-t border-line-soft"></div>

            <a href="{{ route('home') }}" target="_blank" rel="noopener"
               class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[0.8125rem] font-medium text-muted transition hover:bg-canvas hover:text-ink">
                <x-icon name="eye" class="h-3.5 w-3.5 shrink-0" />
                <span class="min-w-0 truncate">Voir le site</span>
            </a>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-[0.8125rem] font-medium text-alert-fort transition hover:bg-alert-bg">
                    <x-icon name="logout" class="h-3.5 w-3.5 shrink-0" />
                    Se déconnecter
                </button>
            </form>
        </div>
    </nav>
</div>
