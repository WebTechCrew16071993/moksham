<?php

namespace App\Filament\Resources\IndentResource\Pages;

use App\Filament\Resources\IndentResource;
use App\Models\CompanySetting;
use App\Models\HsnCode;
use App\Models\ImportCompany;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIndent extends CreateRecord
{
    protected static string $resource = IndentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Attach creator
        $data['user_id'] = auth()->id();
        // New indents should start as 'generated' (shipper signed)
        $data['status'] = 'generated';
        // Snapshot shipper from settings (assuming single settings row)
        $setting = CompanySetting::query()->first();
        if ($setting) {
            $data['shipper_name'] = $setting->company_name;
            $data['shipper_address'] = $setting->company_address;
            $data['shipper_city'] = $setting->company_city;
            $data['shipper_state'] = $setting->company_state;
            $data['shipper_zip'] = $setting->company_zip;
            $data['shipper_country'] = $setting->company_country;
            $data['shipper_email'] = $setting->company_email;
            $data['shipper_phone'] = $setting->company_phone;

            $data['shipper_bank_name'] = $setting->bank_name;
            $data['shipper_bank_address'] = $setting->bank_address;
            $data['shipper_bank_city'] = $setting->bank_city;
            $data['shipper_bank_state'] = $setting->bank_state;
            $data['shipper_bank_zip'] = $setting->bank_zip;
            $data['shipper_bank_country'] = $setting->bank_country;
            $data['shipper_bank_account_number'] = $setting->bank_account_number;
            $data['shipper_bank_swift_code'] = $setting->bank_swift_code;
            $data['shipper_bank_routing_number'] = $setting->bank_routing_number;

            $data['shipper_tax_iec_number'] = $setting->tax_iec_number;
            $data['shipper_tax_gstin'] = $setting->tax_gstin;
            $data['shipper_tax_pan_number'] = $setting->tax_pan_number;

            $data['shipper_signature_path'] = $setting->company_signed_logo; // path to signature image
        }

        // Snapshot consignee from selected ImportCompany
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

        // Snapshot HSN
        if (!empty($data['hsn_code_id'])) {
            $h = HsnCode::find($data['hsn_code_id']);
            if ($h) {
                $data['hsn_code'] = $h->code;
                $data['hsn_category'] = $h->category;
                $data['hsn_description'] = $h->description;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // After create, go back to the Indents listing page
        return static::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        // Keep only Create and Cancel actions
        return [
            $this->getCreateFormAction()->label('Create'),
            $this->getCancelFormAction(),
        ];
    }

    protected function hasCreateAnotherAction(): bool
    {
        // Hide the separate "Create & create another" action
        return false;
    }
}
