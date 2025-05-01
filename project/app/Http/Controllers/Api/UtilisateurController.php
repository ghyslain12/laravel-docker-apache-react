<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class UtilisateurController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(User::all());
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4',
        ]);

        $utilisateur = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($utilisateur, 201);
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found
     */
    public function show(int $id): JsonResponse
    {
        $utilisateur = User::findOrFail($id);
        return response()->json($utilisateur);
    }

    /**
     * Update the specified user from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $utilisateur = User::findOrFail($id);

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $utilisateur->id,
        ]);

        $data = $request->only(['name', 'email']);
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $utilisateur->update($data);
        return response()->json($utilisateur);
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found
     */
    public function destroy(int $id): JsonResponse
    {
        $utilisateur = User::findOrFail($id);

        $utilisateur->customer()->delete();
        $utilisateur->delete();

        return response()->json(null, 204);
    }

    /**
     * Test the connection to the api user.
     *
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'ping',
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
