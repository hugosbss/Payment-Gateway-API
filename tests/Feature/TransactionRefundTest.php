<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\TransactionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionRefundTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_refund_returns_success(): void
    {
        Http::fake([
            config('services.gateways.gateway1.url') . '/login' => Http::response(['token' => 'test-token'], 200),
            config('services.gateways.gateway1.url') . '/transactions/*/charge_back' => Http::response([], 201),
        ]);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@local.test',
            'role' => 'admin',
            'password' => 'password',
        ]);
        Sanctum::actingAs($user);

        $client = Client::create(['name' => 'Cliente Teste', 'email' => 'cliente@local.test']);
        $product = Product::create(['name' => 'Produto A', 'quantity' => 10, 'amount' => 99.90]);
        $gateway = Gateway::create(['name' => 'Gateway 1', 'is_active' => true, 'priority' => 1]);

        $transaction = Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'gw1-id',
            'status' => TransactionStatus::Paid,
            'amount' => 99.90,
            'card_last_numbers' => 6063,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/transactions/' . $transaction->id . '/refund');

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Reembolso efetuado com sucesso']);
    }
}
