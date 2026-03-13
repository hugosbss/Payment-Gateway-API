<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'amount', 'quantity'];

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'transaction_products')->withPivot('quantity');
    }
}
