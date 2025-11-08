<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CertificateOfOriginResource;
use App\Models\CertificateOfOrigin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Builder;

class CertificatesOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '2s';

    protected function getCards(): array
    {
        $base = CertificateOfOrigin::query();
        $this->applyTableFilters($base);

        $total = (clone $base)->count();
        $draft = (clone $base)->where('status', 'draft')->count();
        $owner = (clone $base)->where('status', 'owner_signed')->count();
        $chamber = (clone $base)->where('status', 'chamber_signed')->count();
        $completed = (clone $base)->where('status', 'completed')->count();

        return [
            Card::make('Total', (string) $total)
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->url(CertificateOfOriginResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

            Card::make('Draft', (string) $draft)
                ->color('gray')
                ->icon('heroicon-o-clock'),

            Card::make('Owner', (string) $owner)
                ->color('warning')
                ->icon('heroicon-o-check'),

            Card::make('Chamber', (string) $chamber)
                ->color('info')
                ->icon('heroicon-o-shield-check'),

            Card::make('Done', (string) $completed)
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }

    protected function getColumns(): int
    {
        // Use 3 columns to achieve compact boxed layout similar to Indents (wraps as 3 + 2)
        return 3;
    }

    public static function canView(): bool
    {
        // Show on resource pages but not on the main dashboard
        return !request()->routeIs('filament.admin.pages.dashboard');
    }

    protected function applyTableFilters(Builder $query): void
    {
        $filters = (array) request()->input('tableFilters', []);

        // Status filter support (single or multi-select)
        if (isset($filters['status']) && $filters['status'] !== null && $filters['status'] !== '') {
            $status = $filters['status'];
            if (is_array($status)) {
                $query->whereIn('status', array_filter($status));
            } else {
                $query->where('status', $status);
            }
        }

        // Optional shipped_date range
        if (isset($filters['shipped_date']) && is_array($filters['shipped_date'])) {
            $from = $filters['shipped_date']['from'] ?? null;
            $until = $filters['shipped_date']['until'] ?? null;
            if (!empty($from)) {
                $query->whereDate('shipped_date', '>=', $from);
            }
            if (!empty($until)) {
                $query->whereDate('shipped_date', '<=', $until);
            }
        }

        // You can add more filters here as your table defines them (e.g., shipment_id, packing_list_id)
        if (!empty($filters['shipment_id'])) {
            $query->where('shipment_id', $filters['shipment_id']);
        }
        if (!empty($filters['packing_list_id'])) {
            $query->where('packing_list_id', $filters['packing_list_id']);
        }
    }
}
