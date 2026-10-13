<?php

namespace App\Livewire\Concerns;

use App\Services\CountryPhones;
use Closure;

/**
 * Gestion du telephone dans les formulaires de demande.
 *
 * Le client choisit son pays puis saisit son numero national. Les deux
 * separes car l'indicatif change la conversion : "0151609682" est un numero
 * benin mais ne peut pas etre un numero togo.
 *
 * Partage par les deux formulaires (formation et devis) pour que la
 * validation reste identique partout.
 */
trait ValideTelephone
{
    /** Message affiche quand le numero ne correspond pas au pays choisi. */
    protected function messageTelephone(): string
    {
        $pays = CountryPhones::nom($this->indicatif_pays ?: CountryPhones::PAYS_PAR_DEFAUT);
        $exemple = CountryPhones::exemple($this->indicatif_pays ?: CountryPhones::PAYS_PAR_DEFAUT);

        return "Ce numéro ne semble pas être valide pour : $pays."
            .($exemple ? " Exemple de saisie : $exemple." : '');
    }

    /** Verifie que l'indicatif choisi fait partie de la liste connue. */
    protected function validerIndicatif(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! CountryPhones::existe((string) $value)) {
                $fail('Choisissez un pays dans la liste.');
            }
        };
    }

    /**
     * Verifie le numero face au pays choisi et signale une saisie qui
     * produirait un lien WhatsApp inutilisable.
     */
    protected function validerNumero(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $indicatif = (string) ($this->indicatif_pays ?: CountryPhones::PAYS_PAR_DEFAUT);

            if (! CountryPhones::estValide($indicatif, (string) $value)) {
                $fail($this->messageTelephone());
            }
        };
    }

    /** Exemple de saisie affiche sous le champ, selon le pays choisi. */
    protected function exempleTelephone(): ?string
    {
        return CountryPhones::exemple($this->indicatif_pays ?: CountryPhones::PAYS_PAR_DEFAUT);
    }

    /** Options du menu deroulant des pays. */
    protected function optionsPays(): array
    {
        return CountryPhones::options();
    }
}
