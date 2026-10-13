{{-- Messages de succes et d'erreur (formulaires + administration) --}}
@if (session('success'))
    <div class="border-b border-ok-line bg-ok-bg">
        <div class="container-x flex items-start gap-3 py-4 text-sm font-medium text-ok-ink">
            <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-green-600" />
            <p>{{ session('success') }}</p>
        </div>
    </div>
@endif

@if (session('error') || session('api_error'))
    <div class="border-b border-alert-line bg-alert-bg">
        <div class="container-x flex items-start gap-3 py-4 text-sm font-medium text-alert-ink">
            <x-icon name="close" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
            <p>{{ session('error') ?: session('api_error') }}</p>
        </div>
    </div>
@endif
