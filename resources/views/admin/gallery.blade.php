@extends('layouts.admin')

@section('title', 'Galerie photos')

@section('content')

    <p class="text-sm text-muted">
        Ajoutez et supprimez des photos : elles apparaissent dans la galerie publique avec visionneuse en grand format (CC §13).
    </p>

    <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="card mt-6">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="image" class="field-label">Image <span class="text-red-500">*</span></label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                       @class(['field', 'field-error' => $errors->has('image')]) required>
                <p class="mt-1 text-xs text-muted">JPEG, PNG ou WebP, 5 Mo maximum.</p>
                @error('image') <p class="field-message">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="caption" class="field-label">Légende</label>
                <input type="text" id="caption" name="caption" value="{{ old('caption') }}" class="field">
            </div>
        </div>

        <div class="mt-4 sm:max-w-xs">
            <label for="sort_order" class="field-label">Ordre d'affichage</label>
            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order') }}" class="field">
        </div>

        <button type="submit" class="btn-primary mt-4">
            <x-icon name="upload" class="h-4 w-4" />
            Ajouter à la galerie
        </button>
    </form>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($images as $image)
            @php $url = $content->imageUrl($image['image_path'] ?? null); @endphp
            <article class="card">
                @if ($url)
                    <img src="{{ $url }}" alt="{{ $image['caption'] ?? 'Photo' }}"
                         class="aspect-4/3 w-full rounded-xl object-cover">
                @else
                    <div class="flex aspect-4/3 w-full items-center justify-center rounded-xl bg-canvas text-xs text-muted">
                        Image indisponible
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.gallery.update', $image['id']) }}" class="mt-4 space-y-3"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <p class="text-xs font-medium text-muted">Remplacer la photo <span class="text-muted">(facultatif)</span></p>

                    <input type="file" id="image-{{ $image['id'] }}" name="image"
                           accept="image/jpeg,image/png,image/webp"
                           class="field py-1.5 text-xs">
                    <p class="text-xs text-muted">Laissez vide pour conserver la photo actuelle.</p>

                    <div>
                        <label for="caption-{{ $image['id'] }}" class="field-label">Légende</label>
                        <input type="text" id="caption-{{ $image['id'] }}" name="caption[{{ $image['id'] }}]"
                               value="{{ old('caption.'.$image['id'], $image['caption'] ?? '') }}" class="field">
                        @error('caption.'.$image['id']) <p class="field-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="order-{{ $image['id'] }}" class="field-label">Ordre</label>
                        <input type="number" id="order-{{ $image['id'] }}" name="sort_order[{{ $image['id'] }}]"
                               value="{{ old('sort_order.'.$image['id'], $image['sort_order'] ?? '') }}" class="field">
                    </div>

                    <button type="submit" class="btn-outline px-3 py-2 text-xs">Enregistrer</button>
                </form>

                <form method="POST" action="{{ route('admin.gallery.destroy', $image['id']) }}" class="mt-3"
                      onsubmit="return confirm('Supprimer définitivement cette image ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger px-3 py-2 text-xs">
                        <x-icon name="trash" class="h-4 w-4" />
                        Supprimer
                    </button>
                </form>
            </article>
        @empty
            <p class="card text-center text-sm text-muted sm:col-span-2 lg:col-span-3">Aucune image dans la galerie.</p>
        @endforelse
    </div>

@endsection
