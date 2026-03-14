<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransactionPurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_purchase_returns_success()
    {
        Http::fake([
            config('services.gateways.gateway1.url') . '/login' => Http::response(['token' => 'test-token'], 200),
            config('services.gateways.gateway1.url') . '/transactions' => Http::response(['id' => 'gw1-id'], 201),
        ]);

        Client::create(['name' => 'Cliente Teste', 'email' => 'cliente@local.test']);
        Product::create(['name' => 'Produto A', 'quantity' => 10, 'amount' => 99.90]);
        Gateway::create(['name' => 'Gateway 1', 'is_active' => true, 'priority' => 1]);

        $response = $this->postJson('/api/transactions', [
            'client_id' => 1,
            'products' => [['id' => 1, 'quantity' => 1]],
            'card_number' => '5569000000006063',
            'cvv' => '010',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => 'Compra efetuada com sucesso']);
    }
}
