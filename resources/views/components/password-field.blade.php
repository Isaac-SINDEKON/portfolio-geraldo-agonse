@props([
    'id',
    'name',
    'label',
    'autocomplete' => 'current-password',
    'hint' => null,
])

{{-- Champ de mot de passe avec oeil afficher / masquer.
     Le bouton porte data-password-toggle : le script frontal (resources/js/app.js)
     bascule le type de l'input et les deux icones, sans dependre d'Alpine, car
     la page de connexion ne charge pas Livewire. --}}

<div>
    <label for="{{ $id }}" class="field-label">{{ $label }}</label>

    <div class="relative" data-password>
        <input type="password"
               id="{{ $id }}"
               name="{{ $name }}"
               @class(['field pr-12', 'field-error' => $errors->has($name)])
               autocomplete="{{ $autocomplete }}"
               required>

        <button type="button"
                data-password-toggle
                aria-controls="{{ $id }}"
                aria-label="Afficher le mot de passe"
                aria-pressed="false"
                title="Afficher le mot de passe"
                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 transition-colors hover:text-primary-600 focus-visible:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500">
            <x-icon name="eye" data-password-icon="visible" class="h-5 w-5" />
            <x-icon name="eye-off" data-password-icon="hidden" class="hidden h-5 w-5" />
        </button>
    </div>

    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name) <p class="field-message">{{ $message }}</p> @enderror
</div>
