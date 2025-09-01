<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'class_section_id',
        'birth_date',
        'gender',
        'contact_number',
        'address',
    ];

    protected static function boot()
    {
        parent::boot();

        // Normalize names before saving
        static::saving(function ($student) {
            $student->first_name = self::normalizeName($student->first_name);
            $student->last_name = self::normalizeName($student->last_name);
            if ($student->middle_name) {
                $student->middle_name = self::normalizeName($student->middle_name);
            }
        });
    }

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function classSections()
    {
        return $this->belongsToMany(ClassSection::class, 'class_section_student')
                    ->withPivot('enrollment_date', 'status')
                    ->withTimestamps();
    }

    public function assessmentScores()
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function annotations()
    {
        return $this->hasMany(AssessmentAnnotation::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Normalize student names to handle special characters and case
     * Converts special characters like ñ to n, and applies proper title case
     */
    public static function normalizeName($name)
    {
        if (empty($name)) {
            return $name;
        }

        // Convert to lowercase first
        $name = mb_strtolower($name, 'UTF-8');
        
        // Replace special characters with their ASCII equivalents
        $replacements = [
            'ñ' => 'n', 'é' => 'e', 'ü' => 'u', 'á' => 'a', 
            'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ç' => 'c',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ã' => 'a', 'õ' => 'o', 'ñ' => 'n'
        ];
        
        $name = strtr($name, $replacements);
        
        // Convert to proper title case (first letter of each word capitalized)
        $name = ucwords($name);
        
        // Handle special cases for common prefixes
        $prefixes = ['Mc', 'Mac', 'O\'', 'De', 'Del', 'La', 'Le', 'Van', 'Von', 'Di', 'Da'];
        foreach ($prefixes as $prefix) {
            $name = preg_replace('/\b' . preg_quote($prefix, '/') . '\b/i', $prefix, $name);
        }
        
        return $name;
    }
} 