<?php

namespace App\Http\Controllers;

use App\Services\SiteContent;

class PageController extends Controller
{
    public function __construct(protected SiteContent $content)
    {
    }

    public function home()
    {
        $site = $this->content->all();

        return view('site.home', [
            'domains' => $site['domains'],
            'reasons' => $site['reasons'],
            'formations' => array_slice($site['formations'], 0, 4),
            'testimonials' => array_slice($site['testimonials'], 0, 3),
            // La galerie et le parcours shrew les deux preuves qui manquent le
            // plus a l'accueil : des photos d'intervention et un dereoulement
            // concret. Sans elles, la page reste une suite de cartes.
            'galerie' => array_slice($site['gallery'], 0, 5),
            'experiences' => $site['experiences'],
        ]);
    }

    public function about()
    {
        return view('site.about');
    }

    public function formations()
    {
        return view('site.formations.index', [
            'formations' => $this->content->all()['formations'],
        ]);
    }

    public function formation(string $slug)
    {
        $formation = collect($this->content->all()['formations'])->firstWhere('slug', $slug);

        abort_if(! $formation, 404);

        return view('site.formations.show', compact('formation'));
    }

    public function services()
    {
        return view('site.services');
    }

    public function experience()
    {
        return view('site.experience');
    }

    public function testimonials()
    {
        return view('site.testimonials');
    }

    public function gallery()
    {
        return view('site.gallery');
    }

    public function contact()
    {
        return view('site.contact');
    }
}
