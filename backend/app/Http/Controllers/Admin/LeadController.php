<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Lead::orderByDesc('created_at')
                ->when($request->type, fn ($q) => $q->where('type', $request->type))
                ->get()
        );
    }

    public function destroy(int $id): JsonResponse
    {
        Lead::findOrFail($id)->delete();

        return response()->json(['message' => 'Demande supprimée.']);
    }
}