<?php

namespace App\Livewire;

use App\Livewire\Concerns\ValideTelephone;
use App\Services\ApiClient;
use App\Services\CountryPhones;
use App\Services\SiteContent;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Formulaire « Demander un devis » (CC §15).
 *
 * Volontairement distinct du formulaire de formation : les deux demandes sont
 * différentes, avec leurs propres champs. Ce composant ne contient aucun
 * champ de formation (ni fonction, ni format, ni message).
 */
class DemandeDevis extends Component
{
    use ValideTelephone;

    public string $organisation = '';

    public string $responsable = '';

    public string $telephone = '';

    /** Indicatif du pays choisi par le client, séparé du numéro national. */
    public string $indicatif_pays = CountryPhones::PAYS_PAR_DEFAUT;

    public string $email = '';

    public string $theme = '';

    public string $participants = '';

    public string $ville = '';

    public string $date = '';

    public string $duree = '';

    public string $besoins = '';

    public string $budget = '';

    /** Champ piège anti-spam : invisible pour le client, rempli par les robots. */
    public string $website = '';

    public bool $sent = false;

    public string $errorMessage = '';

    public function mount(): void
    {
        // Un appel à l'action depuis une formation (?form=devis&theme=...)
        // propose déjà le sujet au client, qui reste modifiable.
        $theme = trim((string) request()->query('theme', ''));

        if ($theme !== '') {
            $this->theme = Str::limit($theme, 120, '');
        }
    }

    protected function rules(): array
    {
        return [
            'organisation' => ['required', 'string', 'max:255'],
            'responsable' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:50', $this->validerNumero()],
            'indicatif_pays' => ['required', 'string', $this->validerIndicatif()],
            'email' => ['required', 'email', 'max:255'],
            'theme' => ['nullable', 'string', 'max:255'],
            'participants' => ['nullable', 'integer', 'min:1'],
            'ville' => ['nullable', 'string', 'max:150'],
            'date' => ['nullable', 'string', 'max:100'],
            'duree' => ['nullable', 'string', 'max:100'],
            'besoins' => ['nullable', 'string', 'max:5000'],
            'budget' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.email' => 'Indiquez une adresse email valide.',
        ];
    }

    protected function attributes(): array
    {
        return [
            'organisation' => "l'organisation",
            'responsable' => 'la personne responsable',
            'telephone' => 'le numéro WhatsApp',
            'date' => 'la date',
            'duree' => 'la durée',
        ];
    }

    public function submit(ApiClient $api, SiteContent $content)
    {
        $this->validate();

        // Anti-spam : la requete est acceptee sans rien enregistrer, mais le
        // client voit le meme message de succes qu'une vraie demande.
        if (filled($this->website)) {
            $this->sent = true;
            $this->resetErrorBag();

            return null;
        }

        $payload = ['type' => 'devis'] + array_filter([
            'organisation' => $this->organisation,
            'responsable' => $this->responsable,
            'telephone' => $this->telephone,
            'indicatif_pays' => $this->indicatif_pays,
            'email' => $this->email,
            'theme' => $this->theme,
            'participants' => $this->participants,
            'ville' => $this->ville,
            'date_souhaitee' => $this->date,
            'duree' => $this->duree,
            'besoins' => $this->besoins,
            'budget' => $this->budget,
        ], fn ($valeur) => $valeur !== null && $valeur !== '');

        if ($api->post('/leads', $payload) === null) {
            $this->errorMessage = session('api_error')
                ?? 'Votre demande n\'a pas pu être envoyée. Merci de réessayer.';

            return null;
        }

        $this->sent = true;
        $this->resetErrorBag();
        // On vide les champs un par un : un reset() global remettrait aussi
        // $sent a false et ferait disparaitre le message de succes.
        $this->organisation = '';
        $this->responsable = '';
        $this->telephone = '';
        $this->email = '';
        $this->theme = '';
        $this->participants = '';
        $this->ville = '';
        $this->date = '';
        $this->duree = '';
        $this->besoins = '';
        $this->budget = '';
        $content->forget();
        $this->dispatch('lead-sent', type: 'devis');

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
        return view('livewire.demande-devis', [
            'pays' => $this->optionsPays(),
            'exempleTelephone' => $this->exempleTelephone(),
        ]);
    }
}
