<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'packing_list_id',
        'container_no',
        'seal_no',
        'description',
        'no_of_bales',
        'weight_lbs',
        'weight_kg',
    ];

    protected $casts = [
        'weight_lbs' => 'decimal:3',
        'weight_kg' => 'decimal:3',
    ];

    public function packingList()
    {
        return $this->belongsTo(PackingList::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            // Auto-convert LBS to KG if provided
            if (!is_null($item->weight_lbs)) {
                $kg = round((float) $item->weight_lbs * 0.453592, 3);
                // Store as float; decimal cast will handle formatting
                $item->weight_kg = $kg;
            }
        });
    }
}
