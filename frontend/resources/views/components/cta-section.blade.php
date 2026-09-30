@props(['heading' => null, 'text' => null, 'title' => null, 'textButton' => null, 'form' => 'formation'])

<section class="bg-primary-700 py-14 sm:py-20">
    <div class="container-x">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                {{ $heading ?? ($site['settings']['cta_title'] ?? 'Prêt à optimiser la performance de vos équipes ?') }}
            </h2>
            <p class="mt-4 text-base text-primary-100">
                {{ $text ?? ($site['settings']['cta_text'] ?? 'Contactez-moi dès aujourd\'hui pour discuter de vos besoins en formation.') }}
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact', ['form' => $form]) }}#formulaire-demande" class="btn bg-white text-primary-700 hover:bg-primary-50">
                    {{ $title ?? 'Demander une formation' }}
                </a>
                <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener" class="btn-accent">
                    {{ $textButton ?? 'Me contacter sur WhatsApp' }}
                </a>
            </div>
        </div>
    </div>
</section>
