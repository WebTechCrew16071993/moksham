<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillOfExchange extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id','shipment_id','packing_list_id','bl_correction_id','user_id',
        'ref_no','issue_date','place_of_issue','amount_usd','amount_in_words',
        'drawee_name','drawee_address','issuer_name','issuer_title','authorised_signature_image_path','pdf_path','status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'amount_usd' => 'decimal:2',
    ];

    public function invoice(){ return $this->belongsTo(Invoice::class); }
    public function shipment(){ return $this->belongsTo(Shipment::class); }
    public function packingList(){ return $this->belongsTo(PackingList::class); }
    public function blCorrection(){ return $this->belongsTo(BlCorrection::class, 'bl_correction_id'); }
    public function user(){ return $this->belongsTo(User::class); }
}
