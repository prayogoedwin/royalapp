<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LokasiTerkini;
use App\Support\GoogleMapsLink;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LokasiTerkiniController extends Controller
{
    private function okResponse(mixed $data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => [],
        ], $status);
    }

    public function store(Request $request): JsonResponse
    {
        $employee = $request->user()?->employee;
        if (! $employee) {
            return $this->errorResponse('Employee not found for this user.', 404);
        }

        $request->merge([
            'latitude' => $request->input('latitude', $request->input('lat')),
            'longitude' => $request->input('longitude', $request->input('lng')),
            'waktu_hp' => $request->filled('waktu_hp')
                ? $request->input('waktu_hp')
                : now()->toDateTimeString(),
        ]);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'waktu_hp' => ['required', 'date'],
        ]);

        $lokasi = LokasiTerkini::create([
            'user_id' => $request->user()->id,
            'employee_id' => $employee->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'waktu_hp' => Carbon::parse($validated['waktu_hp']),
        ]);

        $latitude = (float) $lokasi->latitude;
        $longitude = (float) $lokasi->longitude;

        return $this->okResponse([
            'id' => $lokasi->id,
            'user_id' => $lokasi->user_id,
            'employee_id' => $lokasi->employee_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'waktu_hp' => $lokasi->waktu_hp?->toDateTimeString(),
            'created_at' => $lokasi->created_at?->toDateTimeString(),
            'interval_menit' => (int) config('lokasi.interval_menit', 10),
            'maps_url' => GoogleMapsLink::url($latitude, $longitude),
        ], 'Lokasi terkini disimpan.');
    }
}
