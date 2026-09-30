<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Experience;
use App\Models\Formation;
use App\Models\GalleryImage;
use App\Models\Reason;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function site(): JsonResponse
    {
        return response()->json([
            'settings' => Setting::allDecoded(),
            'domains' => Domain::orderBy('sort_order')->get(),
            'reasons' => Reason::orderBy('sort_order')->get(),
            'formations' => Formation::where('active', true)->orderBy('sort_order')->get(),
            'services' => Service::orderBy('sort_order')->get(),
            'experiences' => Experience::orderBy('sort_order')->get(),
            'testimonials' => Testimonial::where('active', true)->orderBy('sort_order')->get(),
            'gallery' => GalleryImage::orderBy('sort_order')->get(),
        ]);
    }

    public function formations(): JsonResponse
    {
        return response()->json(Formation::where('active', true)->orderBy('sort_order')->get());
    }

    public function formation(string $slug): JsonResponse
    {
        $formation = Formation::where('slug', $slug)->where('active', true)->first();

        if (! $formation) {
            return response()->json(['message' => 'Formation introuvable'], 404);
        }

        return response()->json($formation);
    }
}