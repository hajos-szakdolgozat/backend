<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::with('reservation')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount' => 'required|integer',
            'status' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        return Transaction::create($validated);
    }

    public function show(Transaction $transaction)
    {
        return $transaction->load('reservation');
    }
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'amount' => 'integer',
            'status' => 'string',
            'payment_method' => 'string',
        ]);

        $transaction->update($validated);

        return $transaction;
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
