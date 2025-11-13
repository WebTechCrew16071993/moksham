<?php

namespace App\Traits;

use App\Models\CustomsFormHistory;
use Illuminate\Support\Facades\Auth;

trait RecordsCustomsFormHistory
{
    public static function bootRecordsCustomsFormHistory(): void
    {
        static::created(function ($model) {
            try { $model->recordFormHistory('created'); } catch (\Throwable $e) {}
        });

        static::updated(function ($model) {
            try {
                if ($model->isDirty('status')) {
                    $model->recordFormHistory((string) $model->status, null, [
                        'old' => $model->getOriginal('status'),
                        'new' => $model->status,
                    ]);
                } else {
                    $model->recordFormHistory('updated');
                }
            } catch (\Throwable $e) {}
        });
    }

    public function recordFormHistory(string $action, ?string $notes = null, array $metadata = []): void
    {
        $formType = method_exists($this, 'getTable') && str_contains($this->getTable(), 'form9') ? 'form9' : 'form6';
        $shipmentId = $this->shipment_id ?? null;
        CustomsFormHistory::create([
            'shipment_id' => $shipmentId,
            'form_type' => $formType,
            'form_id' => $this->getKey(),
            'action' => $action,
            'user_id' => Auth::id(),
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }
}
