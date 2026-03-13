<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'email'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
