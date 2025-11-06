<?php

namespace App\Filament\Widgets;

use App\Models\CertificateOfOrigin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

abstract class COOStatBase extends BaseWidget
{
    protected static ?string $pollingInterval = '2s';

    protected function baseQuery(): Builder
    {
        $q = CertificateOfOrigin::query();
        // Apply table filters from the index so numbers reflect the table
        $filters = (array) request()->input('tableFilters', []);
        if (isset($filters['status']) && $filters['status'] !== null && $filters['status'] !== '') {
            $status = $filters['status'];
            $q = is_array($status) ? $q->whereIn('status', array_filter($status)) : $q->where('status', $status);
        }
        if (isset($filters['shipped_date']) && is_array($filters['shipped_date'])) {
            $from = $filters['shipped_date']['from'] ?? null;
            $until = $filters['shipped_date']['until'] ?? null;
            if (!empty($from)) $q->whereDate('shipped_date', '>=', $from);
            if (!empty($until)) $q->whereDate('shipped_date', '<=', $until);
        }
        if (!empty($filters['shipment_id'])) $q->where('shipment_id', $filters['shipment_id']);
        if (!empty($filters['packing_list_id'])) $q->where('packing_list_id', $filters['packing_list_id']);
        return $q;
    }

    public static function canView(): bool
    {
        // Always allow; page explicitly includes these header widgets
        return true;
    }
}
