<?php

namespace App\Filament\Resources\IndentResource\Pages;

use App\Filament\Resources\IndentResource;
use App\Models\Indent;
use App\Models\HsnCode;
use App\Models\ImportCompany;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditIndent extends EditRecord
{
    protected static string $resource = IndentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If consignee changed, refresh snapshot
        if (!empty($data['consignee_id'])) {
            $c = ImportCompany::find($data['consignee_id']);
            if ($c) {
                $data['consignee_name'] = $c->name;
                $data['consignee_address'] = $c->address;
                $data['consignee_city'] = $c->city;
                $data['consignee_state'] = $c->state;
                $data['consignee_zip'] = $c->zip;
                $data['consignee_country'] = $c->country;
                $data['consignee_email'] = $c->email;
                $data['consignee_phone'] = $c->phone_number;
                $data['consignee_iec'] = $c->iec;
                $data['consignee_gstin'] = $c->gstin;
                $data['consignee_pan'] = $c->pan;
                $data['consignee_bank_name'] = $c->bank_name;
                $data['consignee_bank_address'] = $c->bank_address;
                $data['consignee_bank_city'] = $c->bank_city;
                $data['consignee_bank_state'] = $c->bank_state;
                $data['consignee_bank_zip'] = $c->bank_zip;
                $data['consignee_bank_country'] = $c->bank_country;
                $data['consignee_bank_account_number'] = $c->bank_account_number;
                $data['consignee_bank_swift_code'] = $c->bank_swift_code;
                $data['consignee_bank_ifsc_code'] = $c->bank_ifsc_code;
            }
        }

        // If HSN changed, refresh snapshot
        if (!empty($data['hsn_code_id'])) {
            $h = HsnCode::find($data['hsn_code_id']);
            if ($h) {
                $data['hsn_code'] = $h->code;
                $data['hsn_category'] = $h->category?->name;
                $data['hsn_description'] = $h->description;
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('upload_signed_pdf')
                ->label('Upload Signed PDF')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn () => $this->record && $this->record->status === 'signed_by_consignee')
                ->form([
                    Forms\Components\FileUpload::make('signed_pdf')
                        ->label('Signed PDF (Consignee)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    /** @var Indent $record */
                    $record = $this->record;
                    /** @var TemporaryUploadedFile|string|null $file */
                    $file = $data['signed_pdf'] ?? null;
                    if ($file instanceof TemporaryUploadedFile) {
                        $filename = $record->indent_no . '_consignee.pdf';
                        Storage::disk('public')->putFileAs('indents', $file, $filename);
                        $record->consignee_signed_pdf_path = 'indents/' . $filename;
                        $record->status = 'completed';
                        $record->save();
                        Notification::make()
                            ->title('Signed PDF uploaded and status marked as completed')
                            ->success()
                            ->send();
                    }
                })
                ->after(function () {
                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
