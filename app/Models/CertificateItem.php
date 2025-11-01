<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_id',
        'marks_numbers',
        'number_of_packages',
        'description',
        'gross_weight_kg',
    ];

    protected $casts = [
        'gross_weight_kg' => 'decimal:3',
    ];

    public function certificate()
    {
        return $this->belongsTo(CertificateOfOrigin::class, 'certificate_id');
    }
}
