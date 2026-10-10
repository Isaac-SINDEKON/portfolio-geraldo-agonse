<?php

namespace App\Http\Controllers;

use App\Services\SiteContent;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(SiteContent $content): Response
    {
        $formations = $content->all()['formations'] ?: [];

        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'freq' => 'monthly'],
            ['loc' => route('about'), 'priority' => '0.8', 'freq' => 'yearly'],
            ['loc' => route('formations'), 'priority' => '0.9', 'freq' => 'monthly'],
            ['loc' => route('services'), 'priority' => '0.8', 'freq' => 'monthly'],
            ['loc' => route('experience'), 'priority' => '0.7', 'freq' => 'yearly'],
            ['loc' => route('testimonials'), 'priority' => '0.7', 'freq' => 'monthly'],
            ['loc' => route('gallery'), 'priority' => '0.6', 'freq' => 'monthly'],
            ['loc' => route('contact'), 'priority' => '0.9', 'freq' => 'yearly'],
        ];

        foreach ($formations as $formation) {
            $urls[] = [
                'loc' => route('formations.show', $formation['slug']),
                'priority' => '0.8',
                'freq' => 'monthly',
            ];
        }

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
