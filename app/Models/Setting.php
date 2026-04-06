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
        'public_publish_min_gallery_images',
        'home_featured_product_ids',
        'merchant_usd_rate',
        'merchant_charge_discount_percent',
        'point_price_sar',
        'point_price_usd',
        'account_name_blur_enabled',
        'account_name_blur_x_offset_from_right',
        'account_name_blur_y',
        'account_name_blur_width',
        'account_name_blur_height',
        'account_name_blur_strength',
        'account_name_blur_mode',
        'account_name_blur_x_offset_from_right_ratio',
        'account_name_blur_y_ratio',
        'account_name_blur_width_ratio',
        'account_name_blur_height_ratio',
        'account_top_area_mode',
        'account_top_area_size_px',
        'account_top_area_width_px',
        'account_top_area_width_ratio',
        'account_top_area_x_from_right_px',
        'account_top_area_blur_strength',
        'account_center_blur_enabled',
        'account_center_blur_x',
        'account_center_blur_y',
        'account_center_blur_x_from_right',
        'account_center_blur_width',
        'account_center_blur_height',
        'account_center_blur_strength',
        'watermark_enabled',
        'watermark_x_offset',
        'watermark_y_offset',
        'watermark_scale_percent',
        'watermark_second_enabled',
        'watermark_second_x_offset',
        'watermark_second_y_offset',
    ];

    protected $casts = [
        'home_featured_product_ids' => 'array',
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}