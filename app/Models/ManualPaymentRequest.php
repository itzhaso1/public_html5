<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualPaymentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'product_id',
        'reserved_diamond_code_id',
        'user_id',
        'player_id',
        'contact_phone',
        'contact_email',
        'amount',
        'currency',
        'payment_method',
        'points_spent',
        'points_refunded_at',
        'receipt_path',
        'status',
        'approved_at',
        'admin_note',
        'shop2topup_trx_id',
        'shop2topup_status',
        'shop2topup_order_id',
        'shop2topup_secure_id',
        'shop2topup_delivery_at',
        'shop2topup_response',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'shop2topup_delivery_at' => 'datetime',
        'shop2topup_response' => 'array',
        'points_refunded_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function diamondCode()
    {
        return $this->hasOne(DiamondCode::class, 'manual_payment_request_id');
    }

    public function reservedDiamondCode()
    {
        return $this->belongsTo(DiamondCode::class, 'reserved_diamond_code_id');
    }
}

