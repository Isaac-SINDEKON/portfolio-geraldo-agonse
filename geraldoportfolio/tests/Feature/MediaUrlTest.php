<?php

namespace Tests\Feature;

use App\Services\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUrlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * En production, AppServiceProvider force le schema HTTPS. Les images
     * passant par le relais /media, elles en profitent aussi : c'est ce qui
     * evite qu'une page en HTTPS charge une image en HTTP, refusee par la regle
     * de contenu mixte.
     */
    public function test_en_production_une_image_reste_en_https(): void
    {
        config(['services.storage.url' => 'http://api.exemple.test/storage']);

        $this->app['env'] = 'production';
        $this->app['url']->forceScheme('https');

        $url = ApiClient::imageUrl('uploads/photo.jpg');

        $this->assertStringStartsWith('https://', $url);
        $this->assertSame(url('/media/uploads/photo.jpg'), $url);

        // Meme une ancienne URL complete du stockage ne doit pas reintroduire
        // du HTTP.
        $this->assertStringStartsWith(
            'https://',
            ApiClient::imageUrl('http://api.exemple.test/storage/uploads/photo.jpg')
        );
    }

    public function test_le_relais_sert_le_fichier_stocke_localement(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/photo.jpg', 'donnees-image');

        $reponse = $this->get('/media/uploads/photo.jpg');

        $reponse->assertOk();
        $this->assertSame('donnees-image', $reponse->baseResponse->getFile()->getContent());

        // Le nom de fichier est genere et n'est jamais reutilise : le navigateur
        // peut donc conserver l'image indefiniment.
        $this->assertStringContainsString('immutable', $reponse->headers->get('Cache-Control'));
    }

    public function test_un_fichier_absent_donne_un_404(): void
    {
        Storage::fake('public');

        $this->get('/media/uploads/absent.jpg')->assertNotFound();
    }

    /** Le dossier de fichiers ne doit pas etre expose en dehors d'uploads. */
    public function test_un_chemin_hors_du_dossier_uploads_donne_un_404(): void
    {
        Storage::fake('public');

        foreach ([
            '/media/../.env',
            '/media/.env',
            '/media/uploads/../../.env',
            '/media/passwd',
            '/media/uploads/',
        ] as $chemin) {
            $this->get($chemin)->assertNotFound();
        }
    }
}
