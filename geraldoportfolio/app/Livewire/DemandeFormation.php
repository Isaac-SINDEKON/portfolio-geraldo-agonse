<?php

namespace App\Livewire;

use App\Livewire\Concerns\ValideTelephone;
use App\Services\CountryPhones;
use App\Services\LeadService;
use App\Services\SiteContent;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Formulaire « Demander une formation » (CC §14).
 *
 * Volontairement distinct du formulaire de devis : les deux demandes sont
 * différentes, avec leurs propres champs. Ce composant ne contient aucun
 * champ de devis et ne dépend d'aucun sélecteur de type.
 */
class DemandeFormation extends Component
{
    use ValideTelephone;

    public string $organisation = '';

    public string $responsable = '';

    public string $fonction = '';

    public string $telephone = '';

    /** Indicatif du pays choisi par le client, séparé du numéro national. */
    public string $indicatif_pays = CountryPhones::PAYS_PAR_DEFAUT;

    public string $email = '';

    public string $theme = '';

    public string $participants = '';

    public string $format = '';

    public string $date_souhaitee = '';

    public string $message = '';

    /** Champ piège anti-spam : invisible pour le client, rempli par les robots. */
    public string $website = '';

    public bool $sent = false;

    public string $errorMessage = '';

    public function mount(): void
    {
        // Un appel à l'action depuis une formation (?form=formation&theme=...)
        // pré-remplit le thème sans pour autant verrouiller le champ.
        $this->theme = Str::limit(trim((string) request()->query('theme', '')), 120, '');
    }

    protected function rules(): array
    {
        return [
            'organisation' => ['required', 'string', 'max:255'],
            'responsable' => ['required', 'string', 'max:255'],
            'fonction' => ['nullable', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:50', $this->validerNumero()],
            'indicatif_pays' => ['required', 'string', $this->validerIndicatif()],
            'email' => ['required', 'email', 'max:255'],
            'theme' => ['nullable', 'string', 'max:255'],
            'participants' => ['nullable', 'integer', 'min:1'],
            'format' => ['nullable', 'in:Présentiel,Distanciel,Hybride'],
            'date_souhaitee' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'format.in' => 'Choisissez un format : présentiel, distanciel ou hybride.',
            'email.email' => 'Indiquez une adresse email valide.',
        ];
    }

    protected function attributes(): array
    {
        return [
            'organisation' => "l'organisation",
            'responsable' => 'la personne responsable',
            'telephone' => 'le numéro WhatsApp',
            'date_souhaitee' => 'la date souhaitée',
        ];
    }

    public function submit(LeadService $leads, SiteContent $content)
    {
        $this->validate();

        // Anti-spam : la requete est acceptee sans rien enregistrer, mais le
        // client voit le meme message de succes qu'une vraie demande.
        if (filled($this->website)) {
            $this->sent = true;
            $this->resetErrorBag();

            return null;
        }

        $payload = ['type' => 'formation'] + array_filter([
            'organisation' => $this->organisation,
            'responsable' => $this->responsable,
            'fonction' => $this->fonction,
            'telephone' => $this->telephone,
            'indicatif_pays' => $this->indicatif_pays,
            'email' => $this->email,
            'theme' => $this->theme,
            'participants' => $this->participants,
            'format' => $this->format,
            'date_souhaitee' => $this->date_souhaitee,
            'message' => $this->message,
        ], fn ($valeur) => $valeur !== null && $valeur !== '');

        try {
            $leads->submit($payload);
        } catch (\Throwable $e) {
            report($e);

            $this->errorMessage = 'Votre demande n\'a pas pu être envoyée. Merci de réessayer.';

            return null;
        }

        $this->sent = true;
        $this->resetErrorBag();
        // On vide les champs un par un : un reset() global remettrait aussi
        // $sent a false et ferait disparaitre le message de succes.
        $this->organisation = '';
        $this->responsable = '';
        $this->fonction = '';
        $this->telephone = '';
        $this->email = '';
        $this->theme = '';
        $this->participants = '';
        $this->format = '';
        $this->date_souhaitee = '';
        $this->message = '';
        $content->forget();
        $this->dispatch('lead-sent', type: 'formation');

        return null;
    }

    /** Réaffiche le formulaire après un envoi réussi, sans changer de type. */
    public function nouvelleDemande(): void
    {
        $this->sent = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        $site = app(SiteContent::class)->all();

        return view('livewire.demande-formation', [
            'themes' => array_map(fn ($formation) => $formation['title'], $site['formations'] ?? []),
            'pays' => $this->optionsPays(),
            'exempleTelephone' => $this->exempleTelephone(),
        ]);
    }
}
