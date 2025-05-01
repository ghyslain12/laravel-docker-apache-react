<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $clients = Customer::with('user')->get();
        return response()->json($clients);
    }

    /**
     * Store a newly created client in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'surnom' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $client = Customer::create([
            'surnom' => $request->surnom,
            'user_id' => $request->user_id,
        ]);

        return response()->json($client, 201);
    }

    /**
     * Display the specified client.
     *
     * @param int $id
     * @throws ModelNotFoundException If the customer is not found
     */
    public function show(int $id): JsonResponse
    {
        $client = Customer::findOrFail($id);
        return response()->json($client->load('user'));
    }

    /**
     * Update the specified client from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $client = Customer::findOrFail($id);

        $request->validate([
            'surnom' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $data = $request->only(['surnom', 'user_id']);

        $client->update($data);

        return response()->json($client);
    }

    /**
     * Remove the specified client from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws ModelNotFoundException If the customer is not found
     */
    public function destroy(int $id): JsonResponse
    {
        $client = Customer::findOrFail($id);

        $client->sale()->delete();
        $client->delete();

        return response()->json(null, 204);
    }
}
