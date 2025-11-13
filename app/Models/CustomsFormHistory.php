<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomsFormHistory extends Model
{
    use HasFactory;

    protected $table = 'customs_form_history';

    protected $fillable = [
        'shipment_id',
        'form_type',
        'form_id',
        'action',
        'user_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function shipment(){ return $this->belongsTo(Shipment::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
