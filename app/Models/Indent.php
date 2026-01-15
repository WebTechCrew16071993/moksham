<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id','indent_no','indent_date','consignee_id','hsn_code_id',
        'shipper_name','shipper_address','shipper_city','shipper_state','shipper_zip','shipper_country','shipper_email','shipper_phone',
        'shipper_bank_name','shipper_bank_address','shipper_bank_city','shipper_bank_state','shipper_bank_zip','shipper_bank_country','shipper_bank_account_number','shipper_bank_swift_code','shipper_bank_routing_number',
        'shipper_tax_iec_number','shipper_tax_gstin','shipper_tax_pan_number',
        'consignee_name','consignee_address','consignee_city','consignee_state','consignee_zip','consignee_country','consignee_email','consignee_phone',
        'consignee_iec','consignee_gstin','consignee_pan',
        'consignee_bank_name','consignee_bank_address','consignee_bank_city','consignee_bank_state','consignee_bank_zip','consignee_bank_country','consignee_bank_account_number','consignee_bank_swift_code','consignee_bank_ifsc_code',
        'hsn_code','hsn_category','hsn_description',
        'kind_attention','quality','origin','quantity','moisture_and_throw','price','payment','shipment_schedule','payload','shipping_line','discharge_port','final_destination','release_type_of_obl',
        'other_terms','claims','remarks',
        'shipper_signature_path','consignee_signature_path',
        // workflow fields
        'status', 'consignee_signed_pdf_path', 'show_signature',
    ];

    protected $casts = [
        'indent_date' => 'date',
        'show_signature' => 'boolean',
    ];

    public function consignee()
    {
        return $this->belongsTo(ImportCompany::class, 'consignee_id');
    }

    public function hsn()
    {
        return $this->belongsTo(HsnCode::class, 'hsn_code_id');
    }

    protected static function booted(): void
    {
        static::created(function (self $indent) {
            if (empty($indent->indent_no)) {
                $indent->indent_no = 999 + $indent->id; // starts from 1000
                // Use quiet saving to avoid triggering observers again
                $indent->saveQuietly();
            }
        });
    }
}
