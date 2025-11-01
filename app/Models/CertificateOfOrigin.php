<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateOfOrigin extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'certificates_of_origin';

    protected $fillable = [
        'shipment_id',
        'user_id',
        'document_no',
        'bl_no',
        'port_of_loading',
        'port_of_discharge',
        'place_of_receipt',
        'exporting_carrier',
        'type_of_move',
        'containerized',
        'total_packages',
        'description',
        'total_gross_weight_kg',
        'total_bales',
        'shipped_date',
        'sworn_date',
        'owner_signature_path',
        'chamber_signature_path',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'containerized' => 'boolean',
        'total_gross_weight_kg' => 'decimal:3',
        'shipped_date' => 'date',
        'sworn_date' => 'date',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CertificateItem::class, 'certificate_id');
    }
}
