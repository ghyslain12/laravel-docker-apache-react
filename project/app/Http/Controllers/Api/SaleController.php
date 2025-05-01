<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    /**
     * Display a listing of the sales.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $sales = Sale::with('materials')->get();
        return response()->json($sales);
    }

    /**
     * Store a newly created sale in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $materials = [];
        foreach ($request->all() as $key => $value) {
            if (str_contains($key, "material_") && $value) {
                list($type, $id) = explode('_', $key);
                $materials[] = $id;
            }
        }

        $sale = Sale::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'customer_id' => $validated['customer_id']
        ]);

        if ($materials) {
            $sale->materials()->attach($materials);
        }

        return response()->json($sale->load('materials'), 201);
    }

    /**
     * Display the specified sale.
     *
     * @param int $id
     * @return JsonResponse
     * @throws ModelNotFoundException If the sale is not found
     */
    public function show(int $id): JsonResponse
    {
        $sale = Sale::findOrFail($id);
        return response()->json($sale->load('materials', 'customer'));
    }

    /**
     * Update the specified sale from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $sale = Sale::findOrFail($id);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'customer_id' => 'required|exists:customers,id',

        ]);

        $sale->update($validated);

        $materials = [];
        foreach ($request->all() as $key => $value) {
            if (str_contains($key, "material_") && $value) {
                list($type, $id) = explode('_', $key);
                $materials[] = $id;
            }
        }

        $sale->materials()->sync($materials);

        return response()->json($sale->load('materials'));
    }

    /**
     * Remove the specified sale from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws ModelNotFoundException If the sale is not found
     */
    public function destroy(int $id): JsonResponse
    {
        $sale = Sale::findOrFail($id);

        $sale->tickets()->delete();
        $sale->delete();

        return response()->json(null, 204);
    }
}
