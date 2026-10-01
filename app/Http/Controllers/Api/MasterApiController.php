<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
