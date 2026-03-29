<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'job_applications';
    protected $appends = ['cv_size_human'];

    protected $fillable = [
        'job_id',
        'freelancer_id',
        'cover_letter',
        'cv_path',
        'cv_original_name',
        'cv_mime_type',
        'cv_size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'cv_size_bytes' => 'integer',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    public function getCvSizeHumanAttribute(): string
    {
        $bytes = $this->cv_size_bytes ?? 0;

        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2) . ' MB';
        }

        if ($bytes >= 1_024) {
            return round($bytes / 1_024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

}
