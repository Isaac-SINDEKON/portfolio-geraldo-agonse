@extends('layouts.site')

@section('title', 'Contact – ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE'))
@section('meta_description', 'Contactez ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE') . ' : demandez une formation ou un devis pour votre entreprise, votre ONG ou votre équipe, au Bénin et au Togo.')
@section('canonical', route('contact'))

@section('content')

    {{-- Bandeau de page : toujours sombre, y compris en thème clair. C'est le
         repère visuel qui dit au visiteur « vous êtes arrivé quelque part ». --}}
    <section class="relative overflow-hidden bg-nuit py-16 text-white sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="grille-technique absolute inset-0"></div>
            <div class="aurora aurora-1 -top-32 left-[6%] h-96 w-96 bg-primary-600/30"></div>
        </div>

        <div class="container-x relative">
            <x-section-title
                variante="sombre"
                align="gauche"
                eyebrow="Contact"
                title="Parlons de vos besoins en formation"
                text="{{ $site['settings']['role'] ?? 'Formateur' }} professionnel au Bénin et au Togo. Décrivez votre besoin : je vous réponds avec une proposition adaptée." />
        </div>
    </section>

    {{-- Choix du type de demande (CC §14 et §15).
         Une seule paire de cartes sur toute la page : cliquer « formation »
         affiche le formulaire de formation, cliquer « devis » affiche celui du
         devis. Le composant Livewire reçoit le choix et n'affiche que les
         champs correspondants. --}}
    <section class="border-b border-line-soft bg-surface py-10 sm:py-14"
             data-reveal="up"
             x-data="{ type: '{{ request()->query('form') === 'devis' ? 'devis' : 'formation' }}' }"
             x-on:demande-type-applique.window="type = $event.detail.type">
        <div class="container-x">
            <x-section-title
                eyebrow="Votre demande"
                title="Que souhaitez-vous me confier ?"
                text="Choisissez le type de demande : le formulaire juste à côté change et affiche uniquement les champs utiles." />

            <div class="mt-8 grid gap-4 sm:grid-cols-2" role="group" aria-label="Type de demande">
                <button type="button"
                        id="choisir-formation"
                        data-type="formation"
                        @click="type = 'formation'; $dispatch('demande-type', 'formation'); document.getElementById('formulaire-demande')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        :aria-pressed="type === 'formation' ? 'true' : 'false'"
                        :class="type === 'formation'
                            ? 'border-primary-600 bg-primary-50/60 shadow-soft'
                            : 'border-line-soft bg-surface hover:border-primary-400 hover:bg-primary-50/40 hover:shadow-soft'"
                        class="choix-demande group flex items-start gap-4 rounded-2xl border-2 p-5 text-left transition sm:p-6">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition"
                          :class="type === 'formation'
                              ? 'bg-primary-600 text-on-brand'
                              : 'bg-primary-100 text-primary-700 group-hover:bg-primary-600 group-hover:text-on-brand'">
                        <x-icon name="graduation" class="h-6 w-6" />
                    </span>
                    <span class="min-w-0">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-bold text-ink">Demander une formation</span>
                            <span x-show="type === 'formation'" x-cloak
                                  class="rounded-full bg-primary-600 px-2 py-0.5 text-[11px] font-bold text-on-brand">
                                Sélectionné
                            </span>
                        </span>
                        <span class="mt-1 block text-sm leading-relaxed text-copy">
                            Un programme du catalogue : durée, contenu, format et nombre de participants.
                        </span>
                        <span class="mt-2 block text-xs leading-relaxed text-muted">
                            Champs : organisation, responsable, fonction, téléphone, email, thème,
                            participants, format, date souhaitée, message.
                        </span>
                    </span>
                </button>

                {{-- Le devis garde une teinte propre : pastille sombre au lieu
                     d'un aplat d'accent, sinon les deux cartes se confondraient
                     puisque l'accent du thème est unique. --}}
                <button type="button"
                        id="choisir-devis"
                        data-type="devis"
                        @click="type = 'devis'; $dispatch('demande-type', 'devis'); document.getElementById('formulaire-devis')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        :aria-pressed="type === 'devis' ? 'true' : 'false'"
                        :class="type === 'devis'
                            ? 'border-primary-600 bg-primary-50/60 shadow-soft'
                            : 'border-line-soft bg-surface hover:border-primary-400 hover:bg-primary-50/40 hover:shadow-soft'"
                        class="choix-demande group flex items-start gap-4 rounded-2xl border-2 p-5 text-left transition sm:p-6">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition"
                          :class="type === 'devis'
                              ? 'bg-nuit text-white'
                              : 'bg-nuit/10 text-ink group-hover:bg-nuit group-hover:text-white'">
                        <x-icon name="briefcase" class="h-6 w-6" />
                    </span>
                    <span class="min-w-0">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-bold text-ink">Demander un devis</span>
                            <span x-show="type === 'devis'" x-cloak
                                  class="rounded-full bg-nuit px-2 py-0.5 text-[11px] font-bold text-white">
                                Sélectionné
                            </span>
                        </span>
                        <span class="mt-1 block text-sm leading-relaxed text-copy">
                            Un besoin sur mesure : intra-entreprise, atelier pratique ou équipe commerciale.
                        </span>
                        <span class="mt-2 block text-xs leading-relaxed text-muted">
                            Champs : organisation, responsable, téléphone, email, thème,
                            participants, ville, date, durée, besoins particuliers, budget indicatif.
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </section>

    <section class="bg-canvas py-14 sm:py-20">
        <div class="container-x grid gap-10 lg:grid-cols-12 lg:gap-12">
            {{-- Coordonnees (CC §16) : 4 colonnes, elle reste lisible sans
                 jamais descendre sous la moitié de la largeur. --}}
            <aside class="space-y-4 lg:col-span-4 lg:self-start lg:sticky lg:top-28">
                <div class="card">
                    <h2 class="text-lg font-bold">Coordonnées directes</h2>
                    <ul class="mt-4 space-y-4 text-sm">
                        <li>
                            <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener"
                               class="flex items-start gap-3 rounded-xl p-2 transition hover:bg-primary-50">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-whatsapp/15 text-whatsapp">
                                    <x-icon name="whatsapp" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-ink">WhatsApp</span>
                                    <span class="block text-copy">
                                        {{-- Le service renvoie le numéro désigné parmi les
                                             numéros supplémentaires, ou le numéro
                                             principal si aucun n'est désigné. --}}
                                        {{ $content->primaryWhatsappNumber() }}
                                    </span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ $content->telUrl() }}"
                               class="flex items-start gap-3 rounded-xl p-2 transition hover:bg-primary-50">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                                    <x-icon name="phone" class="h-5 w-5" />
                                </span>
                                <span>
                                    <span class="block font-semibold text-ink">Téléphone</span>
                                    <span class="block text-copy">
                                        {{ $site['settings']['phone_display'] ?? $site['settings']['phone'] ?? '' }}
                                    </span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ $content->emailUrl() }}"
                               class="flex items-start gap-3 rounded-xl p-2 transition hover:bg-primary-50">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                                    <x-icon name="mail" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-ink">Email</span>
                                    <span class="block break-all text-copy">{{ $site['settings']['email'] ?? '' }}</span>
                                </span>
                            </a>
                        </li>
                        @if (! empty($site['settings']['location']))
                            <li class="flex items-start gap-3 rounded-xl p-2">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                                    <x-icon name="map" class="h-5 w-5" />
                                </span>
                                <span>
                                    <span class="block font-semibold text-ink">Localisation</span>
                                    <span class="block text-copy">{{ $site['settings']['location'] }}</span>
                                </span>
                            </li>
                        @endif
                    </ul>

                    {{-- Numéros supplémentaires saisis par le propriétaire (CC §22) : chacun
                         est cliquable, en appel direct et sur WhatsApp. --}}
                    @php $supplementaires = $content->extraPhones(); @endphp
                    @if ($supplementaires !== [])
                        <div class="mt-5 border-t border-line-soft pt-4">
                            <h3 class="text-sm font-bold text-ink">Autres numéros</h3>
                            <ul class="mt-3 space-y-3 text-sm">
                                @foreach ($supplementaires as $phone)
                                    <li class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <span class="font-semibold text-ink">{{ $phone['label'] }}</span>
                                        <a href="{{ $phone['tel'] }}" class="text-primary-700 underline decoration-primary-300 underline-offset-2 hover:decoration-primary-600">
                                            {{ $phone['number'] }}
                                        </a>
                                        <a href="{{ $phone['whatsapp'] }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-whatsapp hover:underline"
                                           aria-label="Écrire sur WhatsApp au {{ $phone['label'] }}">
                                            <x-icon name="whatsapp" class="h-4 w-4" />
                                            WhatsApp
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-nuit p-6 text-slate-300">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grille-technique absolute inset-0"></div>
                    </div>

                    <div class="relative">
                        <h2 class="text-lg font-bold text-white">Besoin d’une réponse rapide ?</h2>
                        <p class="mt-2 text-sm leading-relaxed">
                            Écrivez directement sur WhatsApp : c’est le canal le plus rapide pour
                            une demande de formation ou un devis.
                        </p>
                        <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener" class="btn-whatsapp mt-5 w-full">
                            <x-icon name="whatsapp" class="h-5 w-5" />
                            Écrire sur WhatsApp
                        </a>
                    </div>
                </div>
            </aside>

            {{-- Deux formulaires réellement distincts (CC §14 et §15).
                 Chacun a ses propres champs et son propre bouton. Les cartes du
                 haut servent uniquement à faire défiler vers le formulaire voulu. --}}
            <div class="space-y-8 lg:col-span-8">
                <div id="formulaire-demande"
                     class="card scroll-mt-28 sm:p-8">
                    <livewire:demande-formation />
                </div>

                <div id="formulaire-devis"
                     class="card scroll-mt-28 sm:p-8">
                    <livewire:demande-devis />
                </div>
            </div>
        </div>
    </section>

@endsection