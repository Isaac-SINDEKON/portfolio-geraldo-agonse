<?php

namespace App\Http\Controllers\Admin;

use App\Models\Lead;

class DashboardController extends AdminController
{
    public function index()
    {
        $content = $this->content->all();

        $leads = Lead::orderByDesc('created_at')->get()
            ->map(fn (Lead $lead) => [
                'id' => $lead->id,
                'type' => $lead->type,
                'data' => $lead->data,
                'created_at' => $lead->created_at?->toIso8601String(),
            ])
            ->all();

        $stats = [
            ['label' => 'Formations', 'value' => count($content['formations'] ?? []), 'icon' => 'layers'],
            ['label' => 'Services', 'value' => count($content['services'] ?? []), 'icon' => 'building'],
            ['label' => 'Témoignages', 'value' => count($content['testimonials'] ?? []), 'icon' => 'quote'],
            ['label' => 'Photos en galerie', 'value' => count($content['gallery'] ?? []), 'icon' => 'gallery'],
            ['label' => 'Demandes reçues', 'value' => count($leads), 'icon' => 'inbox'],
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentLeads' => array_slice($leads, 0, 5),
            'adminUser' => session('admin_user') ?? [],
        ]);
    }
}
