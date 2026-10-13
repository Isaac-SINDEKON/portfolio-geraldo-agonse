<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * Réglages stockés en JSON dans la colonne `value`.
     * La colonne reste un texte pour éviter une migration : la liste des
     * numéros supplémentaires (§22 « coordonnées ») et l'expérience & expertise
     * sont des ensembles structurés, pas des chaînes.
     */
    public const JSON_KEYS = ['extra_phones'];

    public $timestamps = true;

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Tous les réglages, avec les clés JSON décodées en tableaux.
     * Utilisé par l'API publique et l'API d'administration.
     */
    public static function allDecoded(): array
    {
        $settings = static::pluck('value', 'key')->all();

        foreach (self::JSON_KEYS as $key) {
            if (! isset($settings[$key])) {
                continue;
            }

            $decoded = json_decode((string) $settings[$key], true);
            $settings[$key] = is_array($decoded) ? $decoded : [];
        }

        return $settings;
    }
}
