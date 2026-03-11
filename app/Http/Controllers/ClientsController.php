<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Transaction;
use App\TransactionStatus;

class ClientsController extends Controller
{
    public function index()
    {
        $clients = Client::query()->get();

        return response()->json([
            'message' => 'Clientes encontrados',
            'data' => $clients,
        ]);
    }

    public function show(Client $client)
    {
        return response()->json([
            'message' => 'Cliente encontrado',
            'data' => $client,
        ]);
    }

    public function details(Client $client)
    {
        $client->load(['transactions.products']);

        return response()->json([
            'message' => 'Detalhes do cliente e compras encontrados',
            'data' => $client,
        ]);
    }

    public function purchases(Client $client)
    {
        $transactions = $client->transactions()->with('products')->get();

        return response()->json([
            'message' => 'Compras do cliente encontradas',
            'data' => $transactions,
        ]);
    }

    public function detailPurchased(Transaction $transaction)
    {
        $transaction->load(['client', 'products', 'gateway']);

        return response()->json([
            'message' => 'Detalhes da compra encontrados',
            'data' => $transaction,
        ]);
    }

    // Realizar reembolso de uma compra junto ao gateway com validação por roles
    public function refundPuchasedGateway(Transaction $transaction)
    {
        $transaction->update(['status' => TransactionStatus::Failed]);

        return response()->json([
            'message' => 'Reembolso da compra do cliente realizado com sucesso',
            'data' => $transaction,
        ]);
    }
}
