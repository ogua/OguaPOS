<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable. HasTenants
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'role',
        'phone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // public function canAccessTenant(Model $tenant): bool
    // {
    //     return $this->clinics->contains($tenant);
    // }

    public function warehouse(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->warehouse;
    }
 
    // public function canAccessTenant(Model $tenant): bool
    // {
    //     return $this->warehouse()->whereKey($tenant)->exists();
    // }

    public function canAccessPanel(Panel $panel): bool
    {
        $role = auth()->user()->role;

        return match ($panel->getId()) {
            'admin' => $role === 'admin',
            'cashier' => $role === 'cashier',
            'owner' => $role === 'owner',
            default => false
        };
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return "/storage/$this->avatar_url";
    }

    public function avatar(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['avatar_url']
        );
    }


    public function pos()
    {
        return $this->hasOne(Possettings::class,"user_id");   
    }
}
