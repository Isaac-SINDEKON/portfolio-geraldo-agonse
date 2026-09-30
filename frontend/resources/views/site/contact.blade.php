@extends('layouts.site')

@section('title', 'Contact – ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE'))
@section('meta_description', 'Contactez ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE') . ' : demandez une formation ou un devis pour votre entreprise, votre ONG ou votre équipe, au Bénin et au Togo.')
@section('canonical', route('contact'))

@section('content')

    <section class="bg-primary-700 py-14 sm:py-20">
        <div class="container-x">
            <p class="label-eyebrow text-primary-200">Contact</p>
            <h1 class="mt-3 max-w-3xl text-3xl font-extrabold text-white sm:text-4xl">
                Parlons de vos besoins en formation
            </h1>
            <p class="mt-4 max-w-2xl text-base text-primary-100">
                {{ $site['settings']['role'] ?? 'Formateur' }} professionnel au Bénin et au Togo.
                Describez votre besoin : je vous réponds avec une proposition adaptée.
            </p>
        </div>
    </section>

    {{-- Choix du type de demande (CC §14 et §15).
         Une seule paire de cartes sur toute la page : cliquer « formation »
         affiche le formulaire de formation, cliquer « devis » affiche celui du
         devis. Le composant Livewire reçoit le choix et n'affiche que les
         champs correspondants. --}}
    <section class="border-b border-slate-200 bg-white py-10 sm:py-14"
             x-data="{ type: '{{ request()->query('form') === 'devis' ? 'devis' : 'formation' }}' }"
             x-on:demande-type-applique.window="type = $event.detail.type">
        <div class="container-x">
            <div class="max-w-2xl">
                <p class="label-eyebrow">Votre demande</p>
                <h2 class="mt-3 text-2xl font-extrabold text-slate-900 sm:text-3xl">
                    Que souhaitez-vous me confier ?
                </h2>
                <p class="mt-3 text-base text-slate-600">
                    Choisissez le type de demande : le formulaire juste à côté change
                    et affiche uniquement les champs utiles.
                </p>
            </div>

            <div class="mt-7 grid gap-4 sm:grid-cols-2" role="group" aria-label="Type de demande">
                <button type="button"
                        id="choisir-formation"
                        data-type="formation"
                        @click="type = 'formation'; $dispatch('demande-type', 'formation'); document.getElementById('formulaire-demande')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        :aria-pressed="type === 'formation' ? 'true' : 'false'"                        :class="type === 'formation'
                            ? 'border-primary-600 bg-primary-50/60 shadow-soft'
                            : 'border-slate-200 bg-white hover:border-primary-400 hover:bg-primary-50/50 hover:shadow-soft'"
                        class="choix-demande group flex items-start gap-4 rounded-2xl border-2 p-5 text-left transition sm:p-6">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition"
                          :class="type === 'formation'
                              ? 'bg-primary-600 text-white'
                              : 'bg-primary-100 text-primary-700 group-hover:bg-primary-600 group-hover:text-white'">
                        <x-icon name="graduation" class="h-6 w-6" />
                    </span>
                    <span class="min-w-0">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-extrabold text-slate-900">Demander une formation</span>
                            <span x-show="type === 'formation'" x-cloak
                                  class="rounded-full bg-primary-600 px-2 py-0.5 text-[11px] font-bold text-white">
                                Sélectionné
                            </span>
                        </span>
                        <span class="mt-1 block text-sm leading-relaxed text-slate-600">
                            Un programme du catalogue : durée, contenu, format et nombre de participants.
                        </span>
                        <span class="mt-2 block text-xs leading-relaxed text-slate-500">
                            Champs : organisation, responsable, fonction, téléphone, email, thème,
                            participants, format, date souhaitée, message.
                        </span>
                    </span>
                </button>

                <button type="button"
                        id="choisir-devis"
                        data-type="devis"
                        @click="type = 'devis'; $dispatch('demande-type', 'devis'); document.getElementById('formulaire-devis')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        :aria-pressed="type === 'devis' ? 'true' : 'false'"
                        :class="type === 'devis'
                            ? 'border-accent-500 bg-accent-500/10 shadow-soft'
                            : 'border-slate-200 bg-white hover:border-accent-500 hover:bg-accent-500/5 hover:shadow-soft'"
                        class="choix-demande group flex items-start gap-4 rounded-2xl border-2 p-5 text-left transition sm:p-6">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition"
                          :class="type === 'devis'
                              ? 'bg-accent-500 text-white'
                              : 'bg-accent-500/15 text-accent-600 group-hover:bg-accent-500 group-hover:text-white'">
                        <x-icon name="briefcase" class="h-6 w-6" />
                    </span>
                    <span class="min-w-0">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-extrabold text-slate-900">Demander un devis</span>
                            <span x-show="type === 'devis'" x-cloak
                                  class="rounded-full bg-accent-500 px-2 py-0.5 text-[11px] font-bold text-white">
                                Sélectionné
                            </span>
                        </span>
                        <span class="mt-1 block text-sm leading-relaxed text-slate-600">
                            Un besoin sur mesure : intra-entreprise, atelier pratique ou équipe commerciale.
                        </span>
                        <span class="mt-2 block text-xs leading-relaxed text-slate-500">
                            Champs : organisation, responsable, téléphone, email, thème,
                            participants, ville, date, durée, besoins particuliers, budget indicatif.
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 py-14 sm:py-20">
        <div class="container-x grid gap-10 lg:grid-cols-3">
            {{-- Coordonnees (CC §16) --}}
            <aside class="space-y-4 lg:sticky lg:top-28 lg:self-start">
                <div class="card">
                    <h2 class="text-lg font-bold">Coordonnées directes</h2>
                    <ul class="mt-4 space-y-4 text-sm">
                        <li>
                            <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener"
                               class="flex items-start gap-3 rounded-xl p-2 transition hover:bg-accent-500/10">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-whatsapp/15 text-whatsapp">
                                    <x-icon name="whatsapp" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-slate-800">WhatsApp</span>
                                    <span class="block text-slate-600">
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
                                    <span class="block font-semibold text-slate-800">Téléphone</span>
                                    <span class="block text-slate-600">
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
                                    <span class="block font-semibold text-slate-800">Email</span>
                                    <span class="block break-all text-slate-600">{{ $site['settings']['email'] ?? '' }}</span>
                                </span>
                            </a>
                        </li>
                        @if (! empty($site['settings']['location']))
                            <li class="flex items-start gap-3 rounded-xl p-2">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                                    <x-icon name="map" class="h-5 w-5" />
                                </span>
                                <span>
                                    <span class="block font-semibold text-slate-800">Localisation</span>
                                    <span class="block text-slate-600">{{ $site['settings']['location'] }}</span>
                                </span>
                            </li>
                        @endif
                    </ul>

                    {{-- Numéros supplémentaires saisis par le propriétaire (CC §22) : chacun
                         est cliquable, en appel direct et sur WhatsApp. --}}
                    @php $supplementaires = $content->extraPhones(); @endphp
                    @if ($supplementaires !== [])
                        <div class="mt-5 border-t border-slate-200 pt-4">
                            <h3 class="text-sm font-bold text-slate-800">Autres numéros</h3>
                            <ul class="mt-3 space-y-3 text-sm">
                                @foreach ($supplementaires as $phone)
                                    <li class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <span class="font-semibold text-slate-800">{{ $phone['label'] }}</span>
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

                <div class="rounded-2xl bg-slate-900 p-6 text-slate-300">
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
            </aside>

            {{-- Deux formulaires réellement distincts (CC §14 et §15).
                 Chacun a ses propres champs et son propre bouton. Les cartes du
                 haut servent uniquement à faire défiler vers le formulaire voulu. --}}
            <div class="space-y-8 lg:col-span-2">
                <div id="formulaire-demande"
                     class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-soft sm:p-8">
                    <livewire:demande-formation />
                </div>

                <div id="formulaire-devis"
                     class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-soft sm:p-8">
                    <livewire:demande-devis />
                </div>
            </div>
        </div>
    </section>

@endsection
