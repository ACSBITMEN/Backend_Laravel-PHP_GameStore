<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relación: Un rol tiene muchos usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Métodos estáticos para acceder fácilmente a roles específicos
     */
    public static function getCustomer()
    {
        return static::where('name', 'customer')->first();
    }
    
    public static function getAdmin() 
    {
        return static::where('name', 'admin')->first();
    }
    
    public static function getManager()
    {
        return static::where('name', 'manager')->first();
    }

    /**
     * Scope para buscar por nombre de rol
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }
}