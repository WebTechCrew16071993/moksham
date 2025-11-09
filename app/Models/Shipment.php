<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'indent_id',
        'booking_no',
        'booking_date',
        'carrier',
        'vessel',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    public function indent()
    {
        return $this->belongsTo(Indent::class);
    }

    public function packingLists()
    {
        return $this->hasMany(PackingList::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function certificatesOfOrigin()
    {
        return $this->hasMany(CertificateOfOrigin::class);
    }

    public function blCorrections()
    {
        return $this->hasMany(BlCorrection::class);
    }
}
