@extends('layouts.admin')

@section('title', 'Détail de la demande')

@section('content')

    @php
        $data = $lead['data'] ?? [];
        $type = $lead['type'] ?? '';
        $estDevis = $type === 'devis';

        $organ = trim((string) ($data['organisation'] ?? ''));
        $theme = trim((string) ($data['theme'] ?? ''));
        $telephone = trim((string) ($data['telephone'] ?? ''));
        // Indicatif choisi par le client dans le formulaire. Les demandes
        // enregistrees avant son ajout n'en ont pas : elles restent interpretees
        // au Benin, comme auparavant.
        $indicatif = trim((string) ($data['indicatif_pays'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $responsable = trim((string) ($data['responsable'] ?? ''));

        // Le telephone saisi par le client est utilise comme numero WhatsApp
        // et le mailto cible l'email du client : on repond directement a la personne.
        $objet = trim(($estDevis ? 'Votre demande de devis' : 'Votre demande de formation').($theme !== '' ? ' : '.$theme : ''));
        $corps = "Bonjour ".($responsable !== '' ? $responsable : '').",\n\n"
            ."Nous avons bien recu votre demande".($organ !== '' ? ' concernant '.$organ : '').".\n\n"
            ."Nous revenons vers vous tres bientot.\n\n"
            .($estDevis ? 'Geraldo Perridys AGONSE - Formateur' : 'Geraldo Perridys AGONSE - Formateur');

        $lienWhatsapp = \App\Services\SiteContent::prospectWhatsappUrl(
            $telephone,
            'Bonjour '.($responsable !== '' ? $responsable.' ' : '').', Géraldo Perridys AGONSE a bien reçu votre demande'
            .($organ !== '' ? ' concernant '.$organ : '').($theme !== '' ? ' sur le thème « '.$theme.' »' : '').'.',
            $indicatif !== '' ? $indicatif : null
        );
        $lienTel = \App\Services\SiteContent::prospectTelUrl($telephone, $indicatif !== '' ? $indicatif : null);
        $lienMail = \App\Services\SiteContent::prospectMailtoUrl($email, $objet, $corps);
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="break-words text-xl font-extrabold text-ink sm:text-2xl">
                {{ $organ !== '' ? $organ : 'Organisation non précisée' }}
            </h1>
            <p class="mt-1 text-sm text-muted">
                Reçue le {{ \Illuminate\Support\Carbon::parse($lead['created_at'] ?? now())->format('d/m/Y à H:i') }}
            </p>
        </div>

        <span @class([
            'shrink-0 rounded-full px-3 py-1 text-xs font-semibold',
            'bg-accent-500/15 text-accent-600' => $estDevis,
            'bg-primary-soft text-primary-soft-ink' => ! $estDevis,
        ])>
            {{ $estDevis ? 'Demande de devis' : 'Demande de formation' }}
        </span>
    </div>

    {{-- Actions de reponse : WhatsApp, appel et email du client (CC §5) --}}
    <section class="card mt-5">
        <h2 class="text-base font-bold text-ink">Contacter ce client</h2>
        <p class="mt-1 text-sm text-muted">
            Les coordonnées ci-dessous sont celles saisies par le client dans sa demande.
        </p>

        <div class="mt-4 flex flex-wrap gap-2.5">
            @if ($lienWhatsapp)
                <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener" class="btn-accent">
                    <x-icon name="whatsapp" class="h-4 w-4" />
                    WhatsApp {{ $telephone }}
                </a>
            @endif

            @if ($lienTel)
                <a href="{{ $lienTel }}" class="btn-outline">
                    <x-icon name="phone" class="h-4 w-4" />
                    Appeler
                </a>
            @endif

            @if ($lienMail)
                <a href="{{ $lienMail }}" class="btn-outline">
                    <x-icon name="mail" class="h-4 w-4" />
                    Répondre par email
                </a>
            @endif

            @unless ($lienWhatsapp || $lienTel || $lienMail)
                <p class="text-sm text-muted">Aucune coordonnée exploitable n'a été renseignée.</p>
            @endunless
        </div>

        @if ($lienWhatsapp)
            <p class="mt-3 flex items-start gap-2 rounded-lg bg-whatsapp/10 px-3 py-2 text-xs text-whatsapp">
                <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0" />
                <span>Le numéro saisi comme téléphone est utilisé comme numéro WhatsApp pour ouvrir la discussion.</span>
            </p>
        @endif
    </section>

    @php
        $champsCourts = [
            'Organisation' => $organ,
            'Responsable' => $responsable,
            'Fonction' => $data['fonction'] ?? null,
            'Thème' => $theme,
            'Nombre de participants' => $data['participants'] ?? null,
            'Format' => $data['format'] ?? null,
            'Ville' => $data['ville'] ?? null,
            'Date souhaitée' => $data['date_souhaitee'] ?? null,
            'Durée souhaitée' => $data['duree'] ?? null,
            'Budget indicatif' => $data['budget'] ?? null,
        ];

        // Les textes longs sont isoles : ils s'affichent en pleine largeur
        // et conservent les retours a la ligne saisis par le client.
        $textesLongs = array_filter([
            'Message' => $data['message'] ?? null,
            'Besoins particuliers' => $data['besoins'] ?? null,
        ], fn ($valeur) => ! blank($valeur));
    @endphp

    <section class="card mt-5">
        <h2 class="text-base font-bold text-ink">Informations transmises</h2>

        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach ($champsCourts as $label => $value)
                @continue(blank($value))
                <div class="min-w-0">
                    <dt class="text-xs font-semibold tracking-wide text-muted uppercase">{{ $label }}</dt>
                    <dd class="mt-1 text-sm break-words text-ink">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        <dl class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
            <div class="min-w-0">
                <dt class="text-xs font-semibold tracking-wide text-muted uppercase">Téléphone / WhatsApp</dt>
                <dd class="mt-1 text-sm break-words">
                    @if ($lienTel)
                        <a href="{{ $lienTel }}" class="font-semibold text-primary-700 hover:underline">{{ $telephone }}</a>
                    @else
                        <span class="text-ink">{{ $telephone !== '' ? $telephone : 'Non renseigné' }}</span>
                    @endif
                    @if ($indicatif !== '')
                        <span class="mt-0.5 block text-xs text-muted">
                            {{ \App\Services\CountryPhones::nom(preg_replace('/\D+/', '', $indicatif)) }}
                            — lien international :
                            {{ \App\Services\SiteContent::prospectTelUrl($telephone, $indicatif) === null ? 'non généré' : str_replace('tel:+', '+', (string) \App\Services\SiteContent::prospectTelUrl($telephone, $indicatif)) }}
                        </span>
                    @endif
                </dd>
            </div>

            <div class="min-w-0">
                <dt class="text-xs font-semibold tracking-wide text-muted uppercase">Email</dt>
                <dd class="mt-1 text-sm break-all">
                    @if ($lienMail)
                        <a href="{{ $lienMail }}" class="font-semibold text-primary-700 hover:underline">{{ $email }}</a>
                    @else
                        <span class="text-ink">{{ $email !== '' ? $email : 'Non renseigné' }}</span>
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    @foreach ($textesLongs as $label => $texte)
        <section class="card mt-5">
            <h2 class="text-base font-bold text-ink">{{ $label }}</h2>
            <p class="mt-3 max-w-prose text-sm leading-relaxed break-words whitespace-pre-line text-ink">
                {{ $texte }}
            </p>
        </section>
    @endforeach

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.leads.index') }}" class="btn-outline">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Retour aux demandes
        </a>

        <form method="POST" action="{{ route('admin.leads.destroy', $lead['id']) }}"
              onsubmit="return confirm('Supprimer définitivement cette demande ?')" class="ml-auto">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Supprimer
            </button>
        </form>
    </div>

@endsection
