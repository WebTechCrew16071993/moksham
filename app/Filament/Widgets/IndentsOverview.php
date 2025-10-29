<?php

namespace App\Filament\Widgets;

use App\Models\Indent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class IndentsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getCards(): array
    {
        $total = Indent::query()->count();
        $signed = Indent::query()->whereNotNull('consignee_signed_pdf_path')->count();
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
            return request()->routeIs('filament.admin.resources.indents.index');
    }
}
