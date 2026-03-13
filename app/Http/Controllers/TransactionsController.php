<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\PaymentService;
use App\TransactionStatus;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Transaction::class);

        $transactions = Transaction::query()->with(['client', 'gateway'])->get();

        return response()->json([
            'message' => 'Transações encontradas',
            'data' => $transactions,
        ]);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

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

            $paymentService = app(PaymentService::class);
            $paymentResult = $paymentService->processPayment(
                $data['client_id'],
                $data['gateway_id'] ?? null,
                $data['products'],
                $data['card_number'],
                $data['cvv']
            );

            if ($paymentResult['status'] !== TransactionStatus::Paid) {
                return [
                    'error' => $paymentResult['error'] ?? 'Payment failed',
                ];
            }

            $firstProduct = $data['products'][0];

            $transaction = Transaction::create([
                'client_id' => $data['client_id'],
                'gateway_id' => $paymentResult['gateway']->id,
                'external_id' => $paymentResult['external_id'],
                'status' => $paymentResult['status'],
                'amount' => $amount,
                'card_last_numbers' => substr($data['card_number'], -4),
                'product_id' => $firstProduct['id'],
                'quantity' => $firstProduct['quantity'],
            ]);

            $pivotData = [];
            foreach ($data['products'] as $item) {
                $pivotData[$item['id']] = ['quantity' => $item['quantity']];
            }

            $transaction->products()->sync($pivotData);

            return $transaction->load(['client', 'gateway', 'products']);
        });

        if (is_array($transaction) && isset($transaction['error'])) {
            return response()->json([
                'message' => $transaction['error'],
            ], 502);
        }

        return response()->json([
            'message' => 'Compra efetuada com sucesso',
            'data' => $transaction,
        ], 201);
    }

    public function refund(Transaction $transaction)
    {
        $this->authorize('refund', $transaction);

        $paymentService = app(PaymentService::class);
        $result = $paymentService->refund($transaction);

        if ($result['status'] !== TransactionStatus::Refunded) {
            return response()->json([
                'message' => $result['error'] ?? 'Refund failed',
            ], 502);
        }

        $transaction->update(['status' => TransactionStatus::Refunded]);

        return response()->json([
            'message' => 'Reembolso efetuado com sucesso',
            'data' => $transaction,
        ]);
    }
}
