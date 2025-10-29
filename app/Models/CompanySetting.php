<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanySetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name', 'company_address', 'company_city', 'company_state', 'company_zip', 'company_country', 'company_phone', 'company_email',
        'bank_name', 'bank_address', 'bank_city', 'bank_state', 'bank_zip', 'bank_country', 'bank_account_number', 'bank_swift_code', 'bank_routing_number',
        'tax_iec_number', 'tax_gstin', 'tax_pan_number',
        'company_signed_logo',
    ];
}
