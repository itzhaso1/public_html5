<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'key',
        'title',
        'enabled',
        'allowed_for_charge',
        'sort_order',
        'details',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'allowed_for_charge' => 'bool',
        'sort_order' => 'int',
        'details' => 'array',
    ];
}

