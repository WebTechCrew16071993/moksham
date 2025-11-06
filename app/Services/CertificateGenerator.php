<?php

namespace App\Services;

use App\Models\CertificateOfOrigin;
use App\Models\PackingList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CertificateGenerator
{
    public function generateOrUpdateForPackingList(PackingList $packingList): CertificateOfOrigin
    {
        Log::info('CertificateGenerator: start', ['packing_list_id' => $packingList->getKey()]);
        $packingList->loadMissing(['shipment.indent', 'items']);
        $shipment = $packingList->shipment;
        $indent = $shipment?->indent;

        // Compute totals from packing list items
        $totalBales = (int) $packingList->items->sum('no_of_bales');
        $totalWeightKg = (float) $packingList->items->sum(function ($i) {
            if (!is_null($i->weight_kg)) return (float) $i->weight_kg;
            return round(((float) ($i->weight_lbs ?? 0)) * 0.453592, 3);
        });

        $containerCount = $packingList->items->count();
        $shipIn = strtoupper((string) $packingList->ship_in);
        $containersSummary = !empty($packingList->container_no_summary)
            ? $packingList->container_no_summary
            : ($containerCount > 0 && $shipIn ? ($containerCount . ' x ' . $shipIn) : null);

        // Determine desired document number: prefer shipment booking_no if available & unique; else numeric sequence
        $desiredDoc = $shipment?->booking_no ? (string) $shipment->booking_no : null;
        if ($desiredDoc && CertificateOfOrigin::withTrashed()->where('document_no', $desiredDoc)->exists()) {
            $desiredDoc = null; // will fallback to sequence
        }
        $maxExisting = CertificateOfOrigin::withTrashed()
            ->orderByRaw('CAST(document_no AS UNSIGNED) DESC')
            ->value('document_no');
        $nextDoc = 500001;
        if ($maxExisting && preg_match('/(\d+)/', (string) $maxExisting, $m)) {
            $nextDoc = max(500000, (int) $m[1]) + 1;
        }
        while (CertificateOfOrigin::withTrashed()->where('document_no', (string) $nextDoc)->exists()) {
            $nextDoc++;
        }

        // Prefer existing COO linked to this Packing List; fallback to latest by shipment
        $existing = CertificateOfOrigin::query()
            ->where('packing_list_id', $packingList->getKey())
            ->latest()
            ->first();
        if (!$existing && $shipment) {
            $existing = $shipment->certificatesOfOrigin()->latest()->first();
        }

        // Consigned block from indent (Company details of consignee)
        $consignedBlock = null;
        if ($indent) {
            $name = trim((string) $indent->consignee_name);
            $addr = trim((string) $indent->consignee_address);
            $consignedBlock = trim(($name ? (strtoupper($name) . "\n") : '') . $addr) ?: null;
        }

        $data = [
            'packing_list_id' => $packingList->getKey(),
            'shipment_id' => $shipment?->getKey(),
            'user_id' => $packingList->user_id ?? Auth::id(),
            'bl_no' => $shipment?->booking_no,
            'export_references' => $shipment?->booking_no,
            'forwarding_agent' => null,
            'consigned_to' => $consignedBlock,
            'notify_party' => $indent?->notify_party,
            'origin_or_ftz' => null, // falls back to company state/country in PDF
            'domestic_routing_instructions' => null,
            'pre_carriage_by' => null,
            'place_of_receipt' => $indent?->origin ?? $packingList->origin,
            'exporting_carrier' => $shipment?->carrier,
            'port_of_loading' => $packingList->origin ?? null,
            'port_of_discharge' => $packingList->destination ?? $indent?->final_destination,
            'type_of_move' => 'Vessel, Containerized',
            'containerized' => !empty($packingList->ship_in),
            'total_packages' => $containerCount,
            'description' => $packingList->items->first()->description ?? 'WASTEPAPER OCC. 11',
            'total_gross_weight_kg' => $totalWeightKg,
            'total_bales' => $totalBales,
            'shipped_date' => $packingList->ship_date ?? $shipment?->booking_date,
            'sworn_date' => now()->toDateString(),
            'status' => 'draft',
        ];

        if ($existing) {
            $existing->fill($data);
            $existing->saveQuietly();
            Log::info('CertificateGenerator: updated existing', ['id' => $existing->getKey()]);
            return $existing;
        }

        $data['document_no'] = $desiredDoc ?: (string) $nextDoc;
        $created = CertificateOfOrigin::create($data);
        Log::info('CertificateGenerator: created new', ['id' => $created->getKey(), 'doc' => $created->document_no]);
        return $created;
    }
}
