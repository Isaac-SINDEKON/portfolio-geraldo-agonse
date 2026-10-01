{{-- Formulaire « Demander une formation » (CC §14).
     Champs propres à la formation, sans aucun champ de devis. --}}
<div>
    @if ($sent)
        <div class="rounded-2xl border border-green-200 bg-green-50 p-6 text-center" role="status">
            <x-icon name="check" class="mx-auto h-10 w-10 text-green-600" />
            <h3 class="mt-3 text-lg font-bold text-green-900">Votre demande est bien reçue</h3>
            <p class="mt-2 text-sm leading-relaxed text-green-800">
                Je vous réponds sur le numéro WhatsApp que vous avez indiqué, et un accusé
                de réception vous est envoyé par email.
            </p>
            <button type="button" wire:click="nouvelleDemande" class="btn-outline mt-5">
                Envoyer une autre demande
            </button>
        </div>
    @else
        <h2 id="titre-formulaire-formation" class="text-xl font-bold text-slate-900 sm:text-2xl">
            Formulaire de demande de formation
        </h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">
            Tous les champs marqués <span class="text-red-500">*</span> sont nécessaires pour
            vous répondre. Les autres m'aident à préparer une proposition précise.
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
                <label for="formation-website">Ne pas remplir</label>
                <input type="text" id="formation-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="formation-organisation" class="field-label">Organisation <span class="text-red-500">*</span></label>
                    <input type="text" id="formation-organisation" wire:model="organisation"
                           @class(['field', 'field-error' => $errors->has('organisation')]) required
                           placeholder="Ex. Ministère de l'éducation">
                    @error('organisation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="formation-responsable" class="field-label">Responsable <span class="text-red-500">*</span></label>
                    <input type="text" id="formation-responsable" wire:model="responsable"
                           @class(['field', 'field-error' => $errors->has('responsable')]) required
                           placeholder="Prénom et nom">
                    @error('responsable') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="formation-fonction" class="field-label">Fonction</label>
                    <input type="text" id="formation-fonction" wire:model="fonction"
                           @class(['field', 'field-error' => $errors->has('fonction')])
                           placeholder="Ex. Directrice des ressources humaines">
                    @error('fonction') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <x-champ-telephone
                    :pays="$pays"
                    :indicatif="$indicatif_pays"
                    :exemple="$exempleTelephone"
                    prefixe="formation" />

                <div>
                    <label for="formation-email" class="field-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="formation-email" wire:model="email"
                           @class(['field', 'field-error' => $errors->has('email')]) required
                           placeholder="vous@organisation.com">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="formation-theme" class="field-label">Thème <span class="text-red-500">*</span></label>
                    <input type="text" id="formation-theme" wire:model="theme" list="themes-formation"
                           @class(['field', 'field-error' => $errors->has('theme')]) required
                           placeholder="Sélectionnez ou saisissez un thème">
                    <datalist id="themes-formation">
                        @foreach ($themes as $theme)
                            <option value="{{ $theme }}"></option>
                        @endforeach
                    </datalist>
                    @error('theme') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="formation-participants" class="field-label">Nombre de participants</label>
                    <input type="number" min="1" id="formation-participants" wire:model="participants"
                           @class(['field', 'field-error' => $errors->has('participants')])
                           placeholder="Ex. 15">
                    @error('participants') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="formation-format" class="field-label">Format</label>
                    <select id="formation-format" wire:model="format"
                            @class(['field', 'field-error' => $errors->has('format')])>
                        <option value="">Sélectionnez un format</option>
                        <option value="Présentiel">Présentiel</option>
                        <option value="Distanciel">Distanciel</option>
                        <option value="Hybride">Hybride</option>
                    </select>
                    @error('format') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="formation-date" class="field-label">Date souhaitée</label>
                    <input type="text" id="formation-date" wire:model="date_souhaitee"
                           @class(['field', 'field-error' => $errors->has('date_souhaitee')])
                           placeholder="Ex. 15/11/2026">
                    @error('date_souhaitee') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="formation-message" class="field-label">Message</label>
                <textarea id="formation-message" wire:model="message" rows="5"
                          @class(['field', 'field-error' => $errors->has('message')])
                          placeholder="Précisez votre contexte, vos objectifs ou vos contraintes."></textarea>
                @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Envoyer ma demande</span>
                    <span wire:loading wire:target="submit">Envoi en cours…</span>
                </button>

                <p class="text-xs text-slate-500">
                    Transmise à geraldoagonse@gmail.com, utilisée uniquement pour traiter votre demande.
                </p>
            </div>
        </form>
    @endif
</div>
