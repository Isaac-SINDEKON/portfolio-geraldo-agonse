@props(['heading' => null, 'text' => null, 'title' => null, 'textButton' => null, 'form' => 'formation'])

{{-- Appel à l'action final.

     Il occupe toute la largeur et se lit en deux temps : le message à gauche,
     les deux boutons à droite. Sur fond sombre il fait volontairement un fort
     contraste avec les sections claires qui le précèdent. --}}
<section class="relative overflow-hidden bg-nuit py-16 text-white sm:py-24">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="grille-technique absolute inset-0"></div>
        <div class="aurora aurora-1 -top-32 left-[8%] h-96 w-96 bg-primary-600/30"></div>
        <div class="aurora aurora-2 -bottom-40 right-[6%] h-80 w-80 bg-primary-500/20"></div>
    </div>

    <div class="container-x relative">
        <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-7" data-reveal="left">
                <p class="eyebrow inline-flex items-center gap-2 text-primary-300">
                    <span class="h-px w-6 bg-primary-400/70"></span>
                    Prochaine étape
                </p>

                <h2 class="mt-4 text-3xl leading-[1.12] font-bold tracking-tight text-balance text-white sm:text-4xl lg:text-[2.75rem]">
                    {{ $heading ?? ($site['settings']['cta_title'] ?? 'Prêt à optimiser la performance de vos équipes ?') }}
                </h2>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-300">
                    {{ $text ?? ($site['settings']['cta_text'] ?? 'Contactez-moi dès aujourd\'hui pour discuter de vos besoins en formation.') }}
                </p>

                {{-- Réassurance : trois réponses courtes aux trois questions qui
                     bloquent réellement la décision d'appuyer sur le bouton. --}}
                <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-slate-300">
                    @foreach ([
                        ['clock', 'Réponse sous 24 h'],
                        ['shield', 'Devis gratuit et sans engagement'],
                        ['map', 'Bénin, Togo ou à distance'],
                    ] as [$icone, $libelle])
                        <li class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-primary-300">
                                <x-icon :name="$icone" class="h-3.5 w-3.5" />
                            </span>
                            {{ $libelle }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-5" data-reveal="right">
                <div class="relative rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lift backdrop-blur-sm sm:p-8">
                    {{-- Lueur qui suit le pointeur : le panneau réagit sans
                         changer de couleur d'accent, donc sans clignoter. --}}
                    <div class="halo-survol pointer-events-none absolute inset-0 rounded-2xl" data-glow aria-hidden="true"></div>

                    <div class="relative flex flex-col items-stretch gap-3">
                        {{-- Bouton principal magnétique : c'est l'action la plus
                             lue de la page, elle mérite le déplacement. --}}
                        <x-magnetic-btn :href="route('contact', ['form' => $form]).'#formulaire-demande'"
                                        icone="arrow-right" bloc class="py-3.5">
                            {{ $title ?? 'Demander une formation' }}
                        </x-magnetic-btn>

                        <x-magnetic-btn :href="$content->whatsappUrl()" variante="whatsapp" icone="whatsapp"
                                        :externe="true" :force="0.2" bloc class="py-3.5">
                            {{ $textButton ?? 'Me contacter sur WhatsApp' }}
                        </x-magnetic-btn>

                        <a href="{{ $content->telUrl() }}"
                           class="mt-1 flex items-center justify-center gap-2 rounded-xl border border-white/15 px-5 py-3 text-sm font-semibold text-slate-200 transition-colors hover:border-white/30 hover:text-white">
                            <x-icon name="phone" class="h-4 w-4" />
                            {{ $site['settings']['phone_display'] ?? $site['settings']['phone'] ?? 'Appeler' }}
                        </a>
                    </div>

                    <p class="relative mt-5 border-t border-white/10 pt-4 text-center text-xs text-slate-400">
                        Ou écrivez directement sur WhatsApp : c'est le canal le plus rapide.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>