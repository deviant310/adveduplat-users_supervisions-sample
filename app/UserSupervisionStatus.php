<?php

namespace App;

use App\Traits\QueryBuilderFromRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSupervisionStatus extends Model
{
    use HasFactory;
    use QueryBuilderFromRequest;

    protected $table = 'users_supervisions_statuses';

    protected $fillable = [
        'title',
        'is_archive',
        'color'
    ];

    public array $filterable = [
        'title',
        'is_archive',
        'color'
    ];
}
