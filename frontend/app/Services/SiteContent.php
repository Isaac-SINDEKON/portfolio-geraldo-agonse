<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SiteContent
{
    protected static ?array $cache = null;

    /**
     * Cache persistant du contenu, partage par tous les visiteurs.
     *
     * Le cache statique ci-dessus ne vit que le temps d'une requete : sans ce
     * second niveau, chaque page repatriait le contenu depuis l'API, donc une
     * requete HTTP bloquante par page, y compris pour un visiteur qui ne fait
     * que changer de rubrique.
     */
    protected const CLE = 'site-contenu';

    /** Duree pendant laquelle le contenu est tenu pour frais. */
    protected const FRAICHEUR = 300;

    /**
     * Filet de securite : au-dela de la fraicheur on tente de rafraichir, mais
     * une copie plus ancienne reste servie si l'API ne repond pas. Une coupure
     * de quelques minutes cote backend ne doit pas rendre le site vide.
     */
    protected const DUREE = 86400;

    public function __construct(protected ApiClient $api)
    {
    }

    /** Contenu complet du site, mis en cache pour la durée de la requete. */
    public function all(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        // Les tests simulent l'API avec Http::fake() et attendent un contenu
// different d'un test a l'autre : un cache persistant les melangerait.
// On le desactive donc pendant les tests plutot que de le vider entre
        // chaque cas.
        if (app()->runningUnitTests()) {
            return static::$cache = $this->sections($this->api->get('/site') ?? []);
        }

        $enveloppe = Cache::get(self::CLE);

        if (is_array($enveloppe) && ($enveloppe['vu'] ?? 0) > time() - self::FRAICHEUR) {
            return static::$cache = $enveloppe['donnees'];
        }

        $data = $this->api->get('/site');

        // API injoignable : on prefere servir la derniere version connue a un
        // site vide.
        if (! is_array($data)) {
            return static::$cache = is_array($enveloppe)
                ? $enveloppe['donnees']
                : $this->sections([]);
        }

        $donnees = $this->sections($data);

        // Reponse vide alors qu'on attendait du contenu : c'est plus probablement
        // une panne qu'un site legitement vide, donc on ne fige pas cette reponse.
        if (($data['settings'] ?? []) === []) {
            return static::$cache = is_array($enveloppe) ? $enveloppe['donnees'] : $donnees;
        }

        Cache::put(self::CLE, ['vu' => time(), 'donnees' => $donnees], self::DUREE);

        return static::$cache = $donnees;
    }

    /**
     * Ne garde que les sections attendues. Une cle en trop renvoyee par l'API
     * ne doit pas se retrouver dans les vues.
     */
    protected function sections(array $data): array
    {
        return [
            'settings' => $data['settings'] ?? [],
            'domains' => $data['domains'] ?? [],
            'reasons' => $data['reasons'] ?? [],
            'formations' => $data['formations'] ?? [],
            'services' => $data['services'] ?? [],
            'experiences' => $data['experiences'] ?? [],
            'testimonials' => $data['testimonials'] ?? [],
            'gallery' => $data['gallery'] ?? [],
        ];
    }

    /**
     * Ouvre le cache. Appele apres chaque ecriture cote administration
     * (AdminController::back), le changement est donc visible immediatement :
     * la prochaine page relit l'API.
     */
    public function forget(): void
    {
        static::$cache = null;

        if (! app()->runningUnitTests()) {
            Cache::forget(self::CLE);
        }
    }

    public function setting(string $key, string $default = ''): string
    {
        $value = $this->all()['settings'][$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    public function isEmpty(): bool
    {
        return $this->all()['settings'] === [];
    }

    /**
     * Numéros supplémentaires saisis par le propriétaire (CC §22 « coordonnées »).
     *
     * Chaque entrée est un tableau { label, number, is_whatsapp }. Les entrées
     * invalides ou sans numéro sont ignorées afin qu'une saisie incomplète ne
     * casse jamais l'affichage public.
     *
     * @return array<int, array{label: string, number: string, tel: string, whatsapp: ?string, is_primary: bool}>
     */
    public function extraPhones(): array
    {
        $raw = $this->all()['settings']['extra_phones'] ?? [];

        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        if (! is_array($raw)) {
            return [];
        }

        $phones = [];

        foreach ($raw as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $label = trim((string) ($entry['label'] ?? ''));
            $number = trim((string) ($entry['number'] ?? ''));
            $chiffres = self::chiffresWhatsapp($number);

            if ($label === '' || $number === '' || $chiffres === null) {
                continue;
            }

            $phones[] = [
                'label' => $label,
                'number' => $number,
                'tel' => 'tel:'.preg_replace('/[^\d+]/', '', $number),
                'whatsapp' => $this->whatsappLinkFor($chiffres),
                'is_primary' => (bool) ($entry['is_whatsapp'] ?? false),
            ];
        }

        return $phones;
    }

    /** Nombre de WhatsApp à mettre en avant : réglage dédié ou numéro supplémentaire désigné. */
    public function primaryWhatsappNumber(): ?string
    {
        foreach ($this->extraPhones() as $phone) {
            if ($phone['is_primary']) {
                return self::chiffresWhatsapp($phone['number']);
            }
        }

        return self::chiffresWhatsapp($this->setting('whatsapp'));
    }

    protected function whatsappLinkFor(string $digits): string
    {
        $text = $this->setting('whatsapp_message');

        return 'https://wa.me/'.$digits.($text ? '?text='.urlencode($text) : '');
    }

    public function whatsappUrl(?string $message = null): string
    {
        $digits = $this->primaryWhatsappNumber();
        $text = $message ?: $this->setting(
            'whatsapp_message',
            'Bonjour '.$this->setting('name', 'Géraldo Perridys AGONSE').', je souhaite avoir plus d\'informations sur vos formations.'
        );

        if ($digits === null) {
            return '#';
        }

        return 'https://wa.me/'.$digits.($text ? '?text='.urlencode($text) : '');
    }

    public function telUrl(): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $this->setting('phone'));
    }

    public function emailUrl(): string
    {
        $email = $this->setting('email');
        $subject = 'Demande de formation / devis — ' . ($this->setting('name') ?? 'Géraldo Perridys AGONSE');
        $body = "Bonjour,\n\nJe souhaite vous contacter pour :\n- Une formation\n- Un devis sur mesure\n\nMerci de me recontacter à votre convenance.\n\nCordialement,";

        return 'mailto:'.$email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($body);
    }

    /**
     * Numero fourni par un prospect dans un formulaire.
     *
     * Il est utilise comme numero WhatsApp : c'est ce canal que le
     * proprietaire doit utiliser pour repondre directement au client.
     *
     * Le client choisit son pays dans le formulaire, ce qui donne
     * l'indicatif a appliquer : "0151609682" au Benin donne
     * 2290151609682, le meme numero au Togo donne 2280151609682.
     *
     * L'indicatif reste facultatif : les demandes enregistrees avant
     * l'ajout du selecteur de pays sont alors interpretees au Benin, et un
     * numero deja complet est reconnu quel que soit son pays.
     */
    public static function prospectWhatsappUrl(?string $telephone, ?string $message = null, ?string $indicatif = null): ?string
    {
        $chiffres = self::chiffresInternationales($telephone, $indicatif);

        if ($chiffres === null) {
            return null;
        }

        return 'https://wa.me/'.$chiffres.($message ? '?text='.urlencode($message) : '');
    }

    /**
     * Lien d'appel direct vers le telephone fourni par un prospect.
     *
     * On reutilise la normalisation WhatsApp : un numero national Benin
     * ("01 96 27 52 45") devient "tel:+2290196275245", appelable depuis
     * l'etranger.
     */
    public static function prospectTelUrl(?string $telephone, ?string $indicatif = null): ?string
    {
        $chiffres = self::chiffresInternationales($telephone, $indicatif);

        if ($chiffres === null) {
            return null;
        }

        return 'tel:+'.$chiffres;
    }

    /**
     * Convertit un numero saisi et son indicatif pays en chiffres E.164.
     *
     * L'indicatif est celui choisi par le client dans le formulaire. Sans
     * indicatif explicite, on essaie de le deduire du numero lui-meme : c'est
     * ce qui permet a une demande ancienne, enregistree avant l'ajout du
     * selecteur de pays, de rester exploitable.
     *
     * Le Benin a adopte en 2024 un plan ferme de 10 chiffres commencant par
     * "01" (ITU / libphonenumber) : ce 0 fait partie du numero, il ne doit
     * jamais etre retire. Exemple : 01 96 27 52 45 devient 2290196275245.
     *
     * Retourne null quand le numero est absent ou trop court : mieux vaut
     * n'afficher aucun lien qu'un lien qui ne fonctionne pas.
     *
     * @param  string|null  $indicatif  Indicatif choisi, avec ou sans "+".
     */
    protected static function chiffresInternationales(?string $telephone, ?string $indicatif = null): ?string
    {
        $brut = preg_replace('/\D+/', '', (string) $telephone) ?? '';

        if ($brut === '') {
            return null;
        }

        $code = preg_replace('/\D+/', '', (string) $indicatif) ?? '';

        // Prefixe international 00 : le client a colle un numero deja
        // complet, on retire le "00" et on ne rajoute rien.
        if (str_starts_with($brut, '00')) {
            $brut = substr($brut, 2);

            return preg_match('/^\d{8,}$/', $brut) === 1 ? $brut : null;
        }

        // Indicatif explicite choisi par le client : il fait autorite.
        if ($code !== '') {
            return self::assembler($code, $brut);
        }

        // Aucun indicatif choisi : on deduit le pays du numero lui-meme,
        // ce qui garde exploitables les demandes enregistrees avant
        // l'ajout du selecteur de pays. Le plus long indicatif passe en
        // premier pour ne pas confondre 229 avec 2290...
        $codes = array_keys(CountryPhones::PAYS);
        usort($codes, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $connu) {
            if (preg_match('/^'.$connu.'\d/', $brut) === 1) {
                return $brut;
            }
        }

        // Aucun indicatif reconnu : on applique le pays par defaut.
        return self::assembler(CountryPhones::PAYS_PAR_DEFAUT, $brut);
    }

    /**
     * Assemble l'indicatif choisi et le numero national saisi.
     *
     * Si le client a lui-meme colle un numero deja complet (avec son
     * indicatif), on respecte sa saisie au lieu de la prefixer deux fois.
     */
    protected static function assembler(string $indicatif, string $brut): ?string
    {
        // Le numero contient deja cet indicatif : ne pas le doubler.
        if (preg_match('/^'.$indicatif.'\d/', $brut) === 1) {
            return $brut;
        }

        $longueur = strlen($brut);
        $attendu = CountryPhones::longueurNationale($indicatif);

        if ($longueur < 6) {
            return null;
        }

        // Benin : le plan 2024 impose 10 chiffres commencant par "01".
        // - 8 chiffres : ancien format anterieur a la reforme, on remet le
        //   "01" que tous les numeros ont recu ;
        // - 9 chiffres : le client a oublie le "0" de tete, on le remet ;
        // - 10 chiffres : le "01" est attendu, on le garantit.
        if ($indicatif === '229') {
            return '229'.match (true) {
                $longueur === 10 && substr($brut, 0, 2) === '01' => $brut,
                $longueur === 10 => '01'.substr($brut, 2),
                $longueur === 9 => '0'.$brut,
                $longueur >= 8 => '01'.$brut,
                default => null,
            };
        }

        // Prefixe national ("0" en France, au Togo, ...) : il ne fait pas
        // partie du numero international, on le retire quand il est la.
        $trunk = CountryPhones::trunk($indicatif);

        if ($trunk !== '' && str_starts_with($brut, $trunk)
            && ($attendu === null || $longueur === $attendu + strlen($trunk))) {
            $brut = substr($brut, strlen($trunk));
            $longueur = strlen($brut);
        }

        return $indicatif.$brut;
    }

    /**
     * Nombre au format exact attendu par WhatsApp.
     *
     * WhatsApp impose l'indicatif pays suivi du numero national SANS le prefixe
     * national : "+229 01 67 20 00 02" doit devenir "229167200002". Un lien
     * construit avec le 0 conserve ("wa.me/2290167200002") est refuse par
     * WhatsApp, alors que ce meme numero reste parfaitement correct dans un
     * lien tel:, qui lui attend l'ecriture internationale complete.
     *
     * D'ou deux normalisations distinctes dans cette classe : celle-ci pour
     * WhatsApp, chiffresInternationales() pour le telephone.
     */
    public static function chiffresWhatsapp(?string $telephone): ?string
    {
        $brut = preg_replace('/\D+/', '', (string) $telephone) ?? '';

        if ($brut === '') {
            return null;
        }

        // Prefixe international 00 : le numero est deja complet.
        if (str_starts_with($brut, '00')) {
            $brut = substr($brut, 2);
        }

        // Le plus long indicatif passe en premier pour ne pas confondre 229
        // avec un eventuel 2290...
        $codes = array_keys(CountryPhones::PAYS);
        usort($codes, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $connu) {
            if (preg_match('/^'.$connu.'\d/', $brut) === 1) {
                return self::sansPrefixeNational(substr($brut, strlen($connu)), $connu);
            }
        }

        if ($brut === '') {
            return null;
        }

        // Aucun indicatif reconnu : on applique le pays par defaut.
        return self::sansPrefixeNational($brut, CountryPhones::PAYS_PAR_DEFAUT);
    }

    /** Indicatif + numero national depouille de son prefixe national. */
    protected static function sansPrefixeNational(string $national, string $indicatif): string
    {
        // Benin : le plan national commence toujours par "01", et cette paire
        // ne figure jamais dans l'ecriture internationale ("229...").
        if ($indicatif === '229') {
            return $indicatif.(preg_match('/^01\d/', $national) === 1
                ? substr($national, 2)
                : $national);
        }

        $trunk = CountryPhones::trunk($indicatif);

        if ($trunk !== '' && str_starts_with($national, $trunk)) {
            $national = substr($national, strlen($trunk));
        }

        return $indicatif.$national;
    }

    /**
     * Reponse par email a un prospect : l'adresse est bien celle du client,
     * avec un objet et un corps deja rediges.
     */
    public static function prospectMailtoUrl(?string $email, ?string $subject = null, ?string $body = null): ?string
    {
        $adresse = trim((string) $email);

        if ($adresse === '' || filter_var($adresse, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        // L'adresse reste lisible ; objet et corps sont encodes une seule fois
        // par rawurlencode. Le separateur "&" reste brut : Blade echeape
        // l'attribut href, ce qui produit un "&amp;" correct dans le HTML.
        $url = 'mailto:'.$adresse;

        if ($subject) {
            $url .= '?subject='.rawurlencode($subject);
        }

        if ($body) {
            $url .= ($subject ? '&' : '?').'body='.rawurlencode($body);
        }

        return $url;
    }

    public function imageUrl(?string $path): ?string
    {
        return ApiClient::imageUrl($path);
    }

    /**
     * Compare le libelle d'un domaine a celui d'une formation.
     *
     * Le catalogue ne relie pas formellement les deux listes : le seul lien est
     * leur libelle. Or ces libelles ne sont pas toujours identiques ("Gestion
     * du temps" pour le domaine, "Gestion du temps et des priorites" pour la
     * formation correspondante). Une comparaison stricte renvoyait alors une
     * liste vide alors que la formation existe et est publiee.
     *
     * La comparaison se fait donc sur une forme normalisee : minuscules, sans
     * diacritiques ni ponctuation, et l'un des deux libelles doit ouvrir l'autre.
     */
    public static function memeDomaine(?string $domaine, ?string $titre): bool
    {
        $a = self::normaliser($domaine);
        $b = self::normaliser($titre);

        // Trop court pour etre discriminant : on ne joue pas au hasard avec un
        // libelle d'une ou deux lettres.
        if (strlen($a) < 4 || strlen($b) < 4) {
            return false;
        }

        return str_starts_with($a, $b) || str_starts_with($b, $a);
    }

    /** Forme de comparaison d'un libelle : minuscules, sans accents ni separateurs. */
    protected static function normaliser(?string $libelle): string
    {
        $libelle = mb_strtolower(trim((string) $libelle));

        // "clientele" et "clientèle" doivent se reconnaitre. L'extension intl
        // n'est pas garantie sur tous les hebergeurs (Normalizer est absent),
        // les diacritiques latin sont donc traduits explicitement.
        $libelle = strtr($libelle, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ā' => 'a', 'ă' => 'a', 'ą' => 'a',
            'ç' => 'c', 'ć' => 'c', 'č' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ę' => 'e', 'ě' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i',
            'ñ' => 'n', 'ń' => 'n', 'ņ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ō' => 'o', 'ŏ' => 'o', 'ő' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ū' => 'u', 'ŭ' => 'u', 'ů' => 'u', 'ű' => 'u', 'ų' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'æ' => 'ae', 'œ' => 'oe', 'ß' => 'ss',
        ]);

        // Espaces, tirets et autres separateurs ne doivent pas changer le resultat.
        return preg_replace('/[^\p{L}\p{N}]+/u', '', $libelle) ?? $libelle;
    }

    /** Transforme une textarea (une ligne = un element) en tableau. */
    public static function linesToArray(?string $text): array
    {
        if (! $text) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text))));
    }

    public static function arrayToLines(?array $items): string
    {
        return implode("\n", $items ?: []);
    }
}
