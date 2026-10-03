{{-- Bandeau de preuve institutionnelle.

     Les logos défilent en boucle infinie et en niveaux de gris : une couleur
     concurrente de l'accent s'y glisserait et le bandeau deviendrait le point
     le plus saturé de la page. `mask-image` efface les deux extrémités, donc
     aucune bordure ni aucun dégradé rapporté n'est nécessaire.

     La liste est dupliquée deux fois et l'animation.translate de -50 % : le
     raccord est invisible, la boucle ne présente aucun saut. --}}
@props([
    'items' => [],
    'titre' => null,
])

@php
    // Deux moitiés identiques : la seconde est aria-hidden pour ne pas être
    // lue deux fois par un lecteur d'écran.
    $pistes = array_slice($items, 0, 12);
@endphp

@if (count($pistes))
    <section @class([
        'py-14',
        'bg-nuit' => ($variante ?? '') !== 'clair',
    ])>
        <div class="container-x">
            @if ($titre)
                <p class="mb-9 text-center text-xs font-bold tracking-[0.22em] text-muted uppercase">
                    {{ $titre }}
                </p>
            @endif

            <div class="defilement-gris relative overflow-hidden">
                <div class="flex w-max animate-defilement motion-reduce:animate-none">
                    @foreach (range(1, 2) as $copie)
                        <ul class="flex shrink-0 items-center gap-14 pr-14 sm:gap-20 sm:pr-20"
                            @if ($copie === 2) aria-hidden="true" @endif>
                            @foreach ($pistes as $item)
                                <li class="shrink-0">
                                    @if (! empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] ?? '' }}"
                                             loading="lazy" decoding="async"
                                             class="h-9 w-auto max-w-40 object-contain opacity-55 transition-opacity duration-300 hover:opacity-90" />
                                    @else
                                        <span class="whitespace-nowrap font-serif text-lg font-semibold tracking-wide text-ink opacity-55">
                                            {{ $item['name'] ?? '' }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif