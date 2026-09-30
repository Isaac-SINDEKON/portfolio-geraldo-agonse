<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    protected array $models = [
        'domains' => \App\Models\Domain::class,
        'reasons' => \App\Models\Reason::class,
        'services' => \App\Models\Service::class,
        'experiences' => \App\Models\Experience::class,
        'testimonials' => \App\Models\Testimonial::class,
        'formations' => \App\Models\Formation::class,
        'gallery' => \App\Models\GalleryImage::class,
    ];

    protected array $rules = [
        'domains' => ['title' => 'required|string', 'description' => 'required|string', 'icon' => 'nullable|string', 'sort_order' => 'nullable|integer'],
        'reasons' => ['title' => 'required|string', 'description' => 'required|string', 'icon' => 'nullable|string', 'sort_order' => 'nullable|integer'],
        'services' => ['title' => 'required|string', 'description' => 'required|string', 'icon' => 'nullable|string', 'sort_order' => 'nullable|integer'],
        'experiences' => ['title' => 'required|string', 'description' => 'required|string', 'icon' => 'nullable|string', 'sort_order' => 'nullable|integer'],
        'testimonials' => ['author' => 'required|string', 'fonction' => 'nullable|string', 'content' => 'required|string', 'photo' => 'nullable|string', 'sort_order' => 'nullable|integer', 'active' => 'nullable|boolean'],
        'formations' => [
            'title' => 'required|string',
            'slug' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'description' => 'required|string',
            'objectives' => 'nullable|array',
            'public_cible' => 'nullable|string',
            'duree' => 'nullable|string',
            'modalites' => 'nullable|string',
            'programme' => 'nullable|array',
            'icon' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'active' => 'nullable|boolean',
        ],
        'gallery' => ['image_path' => 'required|string', 'caption' => 'nullable|string', 'sort_order' => 'nullable|integer'],
    ];

    public function index(string $resource): JsonResponse
    {
        $model = $this->models[$resource] ?? null;

        if (! $model) {
            return response()->json(['message' => 'Ressource inconnue.'], 404);
        }

        return response()->json($model::orderBy('sort_order')->get());
    }

    public function all(): JsonResponse
    {
        $data = [];
        foreach ($this->models as $key => $model) {
            $data[$key] = $model::orderBy('sort_order')->get();
        }
        $data['settings'] = \App\Models\Setting::allDecoded();

        return response()->json($data);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $data = $this->validatePayload($request, $resource);
        $model = $this->models[$resource];
        $item = $model::create($data);

        return response()->json($item, 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $data = $this->validatePayload($request, $resource);
        $model = $this->models[$resource];
        $item = $model::findOrFail($id);
        $item->update($data);

        return response()->json($item);
    }

    public function destroy(string $resource, int $id): JsonResponse
    {
        $model = $this->models[$resource];
        $item = $model::findOrFail($id);

        if ($resource === 'gallery' && $item->image_path) {
            $full = storage_path('app/public/' . $item->image_path);
            if (is_file($full)) {
                @unlink($full);
            }
        }

        $item->delete();

        return response()->json(['message' => 'Supprimé.']);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        // La liste des numéros est validée à part : validate() ne renvoie que
        // les clés couvertes par une règle, et tous les autres réglages
        // (textes, photos, SEO) n'en ont aucune.
        //
        // Aucune règle 'boolean' sur is_whatsapp : le cast automatique de
        // Laravel transformerait la chaîne "1" d'une case cochée en false
        // avant que la normalisation ne puisse la lire. La conversion est
        // faite explicitement dans normaliseExtraPhones().
        $request->validate([
            'settings.extra_phones' => 'sometimes|array|max:10',
            'settings.extra_phones.*.label' => 'nullable|string|max:80',
            'settings.extra_phones.*.number' => 'nullable|string|max:40',
        ]);

        $settings = (array) $request->input('settings', []);

        foreach ($settings as $key => $value) {
            if ($key === 'extra_phones') {
                $value = $this->normaliseExtraPhones((array) $value);
            }

            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $value]);
        }

        return response()->json(['message' => 'Contenu mis à jour.']);
    }

    /**
     * Convertit la valeur d'une checkbox en boolien.
     *
     * Une case non cochee n'est pas transmise par le navigateur, et une case
     * cochee peut arriver sous la forme "1", "on", "true" ou "yes" selon le
     * client. Le filtre FILTER_VALIDATE_BOOLEAN ne reconnait que certaines
     * ecritures : on normalise explicitement pour ne jamais perdre la
     * designation du numero WhatsApp.
     */
    protected function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(mb_strtolower(trim((string) $value)), ['1', 'on', 'true', 'yes'], true);
    }

    /**
     * Nettoie la liste des numéros supplémentaires (§22) :
     * - un seul numéro peut porter le badge WhatsApp flottant (§17) ;
     * - les entrées sans numéro sont ignorées ;
     * - un même numéro ne peut pas être saisi deux fois.
     */
    protected function normaliseExtraPhones(array $phones): array
    {
        $clean = [];
        $seen = [];
        $whatsappDone = false;

        foreach ($phones as $phone) {
            $number = trim((string) ($phone['number'] ?? ''));
            $label = trim((string) ($phone['label'] ?? ''));

            if ($number === '' || $label === '') {
                continue;
            }

            $digits = preg_replace('/\D+/', '', $number);
            $key = $digits.'|'.mb_strtolower($label);

            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $isWhatsapp = ! $whatsappDone && $this->toBoolean($phone['is_whatsapp'] ?? false);
            $whatsappDone = $whatsappDone || $isWhatsapp;

            $clean[] = [
                'label' => $label,
                'number' => $number,
                'is_whatsapp' => $isWhatsapp,
            ];
        }

        return array_values($clean);
    }

    protected function validatePayload(Request $request, string $resource): array
    {
        $rules = $this->rules[$resource] ?? [];

        if (($resource === 'formations') && $request->route('id')) {
            $rules['slug'] = ['nullable', 'string', Rule::unique('formations')->ignore($request->route('id'))];
        }

        $data = $request->validate($rules);

        // La colonne sort_order est NOT NULL en base : une valeur vide devient 0.
        if (array_key_exists('sort_order', $data) && blank($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        if (isset($data['active'])) {
            $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);
        }

        // Un nouveau programme reçoit un slug ; en modification, le slug
        // existant est conserve : changer la duree ou un texte ne doit pas
        // casser l'URL deja partagee.
        if (($resource === 'formations') && empty($data['slug']) && isset($data['title']) && ! $request->route('id')) {
            $data['slug'] = Str::slug($data['title']) . '-' . Str::lower(Str::random(4));
        }

        return $data;
    }
}