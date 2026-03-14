<?php 

namespace App\Services;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\TransactionStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentService
{
    public function processPayment(int $clientId, ?int $gatewayId, array $products, string $cardNumber, string $cvv): array
    {
        $client = Client::query()->findOrFail($clientId);

        $gateways = Gateway::query()
            ->when($gatewayId, fn ($q) => $q->where('id', $gatewayId))
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        if ($gateways->isEmpty()) {
            return [
                'status' => TransactionStatus::Failed,
                'error' => 'No active gateway available',
            ];
        }

        $productModels = Product::query()
            ->whereIn('id', collect($products)->pluck('id'))
            ->get()
            ->keyBy('id');

        $amount = 0;
        foreach ($products as $item) {
            $product = $productModels->get($item['id']);
            $amount += $product->amount * $item['quantity'];
        }

        $amountInCents = (int) round($amount * 100);

        foreach ($gateways as $gateway) {
            $result = $this->chargeGateway($gateway, $client, $amountInCents, $cardNumber, $cvv);
            if ($result['ok']) {
                return [
                    'status' => TransactionStatus::Paid,
                    'gateway' => $gateway,
                    'external_id' => $result['external_id'],
                    'amount' => $amount,
                ];
            }
        }

        return [
            'status' => TransactionStatus::Failed,
            'error' => 'Erro ao processar pagamento com todos os gateways disponíveis',
        ];
    }

    public function refund(Transaction $transaction): array
    {
        $gateway = $transaction->gateway;

        $result = $this->refundGateway($gateway, $transaction->external_id);

        if ($result['ok']) {
            return [
                'status' => TransactionStatus::Refunded,
            ];
        }

        return [
            'status' => TransactionStatus::Failed,
            'error' => 'Desculpe, ocorreu um erro ao processar o reembolso. Por favor, tente novamente mais tarde.',
        ];
    }

    private function chargeGateway(Gateway $gateway, Client $client, int $amountInCents, string $cardNumber, string $cvv): array
    {
        if ($gateway->name === 'Gateway 1') {
            return $this->chargeGateway1($client, $amountInCents, $cardNumber, $cvv);
        }

        if ($gateway->name === 'Gateway 2') {
            return $this->chargeGateway2($client, $amountInCents, $cardNumber, $cvv);
        }

        return ['ok' => false];
    }

    private function refundGateway(Gateway $gateway, string $externalId): array
    {
        if ($gateway->name === 'Gateway 1') {
            return $this->refundGateway1($externalId);
        }

        if ($gateway->name === 'Gateway 2') {
            return $this->refundGateway2($externalId);
        }

        return ['ok' => false];
    }

    private function chargeGateway1(Client $client, int $amountInCents, string $cardNumber, string $cvv): array
    {
        $baseUrl = config('services.gateways.gateway1.url');
        
        $login = Http::asJson()->post($baseUrl . '/login', [
            'email' => config('services.gateways.gateway1.email'),
            'token' => config('services.gateways.gateway1.token'),
        ]);

        if (!$login->ok() || !($token = $login->json('token'))) {
            return ['ok' => false];
        }

        $response = Http::asJson()
            ->withToken($token)
            ->post($baseUrl . '/transactions', [
            'amount' => $amountInCents,
            'name' => $client->name,
            'email' => $client->email,
            'cardNumber' => $cardNumber,
                'cvv' => $cvv,
        ]);

        if (!$response->successful()) {
            return ['ok' => false];
        }
        
        return [
            'ok' => true,
            'external_id' => $response->json('id') ?? Str::uuid(),
        ];
    }

    private function chargeGateway2(Client $client, int $amountInCents, string $cardNumber, string $cvv): array
    {
        $baseUrl = config('services.gateways.gateway2.url');

        $response = Http::asJson()->withHeaders([
            'Gateway-Auth-Token' => config('services.gateways.gateway2.token'),
            'Gateway-Auth-Secret' => config('services.gateways.gateway2.secret'),
        ])->post($baseUrl . '/transacoes', [
            'valor' => $amountInCents,
            'nome' => $client->name,
            'email' => $client->email,
            'numeroCartao' => $cardNumber,
            'cvv' => $cvv,
        ]);

        if (!$response->successful()) {
            return ['ok' => false];
        }

        return [
            'ok' => true,
            'external_id' => (string) ($response->json('id') ?? Str::uuid()),
        ];
    }

    private function refundGateway1(string $externalId): array
    {
        $baseUrl = config('services.gateways.gateway1.url');
        $login = Http::post($baseUrl . '/login', [
            'email' => config('services.gateways.gateway1.email'),
            'token' => config('services.gateways.gateway1.token'),
        ]);

        if (!$login->ok()) {
            return ['ok' => false];
        }

        $token = $login->json('token');
        if (!$token) {
            return ['ok' => false];
        }

        $response = Http::withToken($token)->post($baseUrl . '/transactions/' . $externalId . '/charge_back');

        return ['ok' => $response->successful()];
    }

    private function refundGateway2(string $externalId): array
    {
        $baseUrl = config('services.gateways.gateway2.url');

        $response = Http::withHeaders([
            'Gateway-Auth-Token' => config('services.gateways.gateway2.token'),
            'Gateway-Auth-Secret' => config('services.gateways.gateway2.secret'),
        ])->post($baseUrl . '/transacoes/reembolso', [
            'id' => $externalId,
        ]);

        return ['ok' => $response->successful()];
    }
}
