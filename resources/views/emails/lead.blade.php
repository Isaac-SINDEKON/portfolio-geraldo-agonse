<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $type === 'devis' ? 'Demande de devis' : 'Demande de formation' }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; padding: 24px; color: #1f2937; }
        .card { background: #ffffff; border-radius: 12px; padding: 24px; max-width: 640px; margin: 0 auto; }
        h1 { color: #4f46e5; font-size: 22px; margin-top: 0; }
        .item { border-bottom: 1px solid #e5e7eb; padding: 8px 0; }
        .label { font-weight: bold; color: #374151; }
        .value { color: #6b7280; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $type === 'devis' ? 'Nouvelle demande de devis' : 'Nouvelle demande de formation' }}</h1>
        @foreach ($data as $key => $value)
            @if (is_string($value) || is_numeric($value))
                <div class="item">
                    <span class="label">{{ ucfirst(str_replace('_', ' ', $key)) }} :</span>
                    <span class="value">{{ $value }}</span>
                </div>
            @endif
        @endforeach
    </div>
</body>
</html>