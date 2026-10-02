<?php

namespace Tests\Feature;

use App\Services\ApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MediaUrlTest extends TestCase
{
    /**
     * En production, AppServiceProvider force le schema HTTPS. Les images
     * passant par le relais du frontend, elles en profitent aussi : c'est ce
     * qui evite qu'une page en HTTPS charge une image en HTTP, refusee par la
     * regle de contenu mixte.
     */
    public function test_en_production_une_image_reste_en_https(): void
    {
        config(['services.backend.storage_url' => 'http://api.exemple.test/storage']);

        $this->app['env'] = 'production';
        $this->app['url']->forceScheme('https');

        $url = ApiClient::imageUrl('uploads/photo.jpg');

        $this->assertStringStartsWith('https://', $url);
        $this->assertSame(url('/media/uploads/photo.jpg'), $url);

        // Meme une URL du backend deja en base ne doit pas reintroduire du HTTP.
        $this->assertStringStartsWith(
            'https://',
            ApiClient::imageUrl('http://api.exemple.test/storage/uploads/photo.jpg')
        );
    }

    public function test_le_relais_transmet_le_fichier_du_backend(): void
    {
        config(['services.backend.storage_url' => 'http://api.exemple.test/storage']);

        Http::fake([
            'api.exemple.test/storage/uploads/photo.jpg' => Http::response('donnees-image', 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $reponse = $this->get('/media/uploads/photo.jpg');

        $reponse->assertOk();
        $this->assertSame('image/jpeg', $reponse->headers->get('Content-Type'));
        $this->assertSame('donnees-image', $reponse->getContent());

        // Le nom de fichier est genere par le backend et n'est jamais reutilise :
        // le navigateur peut donc conserver l'image indefiniment.
        $this->assertStringContainsString('immutable', $reponse->headers->get('Cache-Control'));

        Http::assertSent(fn (Request $r) => $r->url() === 'http://api.exemple.test/storage/uploads/photo.jpg');
    }

    public function test_un_fichier_absent_chez_le_backend_donne_un_404(): void
    {
        Http::fake(['*' => Http::response('', 404)]);

        $this->get('/media/uploads/absent.jpg')->assertNotFound();
    }

    /** Le dossier de fichiers du backend ne doit pas etre expose tel quel. */
    public function test_un_chemin_hors_du_dossier_uploads_donne_un_404(): void
    {
        Http::fake();

        foreach ([
            '/media/../.env',
            '/media/.env',
            '/media/uploads/../../.env',
            '/media/passwd',
            '/media/uploads/',
        ] as $chemin) {
            $this->get($chemin)->assertNotFound();
        }

        // Aucune requete ne doit avoir franchi le backend.
        Http::assertNothingSent();
    }

    /** Le backend etant injoignable, seule l'image manque : la page reste servie. */
    public function test_le_backend_injoignable_donne_un_404_sans_erreur(): void
    {
        Http::fake(fn () => throw new ConnectionException('injoignable'));

        $this->get('/media/uploads/photo.jpg')->assertNotFound();
    }
}
