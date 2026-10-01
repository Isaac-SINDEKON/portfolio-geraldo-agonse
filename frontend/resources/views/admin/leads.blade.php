@extends('layouts.admin')

@section('title', 'Demandes reçues')

@section('content')

    <p class="text-sm text-slate-500">Formulaires « Demander une formation » et « Demander un devis » (CC §14 et §15).</p>

    <form method="GET" action="{{ route('admin.leads.index') }}" role="search"
          class="mt-4 flex flex-wrap items-end gap-2">
        @if ($type !== '')
            <input type="hidden" name="type" value="{{ $type }}">
        @endif

        <div class="min-w-0 flex-1">
            <label for="lead-search" class="field-label text-xs">Rechercher une demande</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input id="lead-search" type="search" name="q" value="{{ $q }}" maxlength="100"
                       autocomplete="off" class="field pl-9"
                       placeholder="Organisation, responsable, e-mail, téléphone, thème…"
                       onchange="this.form.submit()">
            </div>
        </div>

        <button type="submit" class="btn-primary px-4 py-3 text-sm">
            <x-icon name="search" class="h-4 w-4" />
            Rechercher
        </button>

        @if ($q !== '')
            <a href="{{ route('admin.leads.index', $type ? ['type' => $type] : []) }}"
               class="btn-outline px-4 py-3 text-sm">Effacer</a>
        @endif
    </form>

    <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
        @php
            $filters = [
                '' => 'Toutes ('.$counts['all'].')',
                'formation' => 'Formations ('.$counts['formation'].')',
                'devis' => 'Devis ('.$counts['devis'].')',
            ];
        @endphp

        @foreach ($filters as $key => $label)
            @php
                // Changer d'onglet ne doit pas perdre la saisie courante.
                $params = $key ? ['type' => $key] : [];

                if ($q !== '') {
                    $params['q'] = $q;
                }
            @endphp

            <a href="{{ route('admin.leads.index', $params) }}"
               @class([
                   'rounded-lg px-3 py-1.5 transition',
                   'bg-primary-600 text-white' => $type === $key,
                   'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $type !== $key,
               ])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($q !== '')
        <p class="mt-4 text-sm text-slate-500" role="status">
            {{ count($leads) }} {{ count($leads) > 1 ? 'demandes trouvées' : 'demande trouvée' }}
            pour « <span class="font-semibold text-slate-700">{{ $q }}</span> ».
        </p>
    @endif

    <div class="mt-6 space-y-4">
        @forelse ($leads as $lead)
            @php
                $data = $lead['data'] ?? [];
                $telephone = trim((string) ($data['telephone'] ?? ''));
                // Indicatif choisi par le client ; absent sur les demandes
                // enregistrees avant l'ajout du selecteur de pays.
                $indicatif = trim((string) ($data['indicatif_pays'] ?? ''));
                $mailClient = trim((string) ($data['email'] ?? ''));
                $lienWhatsapp = \App\Services\SiteContent::prospectWhatsappUrl(
                    $telephone,
                    'Bonjour '.trim((string) ($data['responsable'] ?? '')).', Géraldo Perridys AGONSE a bien reçu votre demande.',
                    $indicatif !== '' ? $indicatif : null
                );
                $lienMail = \App\Services\SiteContent::prospectMailtoUrl($mailClient);
            @endphp
            <article class="card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.leads.show', $lead['id']) }}"
                               class="text-lg font-bold break-words text-slate-900 hover:text-primary-600">
                                {{ $data['organisation'] ?? 'Organisation non précisée' }}
                            </a>
                            <span @class([
                                'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                'bg-primary-50 text-primary-700' => ($lead['type'] ?? '') === 'formation',
                                'bg-accent-500/15 text-accent-600' => ($lead['type'] ?? '') === 'devis',
                            ])>
                                {{ ($lead['type'] ?? '') === 'devis' ? 'Devis' : 'Formation' }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-slate-600">
                            {{ $data['responsable'] ?? '' }}
                            @if (! empty($data['fonction']))
                                · {{ $data['fonction'] }}
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $data['theme'] ?? '' }}
                            @if (! empty($data['participants']))
                                · {{ $data['participants'] }} participant(s)
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Reçue le {{ \Illuminate\Support\Carbon::parse($lead['created_at'] ?? now())->format('d/m/Y à H:i') }}
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        @if ($lienWhatsapp)
                            <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener"
                               class="btn-accent px-3 py-2 text-xs" title="Contacter ce client sur WhatsApp au {{ $telephone }}">
                                <x-icon name="whatsapp" class="h-4 w-4" />
                                WhatsApp
                            </a>
                        @endif

                        @if ($lienMail)
                            <a href="{{ $lienMail }}" class="btn-outline px-3 py-2 text-xs" title="Répondre à {{ $mailClient }}">
                                <x-icon name="mail" class="h-4 w-4" />
                                Email
                            </a>
                        @endif

                        <a href="{{ route('admin.leads.show', $lead['id']) }}" class="btn-outline px-3 py-2 text-xs">
                            <x-icon name="eye" class="h-4 w-4" />
                            Détails
                        </a>

                        <form method="POST" action="{{ route('admin.leads.destroy', $lead['id']) }}"
                              onsubmit="return confirm('Supprimer définitivement cette demande ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger px-3 py-2 text-xs">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="card text-center text-sm text-slate-500">
                @if ($q !== '')
                    Aucune demande ne correspond à « {{ $q }} ».
                    <a href="{{ route('admin.leads.index', $type ? ['type' => $type] : []) }}"
                       class="font-semibold text-primary-600 underline">Effacer la recherche</a>
                @else
                    Aucune demande pour le moment.
                @endif
            </p>
        @endforelse
    </div>

@endsection
