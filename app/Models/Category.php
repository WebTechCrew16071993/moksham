<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function hsnCodes()
    {
        return $this->hasMany(HsnCode::class, 'category_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $category) {
            if ($category->hsnCodes()->exists()) {
                throw new \RuntimeException('Cannot delete Category: it is used by one or more HSN codes.');
            }
        });
    }
}
