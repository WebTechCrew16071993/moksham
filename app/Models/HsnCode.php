<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HsnCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category',
        'code',
        'description',
    ];

    public function indents()
    {
        return $this->hasMany(Indent::class, 'hsn_code_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $hsn) {
            // Block deletion (including soft delete) if linked to any indents
            if ($hsn->indents()->exists()) {
                throw new \RuntimeException('Cannot delete HSN Code: it is used in one or more Indents.');
            }
        });
    }
}
