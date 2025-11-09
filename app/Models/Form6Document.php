<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form6Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'form6_documents';

    protected $fillable = [
        'shipment_id','packing_list_id','bl_correction_id','invoice_id','user_id',
        'form6_no','form6_date','applicant_ref_no',
        'exporter_name_address','exporter_contact_person','exporter_phone','exporter_fax','exporter_email',
        'generator_name_address','generator_contact_person','generator_phone','generator_fax','generator_email','site_of_generation',
        'importer_id','importer_name_address','importer_contact_person','importer_phone','importer_fax','importer_email',
        'trader_name_address','trader_contact_person','trader_phone','trader_fax','trader_email','actual_user_details',
        'bill_of_lading','country_of_export','country_of_import',
        'quantity_kgs','physical_characteristics','chemical_composition','basel_no','un_shipping_name','un_class','un_no','h_number','y_number','itc_hs','customs_code_hs','other_codes',
        'package_type','package_number','special_handling_requirements',
        'movement_type','expected_shipment_dates','estimated_quantities',
        'transporter_name_address','transporter_registration_no','means_of_transport','date_of_transfer','carrier_signature',
        'exporter_declaration','exporter_signature_name','exporter_signature_date','exporter_signature_image_path',
        'quantity_received_kgs','importer_signature_name','importer_signature_date','importer_signature_image_path',
        'importer_applicant_ref_no','r_code','technology_employed','importer_certification_date','importer_certification_signature',
        'specific_conditions','recovery_operations','terms_and_description','notes','signature_place','signature_designation',
        'status','pdf_path','submitted_at','approved_at','received_at','completed_at'
    ];

    protected $casts = [
        'form6_date' => 'date',
        'quantity_kgs' => 'decimal:3',
        'quantity_received_kgs' => 'decimal:3',
        'date_of_transfer' => 'date',
        'exporter_signature_date' => 'date',
        'importer_signature_date' => 'date',
        'importer_certification_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
        'recovery_operations' => 'array',
    ];

    public function shipment(){ return $this->belongsTo(Shipment::class); }
    public function packingList(){ return $this->belongsTo(PackingList::class); }
    public function bl(){ return $this->belongsTo(BlCorrection::class,'bl_correction_id'); }
    public function invoice(){ return $this->belongsTo(Invoice::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
