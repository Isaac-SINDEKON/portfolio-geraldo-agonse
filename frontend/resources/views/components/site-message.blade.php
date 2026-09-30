{{-- Messages de succes et d'erreur (formulaires + administration) --}}
@if (session('success'))
    <div class="border-b border-green-200 bg-green-50">
        <div class="container-x flex items-start gap-3 py-4 text-sm font-medium text-green-800">
            <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-green-600" />
            <p>{{ session('success') }}</p>
        </div>
    </div>
@endif

@if (session('error') || session('api_error'))
    <div class="border-b border-red-200 bg-red-50">
        <div class="container-x flex items-start gap-3 py-4 text-sm font-medium text-red-800">
            <x-icon name="close" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
            <p>{{ session('error') ?: session('api_error') }}</p>
        </div>
    </div>
@endif
