<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\TransactionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionsController extends Controller
{
    public function index()
    {
        $transactions = Transaction::query()->with(['client', 'gateway'])->get();

        return response()->json([
            'message' => 'Transações encontradas',
            'data' => $transactions,
        ]);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['client', 'gateway', 'products']);

        return response()->json([
            'message' => 'Transação encontrada',
            'data' => $transaction,
        ]);
    }

    public function buy(StoreTransactionRequest $request)
    {
        $data = $request->validated();

        $transaction = DB::transaction(function () use ($data) {
            $products = Product::query()
                ->whereIn('id', collect($data['products'])->pluck('id'))
                ->get()
                ->keyBy('id');

            $amount = 0;
            foreach ($data['products'] as $item) {
                $product = $products->get($item['id']);
                $amount += $product->amount * $item['quantity'];
            }

            $transaction = Transaction::create([
                'client_id' => $data['client_id'],
                'gateway_id' => $data['gateway_id'],
                'external_id' => (string) Str::uuid(),
                'status' => TransactionStatus::Paid,
                'amount' => $amount,
                'card_last_numbers' => 0,
            ]);

            $pivotData = [];
            foreach ($data['products'] as $item) {
                $pivotData[$item['id']] = ['quantity' => $item['quantity']];
            }

            $transaction->products()->sync($pivotData);

            return $transaction->load(['client', 'gateway', 'products']);
        });

        return response()->json([
            'message' => 'Compra efetuada com sucesso',
            'data' => $transaction,
        ], 201);
    }

    public function refund(Transaction $transaction)
    {
        $transaction->update(['status' => TransactionStatus::Failed]);

        return response()->json([
            'message' => 'Reembolso efetuado com sucesso',
            'data' => $transaction,
        ]);
    }
}
