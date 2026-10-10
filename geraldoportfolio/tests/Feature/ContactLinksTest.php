<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\SiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Les deux liens de contact du site ne suivent pas la meme regle :
 * - tel: attend l'ecriture internationale complete, 0 national compris ;
 * - wa.me attend le numero national sans son 0.
 *
 * Confondre les deux produit un lien WhatsApp invalide, silencieusement refuse.
 */
class ContactLinksTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ecrit les reglages en base puis relit le contenu : le cache statique doit
     * etre ouvert pour que le test observe bien ce qu'il vient d'ecrire.
     */
    protected function fakeSite(array $settings): SiteContent
    {
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value]
            );
        }

        $content = app(SiteContent::class);
        $content->forget();

        return $content;
    }

    public function test_le_lien_whatsapp_est_valide(): void
    {
        $content = $this->fakeSite([
            'name' => 'Geraldo Perridys AGONSE',
            'phone' => '+229 01 96 40 00 08',
            'whatsapp' => '+229 01 67 20 00 02',
            'whatsapp_message' => 'Bonjour, je souhaite des informations.',
        ]);

        $url = $content->whatsappUrl();

        $this->assertStringContainsString('wa.me/22967200002', $url);
        $this->assertStringNotContainsString('wa.me/2290', $url);
    }

    public function test_le_lien_tel_garde_l_ecriture_internationale(): void
    {
        $content = $this->fakeSite([
            'phone' => '+229 01 96 40 00 08',
            'whatsapp' => '+229 01 67 20 00 02',
        ]);

        $this->assertSame('tel:+2290196400008', $content->telUrl());
    }

    public function test_un_numero_secondaire_peut_devenir_le_whatsapp_principal(): void
    {
        $content = $this->fakeSite([
            'phone' => '+229 01 96 40 00 08',
            'whatsapp' => '+229 01 67 20 00 02',
            'extra_phones' => json_encode([
                ['label' => 'Bureau', 'number' => '+229 01 96 40 00 08', 'is_whatsapp' => true],
            ]),
        ]);

        $url = $content->whatsappUrl();

        $this->assertStringContainsString('wa.me/22996400008', $url);
        $this->assertStringNotContainsString('2290', $url);
    }

    /** Les deux numeros restent joignables, chacun avec son lien correct. */
    public function test_les_deux_numeros_sont_exposes(): void
    {
        $content = $this->fakeSite([
            'phone' => '+229 01 96 40 00 08',
            'whatsapp' => '+229 01 67 20 00 02',
            'extra_phones' => json_encode([
                ['label' => 'Bureau', 'number' => '+229 01 96 40 00 08', 'is_whatsapp' => false],
            ]),
        ]);

        $phones = $content->extraPhones();

        $this->assertCount(1, $phones);
        $this->assertSame('Bureau', $phones[0]['label']);
        $this->assertSame('tel:+2290196400008', $phones[0]['tel']);
        $this->assertStringContainsString('wa.me/22996400008', $phones[0]['whatsapp']);
    }

    /** Sans numero exploitable, le lien ne doit pas devenir un "wa.me/" vide. */
    public function test_aucun_numero_ne_produit_pas_de_lien_creux(): void
    {
        $content = $this->fakeSite(['name' => 'Geraldo Perridys AGONSE']);

        $this->assertStringNotContainsString('wa.me/?', $content->whatsappUrl());
    }
}
