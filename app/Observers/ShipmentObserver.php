<?php

namespace App\Observers;

use App\Models\Shipment;
use App\Services\ActivityLogger;

class ShipmentObserver
{
    public function created(Shipment $shipment): void
    {
        ActivityLogger::logModel('shipment.created', $shipment, $shipment->user_id ?? null, 'Shipment #'.$shipment->getKey(), null, 'Shipment created');
    }

    public function updated(Shipment $shipment): void
    {
        ActivityLogger::logModel('shipment.updated', $shipment, $shipment->user_id ?? null, 'Shipment #'.$shipment->getKey(), null, 'Shipment updated');
    }

    public function deleted(Shipment $shipment): void
    {
        ActivityLogger::log('shipment.deleted', [
            'user_id' => $shipment->user_id ?? null,
            'subject_type' => Shipment::class,
            'subject_id' => $shipment->getKey(),
            'subject_label' => 'Shipment #'.$shipment->getKey(),
            'description' => 'Shipment deleted',
        ]);
    }
}
