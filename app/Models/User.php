<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- RELATIONSHIPS ---
    
    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }
    
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }
    
    /**
     * Get the categories this user is explicitly allowed to see.
     */
    public function visibleCategories()
    {
        return $this->belongsToMany(Tag::class, 'user_category_visibility', 'user_id', 'category_id');
    }
}
