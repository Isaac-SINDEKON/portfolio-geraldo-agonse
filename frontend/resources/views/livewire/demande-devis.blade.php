{{-- Formulaire « Demander un devis » (CC §15). Champs propres au devis :
     ni fonction, ni format, ni message de formation. --}}
<div>
    @if ($sent)
        <div class="rounded-2xl border border-green-200 bg-green-50 p-6 text-center" role="status">
            <x-icon name="check" class="mx-auto h-10 w-10 text-green-600" />
            <h3 class="mt-3 text-lg font-bold text-green-900">Votre demande de devis est bien reçue</h3>
            <p class="mt-2 text-sm leading-relaxed text-green-800">
                Je vous réponds sur le numéro WhatsApp que vous avez indiqué, et un accusé
                de réception vous est envoyé par email.
            </p>
            <button type="button" wire:click="nouvelleDemande" class="btn-outline mt-5">
                Envoyer une autre demande
            </button>
        </div>
    @else
        <h2 id="titre-formulaire-devis" class="text-xl font-bold text-ink sm:text-2xl">
            Formulaire de demande de devis
        </h2>
        <p class="mt-2 text-sm leading-relaxed text-copy">
            Tous les champs marqués <span class="text-red-500">*</span> sont nécessaires pour
            vous répondre. Le budget est facultatif.
        </p>

        @if ($errorMessage !== '')
            <div class="mt-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
                <p>{{ $errorMessage }}</p>
            </div>
        @endif

        <form wire:submit="submit" class="mt-6 space-y-5" novalidate>
            {{-- Champ piège anti-spam : invisible pour le client, rempli par les robots. --}}
            <div class="hidden" aria-hidden="true">
                <label for="devis-website">Ne pas remplir</label>
                <input type="text" id="devis-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="devis-organisation" class="field-label">Organisation <span class="text-red-500">*</span></label>
                    <input type="text" id="devis-organisation" wire:model="organisation"
                           @class(['field', 'field-error' => $errors->has('organisation')]) required
                           placeholder="Ex. Entreprise SARL">
                    @error('organisation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="devis-responsable" class="field-label">Responsable <span class="text-red-500">*</span></label>
                    <input type="text" id="devis-responsable" wire:model="responsable"
                           @class(['field', 'field-error' => $errors->has('responsable')]) required
                           placeholder="Prénom et nom">
                    @error('responsable') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <x-champ-telephone
                    :pays="$pays"
                    :indicatif="$indicatif_pays"
                    :exemple="$exempleTelephone"
                    prefixe="devis" />

                <div>
                    <label for="devis-email" class="field-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="devis-email" wire:model="email"
                           @class(['field', 'field-error' => $errors->has('email')]) required
                           placeholder="vous@organisation.com">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="devis-theme" class="field-label">Thème <span class="text-red-500">*</span></label>
                    <input type="text" id="devis-theme" wire:model="theme"
                           @class(['field', 'field-error' => $errors->has('theme')]) required
                           placeholder="Ex. Atelier leadership commercial">
                    @error('theme') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="devis-participants" class="field-label">Nombre de participants</label>
                    <input type="number" min="1" id="devis-participants" wire:model="participants"
                           @class(['field', 'field-error' => $errors->has('participants')])
                           placeholder="Ex. 15">
                    @error('participants') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="devis-ville" class="field-label">Ville</label>
                    <input type="text" id="devis-ville" wire:model="ville"
                           @class(['field', 'field-error' => $errors->has('ville')])
                           placeholder="Ex. Cotonou">
                    @error('ville') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="devis-date" class="field-label">Date</label>
                    <input type="text" id="devis-date" wire:model="date"
                           @class(['field', 'field-error' => $errors->has('date')])
                           placeholder="Ex. 15/11/2026">
                    @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="devis-duree" class="field-label">Durée</label>
                    <input type="text" id="devis-duree" wire:model="duree"
                           @class(['field', 'field-error' => $errors->has('duree')])
                           placeholder="Ex. 2 jours">
                    @error('duree') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="devis-besoins" class="field-label">Besoins particuliers</label>
                <textarea id="devis-besoins" wire:model="besoins" rows="5"
                          @class(['field', 'field-error' => $errors->has('besoins')])
                          placeholder="Décrivez vos besoins spécifiques, le public ou les thématiques souhaitées."></textarea>
                @error('besoins') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:max-w-xs">
                <label for="devis-budget" class="field-label">
                    Budget indicatif <span class="font-normal text-muted">(facultatif)</span>
                </label>
                <input type="text" id="devis-budget" wire:model="budget"
                       @class(['field', 'field-error' => $errors->has('budget')])
                       placeholder="Ex. 500 000 FCFA">
                @error('budget') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Demander un devis</span>
                    <span wire:loading wire:target="submit">Envoi en cours…</span>
                </button>

                <p class="text-xs text-muted">
                    Transmise à geraldoagonse@gmail.com, utilisée uniquement pour traiter votre demande.
                </p>
            </div>
        </form>
    @endif
</div>
