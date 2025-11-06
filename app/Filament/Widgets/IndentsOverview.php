<?php

namespace App\Filament\Widgets;

use App\Models\Indent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class IndentsOverview extends BaseWidget
{
    // Faster refresh so counts reflect filter changes quickly
    protected static ?string $pollingInterval = '2s';

    protected function getCards(): array
    {
        $base = Indent::query();
        $this->applyTableFilters($base);
        $total = (clone $base)->count();
        $signed = (clone $base)->whereNotNull('consignee_signed_pdf_path')->count();
        $pending = $total - $signed;

        return [
            Card::make('Indents', (string) $total)
                ->description('Total Indents')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary'),

            Card::make('Signed by Consignee', (string) $signed)
                ->description('With uploaded signed PDF')
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Card::make('Pending Signature', (string) $pending)
                ->description('Awaiting consignee signature')
                ->icon('heroicon-o-clock')
                ->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        // Always allow; ListIndents explicitly includes this widget on the Indents index.
        // Returning true avoids hiding during Livewire sub-requests (search/sort) where routeIs may not match.
        return true;
    }

    /**
     * Apply supported table filters from the current request to the query so
     * the cards mirror what the user sees in the table.
     */
    protected function applyTableFilters(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $filters = (array) request()->input('tableFilters', []);

        // Common status filter (single or multi-select)
        if (isset($filters['status']) && $filters['status'] !== null && $filters['status'] !== '') {
            $status = $filters['status'];
            if (is_array($status)) {
                $query->whereIn('status', array_filter($status));
            } else {
                $query->where('status', $status);
            }
        }

        // Optional date range filter on indent_date (expects ['from' => Y-m-d, 'until' => Y-m-d])
        if (isset($filters['indent_date']) && is_array($filters['indent_date'])) {
            $from = $filters['indent_date']['from'] ?? null;
            $until = $filters['indent_date']['until'] ?? null;
            if (!empty($from)) {
                $query->whereDate('indent_date', '>=', $from);
            }
            if (!empty($until)) {
                $query->whereDate('indent_date', '<=', $until);
            }
        }

        // You can extend this with other known filters (e.g., consignee, hs code) as needed:
        // if (!empty($filters['consignee_id'])) { $query->where('consignee_id', $filters['consignee_id']); }
    }
}
