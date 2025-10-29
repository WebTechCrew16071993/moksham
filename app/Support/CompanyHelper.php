<?php

namespace App\Support;

use App\Models\CompanySetting;

class CompanyHelper
{
    public static function fullAddress(?CompanySetting $s): string
    {
        if (!$s) {
            return '';
        }

        $parts = array_filter([
            trim((string) $s->company_address),
            trim(implode(', ', array_filter([
                (string) $s->company_city,
                (string) $s->company_state,
                (string) $s->company_zip,
            ]))),
            trim((string) $s->company_country),
        ]);

        $line = implode(', ', array_filter($parts));

        $contact = array_filter([
            $s->company_email ? 'Email: '.$s->company_email : null,
            $s->company_phone ? 'Phone: '.$s->company_phone : null,
        ]);

        $contactLine = implode(' | ', $contact);

        return trim($line . ($contactLine ? ' | '.$contactLine : ''));
    }
}
