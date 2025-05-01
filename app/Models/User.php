<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
/**
 * @method bool hasRole(string|array $roles)
 */

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Tables\Columns\Layout\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
/**
 * @method bool hasRole(string $role)
 * @method bool hasAnyRole(array|string $roles)
 * @method bool hasPermissionTo(string $permission)
 */


class User extends Authenticatable implements  HasAvatar
{
    use HasRoles;
    use HasApiTokens, HasFactory, Notifiable,  HasPanelShield;

     protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'foto_perfil',
        'informacion'
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
    ];


    public function getFilamentAvatarUrl(): ?string
    {
        return $this->foto_perfil ? Storage::url($this->foto_perfil) : null;
    }


    public function estudiante(): HasOne {
        return $this->hasOne(Estudiante::class);
    }
    public function docente(): BelongsTo {
        return $this->belongsTo(Docente::class);
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->can('access-admin-panel');
    }

}
