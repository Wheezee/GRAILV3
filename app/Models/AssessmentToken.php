<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AssessmentToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'token',
        'status', // active, used, expired
        'used_at',
        'expires_at'
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($token) {
            if (empty($token->token)) {
                $token->token = strtoupper(Str::random(8));
            } else {
                $token->token = strtoupper($token->token);
            }
        });
        static::updating(function ($token) {
            if (!empty($token->token)) {
                $token->token = strtoupper($token->token);
            }
        });
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    public function isExpired(): bool
    {
        // Check if token has expired OR if the assessment has expired
        return ($this->expires_at && now()->isAfter($this->expires_at)) || 
               ($this->assessment && $this->assessment->isExpired());
    }

    public function markAsUsed(): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now()
        ]);
    }

    public function generateNewToken(): void
    {
        $this->update(['token' => strtoupper(Str::random(8))]);
    }
}
