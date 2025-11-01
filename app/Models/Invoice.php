<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'packing_list_id',
        'shipment_id',
        'user_id',
        'consignee_id',
        'invoice_no',
        'date',
        'employee',
        'obl_ref',
        'no_of_cont',
        'weight_mt',
        'description',
        'moisture_contain',
        'rate_mt',
        'amount',
        'payment_terms',
        'advance',
        'amount_due',
        'return_date',
        'cut_off_date',
        'dept_est',
        'arrival',
        'si_cut_off',
        'f_dest',
        'origin',
        'notify',
        'quotation',
        'remarks',
        'terms_conditions',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'date' => 'date',
        'weight_mt' => 'decimal:3',
        'rate_mt' => 'decimal:2',
        'amount' => 'decimal:2',
        'advance' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'return_date' => 'date',
        'cut_off_date' => 'date',
        'dept_est' => 'date',
        'arrival' => 'date',
        'si_cut_off' => 'date',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function packingList()
    {
        return $this->belongsTo(PackingList::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consignee()
    {
        return $this->belongsTo(ImportCompany::class, 'consignee_id');
    }
}
