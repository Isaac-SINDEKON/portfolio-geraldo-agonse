@props([
    'pays' => [],
    'indicatif' => '229',
    'exemple' => null,
    'prefixe' => 'telephone',
])

{{-- Sélecteur de pays + numéro WhatsApp.
     Partagé par les deux formulaires pour que la saisie soit identique
     partout : le client choisit son indicatif, puis saisit son numéro
     national. Les deux informations sont nécessaires pour construire un
     lien wa.me qui fonctionne (CC §17). --}}

<div>
    <label for="{{ $prefixe }}-pays" class="field-label">Pays</label>
    <select id="{{ $prefixe }}-pays" wire:model.live="indicatif_pays"
            @class(['field', 'field-error' => $errors->has('indicatif_pays')])>
        @foreach ($pays as $p)
            <option value="{{ $p['indicatif'] }}" @selected($indicatif === $p['indicatif'])>
                {{ $p['libelle'] }}
            </option>
        @endforeach
    </select>
    @error('indicatif_pays') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

    <label for="{{ $prefixe }}-numero" class="field-label mt-3">
        Téléphone <span class="text-red-500">*</span>
    </label>
    <input type="tel" id="{{ $prefixe }}-numero" wire:model.blur="telephone" inputmode="tel"
           autocomplete="tel"
           @class(['field', 'field-error' => $errors->has('telephone')]) required
           @if ($exemple) placeholder="Ex. {{ $exemple }}" @endif>
    <p class="mt-1.5 flex items-start gap-1.5 text-xs text-slate-500">
        <x-icon name="whatsapp" class="mt-px h-3.5 w-3.5 shrink-0 text-whatsapp" />
        Votre numéro WhatsApp : c'est par là que je vous répondrai.
        @if ($exemple)
            <span class="block">Saisissez le numéro national, sans l'indicatif : <span class="font-medium">{{ $exemple }}</span>.</span>
        @endif
    </p>
    @error('telephone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
