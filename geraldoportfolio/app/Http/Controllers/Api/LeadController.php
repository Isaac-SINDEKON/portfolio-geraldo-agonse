<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    public function store(Request $request, LeadService $leads): JsonResponse
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

        $lead = $leads->submit($request->all());

        return response()->json([
            'message' => $type === 'devis'
                ? 'Votre demande de devis a bien été envoyée.'
                : 'Votre demande de formation a bien été envoyée.',
            'lead' => $lead,
        ], 201);
    }
}
