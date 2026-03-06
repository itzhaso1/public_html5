<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'winner_user_id',
        'title',
        'slug',
        'game_name',
        'description',
        'starting_price',
        'current_price',
        'final_price',
        'subscription_fee',
        'bid_increment',
        'min_participants',
        'starts_at',
        'ends_at',
        'started_at',
        'ended_at',
        'status',
        'is_visible',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'subscription_fee' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_visible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Auction $auction): void {
            if (empty($auction->slug)) {
                $auction->slug = static::buildUniqueSlug($auction->title);
            }
            if (is_null($auction->current_price)) {
                $auction->current_price = $auction->starting_price;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function buildUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'auction';

        $slug = $base;
        $index = 1;
        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$index;
            $index++;
        }

        return $slug;
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AuctionImage::class)->orderBy('sort_order');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(AuctionSubscription::class);
    }

    public function approvedSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', 'approved');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class)->latest();
    }

    public function getParticipantsCountAttribute(): int
    {
        return (int) ($this->approved_subscriptions_count ?? $this->approvedSubscriptions()->count());
    }

    public function hasStarted(): bool
    {
        return $this->status === 'active';
    }

    public function hasEnded(): bool
    {
        return in_array($this->status, ['ended', 'cancelled'], true)
            || ($this->ends_at instanceof Carbon && $this->ends_at->isPast());
    }

    public function canReceiveBids(): bool
    {
        $now = now();
        return $this->status === 'active'
            && $this->is_visible
            && $this->starts_at <= $now
            && $this->ends_at > $now
            && $this->participants_count >= $this->min_participants;
    }
}
