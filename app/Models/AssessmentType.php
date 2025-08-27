<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'term',
        'weight',
        'order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
} 