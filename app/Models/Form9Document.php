<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RecordsCustomsFormHistory;
use App\Models\CustomsFormHistory;

class Form9Document extends Model
{
    use HasFactory, SoftDeletes, RecordsCustomsFormHistory;

    protected $table = 'form9_documents';

    protected $fillable = [
        'shipment_id','packing_list_id','bl_correction_id','invoice_id','form6_id','user_id',
        'form9_no','form9_date',
        'exporter_name_address','exporter_contact_person','exporter_phone','exporter_fax','exporter_email',
        'generator_name_address','generator_contact_person','generator_phone','generator_fax','generator_email','site_of_generation',
        'importer_id','importer_name_address','importer_contact_person','importer_phone','importer_fax','importer_email',
        'applicant_ref_no','movement_type','bill_of_lading',
        'disposer_name_address','disposer_contact_person','actual_site_of_disposal','disposer_phone','disposer_fax','disposer_email',
        'method_of_recovery','r_code','technology_employed',
        'waste_designation_composition','physical_characteristics','actual_quantity_kgs','waste_description',
        'waste_identification_code','basel_no','oecd_no','un_no','itc_hs','customs_code_hs','other_codes',
        'oecd_classification','oecd_color','oecd_number',
        'packaging_type','packaging_number',
        'un_classification','un_shipping_name','un_identification_no','un_class','h_number','y_number',
        'special_handling_requirements','actual_shipment_date',
        'exporter_declaration','exporter_declaration_date','exporter_signature_name','exporter_signature_image_path',
        'quantity_received_importer','date_received_importer','signature_received_importer','signature_received_importer_image_path','name_received_importer',
        'quantity_received_recycler','quantity_accepted_recycler','date_received_recycler','signature_received_recycler','signature_received_recycler_image_path','name_received_recycler',
        'approximate_recycling_date','method_of_recycling','recycler_certification','recycler_certification_date','recycler_certification_signature','recycler_certification_signature_image_path',
        'specific_conditions','terms_and_description','recovery_operations','notes','means_of_transport',
        'status','pdf_path','submitted_at','approved_at','shipped_at','received_importer_at','received_recycler_at','recycling_scheduled_at','recycling_completed_at','completed_at'
    ];

    protected $casts = [
        'form9_date' => 'date',
        'actual_quantity_kgs' => 'decimal:3',
        'quantity_received_importer' => 'decimal:3',
        'quantity_received_recycler' => 'decimal:3',
        'quantity_accepted_recycler' => 'decimal:3',
        'actual_shipment_date' => 'date',
        'exporter_declaration_date' => 'date',
        'date_received_importer' => 'date',
        'date_received_recycler' => 'date',
        'approximate_recycling_date' => 'date',
        'recycler_certification_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'shipped_at' => 'datetime',
        'received_importer_at' => 'datetime',
        'received_recycler_at' => 'datetime',
        'recycling_scheduled_at' => 'datetime',
        'recycling_completed_at' => 'datetime',
        'completed_at' => 'datetime',
        'recovery_operations' => 'array',
    ];

    public function shipment(){ return $this->belongsTo(Shipment::class); }
    public function packingList(){ return $this->belongsTo(PackingList::class); }
    public function bl(){ return $this->belongsTo(BlCorrection::class,'bl_correction_id'); }
    public function invoice(){ return $this->belongsTo(Invoice::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function histories(){
        return $this->hasMany(CustomsFormHistory::class, 'form_id')
            ->where('form_type', 'form9')
            ->latest('id');
    }
    public function carriers(){
        return $this->hasMany(Form9Carrier::class, 'form9_id');
    }
}
