<?php

namespace Tests\Unit;

use App\Services\SiteContent;
use PHPUnit\Framework\TestCase;

class SiteContentTest extends TestCase
{
    /**
     * Le catalogue lie les domaines a leurs formations par le libelle seulement.
     * Le libelle du domaine est souvent plus court que celui de la formation.
     */
    public function test_un_libelle_plus_court_designe_la_formation(): void
    {
        $this->assertTrue(SiteContent::memeDomaine(
            'Gestion du temps',
            'Gestion du temps et des priorités'
        ));
    }

    public function test_les_accents_et_la_casse_ne_comptent_pas(): void
    {
        $this->assertTrue(SiteContent::memeDomaine(
            'Fidélisation de la clientèle',
            'FIDELISATION DE LA CLIENTELE'
        ));
    }

    public function test_un_libelle_identique_correspond_toujours(): void
    {
        $this->assertTrue(SiteContent::memeDomaine(
            'Vente et techniques commerciales',
            'Vente et techniques commerciales'
        ));
    }

    public function test_deux_domaines_differents_ne_correspondent_pas(): void
    {
        $this->assertFalse(SiteContent::memeDomaine(
            'Gestion du temps',
            'Productivité professionnelle'
        ));
    }

    public function test_une_valeur_absente_ne_correspond_a_rien(): void
    {
        $this->assertFalse(SiteContent::memeDomaine(null, 'Gestion du temps'));
        $this->assertFalse(SiteContent::memeDomaine('Gestion du temps', null));
        $this->assertFalse(SiteContent::memeDomaine('', ''));
    }
}
