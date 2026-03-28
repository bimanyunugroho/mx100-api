<?php

namespace App\Models;

use App\Enums\StatusJobEnum;
use App\Enums\TypeJobEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'jobs';

    protected $fillable = [
        'employer_id',
        'title',
        'description',
        'requirements',
        'salary_range',
        'location',
        'type',
        'status',
        'published_at'
    ];

    protected function casts(): array
    {
        return [
            'status'       => StatusJobEnum::class,
            'type'         => TypeJobEnum::class,
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', StatusJobEnum::PUBLISHED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', StatusJobEnum::DRAFT);
    }

    public function scopeLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'ilike', "%{$location}%");
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%");
        });
    }

    public function isPublished(): bool
    {
        return $this->status === StatusJobEnum::PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === StatusJobEnum::DRAFT;
    }

    public function isClosed(): bool
    {
        return $this->status === StatusJobEnum::CLOSED;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->employer_id === $user->id;
    }

    public function isApplicable(): bool
    {
        return $this->status->isApplicable();
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }
}
