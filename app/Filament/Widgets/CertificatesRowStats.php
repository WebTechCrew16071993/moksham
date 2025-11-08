<?php

namespace App\Filament\Widgets;

use App\Models\CertificateOfOrigin;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class CertificatesRowStats extends Widget
{
    protected static string $view = 'filament.widgets.certificates-row-stats';

    protected static ?string $pollingInterval = '2s';

    public array $stats = [];

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $q = $this->baseQuery();
        $this->stats = [
            'total' => (clone $q)->count(),
            'draft' => (clone $q)->where('status', 'draft')->count(),
            'owner' => (clone $q)->where('status', 'owner_signed')->count(),
            'chamber' => (clone $q)->where('status', 'chamber_signed')->count(),
            'done' => (clone $q)->where('status', 'completed')->count(),
        ];
    }

    protected function baseQuery(): Builder
    {
        $q = CertificateOfOrigin::query();
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
        // Do not render on the main dashboard; allow on pages that include it explicitly
        return !request()->routeIs('filament.admin.pages.dashboard');
    }
}
