<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTopupRequest extends Model
{
    use HasFactory;

    protected $table = 'wallet_topup_requests';

    protected $fillable = [
        'user_id',
        'points',
        'point_price_sar',
        'point_price_usd',
        'amount_sar',
        'amount_usd',
        'receipt_path',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

