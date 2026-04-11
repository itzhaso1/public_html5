<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'manual_payment_request_id',
        'user_id',
        'code',
        'image_path',
        'luck_weight',
        'luck_label',
        'status',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'luck_weight' => 'int',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function manualPaymentRequest()
    {
        return $this->belongsTo(ManualPaymentRequest::class, 'manual_payment_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

