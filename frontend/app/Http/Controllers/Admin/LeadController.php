<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Demandes recues via les formulaires (CC §14 et §15).
 * Les reponses sont memorisees par l'API backend.
 */
class LeadController extends AdminController
{
    public function index(Request $request)
    {
        $type = $request->string('type')->toString();

        abort_if(! in_array($type, ['formation', 'devis', ''], true), 404);

        $all = $this->api->get('admin/leads') ?: [];

        $counts = [
            'all' => count($all),
            'formation' => count(array_filter($all, fn ($lead) => ($lead['type'] ?? '') === 'formation')),
            'devis' => count(array_filter($all, fn ($lead) => ($lead['type'] ?? '') === 'devis')),
        ];

        $leads = $type === ''
            ? $all
            : array_values(array_filter($all, fn ($lead) => ($lead['type'] ?? '') === $type));

        return view('admin.leads', [
            'leads' => $leads,
            'type' => $type,
            'counts' => $counts,
        ]);
    }

    public function show(int $id)
    {
        $leads = $this->api->get('admin/leads') ?: [];
        $lead = collect($leads)->firstWhere('id', $id);

        abort_if(! $lead, 404, 'Demande introuvable.');

        return view('admin.lead', compact('lead'));
    }

    public function destroy(int $id): RedirectResponse
    {
        if ($this->api->delete("admin/leads/{$id}") === null) {
            return $this->fail('Impossible de supprimer cette demande.');
        }

        return $this->back('La demande a été supprimée.');
    }
}
