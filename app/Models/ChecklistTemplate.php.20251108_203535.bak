<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function assets()
    {
        return $this->belongsToMany(
            Asset::class,
            'asset_checklist_template'
        )->withPivot('component_name')->withTimestamps();
    }

    public function fields()
    {
        return $this->hasMany(ChecklistTemplateField::class)
                    ->orderBy('display_order');
    }
}
