<?php

namespace App\Http\Controllers;

use App\Services\PlaceSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceSearchController extends Controller
{
    public function search(Request $request, PlaceSearchService $places): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        return response()->json([
            'status' => true,
            'data' => $places->search($query),
        ]);
    }

    public function details(Request $request, PlaceSearchService $places): JsonResponse
    {
        $placeId = trim((string) $request->query('place_id', ''));
        $place = $places->details($placeId);

        if ($place === null) {
            return response()->json([
                'status' => false,
                'message' => 'Place not found.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $place,
        ]);
    }
}
