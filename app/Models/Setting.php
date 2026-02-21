<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UploadMedia2;

class Setting extends Model
{
    use HasFactory, UploadMedia2;
    protected $table = 'settings';
    protected $fillable = [
        'name',
        'email',
        'description',
        'phone',
        'address',
        'status',
        'currency',
        'loyalty_points',
        'delivery_fees',
        'version',
        'home_quick_charge_title',
        'home_quick_codes_title',
        'home_quick_cash_exchange_title',
        'home_quick_money_exchange_title',
        'cash_exchange_enabled',
        'money_exchange_enabled',
        'charge_enabled',
        'codes_enabled',
        'merchant_usd_rate',
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}