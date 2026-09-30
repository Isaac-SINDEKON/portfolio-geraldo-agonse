<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewLeadMail;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeadController extends Controller
{
    /**
     * Champs attendus par le formulaire « Demander une formation ».
     */
    protected array $formationFields = [
        'organisation', 'responsable', 'fonction', 'telephone', 'indicatif_pays', 'email',
        'theme', 'participants', 'format', 'date_souhaitee', 'message',
    ];

    /**
     * Champs attendus par le formulaire « Demander un devis ».
     */
    protected array $quoteFields = [
        'organisation', 'responsable', 'telephone', 'indicatif_pays', 'email', 'theme',
        'participants', 'ville', 'date_souhaitee', 'duree', 'besoins', 'budget',
    ];

    public function store(Request $request): JsonResponse
    {
        $type = $request->input('type') === 'devis' ? 'devis' : 'formation';

        $request->validate([
            'type' => ['required', 'in:formation,devis'],
            'organisation' => ['required', 'string', 'max:255'],
            'responsable' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:50'],
            // Indicatif choisi par le client : sans lui, un numero national
            // togo serait interprete comme benin et le lien WhatsApp
            // ne mènerait pas au bon contact.
            'indicatif_pays' => ['nullable', 'string', 'max:6', 'regex:/^\+?\d{1,4}$/'],
            'email' => ['required', 'email', 'max:255'],
            'theme' => ['nullable', 'string', 'max:255'],
        ]);

        // Anti-spam (CC 25) : le champ website est invisible pour le client mais
        // se remplit automatiquement pour les robots. La requete est acceptee
        // sans rien enregistrer ni envoyer, pour ne pas alerter le robot.
        if (filled((string) $request->input('website'))) {
            Log::info('Demande anti-spam ignoree (champ piege rempli).');

            return response()->json([
                'message' => $type === 'devis'
                    ? 'Votre demande de devis a bien été envoyée.'
                    : 'Votre demande de formation a bien été envoyée.',
            ], 201);
        }

        $data = collect($request->all())
            ->only($type === 'devis' ? $this->quoteFields : $this->formationFields)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        $lead = Lead::create([
            'type' => $type,
            'data' => $data,
        ]);

        $this->sendMail($data, $type);

        return response()->json([
            'message' => $type === 'devis'
                ? 'Votre demande de devis a bien été envoyée.'
                : 'Votre demande de formation a bien été envoyée.',
            'lead' => $lead,
        ], 201);
    }

    protected function sendMail(array $data, string $type): void
    {
        // Destinataire fixe du CC §14 et §15 : geraldoagonse@gmail.com.
        // Volontairement indépendant de l'adresse d'expédition, pour que
        // changer l'expéditeur ne fasse jamais perdre une demande.
        $recipient = config('mail.leads_to');

        if (! $recipient) {
            Log::error('Aucun destinataire pour les demandes : définir MAIL_LEADS_ADDRESS.');

            return;
        }

        try {
            Mail::to($recipient)->send(new NewLeadMail($data, $type));
        } catch (\Throwable $e) {
            // La demande reste enregistrée en base même si l'email échoue.
            Log::error('Envoi de l\'email de demande impossible : '.$e->getMessage());
        }
    }
}
