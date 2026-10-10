<?php

namespace App\Services;

use App\Mail\NewLeadMail;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Enregistrement d'une demande (formation ou devis) et notification par email.
 *
 * Logique partagee par l'API publique et les formulaires Livewire : la demande
 * est ecrite en base, puis un email est envoye au proprietaire. L'echec de
 * l'email ne remet jamais en cause l'enregistrement de la demande.
 */
class LeadService
{
    /**
     * Champs attendus par le formulaire « Demander une formation ».
     */
    protected const FORMATION_FIELDS = [
        'organisation', 'responsable', 'fonction', 'telephone', 'indicatif_pays', 'email',
        'theme', 'participants', 'format', 'date_souhaitee', 'message',
    ];

    /**
     * Champs attendus par le formulaire « Demander un devis ».
     */
    protected const QUOTE_FIELDS = [
        'organisation', 'responsable', 'telephone', 'indicatif_pays', 'email', 'theme',
        'participants', 'ville', 'date_souhaitee', 'duree', 'besoins', 'budget',
    ];

    /**
     * Enregistre une demande a partir des donnees brutes d'un formulaire.
     *
     * @param  array<string, mixed>  $payload  donnees du formulaire (type inclus)
     */
    public function submit(array $payload): Lead
    {
        $type = ($payload['type'] ?? '') === 'devis' ? 'devis' : 'formation';

        $data = collect($payload)
            ->only($type === 'devis' ? self::QUOTE_FIELDS : self::FORMATION_FIELDS)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        $lead = Lead::create([
            'type' => $type,
            'data' => $data,
        ]);

        $this->sendMail($data, $type);

        return $lead;
    }

    protected function sendMail(array $data, string $type): void
    {
        // Destinataire fixe du CC §14 et §15 : geraldoagonse@gmail.com.
        // Volontairement independant de l'adresse d'expedition, pour que
        // changer l'expediteur ne fasse jamais perdre une demande.
        $recipient = config('mail.leads_to');

        if (! $recipient) {
            Log::error('Aucun destinataire pour les demandes : definir MAIL_LEADS_ADDRESS.');

            return;
        }

        try {
            Mail::to($recipient)->send(new NewLeadMail($data, $type));
        } catch (\Throwable $e) {
            // La demande reste enregistree en base meme si l'email echoue.
            Log::error('Envoi de l\'email de demande impossible : '.$e->getMessage());
        }
    }
}
