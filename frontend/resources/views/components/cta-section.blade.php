@props(['heading' => null, 'text' => null, 'title' => null, 'textButton' => null, 'form' => 'formation'])

<section class="bg-slate-50 py-16 sm:py-20">
    <div class="container-x">
        <div class="mx-auto max-w-3xl rounded-2xl bg-slate-900 px-6 py-14 text-center sm:px-12">
            <h2 class="text-3xl leading-tight font-extrabold tracking-tight text-white sm:text-4xl">
                {{ $heading ?? ($site['settings']['cta_title'] ?? 'Prêt à optimiser la performance de vos équipes ?') }}
            </h2>
            <p class="mt-4 text-base text-slate-300">
                {{ $text ?? ($site['settings']['cta_text'] ?? 'Contactez-moi dès aujourd\'hui pour discuter de vos besoins en formation.') }}
            </p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact', ['form' => $form]) }}#formulaire-demande" class="btn-primary">
                    {{ $title ?? 'Demander une formation' }}
                </a>
                <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener" class="btn-accent">
                    {{ $textButton ?? 'Me contacter sur WhatsApp' }}
                </a>
            </div>
        </div>
    </div>
</section>
