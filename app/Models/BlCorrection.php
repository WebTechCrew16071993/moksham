<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlCorrection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'packing_list_id',
        'user_id',
        'bl_no',
        'booking_no',
        'bl_date',
        'shipper_details',
        'shipper_phone',
        'shipper_email',
        'consignee_id',
        'consignee_details',
        'consignee_iec',
        'consignee_gstin',
        'consignee_pan',
        'consignee_email',
        'notify_party_details',
        'notify_party_iec',
        'notify_party_gstin',
        'notify_party_pan',
        'notify_party_email',
        'port_of_loading',
        'origin',
        'destination',
        'net_weight_kgs',
        'net_weight_mts',
        'cargo_value',
        'currency',
        'packaging_type',
        'ship_in',
        'no_of_containers',
        'container_type',
        'document_type',
        'total_bales',
        'hs_code',
        'commodity_description',
        'status',
        'sent_to_buyer_at',
        'buyer_reviewed_at',
        'buyer_comments',
        'approved_at',
        'approved_by',
        'draft_pdf_path',
        'final_pdf_path',
        'revision_number',
        'parent_bl_id',
    ];

    protected $casts = [
        'bl_date' => 'date',
        'net_weight_kgs' => 'decimal:3',
        'net_weight_mts' => 'decimal:3',
        'cargo_value' => 'decimal:2',
        'sent_to_buyer_at' => 'datetime',
        'buyer_reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(BlCorrectionItem::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_bl_id');
    }
}
