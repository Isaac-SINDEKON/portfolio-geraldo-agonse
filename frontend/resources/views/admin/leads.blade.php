@extends('layouts.admin')

@section('title', 'Demandes reçues')

@section('content')

    <p class="text-sm text-slate-500">Formulaires « Demander une formation » et « Demander un devis » (CC §14 et §15).</p>

    <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
        @php
            $filters = [
                '' => 'Toutes ('.$counts['all'].')',
                'formation' => 'Formations ('.$counts['formation'].')',
                'devis' => 'Devis ('.$counts['devis'].')',
            ];
        @endphp

        @foreach ($filters as $key => $label)
            <a href="{{ route('admin.leads.index', $key ? ['type' => $key] : []) }}"
               @class([
                   'rounded-lg px-3 py-1.5 transition',
                   'bg-primary-600 text-white' => $type === $key,
                   'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $type !== $key,
               ])>
                {{ $label }}
            </a>
        @endforeach
    </div>

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
            <p class="card text-center text-sm text-slate-500">Aucune demande pour le moment.</p>
        @endforelse
    </div>

@endsection
