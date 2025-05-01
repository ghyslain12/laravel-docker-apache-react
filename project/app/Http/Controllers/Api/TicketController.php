<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $tickets = Ticket::with('sales')->get();
        return response()->json($tickets);
    }

    /**
     * Store a newly created ticket in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the sale is not found
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required',
            'sale_id' => 'required|exists:sales,id', // Ajout de la validation pour sale_id
        ]);

        $ticket = Ticket::create([
            'titre' => $request->titre,
            'description' => $request->description,
        ]);

        $sale = Sale::findOrFail($request->sale_id);
        $sale->tickets()->attach($ticket);

        //$ticket->sales()->attach($request->validated('sale_id')); // Associe le Sale

        return response()->json($ticket, 201);
    }

    /**
     * Display the specified ticket.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the ticket is not found
     */
    public function show(int $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);
        return response()->json($ticket->load('sales'));
    }

    /**
     * Update the specified ticket from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the ticket is not found
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required',
            'sale_id' => 'required|exists:sales,id',
        ]);

        $data = $request->only(['titre', 'description']);

        $ticket->update($data);
        
        $ticket->sales()->sync([$request->sale_id]);
        $ticket->load('sales');

        return response()->json($ticket);
    }

    /**
     * Remove the specified ticket from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the ticket is not found
     */
    public function destroy(int $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();
        return response()->json(null, 204);
    }
}
