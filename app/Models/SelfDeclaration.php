<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelfDeclaration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id','shipment_id','packing_list_id','bl_correction_id','user_id',
        'ref_year','ref_container_count','ref_invoice_no',
        'certificate_number','issue_date','invoice_no','importer_details','goods_description','total_quantity_kgs',
        'declaration_points','signer_name','signer_title','pdf_path','status','show_signature',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'total_quantity_kgs' => 'decimal:3',
        'declaration_points' => 'array',
        'show_signature' => 'boolean',
    ];

    public function invoice(){ return $this->belongsTo(Invoice::class); }
    public function shipment(){ return $this->belongsTo(Shipment::class); }
    public function packingList(){ return $this->belongsTo(PackingList::class); }
    public function blCorrection(){ return $this->belongsTo(BlCorrection::class, 'bl_correction_id'); }
    public function user(){ return $this->belongsTo(User::class); }
    
}
