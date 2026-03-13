<?php

namespace App\Models;

use App\TransactionStatus;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'gateway_id',
        'external_id',
        'status',
        'amount',
        'card_last_numbers',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'transaction_products')->withPivot('quantity');
    }
}
