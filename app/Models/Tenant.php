<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bank_account_number' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Tenant $tenant): void {
            if (empty($tenant->attributes['name']) && ! empty($tenant->attributes['display_name'])) {
                $tenant->attributes['name'] = $tenant->attributes['display_name'];
            }
            if (empty($tenant->attributes['display_name']) && ! empty($tenant->attributes['name'])) {
                $tenant->attributes['display_name'] = $tenant->attributes['name'];
            }
            if (empty($tenant->attributes['slug'])) {
                $src = $tenant->attributes['display_name'] ?? $tenant->attributes['name'] ?? null;
                if ($src) {
                    $tenant->attributes['slug'] = Str::slug($src);
                }
            }
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] ?? $this->attributes['name'] ?? '';
    }

    public function getNameAttribute(): string
    {
        return $this->attributes['name'] ?? $this->attributes['display_name'] ?? '';
    }

    /**
     * @return BelongsTo<Canteen, $this>
     */
    public function canteen(): BelongsTo
    {
        return $this->belongsTo(Canteen::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasOne<TenantBalance, $this>
     */
    public function balance(): HasOne
    {
        return $this->hasOne(TenantBalance::class, 'tenant_id');
    }

    /**
     * @return HasMany<CommissionScheme, $this>
     */
    public function commissionSchemes(): HasMany
    {
        return $this->hasMany(CommissionScheme::class);
    }

    /**
     * @return HasMany<UserTenantRole, $this>
     */
    public function tenantRoles(): HasMany
    {
        return $this->hasMany(UserTenantRole::class);
    }

    /**
     * @return HasMany<TenantBankAccount, $this>
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(TenantBankAccount::class);
    }

    /**
     * @return HasMany<Menu, $this>
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * @return HasMany<Modifier, $this>
     */
    public function modifiers(): HasMany
    {
        return $this->hasMany(Modifier::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<TenantOrder, $this>
     */
    public function tenantOrders(): HasMany
    {
        return $this->hasMany(TenantOrder::class);
    }

    /**
     * @return HasMany<Withdrawal, $this>
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }
}
