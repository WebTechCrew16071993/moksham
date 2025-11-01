<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'user_id',
        'date',
        'employee',
        'origin',
        'destination',
        'ship_date',
        'arrival_date',
        'order_no',
        'ship_in',
        'container_no_summary',
        'contact',
        'phone',
        'total_bales',
        'total_weight_lbs',
        'total_weight_kg',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'date' => 'date',
        'ship_date' => 'date',
        'arrival_date' => 'date',
        'total_weight_lbs' => 'decimal:3',
        'total_weight_kg' => 'decimal:3',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PackingListItem::class)->orderBy('id');
    }

    // Convenience: latest invoice via shipment relation
    public function latestInvoice()
    {
        return $this->shipment?->invoices()->latest()->first();
    }

    // Accessors: compute totals from items when not stored
    public function getTotalBalesAttribute($value)
    {
        if (!is_null($value) && (int) $value > 0) {
            return $value;
        }
        return (int) $this->items()->sum('no_of_bales');
    }

    public function getTotalWeightLbsAttribute($value)
    {
        if (!is_null($value) && (float) $value > 0) {
            return $value;
        }
        return (string) number_format((float) $this->items()->sum('weight_lbs'), 3, '.', '');
    }

    public function getTotalWeightKgAttribute($value)
    {
        if (!is_null($value) && (float) $value > 0) {
            return $value;
        }
        return (string) number_format((float) $this->items()->sum('weight_kg'), 3, '.', '');
    }

    // Helper to recompute and persist totals using current items
    public function recomputeTotals(bool $persist = true): void
    {
        $lbs = (float) $this->items()->sum('weight_lbs');
        $kg  = (float) $this->items()->sum('weight_kg');
        $bales = (int) $this->items()->sum('no_of_bales');
        $this->total_weight_lbs = number_format($lbs, 3, '.', '');
        $this->total_weight_kg  = number_format($kg, 3, '.', '');
        $this->total_bales      = $bales;
        if ($persist && $this->exists) {
            $this->saveQuietly();
        }
    }

    protected static function booted(): void
    {
        // After the model and its relations are saved by Filament, recompute totals
        static::saved(function (self $pl) {
            $pl->recomputeTotals(true);
        });
    }
}
