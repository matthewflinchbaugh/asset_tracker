<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistLogData extends Model
{
    use HasFactory;
    protected $fillable = ['maintenance_log_id', 'checklist_template_field_id', 'value'];

    public function field()
    {
        return $this->belongsTo(ChecklistTemplateField::class, 'checklist_template_field_id');
    }

    public function maintenanceLog()
    {
        return $this->belongsTo(MaintenanceLog::class);
    }
}
