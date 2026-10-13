{{--
    Liens de partage visibles (CC §30).
    Titre, description et URL reprennent exactement les balises Open Graph de
    la page : le partage affiche donc le meme apercu que les metadonnees.
    Deux emplacements : pied de page (toutes les pages) et bas de fiche
    formation (contenu le plus partage).
--}}
@props([
    'variant' => 'footer',
    'titre' => null,
    'description' => null,
])

@php
    $parametres = $site['settings'] ?? [];
    $partageTitre = trim($titre ?? (($parametres['name'] ?? 'Geraldo Perridys AGONSE').' - '.($parametres['role'] ?? 'Formateur')));
    $partageDescription = $description ?? ($parametres['tagline'] ?? '');
    $partageUrl = url()->current();
    $encode = fn ($v) => rawurlencode((string) $v);

    // Theme : le pied de page est sombre, la section de page suit le thème.
    $sombre = $variant !== 'page';
    $texteTitre = $sombre ? 'text-white' : 'text-ink';
    $pastille = $sombre ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-surface text-copy hover:bg-primary-50 hover:text-primary-700';

    $liens = [
        [
            'nom' => 'WhatsApp',
            'couleur' => 'bg-whatsapp hover:bg-[#1eb855]',
            'href' => 'https://wa.me/?text='.$encode($partageTitre.' - '.$partageDescription).'%20'.$encode($partageUrl),
            'icone' => 'whatsapp',
        ],
        [
            'nom' => 'Facebook',
            'couleur' => 'bg-[#1877f2] hover:bg-[#1667d8]',
            'href' => 'https://www.facebook.com/sharer/sharer.php?u='.$encode($partageUrl),
            'icone' => 'facebook',
        ],
        [
            'nom' => 'LinkedIn',
            'couleur' => 'bg-[#0a66c2] hover:bg-[#0959a8]',
            'href' => 'https://www.linkedin.com/sharing/share-offsite/?url='.$encode($partageUrl),
            'icone' => 'linkedin',
        ],
        [
            'nom' => 'X',
            'couleur' => 'bg-slate-800 hover:bg-slate-700',
            'href' => 'https://twitter.com/intent/tweet?text='.$encode($partageTitre).'&url='.$encode($partageUrl),
            'icone' => 'x',
        ],
    ];
@endphp

<div class="partage flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6"
     data-partage data-titre="{{ $partageTitre }}" data-url="{{ $partageUrl }}">
    <p class="flex shrink-0 items-center gap-2 text-sm font-bold {{ $texteTitre }}">
        <svg class="h-4 w-4 shrink-0 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="18" cy="5" r="3" />
            <circle cx="6" cy="12" r="3" />
            <circle cx="18" cy="19" r="3" />
            <path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4" />
        </svg>
        Partager cette page
    </p>

    <ul class="flex flex-wrap items-center gap-2">
        @foreach ($liens as $lien)
            <li>
                <a href="{{ $lien['href'] }}"
                   target="_blank"
                   rel="noopener"
                   aria-label="Partager sur {{ $lien['nom'] }}"
                   title="Partager sur {{ $lien['nom'] }}"
                   class="group flex items-center gap-2 rounded-full px-3 py-2 text-xs font-semibold transition {{ $pastille }}">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white transition group-hover:scale-110 {{ $lien['couleur'] }}">
                        <x-icon :name="$lien['icone']" class="h-3.5 w-3.5" />
                    </span>
                    {{ $lien['nom'] }}
                </a>
            </li>
        @endforeach

        {{-- Copie du lien : reste disponible meme sans reseau social installe.
             `window.copierTexte` (app.js) retombe sur execCommand hors contexte
             securise ; sans lui, `navigator.clipboard` est undefined en HTTP et
             le bouton ne faisait rien du tout. --}}
        <li>
            <button type="button"
                    x-data="{ fait: false }"
                    x-on:click="
                        window.copierTexte($root.dataset.url).then(function (etat) {
                            fait = etat;
                            setTimeout(function () { fait = false; }, 2000);
                        });
                    "
                    aria-label="Copier le lien de la page"
                    class="group flex items-center gap-2 rounded-full px-3 py-2 text-xs font-semibold transition {{ $pastille }}">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white transition group-hover:scale-110">
                    <x-icon name="link" class="h-3.5 w-3.5" />
                </span>
                <span x-text="fait ? 'Lien copié !' : 'Copier le lien'">Copier le lien</span>
            </button>
        </li>
    </ul>
</div>
