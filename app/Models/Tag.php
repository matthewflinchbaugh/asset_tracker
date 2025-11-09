<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    
    // We keep the table name 'categories' to avoid database migration conflicts
    protected $table = 'categories'; 

    protected $fillable = [
        'name',
    ];

    /**
     * Get the assets that have this tag (many-to-many).
     */
    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'asset_tag', 'category_id', 'asset_id');
    }

    /**
     * Get the users who have visibility to this tag (formerly category visibility).
     */
    public function usersWithVisibility()
    {
        return $this->belongsToMany(User::class, 'user_category_visibility', 'category_id', 'user_id');
    }
}
