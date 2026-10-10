<?php

namespace App\Services;

/**
 * Indicatifs telephoniques internationaux, utilises par les formulaires de
 * demande pour que le client saisisse un numero joignable quel que soit son
 * pays (le site est reference au Benin et au Togo, CC §17).
 *
 * Chaque pays indique :
 * - "nom"      : libelle affiche dans le menu deroulant ;
 * - "national" : longueur du numero national significatif, hors indicatif ;
 * - "trunk"    : prefixe national a retirer quand le client le saisit
 *                ("0" en France, au Togo, ...) ;
 * - "exemple"  : exemple de saisie, affiche sous le champ telephone.
 *
 * Convention : les cles sont uniquement des chiffres, sans "+", afin de
 * pouvoir etre utilisees directement dans une expression reguliere.
 */
final class CountryPhones
{
    public const PAYS = [
        '229' => ['nom' => 'Bénin', 'national' => 10, 'trunk' => '', 'exemple' => '01 96 12 34 56'],
        '228' => ['nom' => 'Togo', 'national' => 8, 'trunk' => '0', 'exemple' => '90 11 22 33'],
        '233' => ['nom' => 'Ghana', 'national' => 9, 'trunk' => '0', 'exemple' => '024 123 4567'],
        '234' => ['nom' => 'Nigeria', 'national' => 10, 'trunk' => '0', 'exemple' => '803 123 4567'],
        '235' => ['nom' => 'Tchad', 'national' => 8, 'trunk' => '', 'exemple' => '63 21 45 67'],
        '237' => ['nom' => 'Cameroun', 'national' => 8, 'trunk' => '6', 'exemple' => '6 99 12 34 56'],
        '225' => ['nom' => "Côte d'Ivoire", 'national' => 10, 'trunk' => '0', 'exemple' => '01 23 45 67 89'],
        '223' => ['nom' => 'Mali', 'national' => 8, 'trunk' => '', 'exemple' => '76 12 34 56'],
        '221' => ['nom' => 'Sénégal', 'national' => 9, 'trunk' => '', 'exemple' => '77 123 45 67'],
        '226' => ['nom' => 'Burkina Faso', 'national' => 8, 'trunk' => '', 'exemple' => '70 12 34 56'],
        '227' => ['nom' => 'Niger', 'national' => 8, 'trunk' => '', 'exemple' => '96 12 34 56'],
        '241' => ['nom' => 'Gabon', 'national' => 8, 'trunk' => '0', 'exemple' => '06 12 34 56'],
        '242' => ['nom' => 'Congo', 'national' => 9, 'trunk' => '0', 'exemple' => '06 12 34 56'],
        '243' => ['nom' => 'RD Congo', 'national' => 9, 'trunk' => '0', 'exemple' => '081 234 5678'],
        '244' => ['nom' => 'Angola', 'national' => 9, 'trunk' => '', 'exemple' => '923 123 456'],
        '254' => ['nom' => 'Kenya', 'national' => 9, 'trunk' => '0', 'exemple' => '712 123456'],
        '255' => ['nom' => 'Tanzanie', 'national' => 9, 'trunk' => '0', 'exemple' => '712 345 678'],
        '256' => ['nom' => 'Ouganda', 'national' => 9, 'trunk' => '0', 'exemple' => '712 345 678'],
        '260' => ['nom' => 'Zambie', 'national' => 9, 'trunk' => '0', 'exemple' => '712 345 678'],
        '263' => ['nom' => 'Zimbabwe', 'national' => 9, 'trunk' => '0', 'exemple' => '71 234 5678'],
        '250' => ['nom' => 'Rwanda', 'national' => 9, 'trunk' => '0', 'exemple' => '78 123 4567'],
        '257' => ['nom' => 'Burundi', 'national' => 8, 'trunk' => '', 'exemple' => '79 12 34 56'],
        '33' => ['nom' => 'France', 'national' => 9, 'trunk' => '0', 'exemple' => '6 12 34 56 78'],
        '32' => ['nom' => 'Belgique', 'national' => 9, 'trunk' => '0', 'exemple' => '470 12 34 56'],
        '41' => ['nom' => 'Suisse', 'national' => 9, 'trunk' => '0', 'exemple' => '79 123 45 67'],
        '44' => ['nom' => 'Royaume-Uni', 'national' => 9, 'trunk' => '0', 'exemple' => '7911 123456'],
        '39' => ['nom' => 'Italie', 'national' => 10, 'trunk' => '', 'exemple' => '312 345 6789'],
        '34' => ['nom' => 'Espagne', 'national' => 9, 'trunk' => '', 'exemple' => '612 345 678'],
        '49' => ['nom' => 'Allemagne', 'national' => 11, 'trunk' => '0', 'exemple' => '151 12345678'],
        '351' => ['nom' => 'Portugal', 'national' => 9, 'trunk' => '', 'exemple' => '912 345 678'],
        '352' => ['nom' => 'Luxembourg', 'national' => 9, 'trunk' => '', 'exemple' => '621 123 456'],
        '212' => ['nom' => 'Maroc', 'national' => 9, 'trunk' => '0', 'exemple' => '612 345 678'],
        '213' => ['nom' => 'Algérie', 'national' => 9, 'trunk' => '0', 'exemple' => '555 12 34 56'],
        '216' => ['nom' => 'Tunisie', 'national' => 8, 'trunk' => '', 'exemple' => '20 123 456'],
        '20' => ['nom' => 'Égypte', 'national' => 10, 'trunk' => '0', 'exemple' => '100 123 4567'],
        '1' => ['nom' => 'Canada / États-Unis', 'national' => 10, 'trunk' => '', 'exemple' => '202 555 0147'],
        '55' => ['nom' => 'Brésil', 'national' => 11, 'trunk' => '0', 'exemple' => '11 91234 5678'],
        '7' => ['nom' => 'Russie', 'national' => 10, 'trunk' => '8', 'exemple' => '912 345 67 89'],
        '27' => ['nom' => 'Afrique du Sud', 'national' => 9, 'trunk' => '0', 'exemple' => '82 123 4567'],
        '91' => ['nom' => 'Inde', 'national' => 10, 'trunk' => '0', 'exemple' => '98765 43210'],
        '86' => ['nom' => 'Chine', 'national' => 11, 'trunk' => '0', 'exemple' => '138 0013 8000'],
    ];

    /**
     * Pays propose en premier : le site cible ces deux pays (CC §17).
     * Le Benin est le defaut, c'est le pays du proprietaire.
     */
    public const PAYS_PAR_DEFAUT = '229';

    public static function existe(string $indicatif): bool
    {
        return isset(self::PAYS[$indicatif]);
    }

    public static function nom(string $indicatif): string
    {
        return self::PAYS[$indicatif]['nom'] ?? $indicatif;
    }

    /** Exemple de saisie affiche sous le champ telephone. */
    public static function exemple(string $indicatif): ?string
    {
        return self::PAYS[$indicatif]['exemple'] ?? null;
    }

    /**
     * Longueur attendue du numero national significatif (hors indicatif
     * et hors prefixe national). Null quand la longueur n'est pas fiable.
     */
    public static function longueurNationale(string $indicatif): ?int
    {
        $longueur = self::PAYS[$indicatif]['national'] ?? null;

        return $longueur === null ? null : (int) $longueur;
    }

    /** Prefixe national a retirer quand le client le tape (ex. "0"). */
    public static function trunk(string $indicatif): string
    {
        return self::PAYS[$indicatif]['trunk'] ?? '';
    }

    /**
     * Verifie qu'un numero saisi est plausible pour le pays choisi.
     *
     * L'objectif n'est pas d'etre exhaustif mais d'arreter les fautes de
     * frappe qui produiraient un lien WhatsApp mort : chiffre manquant,
     * prefixe du pays oublie, numero trop court.
     *
     * Un numero deja saisi au format international est accepte tel quel,
     * quel que soit le pays choisi dans le menu.
     */
    public static function estValide(string $indicatif, string $telephone): bool
    {
        $chiffres = preg_replace('/\D+/', '', $telephone) ?? '';

        if ($chiffres === '') {
            return false;
        }

        // Format international "00..." : complet, on le respecte.
        if (str_starts_with($chiffres, '00')) {
            return preg_match('/^\d{8,15}$/', substr($chiffres, 2)) === 1;
        }

        // Le client peut coller un numero deja complet, meme en ayant
        // choisi un autre pays dans le menu : la saisie prime sur le menu,
        // sinon il devrait retoucher un numero pourtant correct.
        $codes = array_keys(self::PAYS);
        usort($codes, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $connu) {
            if (preg_match('/^'.$connu.'(\d+)$/', $chiffres, $captures) !== 1) {
                continue;
            }

            $reste = $captures[1];
            $attenduConnu = self::longueurNationale($connu);

            // Assez de chiffres derriere l'indicatif, et longueur conforme
            // quand on la connait : evite d'accepter "1 234 567" comme un
            // numero americain a 10 chiffres.
            if (strlen($reste) >= 6
                && ($attenduConnu === null || strlen($reste) === $attenduConnu)) {
                return true;
            }
        }

        // L'indicatif choisi est deja present dans la saisie.
        if (preg_match('/^'.$indicatif.'\d/', $chiffres) === 1) {
            return true;
        }

        $longueur = strlen($chiffres);
        $attendu = self::longueurNationale($indicatif);

        if ($attendu === null) {
            return $longueur >= 6;
        }

        // Benin : 8 chiffres (format d'avant 2024), 9 sans le "0" de tete,
        // ou 10 au format actuel. Les trois saisies sont acceptees.
        if ($indicatif === '229') {
            return $longueur >= 8 && $longueur <= 10;
        }

        $trunk = self::trunk($indicatif);

        if ($trunk !== '' && $longueur === $attendu + strlen($trunk)) {
            return true;
        }

        return $longueur === $attendu;
    }

    /**
     * Liste des pays pour le menu deroulant, Benin et Togo en tete,
     * puis les autres pays cibles, puis le reste par ordre alphabetique.
     */
    public static function options(): array
    {
        $tete = ['229', '228'];
        $reste = array_keys(self::PAYS);
        $reste = array_values(array_diff($reste, $tete));
        usort($reste, fn ($a, $b) => strcmp(self::nom($a), self::nom($b)));

        $liste = [];

        foreach ([...$tete, ...$reste] as $indicatif) {
            $liste[] = [
                'indicatif' => $indicatif,
                'libelle' => '+'.$indicatif.' — '.self::PAYS[$indicatif]['nom'],
            ];
        }

        return $liste;
    }
}
