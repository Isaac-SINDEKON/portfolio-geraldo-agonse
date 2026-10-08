<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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

        $q = trim($request->string('q')->toString());

        if (mb_strlen($q) > 100) {
            $q = mb_substr($q, 0, 100);
        }

        $all = $this->api->get('admin/leads') ?: [];

        $counts = [
            'all' => count($all),
            'formation' => count(array_filter($all, fn ($lead) => ($lead['type'] ?? '') === 'formation')),
            'devis' => count(array_filter($all, fn ($lead) => ($lead['type'] ?? '') === 'devis')),
        ];

        $leads = $type === ''
            ? $all
            : array_values(array_filter($all, fn ($lead) => ($lead['type'] ?? '') === $type));

        // La recherche s'applique apres le filtre par type : les compteurs des
        // onglets restent donc stables, et « Devis » affiche bien les resultats
        // du texte saisis dans l'onglet Formation.
        if ($q !== '') {
            $leads = array_values(array_filter($leads, fn ($lead) => $this->correspond($lead, $q)));
        }

        return view('admin.leads', [
            'leads' => $leads,
            'type' => $type,
            'counts' => $counts,
            'q' => $q,
        ]);
    }

    /**
     * Chaque mot saisi doit se retrouver dans la demande, sans diacritique ni
     * casse : « societe generale » trouve « Société Générale », et un numero
     * saisi sans espaces trouve « 01 96 27 52 45 ».
     *
     * Les mots sont combines en ET, ce qui permet « zoro gmail » ou
     * « productivite professionnelle », et rend la saisie en plusieurs mots
     * insensible aux espaces superflus.
     */
    protected function correspond(array $lead, string $besoin): bool
    {
        $termes = preg_split('/\s+/', $this->normaliser($besoin), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($termes === []) {
            return true;
        }

        $texte = $this->texteRechercheable($lead);
        $chiffres = preg_replace('/\D+/', '', $texte) ?? '';

        foreach ($termes as $terme) {
            if (str_contains($texte, $terme)) {
                continue;
            }

            // Repli sur les seuls chiffres du terme : c'est ce qui permet de
            // retrouver un telephone saisi sans espaces ni separateurs.
            $chiffresDuTerme = preg_replace('/\D+/', '', $terme) ?? '';

            if ($chiffresDuTerme === '' || ! str_contains($chiffres, $chiffresDuTerme)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Concatene tout ce qui est rechercheable dans une demande.
     *
     * Les valeurs de « data » sont parcouries recursivement plutot que listees
     * champ par champ : une recherche couvre ainsi automatiquement les
     * nouveaux champs d'un formulaire, sans avoir a maintenir une liste ici.
     */
    protected function texteRechercheable(array $lead): string
    {
        $morceaux = [(string) ($lead['type'] ?? '')];

        $morceaux[] = ($lead['type'] ?? '') === 'devis' ? 'Devis' : 'Formation';

        if (! empty($lead['created_at'])) {
            $morceaux[] = Carbon::parse($lead['created_at'])->format('d/m/Y');
        }

        // La variable est necessaire : array_walk_recursive() attend un
        // argument par reference et refuse une expression.
        $donnees = (array) ($lead['data'] ?? []);

        array_walk_recursive($donnees, function ($valeur) use (&$morceaux) {
            if (is_scalar($valeur)) {
                $morceaux[] = (string) $valeur;
            }
        });

        return $this->normaliser(implode(' ', array_filter($morceaux, fn ($morceau) => $morceau !== '')));
    }

    /**
     * Forme de comparaison : minuscules, sans diacritique.
     * Str::ascii() passe par voku/portable-ascii et fonctionne sans extension intl.
     */
    protected function normaliser(string $texte): string
    {
        return mb_strtolower(trim(Str::ascii($texte)));
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
