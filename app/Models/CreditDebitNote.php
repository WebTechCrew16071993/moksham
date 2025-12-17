<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditDebitNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'invoice_id',
        'note_no',
        'date',
        'consignee_id',
        'heading',
        'row1_details',
        'row1_amount_usd',
        'row1_amount_credit',
        'subheader',
        'row2_details',
        'row2_amount_usd',
        'row2_amount_credit',
        'total_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'row1_amount_usd' => 'decimal:2',
        'row1_amount_credit' => 'decimal:2',
        'row2_amount_usd' => 'decimal:2',
        'row2_amount_credit' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function consignee()
    {
        return $this->belongsTo(ImportCompany::class, 'consignee_id');
    }
}
