<?php

namespace Database\Seeders;

use App\TransactionStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Seed core data for local testing.
     */
    public function run(): void
    {
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Teste',
            'email' => 'admin@local.test',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gatewayId1 = DB::table('gateways')->insertGetId([
            'name' => 'Gateway 1',
            'is_active' => true,
            'priority' => 1,
        ]);

        $gatewayId2 = DB::table('gateways')->insertGetId([
            'name' => 'Gateway 2',
            'is_active' => true,
            'priority' => 2,
        ]);

        $clientId1 = DB::table('clients')->insertGetId([
            'name' => 'Cliente Teste',
            'email' => 'cliente@local.test',
        ]);

        $productId1 = DB::table('products')->insertGetId([
            'name' => 'Produto A',
            'quantity' => 10,
            'amount' => 99.90,
        ]);

        $productId2 = DB::table('products')->insertGetId([
            'name' => 'Produto B',
            'quantity' => 20,
            'amount' => 49.90,
        ]);

        $transactionId1 = DB::table('transactions')->insertGetId([
            'client_id' => $clientId1,
            'gateway_id' => $gatewayId1,
            'external_id' => (string) Str::uuid(),
            'status' => TransactionStatus::Paid->name,
            'amount' => 149.80,
            'card_last_numbers' => 6063,
            'product_id' => $productId1,
            'quantity' => 1,
        ]);

        DB::table('transaction_products')->insert([
            [
                'transaction_id' => $transactionId1,
                'product_id' => $productId1,
                'quantity' => 1,
            ],
            [
                'transaction_id' => $transactionId1,
                'product_id' => $productId2,
                'quantity' => 1,
            ],
        ]);
    }
}
