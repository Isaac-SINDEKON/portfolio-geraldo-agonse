<?php

namespace Tests\Unit;

use App\Services\ApiClient;
use Tests\TestCase;

class ApiClientImageUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.backend.storage_url' => 'http://127.0.0.1:8000/storage']);
    }

    /** Les images passent par le relais du frontend, jamais par l'URL du backend. */
    public function test_un_chemin_relatif_est_servi_par_le_frontend(): void
    {
        $this->assertSame(
            url('/media/uploads/photo.jpg'),
            ApiClient::imageUrl('uploads/photo.jpg')
        );
    }

    /**
     * La galerie et les photos de temoignages stockent l'URL complete du backend
     * en base : elle doit etre reduite au meme relais, sinon l'image depend
     * encore du backend et disparait quand le navigateur ne peut pas l'atteindre.
     */
    public function test_l_url_du_backend_est_reduite_au_relais(): void
    {
        $this->assertSame(
            url('/media/uploads/photo.jpg'),
            ApiClient::imageUrl('http://127.0.0.1:8000/storage/uploads/photo.jpg')
        );
    }

    public function test_un_chemin_vide_donne_aucune_url(): void
    {
        $this->assertNull(ApiClient::imageUrl(null));
        $this->assertNull(ApiClient::imageUrl(''));
        $this->assertNull(ApiClient::imageUrl('   '));
    }

    public function test_une_ressource_externe_est_laissee_intacte(): void
    {
        $cdn = 'https://cdn.exemple.com/photo.jpg';

        $this->assertSame($cdn, ApiClient::imageUrl($cdn));
    }

    public function test_une_image_inline_est_laissee_intacte(): void
    {
        $inline = 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=';

        $this->assertSame($inline, ApiClient::imageUrl($inline));
    }
}
