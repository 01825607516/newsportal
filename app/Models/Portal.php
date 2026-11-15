<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portal extends Model
{
    protected $fillable = [
        'portal_name', 'portal_reg_no', 'editor_name', 'editor_email', 'editor_mobile',
        'database_name', 'status', 'provisioned_at', 'provision_error', 'last_login_at',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public const MOBILE_REGEX = '/^(?:\+?88)?01[3-9]\d{8}$/';

    /** +8801712-345678 / 8801712345678 / 01712 345678  =>  01712345678 */
    public static function normalizeMobile(string $mobile): string
    {
        $digits = preg_replace('/[\s\-()]/', '', $mobile);
        return preg_replace('/^(\+?88)/', '', $digits);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(PortalCharge::class);
    }

    public function isProvisioned(): bool
    {
        return $this->provisioned_at !== null && $this->database_name !== null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
