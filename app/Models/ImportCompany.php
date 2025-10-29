<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'name','person_name','address','city','state','zip','country','iec','gstin','pan','email','phone_number','notes',
        'bank_name','bank_address','bank_city','bank_state','bank_zip','bank_country','bank_account_number','bank_swift_code','bank_ifsc_code',
    ];
}
