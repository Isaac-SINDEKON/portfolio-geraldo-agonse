<?php

namespace Tests\Unit;

use App\Services\SiteContent;
use Tests\TestCase;

class WhatsappNumberTest extends TestCase
{
    /**
     * WhatsApp impose l'indicatif pays suivi du numero national SANS le prefixe
     * national : "+229 01 67 20 00 02" devient "22967200002". Un lien conserve
     * avec le 0 ("wa.me/2290167200002") est refuse par WhatsApp, alors que ce
     * meme numero reste correct dans un lien tel:.
     */
    public function test_le_zero_national_est_retire_pour_whatsapp(): void
    {
        $this->assertSame('22967200002', SiteContent::chiffresWhatsapp('+229 01 67 20 00 02'));
        $this->assertSame('22967200002', SiteContent::chiffresWhatsapp('2290167200002'));
    }

    /** Le proprietaire peut coller son numero sous toutes ses formes. */
    public function test_toutes_les_saisies_du_proprietaire_donnent_la_meme_cible(): void
    {
        foreach ([
            '+229 01 67 20 00 02',
            '+229-01-67-20-00-02',
            '229 01 67 20 00 02',
            '(00229) 01 67 20 00 02',
            '002290167200002',
            '22967200002',
        ] as $saisie) {
            $this->assertSame('22967200002', SiteContent::chiffresWhatsapp($saisie), "Saisie : $saisie");
        }
    }

    /** Numero national seul, sans indicatif ni zero de tete. */
    public function test_le_numero_national_seul_complete_par_le_pays(): void
    {
        $this->assertSame('22967200002', SiteContent::chiffresWhatsapp('01 67 20 00 02'));
    }

    /** Ancien format anterieur a la reforme du plan beninois. */
    public function test_un_numero_court_est_compl_sans_double_zero(): void
    {
        $this->assertSame('22998545414', SiteContent::chiffresWhatsapp('+229 98 54 54 14'));
    }

    public function test_un_prefixe_national_etranger_est_egalement_retire(): void
    {
        $this->assertSame('22890123456', SiteContent::chiffresWhatsapp('+228 90 12 34 56'));
    }

    public function test_une_saisie_inutilisable_donne_null(): void
    {
        $this->assertNull(SiteContent::chiffresWhatsapp(''));
        $this->assertNull(SiteContent::chiffresWhatsapp('   '));
        $this->assertNull(SiteContent::chiffresWhatsapp(null));
        $this->assertNull(SiteContent::chiffresWhatsapp('aucun chiffre'));
    }
}