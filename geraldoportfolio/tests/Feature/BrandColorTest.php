<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Champ de marque et reglages de couleur.
 *
 * Couvre la promesse faite a l'internaute : un seul reglage de couleur
 * recolore tout le site. Ces tests verrouillent le contrat de bout en bout,
 * du formulaire admin jusqu'a l'attribut style pose sur <html> - c'est
 * precisement ce maillon qui s'etait casse pendant la mise en place.
 */
class BrandColorTest extends TestCase
{
    use RefreshDatabase;

    /** Le contenu de reference porte les couleurs par defaut. */
    protected $seed = true;

    /**
     * L'administration ne passe pas par le guard Laravel mais par une simple
     * cle de session posee a la connexion, il faut donc la simuler directement.
     */
    private function connecte(): static
    {
        return $this->withSession(['admin_token' => 'jeton-de-test']);
    }

    /** Le formulaire admin expose bien les deux champs de couleur. */
    public function test_le_formulaire_admin_expose_les_champs_de_couleur(): void
    {
        $this->connecte()
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('name="brand_color"', escape: false)
            ->assertSee('name="brand_accent"', escape: false)
            ->assertSee('name="clients"', escape: false);
    }

    /** L'accueil recoit bien les couleurs enregistrees en base. */
    public function test_la_couleur_de_marque_arrivee_dans_le_html(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('--brand:#1d4ed8', escape: false)
            ->assertSee('--brand-accent:#06b6d4', escape: false);
    }

    /** Une couleur hexadecimale est acceptee. */
    public function test_la_couleur_est_validee_au_format_hexadecimal(): void
    {
        $this->connecte()
            ->post(route('admin.settings.update'), [
                'brand_color' => '#047857',
                'brand_accent' => '#b45309',
            ])
            ->assertSessionHasNoErrors();
    }

    /** Une valeur non hexadecimale ne doit jamais atteindre le CSS. */
    public function test_une_couleur_invalide_est_rejetee(): void
    {
        $this->connecte()
            ->post(route('admin.settings.update'), ['brand_color' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('brand_color');
    }
}
