<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form9Carrier extends Model
{
    use HasFactory;

    protected $table = 'form9_carriers';

    protected $fillable = [
        'form9_id', 'carrier_type', 'name_address', 'registration_no', 'phone', 'fax', 'transport_identity', 'transfer_date', 'signature', 'signature_image_path'
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function form9() { return $this->belongsTo(Form9Document::class, 'form9_id'); }
}
