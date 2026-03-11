<?php 

namespace App\Services;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function processPayment($clientId, $gatewayId, $products)
    {
        
    }
}
