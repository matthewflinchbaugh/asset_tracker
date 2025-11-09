<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistTemplateField extends Model
{
    use HasFactory;
    protected $fillable = ['checklist_template_id', 'label', 'field_type', 'display_order'];

    public function template()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }
}
