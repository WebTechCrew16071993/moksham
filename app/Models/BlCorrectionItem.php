<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlCorrectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bl_correction_id',
        'container_no',
        'seal_no',
        'commodity',
        'hs_code',
        'no_of_bales',
        'weight_kgs',
        'correction_notes',
    ];

    protected $casts = [
        'weight_kgs' => 'decimal:3',
        'no_of_bales' => 'integer',
    ];

    public function bl()
    {
        return $this->belongsTo(BlCorrection::class, 'bl_correction_id');
    }
}
