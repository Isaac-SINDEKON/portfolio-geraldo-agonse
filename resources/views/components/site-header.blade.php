@php
    // Les huit entrées sont servies par le menu modal, sur toutes les largeurs.
    // Aucune barre horizontale n'apparaît dans le bandeau : huit liens serrés
    // jusqu'à 1536 px se lisaient mal et imposaient des libellés courts. Le
    // panneau, lui, affiche les libellés complets sans jamais tronquer.
    $navigation = [
        ['route' => 'home', 'label' => 'Accueil', 'court' => 'Accueil', 'url' => route('home')],
        ['route' => 'about', 'label' => 'À propos', 'court' => 'À propos', 'url' => route('about')],
        ['route' => 'formations', 'label' => 'Formations', 'court' => 'Formations', 'url' => route('formations')],
        ['route' => 'services', 'label' => 'Services aux entreprises', 'court' => 'Services', 'url' => route('services')],
        ['route' => 'experience', 'label' => 'Expérience & expertise', 'court' => 'Parcours', 'url' => route('experience')],
        ['route' => 'testimonials', 'label' => 'Témoignages', 'court' => 'Avis', 'url' => route('testimonials')],
        ['route' => 'gallery', 'label' => 'Galerie', 'court' => 'Galerie', 'url' => route('gallery')],
        ['route' => 'contact', 'label' => 'Contact', 'court' => 'Contact', 'url' => route('contact')],
    ];

    $s = $site['settings'] ?? [];

    // Sur grand écran, cinq liens tiennent dans le bandeau. Les trois rubriques
    // qui parlent de la personne et de sa crédibilité (À propos, Expérience &
    // expertise, Témoignages) sont regroupées sous un seul menu : le bandeau
    // respire, et la grammaire du site dit clairement « qui est le formateur »
    // d'un côté, « ce qu'il propose » de l'autre.
    $secondaires = ['about', 'experience', 'testimonials'];

    $sousMenu = array_values(array_filter($navigation, fn ($item) => in_array($item['route'], $secondaires, true)));

    $principales = array_values(array_filter($navigation, fn ($item) => ! in_array($item['route'], $secondaires, true)));
@endphp

{{-- Le composant porte l'état du menu (`open`, `compact`) et le voletmodal est
     son frère, et non son enfant.

     C'est indispensable : le bandeau est flouté (`backdrop-blur`), or tout
     `filter` ou `backdrop-filter` devient le référent des descendants en
     position `fixed`. Un `fixed inset-0` placé à l'intérieur du bandeau ne
     couvrirait que la hauteur du bandeau au lieu de l'écran.

     `contents` sur ce conteneur est ce qui rend le bandeau vraiment figé. Un
     élément `sticky` est borné par la hauteur de son parent : ce parent ne
     contient que le bandeau et le volet (en `fixed`, donc hors flux), il
     mesure donc exactement la hauteur du bandeau et la course du collage vaut
     zéro. En ne générant aucune boîte (`display: contents`), le parent disparaît
     au profit de `<body>` : le bandeau reste alors collé sur toute la page.
     --}}
<div x-data="headerBar"
     x-on:scroll.window="mesurer()"
     x-on:keydown.escape.window="open && fermer()"
     class="contents">

    {{-- Bandeau : il se contracte au défilement (80 px puis 64 px avec une ombre
         légère) et reste AU-DESSUS du panneau modal, z-70 contre z-60. Le bouton
         trois traits devient donc la croix de fermeture, toujours visible et
         toujours au même endroit : rien n'est à chercher pour sortir du menu. --}}
    <header class="sticky top-0 z-[70] border-b border-line-soft transition-all duration-300"
            :class="open ? 'border-transparent bg-canvas/95 backdrop-blur-2xl' : (compact ? 'bg-canvas/95 shadow-soft backdrop-blur-xl' : 'bg-canvas/80 backdrop-blur-xl')">

        {{-- Barre utilitaire : informations pratiques, sans emphraser le hero.
             Elle disparaît au défilement ; la disparition est animée sans
             x-collapse, ce plugin n'étant pas chargé par défaut. --}}
        <div x-show="! compact && ! open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="-translate-y-3 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="hidden border-b border-line-soft bg-surface/60 lg:block">
            <div class="container-x flex h-10 items-center justify-between text-xs text-muted">
                <p class="flex items-center gap-2">
                    <span class="pulse-doux inline-block h-1.5 w-1.5 rounded-full bg-whatsapp"></span>
                    Formations professionnelles au Bénin et au Togo — sur site, en intra et à distance
                </p>
                <p class="flex items-center gap-5">
                    @if (! empty($s['location']))
                        <span class="flex items-center gap-1.5">
                            <x-icon name="map" class="h-3.5 w-3.5" />
                            {{ $s['location'] }}
                        </span>
                    @endif
                    @if (! empty($s['phone_display'] ?? ($s['phone'] ?? null)))
                        <a href="{{ $content->telUrl() }}" class="flex items-center gap-1.5 transition-colors hover:text-primary-600">
                            <x-icon name="phone" class="h-3.5 w-3.5" />
                            {{ $s['phone_display'] ?? ($s['phone'] ?? '') }}
                        </a>
                    @endif
                </p>
            </div>
        </div>

        <div class="container-x">
            <div class="flex items-center justify-between gap-4 py-3 transition-all duration-300"
                 :class="compact ? 'lg:py-2' : ''">

                {{-- Marque : le logo se réduit légèrement au défilement ----------}}
                <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-3">
                    <span class="transition-transform duration-300 group-hover:scale-105">
                        <x-marque variante="entete" />
                    </span>
                    <span class="hidden leading-tight sm:block">
                        <span class="block text-sm font-bold tracking-tight text-ink">
                            {{ $s['name'] ?? 'Géraldo Perridys AGONSE' }}
                        </span>
                        <span class="block text-xs text-muted">
                            {{ $s['role'] ?? 'Formateur' }}
                        </span>
                    </span>
                </a>

                {{-- Navigation horizontale : sur grand écran seulement.
                     Huit liens ne tiennent pas dans le bandeau jusqu'à 1280 px
                     sans se tasser ; en dessous, c'est le panneau modal qui
                     prend le relais (bouton « trois traits » plus bas). Le
                     basculement se fait à xl, pas à lg : entre 1024 et 1280 px
                     il resterait trop serré pour être lisible, autant garder
                     le panneau qui affiche les libelles en entier. --}}
                <nav class="hidden flex-1 items-center justify-center gap-1 xl:flex"
                     aria-label="Navigation principale">
                    @foreach ($principales as $item)
                        @php $actif = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                        <a href="{{ $item['url'] }}"
                           @if ($actif) aria-current="page" @endif
                           title="{{ $item['label'] }}"
                           @class([
                               'relative rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-200',
                               'text-primary-600' => $actif,
                               'text-copy hover:bg-canvas hover:text-ink' => ! $actif,
                           ])>
                            @if ($actif)
                                <span class="absolute inset-x-3 -bottom-px h-0.5 rounded-full bg-primary-600"
                                      aria-hidden="true"></span>
                            @endif
                            {{ $item['court'] }}
                        </a>
                    @endforeach

                    {{-- Sous-menu « Le formateur » : À propos, Parcours, Avis.
                         Il s'ouvre au survol comme au clic, se ferme à la prise
                         de focus ailleurs et avec Échap, pour rester utilisable
                         au clavier autant qu'à la souris. --}}
                    @php $sousMenuActif = collect($sousMenu)->contains(fn ($i) => request()->routeIs($i['route']) || request()->routeIs($i['route'].'.*')); @endphp
                    <div x-data="{ ouvert: false }"
                         x-on:keydown.escape="ouvert = false"
                         class="relative"
                         x-on:mouseenter="ouvert = true"
                         x-on:mouseleave="ouvert = false">
                        <button type="button"
                                x-on:click="ouvert = ! ouvert"
                                :aria-expanded="ouvert"
                                aria-haspopup="true"
                                @class([
                                    'flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-200',
                                    'text-primary-600' => $sousMenuActif,
                                    'text-copy hover:bg-canvas hover:text-ink' => ! $sousMenuActif,
                                ])>
                            Le formateur
                            <x-icon name="chevron-down"
                                    class="h-3.5 w-3.5 transition-transform duration-200"
                                    ::class="ouvert && 'rotate-180'" />
                        </button>

                        <div x-show="ouvert"
                             x-cloak
                             x-on:click.outside="ouvert = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-1/2 top-full z-50 mt-1 w-56 -translate-x-1/2 overflow-hidden rounded-2xl border border-line bg-surface py-1.5 shadow-lift">
                            @foreach ($sousMenu as $item)
                                @php $actif = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                                <a href="{{ $item['url'] }}"
                                   @if ($actif) aria-current="page" @endif
                                   @class([
                                       'block px-4 py-2.5 text-sm transition-colors',
                                       'text-primary-600' => $actif,
                                       'text-copy hover:bg-canvas hover:text-ink' => ! $actif,
                                   ])>
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </nav>

                {{-- Actions : thème, appel à l'action, puis ouverture du menu --}}
                <div class="flex shrink-0 items-center gap-2 sm:gap-3">

                    {{-- Thème clair / sombre. L'icône montre le thème dans lequel
                         on va basculer, pas celui qui est actif : le clic reste
                         devinable sans avoir à lire l'écran. --}}
                    <div x-data="basculeTheme">
                        <button type="button"
                                x-on:click="basculer()"
                                :title="libelle"
                                :aria-label="libelle"
                                class="group relative flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-line bg-card text-copy
                                       transition-all duration-300 hover:border-primary-400 hover:text-primary-600 hover:shadow-soft">
                            <span class="absolute inset-0 bg-primary-50 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>

                            <x-icon name="sun"
                                    class="h-5 w-5 transition-all duration-500"
                                    x-show="! sombre"
                                    x-transition:enter="transition duration-300"
                                    x-transition:enter-start="rotate-90 scale-50 opacity-0"
                                    x-transition:enter-end="rotate-0 scale-100 opacity-100" />

                            <x-icon name="moon" x-cloak
                                    class="h-5 w-5 transition-all duration-500"
                                    x-show="sombre"
                                    x-transition:enter="transition duration-300"
                                    x-transition:enter-start="-rotate-90 scale-50 opacity-0"
                                    x-transition:enter-end="rotate-0 scale-100 opacity-100" />
                        </button>
                    </div>

                    <a href="{{ route('contact', ['form' => 'formation']) }}#formulaire-demande"
                       class="btn-primary hidden sm:inline-flex">
                        Demander une formation
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>

                    {{-- Ouverture et fermeture du panneau. Le libellé suit l'état,
                         donc le bouton reste compréhensible au lecteur d'écran
                         comme à la souris, et `aria-controls` nomme bien le
                         panneau qu'il pilote.

                         `xl:hidden` : au-delà de 1280 px la barre de navigation
                         est visible, le bouton ferait doublon. --}}
                    <button type="button"
                            x-on:click="basculer()"
                            :aria-expanded="open"
                            :aria-label="open ? 'Fermer le menu' : 'Ouvrir le menu'"
                            aria-haspopup="dialog"
                            aria-controls="menu-principal"
                            class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-card text-ink transition-colors hover:border-primary-400 hover:text-primary-600 hover:shadow-soft xl:hidden">
                        <x-icon name="menu" class="h-5 w-5"
                                x-show="! open"
                                x-transition:enter="transition duration-200"
                                x-transition:enter-start="rotate-90 opacity-0"
                                x-transition:enter-end="rotate-0 opacity-100" />

                        <x-icon name="close" x-cloak class="h-5 w-5"
                                x-show="open"
                                x-transition:enter="transition duration-200"
                                x-transition:enter-start="-rotate-90 opacity-0"
                                x-transition:enter-end="rotate-0 opacity-100" />
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- ------------------------------------------------------------------
         Menu de navigation : une seule presentation, toutes largeurs.

         Il y avait deux menus : une carte compacte sur telephone et un modal
         editorial plein ecran a partir de `sm:`. Deux Presentations, c'est deux
         jeux de reglages a entretenir, et la seconde etait bien trop grande sur
         un petit ecran. Il n'y en a plus qu'une : la carte compacte, partout ou
         le bouton hamburger est affiche (sous `xl`).

         La carte est ancree en haut a droite, comme le menu d'administration :
         elle occupe le plus petit rectangle qui tienne les huit rubriques, et le
         reste de l'ecran reste lisible derriere le voile. Elle defile seule si la
         liste est plus haute que la place disponible.

         Le fond vient d'une media query dans app.css, pas d'une utilitaire
         Tailwind : les deux sont posees sur le meme element que `.menu-modal`,
         qui est emise apres la couche des utilitaires.
         ------------------------------------------------------------------ --}}
    <div x-show="open"
         x-cloak
         x-transition.opacity
         x-on:click="fermer()"
         class="fixed inset-0 z-[60] bg-nuit/60 backdrop-blur-[2px]"
         aria-hidden="true"></div>

    <nav id="menu-principal"
         x-show="open"
         x-cloak
         x-on:keydown="piegerFocus($event)"
         :class="open ? 'menu-ouvert' : ''"
         role="dialog"
         aria-modal="true"
         aria-label="Menu de navigation"
         class="menu-modal fixed top-[4.25rem] right-3 z-[61] w-[min(20rem,calc(100vw-1.5rem))]
                max-h-[calc(100dvh-6rem)] origin-top-right overflow-x-hidden overflow-y-auto overscroll-contain
                rounded-2xl border border-line p-2 shadow-lift lg:top-[6.75rem]">

        <div class="flex flex-col">
            @foreach ($navigation as $index => $item)
                @php $actif = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                <a href="{{ $item['url'] }}"
                   @if ($actif) aria-current="page" @endif
                   x-on:click="fermer()"
                   @class([
                       'flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[0.8125rem] transition-colors',
                       'bg-primary-soft font-semibold text-primary-soft-ink' => $actif,
                       'font-medium text-ink hover:bg-canvas' => ! $actif,
                   ])>
                    <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>

                    @if ($actif)
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary-600" aria-hidden="true"></span>
                    @else
                        <x-icon name="arrow-up-right" class="h-3.5 w-3.5 shrink-0 text-muted" />
                    @endif
                </a>
            @endforeach

            @if (! empty($s['phone_display'] ?? ($s['phone'] ?? null)) || ! empty($s['email']))
                <div class="mt-1.5 border-t border-line-soft pt-1.5">
                    @if (! empty($s['phone_display'] ?? ($s['phone'] ?? null)))
                        <a href="{{ $content->telUrl() }}"
                           class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[0.8125rem] font-medium text-muted transition-colors hover:bg-canvas hover:text-ink">
                            <x-icon name="phone" class="h-3.5 w-3.5 shrink-0" />
                            <span class="min-w-0 truncate">{{ $s['phone_display'] ?? ($s['phone'] ?? '') }}</span>
                        </a>
                    @endif

                    @if (! empty($s['email']))
                        <a href="{{ $content->emailUrl() }}"
                           class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[0.8125rem] font-medium text-muted transition-colors hover:bg-canvas hover:text-ink">
                            <x-icon name="mail" class="h-3.5 w-3.5 shrink-0" />
                            <span class="min-w-0 truncate">{{ $s['email'] }}</span>
                        </a>
                    @endif
                </div>
            @endif

            <a href="{{ route('contact', ['form' => 'formation']) }}#formulaire-demande"
               class="mt-1.5 flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-2.5 py-2 text-[0.8125rem] font-semibold text-on-brand transition-colors hover:bg-primary-hover"
               x-on:click="fermer()">
                Demander une formation
                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>
    </nav>
</div>