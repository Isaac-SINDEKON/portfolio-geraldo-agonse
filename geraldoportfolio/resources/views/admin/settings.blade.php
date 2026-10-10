@extends('layouts.admin')

@section('title', 'Présentation & coordonnées')

@section('content')

    <p class="text-sm text-muted">
        Identité, coordonnées, textes de la page À propos, galerie et référencement. Les modifications sont
        appliquées immédiatement sur le site public.
    </p>

    @php
        $grouped = [];
        foreach ($fields as $key => $field) {
            $grouped[$field['group']][$key] = $field;
        }
    @endphp

    {{-- La suppression d'une image n'est immediate qu'a l'enregistrement : la
         case a cocher ne fait que marquer l'intention. La confirmation se pose
         donc au moment de valider, la ou l'action devient reelle — sinon on
         interroge l'utilisateur avant qu'il n'ait choisi. --}}
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6"
          x-data="{}"
          x-on:submit="if (document.querySelector('input[name$=\'_remove\']:checked') && ! window.confirm('Retirer definitivement l\'image selectionnee ? Cette action est irreversible.')) { $event.preventDefault(); }">
        @csrf

        @foreach ($grouped as $groupName => $groupFields)
            <section class="card">
                <h2 class="text-lg font-bold">{{ $groupName }}</h2>

                @foreach ($groupFields as $key => $field)
                    <div class="mt-4">
                        <label for="{{ $key }}" class="field-label">
                            {{ $field['label'] }}
                            @if ($field['required'] ?? false)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        @if ($field['type'] === 'textarea')
                            <textarea id="{{ $key }}" name="{{ $key }}" rows="4"
                                      @class(['field', 'field-error' => $errors->has($key)])>{{ old($key, $settings[$key] ?? '') }}</textarea>
                        @elseif ($field['type'] === 'color')
                            {{-- Sélecteur de couleur.

                                 Un seul champ nommé est la source de vérité : les
                                 pastilles le pilotent au lieu d'être des radios.
                                 Deux contrôles partageant le même nom s'enverraient
                                 en double, et le second l'emporterait au moment de
                                 l'enregistrement — un ajustement fin se verrait
                                 écrasé par le dernier preset choisi. --}}
                            <div class="flex flex-wrap items-center gap-3"
                                 x-data="{ teinte: @js(old($key, $settings[$key] ?? '#1d4ed8')) }">
                                <input type="color" name="{{ $key }}" x-model="teinte"
                                       @class(['h-11 w-16 cursor-pointer rounded-lg border border-line bg-white p-1', 'field-error' => $errors->has($key)])>

                                <div class="flex flex-wrap gap-2" role="group" aria-label="Nuancier">
                                    @foreach (['#1d4ed8', '#1e40af', '#0f172a', '#0e7490', '#047857', '#7c3aed', '#b45309', '#be123c'] as $teinte)
                                        <button type="button"
                                                title="{{ $teinte }}"
                                                aria-label="Appliquer la teinte {{ $teinte }}"
                                                class="h-8 w-8 rounded-full ring-offset-2 transition hover:scale-110"
                                                :class="teinte === @js($teinte) ? 'ring-2 ring-ink' : ''"
                                                style="background: {{ $teinte }}"
                                                x-on:click="teinte = @js($teinte)"></button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <input type="{{ $field['type'] === 'email' ? 'email' : 'text' }}" id="{{ $key }}" name="{{ $key }}"
                                   value="{{ old($key, $settings[$key] ?? '') }}"
                                   @class(['field', 'field-error' => $errors->has($key)])
                                   @if ($field['required'] ?? false) required @endif>
                        @endif

                        @if (! empty($field['hint']))
                            <p class="mt-1 text-xs text-muted">{{ $field['hint'] }}</p>
                        @endif
                        @error($key) <p class="field-message">{{ $message }}</p> @enderror
                    </div>

                    @if ($field['type'] === 'image')
                        @php $imageUrl = $content->imageUrl($settings[$key] ?? null); @endphp
                        <div class="mt-3 flex flex-wrap items-center gap-4">
                            @if ($imageUrl)
                                {{-- Un logo n'est pas une photo : contain evite de le
                                     rogner dans l'apercu, comme dans l'affichage reel. --}}
                                <img src="{{ $imageUrl }}" alt="{{ $field['label'] }}"
                                     @class([
                                         'h-24 w-24 rounded-xl border border-line',
                                         'object-contain p-1' => ($field['preview'] ?? null) === 'contain',
                                         'object-cover' => ($field['preview'] ?? null) !== 'contain',
                                     ])>
                            @endif
                            <label for="{{ $key }}-file" class="btn-outline cursor-pointer text-xs">
                                <x-icon name="upload" class="h-4 w-4" />
                                Choisir une image
                            </label>
                            <input type="file" id="{{ $key }}-file" name="{{ $key }}"
                                   accept="image/jpeg,image/png,image/webp" class="sr-only">
                            <p class="text-xs text-muted">JPEG, PNG ou WebP, 5 Mo maximum.</p>
                        </div>

                        @if ($imageUrl)
                            <label for="{{ $key }}-remove" class="mt-3 flex w-fit cursor-pointer items-center gap-2 text-xs font-medium text-alert-fort hover:text-alert-ink">
                                <input type="checkbox" id="{{ $key }}-remove" name="{{ $key }}_remove" value="1"
                                       class="h-4 w-4 rounded border-line text-alert-fort focus:ring-alert-fort">
                                Retirer l'image
                            </label>
                        @endif
                    @endif
                @endforeach

                {{-- Numéros supplémentaires : le propriétaire peut en ajouter
                     autant qu'il le souhaite (CC §22, sur le modèle des diplômes
                     et services extensibles des §4 et §10). --}}
                @if ($groupName === 'Coordonnées')
                    <div class="mt-6 border-t border-line pt-5"
                         x-data="{
                            phones: {{ Js::from($extraPhones) }},
                            add() { this.phones.push({ label: '', number: '', is_whatsapp: false }) },
                            remove(i) { this.phones.splice(i, 1) },
                            onlyOne(i) {
                                if (this.phones[i].is_whatsapp) {
                                    this.phones.forEach((p, k) => { if (k !== i) p.is_whatsapp = false })
                                }
                            },
                         }">

                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-bold text-ink">Numéros supplémentaires</h3>
                                <p class="mt-1 max-w-xl text-xs text-muted">
                                    Ajoutez d'autres numéros (bureau, second mobile, numéro d'un autre pays…).
                                    Chacun devient cliquable sur le site. Un seul peut porter le badge
                                    WhatsApp pour le bouton flottant (CC §17).
                                </p>
                            </div>
                            <button type="button" class="btn-outline text-xs" @click="add()">
                                <x-icon name="plus" class="h-4 w-4" />
                                Ajouter un numéro
                            </button>
                        </div>

                        {{-- Retirer la dernière ligne ne transmet aucun champ
                             extra_phones[...]. Ce marqueur distingue « liste
                             volontairement vide » d'un envoi partiel. --}}
                        <input type="hidden" name="extra_phones_present" value="1">

                        <template x-if="phones.length === 0">
                            <p class="mt-4 rounded-xl border border-dashed border-line bg-canvas px-4 py-6 text-center text-xs text-muted">
                                Aucun numéro supplémentaire. Utilisez « Ajouter un numéro » pour en créer un.
                            </p>
                        </template>

                        <div class="mt-4 space-y-3">
                            <template x-for="(phone, i) in phones" :key="i">
                                <div class="rounded-xl border border-line bg-canvas p-3">
                                    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)_auto]">
                                        <div>
                                            <label :for="'phone-label-' + i" class="field-label text-xs">Libellé</label>
                                            <input :id="'phone-label-' + i" type="text"
                                                   :name="'extra_phones[' + i + '][label]'"
                                                   x-model="phone.label" class="field text-sm"
                                                   placeholder="Bureau, Mobile Togo…">
                                        </div>
                                        <div>
                                            <label :for="'phone-number-' + i" class="field-label text-xs">Numéro</label>
                                            <input :id="'phone-number-' + i" type="tel"
                                                   :name="'extra_phones[' + i + '][number]'"
                                                   x-model="phone.number" class="field text-sm"
                                                   placeholder="+228 90 00 00 00">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" class="btn-ghost text-xs text-red-600"
                                                    @click="remove(i)">
                                                <x-icon name="trash" class="h-4 w-4" />
                                                Retirer
                                            </button>
                                        </div>
                                    </div>

                                    <label class="mt-3 flex cursor-pointer items-center gap-2 text-xs font-medium text-ink">
                                        <input type="checkbox" value="1"
                                               :name="'extra_phones[' + i + '][is_whatsapp]'"
                                               x-model="phone.is_whatsapp"
                                               @change="onlyOne(i)"
                                               class="rounded border-line text-primary-600 focus:ring-primary-500">
                                        Utiliser ce numéro pour le bouton WhatsApp flottant
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                @endif
            </section>
        @endforeach

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" />
                Enregistrer
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Annuler</a>
        </div>
    </form>

@endsection
