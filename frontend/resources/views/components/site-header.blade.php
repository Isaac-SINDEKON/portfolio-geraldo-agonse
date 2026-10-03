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
         Panneau modal : plein écran, sur toutes les largeurs.
         Il couvre la page (fond opaque), garde la moitié gauche pour
         l'identité et les coordonnées directes, et donne les huit rubriques
         sur la moitié droite en lignes larges. Le défilement de la page est
         bloqué tant qu'il est ouvert (voir `headerBar`), sinon le visiteur
         lirait un texte qui glisse pendant qu'il choisit une section.
         ------------------------------------------------------------------ --}}
    <div id="menu-principal"
         x-show="open"
         x-cloak
         x-on:keydown="piegerFocus($event)"
         :class="open ? 'menu-ouvert' : ''"
         role="dialog"
         aria-modal="true"
         aria-label="Menu de navigation"
         class="menu-modal fixed inset-0 z-[60] overflow-y-auto overflow-x-hidden overscroll-contain">

        {{-- Décor : deux halos de couleur et une trame de filets. La trame est
             masquée vers le bas pour que le fond reste calme sous le texte. --}}
        <div class="menu-modal__fond pointer-events-none absolute inset-0" aria-hidden="true"></div>

        <div class="relative mx-auto flex min-h-full w-full max-w-[1400px] flex-col px-5 pt-24 pb-8 sm:px-8 lg:pt-28">

            <div class="grid flex-1 gap-x-12 gap-y-10 lg:grid-cols-12">

                {{-- Colonne identité : on donne la réponse à « qui appelle-t-on »
                     avant d'imposer le choix d'une rubrique. --}}
                <aside class="lg:col-span-4 xl:col-span-3">
                    <p class="lien-modal font-serif text-3xl font-semibold leading-tight tracking-tight text-ink sm:text-4xl"
                       style="transition-delay: 0ms">
                        Où voulez-vous aller&nbsp;?
                    </p>

                    <p class="lien-modal mt-4 max-w-sm text-sm leading-relaxed text-muted"
                       style="transition-delay: 45ms">
                        Formations, interventions en entreprise et accompagnement.
                        Huit sections, un seul geste.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm">
                        @if (! empty($s['phone_display'] ?? ($s['phone'] ?? null)))
                            <li class="lien-modal" style="transition-delay: 90ms">
                                <a href="{{ $content->telUrl() }}"
                                   class="group flex items-center gap-3 text-copy transition-colors hover:text-primary-600">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line bg-card text-muted transition-colors group-hover:border-primary-400 group-hover:text-primary-600">
                                        <x-icon name="phone" class="h-4 w-4" />
                                    </span>
                                    {{ $s['phone_display'] ?? ($s['phone'] ?? '') }}
                                </a>
                            </li>
                        @endif

                        @if (! empty($s['email']))
                            <li class="lien-modal" style="transition-delay: 135ms">
                                <a href="{{ $content->emailUrl() }}"
                                   class="group flex items-center gap-3 break-all text-copy transition-colors hover:text-primary-600">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line bg-card text-muted transition-colors group-hover:border-primary-400 group-hover:text-primary-600">
                                        <x-icon name="mail" class="h-4 w-4" />
                                    </span>
                                    {{ $s['email'] }}
                                </a>
                            </li>
                        @endif

                        @if (! empty($s['location']))
                            <li class="lien-modal flex items-center gap-3 text-copy" style="transition-delay: 180ms">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line bg-card text-muted">
                                    <x-icon name="map" class="h-4 w-4" />
                                </span>
                                {{ $s['location'] }}
                            </li>
                        @endif
                    </ul>
                </aside>

                {{-- Colonne navigation : les huit rubriques en lignes pleines
                     largeur. Le numéro sert de repère visuel et confirme d'un
                     coup d'œil qu'il n'y a rien d'autre à découvrir. --}}
                <nav class="lg:col-span-8 xl:col-span-9" aria-label="Navigation principale">
                    @foreach ($navigation as $index => $item)
                        @php($numero = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT))
                        <a href="{{ $item['url'] }}"
                           @if (request()->routeIs($item['route'])) aria-current="page" @endif
                           x-on:click="fermer()"
                           style="transition-delay: {{ 90 + $index * 45 }}ms"
                           class="lien-modal group flex items-center gap-4 border-b border-line-soft py-4 sm:gap-6 sm:py-5">
                            <span class="w-8 shrink-0 text-xs font-semibold tabular-nums text-muted">{{ $numero }}</span>

                            <span class="lien-modal__titre flex-1 font-serif text-3xl font-semibold leading-none tracking-tight text-ink transition-transform duration-300 ease-out group-hover:translate-x-2 sm:text-4xl xl:text-5xl">
                                {{ $item['label'] }}
                            </span>

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-line text-muted transition-all duration-300 ease-out
                                         group-hover:border-primary-500 group-hover:bg-primary-600 group-hover:text-on-brand">
                                <x-icon name="arrow-up-right" class="h-4 w-4 transition-transform duration-300 ease-out group-hover:rotate-45" />
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Pied de panneau : les deux décisions possibles, et rien de plus. --}}
            <div class="mt-10 flex flex-col gap-4 border-t border-line-soft pt-6 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('contact', ['form' => 'formation']) }}#formulaire-demande"
                   class="btn-primary"
                   x-on:click="fermer()">
                    Demander une formation
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>

                <a href="{{ $content->whatsappUrl() }}"
                   target="_blank"
                   rel="noopener"
                   class="btn-whatsapp">
                    <x-icon name="whatsapp" class="h-4 w-4" />
                    Écrire sur WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>