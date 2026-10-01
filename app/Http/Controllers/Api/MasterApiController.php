<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class MasterApiController extends Controller
{
    public function roles(): JsonResponse
    {
        $roles = Role::query()
            ->select(['id', 'name', 'key'])
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'key' => $role->key,
            ])
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => $roles,
        ]);
    }

    public function positions(): JsonResponse
    {
        $positions = Position::query()
            ->select(['id', 'nama', 'key'])
            ->orderBy('nama')
            ->get()
            ->map(fn (Position $position) => [
                'id' => $position->id,
                'nama' => $position->nama,
                'key' => $position->key,
            ])
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => $positions,
        ]);
    }
}
