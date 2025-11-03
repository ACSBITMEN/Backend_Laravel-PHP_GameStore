<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'password',
        'phone',
        'country',
        'avatar',
        'role_id',
        'status',
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
        'status' => 'boolean',
    ];

    /**
     * Relación: Un usuario pertenece a un rol
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Accessor para nombre completo
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Métodos para verificar roles
     */
    public function isAdmin(): bool
    {
        return $this->role->name === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role->name === 'manager';
    }

    public function isCustomer(): bool
    {
        return $this->role->name === 'customer';
    }

    /**
     * Scopes para consultas comunes
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByRole($query, $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
    }
}