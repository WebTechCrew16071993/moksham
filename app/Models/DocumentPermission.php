<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resource',
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
    ];
}
