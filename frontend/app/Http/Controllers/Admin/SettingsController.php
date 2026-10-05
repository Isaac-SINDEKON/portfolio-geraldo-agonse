<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Contenu general du site (CC §22) : identite, coordonnees, galerie, SEO, photos.
 */
class SettingsController extends AdminController
{
    public function edit()
    {
        return view('admin.settings', [
            'settings' => $this->content->all()['settings'],
            'fields' => self::fields(),
            'extraPhones' => $this->content->extraPhones(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $required = array_keys(array_filter(self::fields(), fn ($field) => $field['required'] ?? false));

        // Les champs obligatoires ne sont exigés que s'ils sont présents :
        // la validation reste stricte sur le formulaire complet du site, mais
        // un envoi partiel (reprise, script) ne doit pas être rejeté.
        $rules = [];
        foreach ($required as $key) {
            $rules[$key] = ['sometimes', 'required', 'string', 'max:255'];
        }
        $rules['email'] = ['sometimes', 'required', 'email', 'max:255'];
        $rules['seo_description'] = ['nullable', 'string', 'max:500'];
        $rules['seo_keywords'] = ['nullable', 'string', 'max:500'];

        // Couleurs de marque : hexadécimal strict. La valeur est injectée telle
        // quelle dans un attribut style, donc aucun caractère autre que # et
        // 0-9a-f ne doit pouvoir passer jusqu'au CSS.
        $rules['brand_color'] = ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];
        $rules['brand_accent'] = ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];

        // Numéros supplémentaires (CC §22) : jusqu'à 10 lignes, chacune avec un
        // libellé et un numéro. Les lignes vides sont tolérées pour que le
        // propriétaire puisse ajouter une ligne avant de la remplir.
        $rules['extra_phones'] = ['nullable', 'array', 'max:10'];
        $rules['extra_phones.*.label'] = ['nullable', 'string', 'max:80'];
        $rules['extra_phones.*.number'] = ['nullable', 'string', 'max:40'];
        $rules['extra_phones_present'] = ['nullable', 'boolean'];

        // Retrait d'une image : une case à cocher dédiée par champ image,
        // car un input file vide n'est jamais transmis par le navigateur et ne
        // peut donc pas servir à effacer la valeur enregistrée.
        foreach ($this->imageFields() as $field) {
            $rules[$field.'_remove'] = ['nullable', 'boolean'];
        }

        $request->validate($rules, [], ['email' => 'adresse email']);

        // Mise à jour non destructive : seuls les champs réellement transmis
        // sont écrits. Écrire '' pour un champ absent effacerait la photo de
        // l'accueil, les mots-clés SEO ou l'accroche dès qu'un envoi partiel
        // arrive (formulaire partiel, appel API, script de reprise).
        $settings = [];

        foreach (array_keys(self::fields()) as $key) {
            if ($request->has($key)) {
                $settings[$key] = (string) $request->input($key, '');
            }
        }

        // La liste des numéros est une donnée structurée : elle n'est écrite que
        // si le formulaire l'a renvoyée ou si son marqueur est présent, pour
        // que le retrait de la dernière ligne vide réellement la liste.
        if ($request->has('extra_phones') || $request->boolean('extra_phones_present')) {
            $settings['extra_phones'] = $this->collectExtraPhones($request);
        }

        // Les champs image sont déduits de la definition ci-dessus, pour qu'un
        // nouveau champ image soit pris en compte sans avoir à modifier deux
        // endroits. Televersements et retraits sont listes avant l'ecriture des
        // textes : envoyer un logo sans toucher aux autres champs est un
        // enregistrement valide, pas un envoi vide.
        $champsImage = $this->imageFields();

        $televersements = array_values(array_filter(
            $champsImage,
            fn ($field) => $request->hasFile($field)
        ));

        // Un nouveau fichier prime sur le retrait : choisir une image et
        // cocher "retirer" dans le meme envoi doit remplacer, pas effacer.
        $retraits = array_values(array_filter(
            $champsImage,
            fn ($field) => ! $request->hasFile($field) && $request->boolean($field.'_remove')
        ));

        if ($settings === [] && $televersements === [] && $retraits === []) {
            return $this->back('Aucun changement à enregistrer.');
        }

        if ($settings !== [] && $this->api->put('admin/settings', ['settings' => $settings]) === null) {
            return $this->fail('Impossible d\'enregistrer les informations du site.');
        }

        // Logo et photos du formateur (accueil et page À propos).
        foreach ($televersements as $field) {
            $upload = $this->api->upload('admin/upload', $request->file($field), 'image');

            if ($upload === null) {
                return $this->fail('Le contenu a été enregistré, mais le téléversement de l\'image a échoué.');
            }

            $this->api->put('admin/settings', ['settings' => [$field => $upload['path'] ?? null]]);
        }

        foreach ($retraits as $field) {
            // Le retrait n'efface que la valeur du réglage : le fichier reste
            // sur le disque, comme pour les autres contenus. Une image
            // supprimée par erreur redevient ainsi rejouable sans téléversement.
            if ($this->api->put('admin/settings', ['settings' => [$field => '']]) === null) {
                return $this->fail('Le contenu a été enregistré, mais le retrait de l\'image a échoué.');
            }
        }

        return $this->back('Les informations du site ont été enregistrées.');
    }

    /**
     * Une case à cocher n'est transmise que si elle est cochée, et selon le
     * client la valeur peut arriver sous la forme "1", "on", "true" ou "yes".
     */
    protected function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(mb_strtolower(trim((string) $value)), ['1', 'on', 'true', 'yes'], true);
    }

    /**
     * Ne conserve que les lignes réellement remplies (libellé + numéro).
     * Un seul numéro peut porter le badge WhatsApp flottant (CC §17) : le
     * premier coché gagne, les suivants sont ignorés.
     */
    protected function collectExtraPhones(Request $request): array
    {
        $rows = (array) $request->input('extra_phones', []);
        $phones = [];
        $whatsappTaken = false;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $number = trim((string) ($row['number'] ?? ''));

            if ($label === '' || $number === '') {
                continue;
            }

            $isWhatsapp = ! $whatsappTaken && $this->toBoolean($row['is_whatsapp'] ?? false);
            $whatsappTaken = $whatsappTaken || $isWhatsapp;

            $phones[] = [
                'label' => $label,
                'number' => $number,
                'is_whatsapp' => $isWhatsapp,
            ];
        }

        return $phones;
    }

    /**
     * Champs editables sans toucher au code (CC §22).
     */
    public static function fields(): array
    {
        return [
            // Bandeau de confiance affiche sous l'accueil. La section
            // « Identite visuelle » qui portait aussi brand_color et
            // brand_accent a ete retiree : ces deux curseurs ne recoloraient
            // plus rien (la palette publique est fixee par app.css), donc ils
            // n'etaient que du bruit dans le formulaire.
            'clients' => ['label' => 'Organisations clientes (une par ligne)', 'type' => 'textarea', 'group' => 'Bandeau clients', 'hint' => 'Affichées dans le bandeau de confiance sous l\'accueil. Laissez vide pour masquer le bandeau.'],

            // Identite
            'name' => ['label' => 'Nom complet', 'type' => 'text', 'required' => true, 'group' => 'Identité'],
            'role' => ['label' => 'Fonction', 'type' => 'text', 'required' => true, 'group' => 'Identité'],
            'logo' => ['label' => 'Logo', 'type' => 'image', 'group' => 'Identité', 'preview' => 'contain', 'hint' => 'Affiché partout : site public et administration. Sans logo, les initiales « GA » restent visibles.'],
            'tagline' => ['label' => 'Accroche', 'type' => 'text', 'required' => true, 'group' => 'Identité'],
            'hero_text' => ['label' => 'Présentation courte (accueil)', 'type' => 'textarea', 'group' => 'Identité'],
            'hero_photo' => ['label' => 'Photo principale (accueil)', 'type' => 'image', 'group' => 'Identité'],
            'cta_title' => ['label' => 'Appel à l\'action : titre', 'type' => 'text', 'group' => 'Identité'],
            'cta_text' => ['label' => 'Appel à l\'action : texte', 'type' => 'textarea', 'group' => 'Identité'],

            // Bandeau de statistiques affiche sous le titre de l'accueil.
            // Les quatre couples libelle/valeur sont lus tels quels par la page
            // d'accueil ; un couple laisse vide retombe sur la valeur affichee
            // par defaut, ce qui evite un bandeau vide si un champ est oublie.
            'stat_1_label' => ['label' => 'Statistique 1 : libellé', 'type' => 'text', 'group' => 'Bandeau de statistiques'],
            'stat_1_value' => ['label' => 'Statistique 1 : chiffre', 'type' => 'text', 'group' => 'Bandeau de statistiques'],
            'stat_2_label' => ['label' => 'Statistique 2 : libellé', 'type' => 'text', 'group' => 'Bandeau de statistiques'],
            'stat_2_value' => ['label' => 'Statistique 2 : chiffre', 'type' => 'text', 'group' => 'Bandeau de statistiques'],
            'stat_3_label' => ['label' => 'Statistique 3 : libellé', 'type' => 'text', 'group' => 'Bandeau de statistiques'],
            'stat_3_value' => ['label' => 'Statistique 3 : chiffre', 'type' => 'text', 'group' => 'Bandeau de statistiques'],
            'stat_4_label' => ['label' => 'Statistique 4 : libellé', 'type' => 'text', 'group' => 'Bandeau de statistiques'],
            'stat_4_value' => ['label' => 'Statistique 4 : chiffre', 'type' => 'text', 'group' => 'Bandeau de statistiques'],

            // A propos
            'about_parcours' => ['label' => 'À propos : parcours', 'type' => 'textarea', 'group' => 'À propos'],
            'about_approche' => ['label' => 'À propos : approche professionnelle', 'type' => 'textarea', 'group' => 'À propos'],
            'about_expertise' => ['label' => 'À propos : domaines d\'expertise', 'type' => 'textarea', 'group' => 'À propos'],
            'about_qualifications' => ['label' => 'À propos : qualifications (une par ligne)', 'type' => 'textarea', 'group' => 'À propos'],
            'about_photo' => ['label' => 'Photo de la page À propos', 'type' => 'image', 'group' => 'À propos'],

            // Coordonnees
            'whatsapp' => ['label' => 'WhatsApp principal (format international)', 'type' => 'text', 'required' => true, 'group' => 'Coordonnées', 'hint' => 'Utilisé si aucun numéro supplémentaire n\'est désigné comme WhatsApp plus bas.'],
            'whatsapp_display' => ['label' => 'WhatsApp principal (affichage)', 'type' => 'text', 'group' => 'Coordonnées'],
            'whatsapp_message' => ['label' => 'WhatsApp : message prérempli', 'type' => 'textarea', 'group' => 'Coordonnées', 'hint' => 'Utilisé par le bouton flottant présent sur toutes les pages (CC §17).'],
            'phone' => ['label' => 'Téléphone principal', 'type' => 'text', 'required' => true, 'group' => 'Coordonnées'],
            'phone_display' => ['label' => 'Téléphone principal (affichage)', 'type' => 'text', 'group' => 'Coordonnées'],
            'email' => ['label' => 'Email', 'type' => 'email', 'required' => true, 'group' => 'Coordonnées'],
            'location' => ['label' => 'Localisation', 'type' => 'text', 'group' => 'Coordonnées'],

            // Galerie
            'gallery_title' => ['label' => 'Galerie : titre', 'type' => 'text', 'group' => 'Galerie'],
            'gallery_text' => ['label' => 'Galerie : texte', 'type' => 'textarea', 'group' => 'Galerie'],

            // SEO
            'seo_title' => ['label' => 'Titre SEO', 'type' => 'text', 'group' => 'SEO'],
            'seo_description' => ['label' => 'Meta description SEO', 'type' => 'textarea', 'group' => 'SEO'],
            'seo_keywords' => ['label' => 'Mots-clés SEO', 'type' => 'textarea', 'group' => 'SEO'],
        ];
    }

    /**
     * Champs acceptant une image (logo, photos du formateur).
     *
     * Cette liste est derivée de fields() et non redigée en dur : c'est
     * elle qui pilote le téléversement et le retrait. Un champ image ajoute
     * dans fields() est donc pris en compte automatiquement.
     *
     * @return array<int, string>
     */
    protected function imageFields(): array
    {
        return array_keys(array_filter(
            self::fields(),
            fn ($field) => ($field['type'] ?? null) === 'image'
        ));
    }
}
