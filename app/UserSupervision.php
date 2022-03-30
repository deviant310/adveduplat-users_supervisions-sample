<?php

namespace App;

use App\Traits\QueryBuilderFromRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class UserSupervision extends Model
{
    use HasFactory;
    use LogsActivity;
    use QueryBuilderFromRequest;

    protected static array $logAttributes = ['*'];

    protected static bool $logOnlyDirty = true;

    protected $table = 'users_supervisions';

    protected $fillable = [
        'user_id',
        'course_id',
        'status_id',
        'comment',
        'deadline_at'
    ];

    protected array $filterable = [
        'user_id',
        'course_id',
        'status_id',
        'comment',
        'deadline_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(UserSupervisionStatus::class);
    }

    public function tags(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                UserSupervisionTag::class,
                'users_supervisions_to_tags',
                'user_supervision_id',
                'user_supervision_tag_id'
            )
            ->withTimestamps();
    }

    public function curators(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                User::class,
                'users_supervisions_to_curators',
                'user_supervision_id',
                'curator_id'
            )
            ->withTimestamps();
    }

    public function managers(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                User::class,
                'users_supervisions_to_managers',
                'user_supervision_id',
                'manager_id'
            )
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(UserSupervisionEvent::class, 'subject_id');
    }

    public function getProgressAttribute(): array
    {
        return [
            'sections_completed' => (new Sections)
                ->leftJoin('user_completed_section', 'user_completed_section.section_id', 'sections.id')
                ->where(
                    [
                        ['sections.course_id', $this->course_id],
                        ['user_completed_section.user_id', $this->user_id],
                        ['user_completed_section.is_completed', true]
                    ])
                ->count('sections.course_id'),
            'sections_total' => Sections::where('course_id', $this->course_id)->count('course_id')
        ];
    }
}
