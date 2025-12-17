<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IndentResource;
use App\Filament\Resources\PackingListResource;
use App\Filament\Resources\CertificateOfOriginResource;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\BlCorrectionResource;
use App\Filament\Resources\Form6DocumentResource;
use App\Filament\Resources\Form9DocumentResource;
use App\Filament\Resources\BillOfExchangeResource;
use App\Filament\Resources\DocumentaryCollectionLetterResource;
use App\Filament\Resources\SelfDeclarationResource;
use App\Filament\Resources\CreditNoteResource;
use App\Filament\Resources\DebitNoteResource;
use App\Models\Indent;
use App\Models\PackingList;
use App\Models\CertificateOfOrigin;
use App\Models\Invoice;
use App\Models\BlCorrection;
use App\Models\Form6Document;
use App\Models\Form9Document;
use App\Models\BillOfExchange;
use App\Models\DocumentaryCollectionLetter;
use App\Models\SelfDeclaration;
use App\Models\CreditDebitNote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Auth;

class DocumentsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getCards(): array
    {
        $intentList = $this->getIntent();
        $packagingList = $this->getPackagingListCount();
        // $cca = $this->getCcaCount();
        // $psic = $this->getPsicCount();
        // $cco = $this->getCcoCount();
        $form6 = $this->getForm6Count();
        $form9 = $this->getForm9Count();
        $invoices = $this->getInvoicesCount();
        $bls = $this->getBlCount();
        $dcl = $this->getDclFinalizedCount();
        $boe = $this->getBoeFinalizedCount();
        $sdec = $this->getSelfDeclarationFinalizedCount();
        $creditNotes = $this->getCreditNotesCount();
        $debitNotes = $this->getDebitNotesCount();

        $total = $intentList  + $packagingList + $bls + $form6 + $form9 + $invoices + $dcl + $boe + $sdec + $creditNotes + $debitNotes;
        // $total = $intentList  + $packagingList + $cca + $psic + $cco + $form6 + $form9 + $invoices;

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
                ->icon('heroicon-o-archive-box')
                ->url(PackingListResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

            Card::make('BLs', (string) $bls)
                ->color('orange')
                ->icon('heroicon-o-document-text')
                ->url(BlCorrectionResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

            
            Card::make('Invoices', (string) $invoices)
                ->color('rose')
                ->icon('heroicon-o-receipt-percent')
                ->url(InvoiceResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

           
            // Card::make('COO', (string) $cco)
            //     ->color('gray')
            //     ->icon('heroicon-o-clipboard-document-check')
            //     ->url(CertificateOfOriginResource::getUrl('index'))
            //     ->extraAttributes(['wire:navigate' => true]),

            // Card::make('CCA', (string) $cca)
            //     ->description('Certificate Chemical Analysis')
            //     ->color('info')
            //     ->icon('heroicon-o-document-text'),

            // Card::make('PSIC', (string) $psic)
            //     ->color('warning')
            //     ->icon('heroicon-o-shield-check'),

            

            Card::make('Form 6', (string) $form6)
                ->color('indigo')
                ->icon('heroicon-o-document')
                ->url(Form6DocumentResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

            Card::make('Form 9', (string) $form9)
                ->color('violet')
                ->icon('heroicon-o-document')
                ->url(Form9DocumentResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),
            Card::make('DCL (Finalized)', (string) $dcl)
                ->color('info')
                ->icon('heroicon-o-document-magnifying-glass')
                ->url(DocumentaryCollectionLetterResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

            Card::make('BOE (Finalized)', (string) $boe)
                ->color('info')
                ->icon('heroicon-o-document')
                ->url(BillOfExchangeResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),


            Card::make('Self-Declaration (Finalized)', (string) $sdec)
                ->color('info')
                ->icon('heroicon-o-document-check')
                ->url(SelfDeclarationResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

            Card::make('Credit Notes', (string) $creditNotes)
                ->color('cyan')
                ->icon('heroicon-o-receipt-percent')
                ->url(CreditNoteResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

            Card::make('Debit Notes', (string) $debitNotes)
                ->color('pink')
                ->icon('heroicon-o-receipt-percent')
                ->url(DebitNoteResource::getUrl('index'))
                ->extraAttributes(['wire:navigate' => true]),

        ];
    }

    public static function canView(): bool
    {
        if (!request()->routeIs('filament.admin.pages.dashboard')) {
            return false;
        }
        $user = Auth::user();
        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    // Dynamic counts (exclude drafts where applicable)
    protected function getIntent(): int {
        return Indent::query()->where(function($q){ $q->whereNull('status')->orWhere('status','!=','draft'); })->count();
    }
    protected function getPackagingListCount(): int {
        return PackingList::query()->where('status', '!=', 'draft')->count();
    }
    protected function getCcaCount(): int { return 0; }
    protected function getPsicCount(): int { return 0; }
    protected function getCcoCount(): int {
        return CertificateOfOrigin::query()->where('status', '!=', 'draft')->count();
    }
    protected function getBlCount(): int {
        return BlCorrection::query()->where(function($q){ $q->whereNull('status')->orWhere('status','!=','draft'); })->count();
    }
    protected function getForm6Count(): int {
        return Form6Document::query()->where(function($q){ $q->whereNull('status')->orWhere('status','!=','draft'); })->count();
    }
    protected function getForm9Count(): int {
        return Form9Document::query()->where(function($q){ $q->whereNull('status')->orWhere('status','!=','draft'); })->count();
    }
    protected function getInvoicesCount(): int {
        return Invoice::query()->where(function($q){ $q->whereNull('status')->orWhere('status','!=','draft'); })->count();
    }

    // Finalized-only counters for three additional documents
    protected function getBoeFinalizedCount(): int {
        return BillOfExchange::query()->where('status', 'finalized')->count();
    }
    protected function getDclFinalizedCount(): int {
        return DocumentaryCollectionLetter::query()->where('status', 'finalized')->count();
    }
    protected function getSelfDeclarationFinalizedCount(): int {
        return SelfDeclaration::query()->where('status', 'finalized')->count();
    }
    protected function getCreditNotesCount(): int {
        return CreditDebitNote::query()->where('type', 'credit')->count();
    }
    protected function getDebitNotesCount(): int {
        return CreditDebitNote::query()->where('type', 'debit')->count();
    }
}
