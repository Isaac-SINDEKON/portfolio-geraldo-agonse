@extends('layouts.site')

@section('title', 'Page introuvable – ' . ($site['settings']['name'] ?? 'Géraldo Perridys AGONSE'))
@section('meta_description', '')
@section('robots', 'noindex, follow')

@section('content')

    <section class="bg-slate-50 py-24 sm:py-32">
        <div class="container-x text-center">
            <p class="text-6xl font-extrabold text-primary-600">404</p>
            <h1 class="mt-6 text-3xl font-extrabold sm:text-4xl">Cette page n’existe pas</h1>
            <p class="mx-auto mt-4 max-w-lg text-slate-600">
                Le lien que vous avez suivi est peut-être obsolète. Retrouvez le contenu depuis l’accueil
                ou la page Contact.
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="btn-primary">Retour à l’accueil</a>
                <a href="{{ route('contact') }}" class="btn-outline">Me contacter</a>
            </div>
        </div>
    </section>

@endsection
