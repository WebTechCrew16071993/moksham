<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentaryCollectionLetter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id','shipment_id','packing_list_id','bl_correction_id','user_id','consignee_id',
        'letter_no','letter_date','sa_contract_no','ref_invoice_no','amount_usd',
        'notes','signer_name','signer_title','pdf_path','status',
        'ref_year','ref_container_count',
    ];

    protected $casts = [
        'letter_date' => 'date',
        'amount_usd' => 'decimal:2',
    ];

    public function invoice(){ return $this->belongsTo(Invoice::class); }
    public function shipment(){ return $this->belongsTo(Shipment::class); }
    public function packingList(){ return $this->belongsTo(PackingList::class); }
    public function blCorrection(){ return $this->belongsTo(BlCorrection::class, 'bl_correction_id'); }
    public function user(){ return $this->belongsTo(User::class); }
    public function consignee(){ return $this->belongsTo(ImportCompany::class, 'consignee_id'); }
}
