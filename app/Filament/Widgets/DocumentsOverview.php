<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IndentResource;
use App\Models\Indent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class DocumentsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getCards(): array
    {
        $intentList = $this->getIntent();
        $packagingList = $this->getPackagingListCount();
        $cca = $this->getCcaCount();
        $psic = $this->getPsicCount();
        $cco = $this->getCcoCount();
        $form6 = $this->getForm6Count();
        $form9 = $this->getForm9Count();
        $invoices = $this->getInvoicesCount();

        $total = $intentList  + $packagingList + $cca + $psic + $cco + $form6 + $form9 + $invoices;

        return [
            Card::make('Total Documents', (string) $total)
                ->description('All document types combined')
                ->color('primary')
                ->icon('heroicon-o-rectangle-stack'),


            Card::make('Indents', (string) $intentList)
                ->color('success')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(IndentResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

            Card::make('Packaging List', (string) $packagingList)
                ->color('success')
                ->icon('heroicon-o-archive-box'),

            Card::make('CCA', (string) $cca)
                ->description('Certificate Chemical Analysis')
                ->color('info')
                ->icon('heroicon-o-document-text'),

            Card::make('PSIC', (string) $psic)
                ->color('warning')
                ->icon('heroicon-o-shield-check'),

            Card::make('CCO', (string) $cco)
                ->color('gray')
                ->icon('heroicon-o-clipboard-document-check'),

            Card::make('Form 6', (string) $form6)
                ->color('indigo')
                ->icon('heroicon-o-document'),

            Card::make('Form 9', (string) $form9)
                ->color('violet')
                ->icon('heroicon-o-document'),

            Card::make('Invoices', (string) $invoices)
                ->color('rose')
                ->icon('heroicon-o-receipt-percent'),
        ];
    }

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.dashboard');
    }

    // TODO: Replace the placeholder 0s below with real queries once we map where these documents are stored.
    protected function getIntent(): int { return Indent::query()->count(); }
    protected function getPackagingListCount(): int { return 0; }
    protected function getCcaCount(): int { return 0; }
    protected function getPsicCount(): int { return 0; }
    protected function getCcoCount(): int { return 0; }
    protected function getForm6Count(): int { return 0; }
    protected function getForm9Count(): int { return 0; }
    protected function getInvoicesCount(): int { return 0; }
}
