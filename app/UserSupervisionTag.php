<?php

namespace App;

use App\Traits\QueryBuilderFromRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserSupervisionTag extends Model
{
    use HasFactory;
    use QueryBuilderFromRequest;

    protected $table = 'users_supervisions_tags';

    protected $fillable = [
        'title'
    ];

    public array $filterable = [
        'title'
    ];

    public function supervisions(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                UserSupervision::class,
                'users_supervisions_to_tags',
                'user_supervision_tag_id',
                'user_supervision_id',
            )
            ->withTimestamps();
    }
}
