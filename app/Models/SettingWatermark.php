<?php

namespace App\Models;

use App\Models\Concerns\UploadMedia2;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingWatermark extends Model
{
    use HasFactory, UploadMedia2;

    protected $table = 'setting_watermarks';

    protected $fillable = [
        'setting_id',
        'title',
        'enabled',
        'x_offset',
        'y_offset',
        'scale_percent',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'x_offset' => 'integer',
        'y_offset' => 'integer',
        'scale_percent' => 'integer',
        'sort_order' => 'integer',
    ];

    public function setting()
    {
        return $this->belongsTo(Setting::class, 'setting_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
