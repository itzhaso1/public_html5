<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\{UploadMedia2, UploadVideoTrait};
use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Support\Facades\Schema;
class Product extends Model implements TranslatableContract {
    use HasFactory, UploadMedia2, UploadVideoTrait, Translatable;
    protected $table = 'products';
    protected $fillable = [
        'slug',
        'type_id',
        'category_id',
        'brand_id',
        'price',
        'price_before_discount',
        'deal_ends_at',
        'stock',
        'sku',
        'featured',
        'status',
        'published_at',
        'client_number',
        'client_email',
        'publish_source',
        'review_note',
        'review_reject_reasons',
        'reviewed_by',
        'reviewed_at',
        'rejected_at',
        'service_type',
        'points_price',
        // Shop2TopUp offer id (column name in DB is itemID)
        'itemID',

        'erp_id'
        
        
    ];
    protected $with = ['translations'];
    protected $casts = [
        'featured' => 'bool',
        'published_at' => 'datetime',
        'deal_ends_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'review_reject_reasons' => 'array',
        'points_price' => 'int',
    ];

    public $translatedAttributes = [
        'name',
        'description',
        'short_description',
        'meta_title',
        'meta_description'
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleriable');
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'product_section');
    }

    /*public function categories() {
        return $this->belongsToMany(Category::class, 'category_product');
    }*/

    public function videos()
    {
        return $this->hasMany(ProductVideo::class);
    }

    public function diamondCodes()
    {
        return $this->hasMany(DiamondCode::class, 'product_id');
    }

    /**
     * Thumbnail image for "codes" products taken from an available code image.
     * This lets admins upload "code images" from dashboard and have them show on listings/home.
     */
    public function codeThumbnail()
    {
        return $this->hasOne(DiamondCode::class, 'product_id')
            ->where('status', 'available')
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->latest('id');
    }

    public function manualPaymentRequests()
    {
        return $this->hasMany(ManualPaymentRequest::class, 'product_id');
    }

    /**
     * Hide unapproved public submissions from website lists.
     * Admin-created products keep existing behavior.
     */
    public function scopeWebsiteVisible($query)
    {
        static $hasColumn = null;
        if ($hasColumn === null) {
            try {
                $hasColumn = Schema::hasColumn('products', 'publish_source');
            } catch (\Throwable $e) {
                $hasColumn = false;
            }
        }
        if (! $hasColumn) {
            return $query;
        }

        return $query->where(function ($q) {
            $q->whereNull('publish_source')
                ->orWhere('publish_source', '!=', 'public')
                ->orWhere(function ($q2) {
                    $q2->where('publish_source', 'public')->where('status', 'published');
                });
        });
    }
    
    public function getImageUrl(): string
    {
        try {
            $url = $this->getMediaUrl('product', $this, null, 'media', 'product');
            if (! empty($url)) {
                return (string) $url;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return asset('img/قريبا.jpg');
    }

}
