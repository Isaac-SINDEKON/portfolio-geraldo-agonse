@props([
    'partageTitre' => null,
    'partageDescription' => null,
])

@php
    $s = $site['settings'] ?? [];

    // Colonnes du pied de page : la première est large, les autres partagées.
    // Le quadrillage va jusqu'à 4 colonnes dès lg, ce qui supprime la dernière
    // colonne vide en bas de page.
    $liensNavigation = [
        ['Accueil', route('home')],
        ['À propos', route('about')],
        ['Formations', route('formations')],
        ['Services aux entreprises', route('services')],
        ['Expérience & expertise', route('experience')],
        ['Témoignages', route('testimonials')],
        ['Galerie', route('gallery')],
        ['Contact', route('contact')],
    ];

    $liensDomaines = array_slice($site['domains'] ?? [], 0, 5);
@endphp

<footer class="relative overflow-hidden border-t border-white/5 bg-slate-950 text-slate-300">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="aurora aurora-3 -top-24 left-1/3 h-72 w-72 bg-primary-600/15"></div>
    </div>

    <div class="container-x relative">
        {{-- Rangée haute : promesse à gauche, navigation et contact au centre,
             domaine d'intervention à droite. Tout le compte est occupé. --}}
        <div class="grid gap-10 py-14 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-4" data-reveal="up">
                <div class="flex items-center gap-3">
                    <x-marque variante="entete" />
                    <span class="leading-tight">
                        <span class="block text-base font-extrabold text-white">
                            {{ $s['name'] ?? 'Géraldo Perridys AGONSE' }}
                        </span>
                        <span class="block text-xs text-slate-400">
                            {{ $s['role'] ?? 'Formateur' }}
                        </span>
                    </span>
                </div>

                <p class="mt-5 text-sm leading-relaxed text-slate-400">
                    {{ $s['tagline'] ?? '' }}
                </p>

                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ([
                        ['Bénin', 'map'],
                        ['Togo', 'map'],
                        ['À distance', 'compass'],
                    ] as [$lieu, $icone])
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 px-3 py-1 text-xs text-slate-300">
                            <x-icon :name="$icone" class="h-3.5 w-3.5 text-primary-400" />
                            {{ $lieu }}
                        </span>
                    @endforeach
                </div>
            </div>

            <nav class="lg:col-span-3" aria-label="Navigation de pied de page" data-reveal="up">
                <h2 class="text-sm font-bold tracking-[0.16em] text-white uppercase">Navigation</h2>
                <ul class="mt-5 grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm lg:grid-cols-1">
                    @foreach ($liensNavigation as [$libelle, $url])
                        <li>
                            <a href="{{ $url }}"
                               class="group inline-flex items-center gap-1.5 text-slate-400 transition-colors hover:text-white">
                                {{ $libelle }}
                                <x-icon name="arrow-right"
                                        class="h-3.5 w-3.5 -translate-x-1 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="lg:col-span-3" data-reveal="up">
                <h2 class="text-sm font-bold tracking-[0.16em] text-white uppercase">Contact</h2>
                <ul class="mt-5 space-y-3.5 text-sm">
                    <li>
                        <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener"
                           class="flex items-start gap-2.5 text-slate-400 transition-colors hover:text-white">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-whatsapp/15 text-whatsapp">
                                <x-icon name="whatsapp" class="h-4 w-4" />
                            </span>
                            <span class="pt-1">{{ $s['whatsapp_display'] ?? $s['whatsapp'] ?? '' }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $content->telUrl() }}"
                           class="flex items-start gap-2.5 text-slate-400 transition-colors hover:text-white">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-500/15 text-primary-400">
                                <x-icon name="phone" class="h-4 w-4" />
                            </span>
                            <span class="pt-1">{{ $s['phone_display'] ?? $s['phone'] ?? '' }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $content->emailUrl() }}"
                           class="flex items-start gap-2.5 break-all text-slate-400 transition-colors hover:text-white">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-500/15 text-primary-400">
                                <x-icon name="mail" class="h-4 w-4" />
                            </span>
                            <span class="pt-1">{{ $s['email'] ?? '' }}</span>
                        </a>
                    </li>
                    @if (! empty($s['location']))
                        <li class="flex items-start gap-2.5 text-slate-400">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-500/15 text-primary-400">
                                <x-icon name="map" class="h-4 w-4" />
                            </span>
                            <span class="pt-1">{{ $s['location'] }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            @if (count($liensDomaines))
                <div class="lg:col-span-2" data-reveal="up">
                    <h2 class="text-sm font-bold tracking-[0.16em] text-white uppercase">Expertises</h2>
                    <ul class="mt-5 space-y-2.5 text-sm">
                        @foreach ($liensDomaines as $domaine)
                            <li>
                                <a href="{{ route('formations', ['domaine' => $domaine['title'] ?? '']) }}"
                                   class="text-slate-400 transition-colors hover:text-white">
                                    {{ $domaine['title'] ?? '' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Partage : visible au pied de toutes les pages (CC §30) ------------}}
        <div class="border-t border-white/10 py-6">
            <x-partage :titre="$partageTitre ?? null" :description="$partageDescription ?? null" />
        </div>

        <div class="flex flex-col items-center justify-between gap-4 border-t border-white/10 py-6 text-center text-xs text-slate-500 sm:flex-row sm:text-left">
            <p>© {{ date('Y') }} {{ $s['name'] ?? 'Géraldo Perridys AGONSE' }}. Tous droits réservés.</p>

            {{-- Accès discret à l'administration du site (propriétaire) --------}}
            <a href="{{ route('admin.login') }}"
               title="Espace administration"
               aria-label="Espace administration"
               class="group flex items-center gap-2 rounded-full border border-white/10 px-3 py-1.5 text-[11px] text-slate-500 transition hover:border-primary-500 hover:text-primary-300">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-600 transition group-hover:bg-primary-400"></span>
                Administration
            </a>
        </div>
    </div>
</footer>