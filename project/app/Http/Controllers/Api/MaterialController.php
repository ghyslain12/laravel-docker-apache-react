<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    /**
     * Display a listing of the materials.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Material::all());
    }

    /**
     * Store a newly created material in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'designation' => 'required|string|max:255',
        ]);

        $material = Material::create([
            'designation' => $request->designation,
        ]);

        return response()->json($material, 201);
    }

    /**
     * Display the specified material.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the material is not found
     */
    public function show(int $id): JsonResponse
    {
        $material = Material::findOrFail($id);
        return response()->json($material);
    }

    /**
     * Update the specified material from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the material is not found
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $material = Material::findOrFail($id);

        $request->validate([
            'designation' => 'required|string|max:255',
        ]);

        $data = $request->only(['designation']);

        $material->update($data);
        return response()->json($material);
    }

    /**
     * Remove the specified material from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the material is not found
     */
    public function destroy(int $id): JsonResponse
    {
        $material = Material::findOrFail($id);
        $material->delete();
        return response()->json(null, 204);
    }

    /**
     * Test the connection to the api material.
     *
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'pong',
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
