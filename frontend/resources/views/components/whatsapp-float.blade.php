{{--
    Bouton WhatsApp flottant présent sur toutes les pages (CC §17).
    Le message prérempli est administrable depuis Contenu > Coordonnées.
--}}
<a href="{{ $content->whatsappUrl() }}"
   target="_blank" rel="noopener"
   aria-label="Contacter sur WhatsApp"
   class="group fixed right-4 bottom-4 z-50 flex items-center gap-2 rounded-full bg-whatsapp px-4 py-3 text-white shadow-lg transition hover:bg-[#1eb855] hover:shadow-xl sm:right-7 sm:bottom-7 sm:px-5 sm:py-4">
    <svg class="h-7 w-7 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M17.5 14.4c-.3-.2-1.7-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.7 1-.9 1.2-.2.2-.3.2-.6.1-.3-.2-1.2-.5-2.3-1.4-.9-.8-1.4-1.7-1.6-2-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5 0-.2 0-.4 0-.5 0-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3z"/>
        <path d="M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2zm0 18.2c-1.6 0-3.1-.4-4.4-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1112 20.2z"/>
    </svg>
    <span class="hidden text-sm font-semibold sm:inline">Me contacter sur WhatsApp</span>
    <span class="absolute -top-1 -left-1 h-2.5 w-2.5 animate-ping rounded-full bg-whatsapp opacity-75"></span>
</a>
