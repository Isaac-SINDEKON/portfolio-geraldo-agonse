@props([
    'partageTitre' => null,
    'partageDescription' => null,
])

<footer class="border-t border-slate-800 bg-slate-900 text-slate-300">
    <div class="container-x py-12">
        <div class="grid gap-10 md:grid-cols-4">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3">
                    <x-marque variante="entete" />
                    <span class="leading-tight">
                        <span class="block text-base font-bold text-white">{{ $site['settings']['name'] ?? 'Géraldo Perridys AGONSE' }}</span>
                        <span class="block text-xs text-slate-400">{{ $site['settings']['role'] ?? 'Formateur' }}</span>
                    </span>
                </div>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-400">
                    {{ $site['settings']['tagline'] ?? '' }}
                </p>
            </div>

            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">Navigation</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Accueil</a></li>
                    <li><a href="{{ route('about') }}" class="transition hover:text-white">À propos</a></li>
                    <li><a href="{{ route('formations') }}" class="transition hover:text-white">Formations</a></li>
                    <li><a href="{{ route('services') }}" class="transition hover:text-white">Services entreprises</a></li>
                    <li><a href="{{ route('experience') }}" class="transition hover:text-white">Expérience & expertise</a></li>
                    <li><a href="{{ route('testimonials') }}" class="transition hover:text-white">Témoignages</a></li>
                    <li><a href="{{ route('gallery') }}" class="transition hover:text-white">Galerie</a></li>
                    <li><a href="{{ route('contact') }}" class="transition hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">Contact</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    <li>
                        <a href="{{ $content->whatsappUrl() }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 transition hover:text-white">
                            <x-icon name="whatsapp" class="h-4 w-4" />
                            {{ $site['settings']['whatsapp_display'] ?? $site['settings']['whatsapp'] ?? '' }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ $content->telUrl() }}" class="flex items-center gap-2 transition hover:text-white">
                            <x-icon name="phone" class="h-4 w-4" />
                            {{ $site['settings']['phone_display'] ?? $site['settings']['phone'] ?? '' }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ $content->emailUrl() }}" class="flex items-center gap-2 break-all transition hover:text-white">
                            <x-icon name="mail" class="h-4 w-4" />
                            {{ $site['settings']['email'] ?? '' }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Partage : visible au pied de toutes les pages (CC §30) --}}
        <div class="mt-10 border-t border-slate-800 pt-8">
            <x-partage :titre="$partageTitre ?? null" :description="$partageDescription ?? null" />
        </div>

        <div class="mt-10 flex flex-col items-center gap-3 border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
            <p>© {{ date('Y') }} {{ $site['settings']['name'] ?? 'Géraldo Perridys AGONSE' }}. Tous droits réservés.</p>

            {{-- Acces discret a l'administration du site (proprietaire uniquement) --}}
            <a href="{{ route('admin.login') }}"
               title="Espace administration"
               aria-label="Espace administration"
               class="group flex items-center gap-2 rounded-full border border-slate-700 px-3 py-1.5 text-[11px] text-slate-500 transition hover:border-primary-500 hover:text-primary-300">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-600 transition group-hover:bg-primary-400"></span>
                Administration
            </a>
        </div>
    </div>
</footer>
