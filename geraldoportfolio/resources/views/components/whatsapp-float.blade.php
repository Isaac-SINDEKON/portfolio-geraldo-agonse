{{--
    Bouton WhatsApp flottant présent sur toutes les pages (CC §17).
    Le message prérempli est administrable depuis Contenu > Coordonnées.

    Il se replie en pastille ronde et s'étire au survol ou au focus clavier :
    le libellé n'occupe l'écran que lorsqu'il est utile, et la pastille ne
    masque jamais le contenu sur mobile.

    Deux erreurs corrigées ici :
      - le halo était en `z-index: -10`, donc peint SOUS le fond du bouton et
        totalement invisible. Il est désormais un frère positionné en `z-0`,
        le bouton passant devant en `z-10`.
      - l'animation « salut » et la respiration du halo se disputaient la même
        attention. Il n'en reste qu'une seule, la respiration de l'anneau.
--}}
<div class="fixed right-4 bottom-4 z-50 sm:right-6 sm:bottom-6">
    {{-- Anneau respirant : deux anneaux qui s'écartent en boucle. C'est le
         seul mouvement perpetual du site, et il est neutralisé par la
         préférence « moins d'animation ». --}}
    <span class="halo-whatsapp pointer-events-none absolute inset-0 -z-0 rounded-full"
          aria-hidden="true"></span>

    <a href="{{ $content->whatsappUrl() }}"
       target="_blank" rel="noopener"
       aria-label="Contacter sur WhatsApp"
       data-frappe
       class="wa-flottant group relative z-10 flex items-center rounded-full bg-whatsapp py-3.5 pr-3.5 pl-3.5 text-white shadow-lift
              transition-[padding,background-color,box-shadow] duration-300 ease-out
              hover:bg-[#1eb855] hover:shadow-glow
              focus-visible:pr-5 motion-reduce:transition-none sm:hover:pr-5">

        <span class="frappe-icone relative shrink-0" aria-hidden="true">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.5 14.4c-.3-.2-1.7-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.7 1-.9 1.2-.2.2-.3.2-.6.1-.3-.2-1.2-.5-2.3-1.4-.9-.8-1.4-1.7-1.6-2-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5 0-.2 0-.4 0-.5 0-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3z"/>
                <path d="M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2zm0 18.2c-1.6 0-3.1-.4-4.4-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1112 20.2z"/>
            </svg>
        </span>

        {{-- Le libellé est présent dans le DOM mais replié : `max-width: 0`
             l'empile sans le supprimer, donc la largeur s'anime au lieu de
             sauter. Il est masqué aux lecteurs d'écran, l'attribut aria-label
             du lien donnant déjà le nom accessible. --}}
        <span class="ml-0 max-w-0 overflow-hidden text-sm font-semibold whitespace-nowrap opacity-0 transition-all duration-300 ease-out group-hover:ml-2.5 group-hover:max-w-52 group-hover:opacity-100 group-focus-visible:ml-2.5 group-focus-visible:max-w-52 group-focus-visible:opacity-100 motion-reduce:transition-none"
              aria-hidden="true">
            Me contacter sur WhatsApp
        </span>
    </a>
</div>