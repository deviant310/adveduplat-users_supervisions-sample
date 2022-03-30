<?php

namespace App;

use App\Traits\QueryBuilderFromRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSupervisionEvent extends Model
{
    use QueryBuilderFromRequest;

    protected $table = "activity_log";

    protected $hidden = [
        'causer_type',
        'subject_type'
    ];

    protected $casts = [
        'properties' => 'array'
    ];

    protected array $filterable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->filterable = collect([
            'log_name',
            'description',
            'subject_id',
            'causer_id',
            'created_at',
            'updated_at',
        ])
            ->merge(
                collect((new UserSupervision)->getFillable())
                    ->map(fn($key) => "properties->attributes->$key")
            )
            ->all();
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function (Builder $query) {
            $query->where('subject_type', UserSupervision::class);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function supervision(): BelongsTo
    {
        return $this->belongsTo(UserSupervision::class, 'subject_id');
    }
}
