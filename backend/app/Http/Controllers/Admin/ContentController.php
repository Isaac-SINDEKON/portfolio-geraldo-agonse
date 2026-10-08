<?php

namespace App\Http\Controllers\Admin;

/**
 * Point d'entree unique du contenu (CC §22).
 *
 * L'administration expose une seule rubrique « Contenu du site » qui renvoie
 * vers les sept sections editables. Cette indirection n'est pas decorative :
 * avec dix destinations de premier niveau, aucune barre de navigation ne
 * reste lisible sur un ecran de telephone, et trois sections
 * (Services §10, Experience §11, Raisons §7) n'etaient accessibles par aucun
 * lien. Le hub rend chaque section atteignable et lisible partout.
 */
class ContentController extends AdminController
{
    public function index()
    {
        // `site` et non `content` : la variable $content est injectee dans
        // toutes les vues et designe le service SiteContent.
        return view('admin.content', [
            'site' => $this->content->all(),
        ]);
    }
}
