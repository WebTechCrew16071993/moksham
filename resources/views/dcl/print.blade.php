@php($setting = $setting ?? \App\Models\CompanySetting::first())
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documentary Collection Letter</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
        .title { font-size: 16px; font-weight: bold; margin:20px 0; }
        .muted { color:#555; }
        .box { border:1px solid #000; padding:14px; }
        .mt-2{ margin-top:8px; } .mt-3{ margin-top:12px; } .mb-2{ margin-bottom:8px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <img src="{{ public_path('images/moksham-logo.png') }}" style="height:50px;">
            <div class="muted">{{ $setting?->company_address }}<br>{{ trim(($setting?->company_city.' '.$setting?->company_state.' '.$setting?->company_zip)) }}<br>{{ $setting?->company_country }}</div>
            @if (isset($setting?->company_email))
                <div>Email : {{ $setting?->company_email }}</div>
            @endif 
        </div>
        <div class="muted" style="text-align:right">
            <div>Documents for Invoice No.: {{ $letter->invoice?->invoice_no }}</div>
            <div>MOKSHAM EXPORT IMPORT LLC.</div>
            <div>SA's Contract No.: {{ $letter->sa_contract_no }}</div>
        </div>
    </div>

    <div class="muted" style="text-align:right">Date: {{ \Illuminate\Support\Carbon::parse($letter->letter_date ?? now())->format('M d,Y') }}</div>

    <div class="title">RE : Documentary collection on our customer {{ $letter->consignee?->name }} in {{ $letter->consignee?->country }}.</div>

    <p>Dear Sirs,</p>
    <p>Please find attached the whole set of documents for our a/m customer under our invoice number <strong>{{ $letter->invoice?->invoice_no }}</strong> for <strong>USD {{ number_format($letter->amount_usd,2) }}</strong></p>

    <p>Please present these documents for acceptance/collection through:</p>

    <div class="">
        @if($letter->consignee && ($letter->consignee->bank_name || $letter->consignee->bank_account_number))
            <strong>{{ $letter->consignee->bank_name }}</strong>
            @if($letter->consignee->bank_address)
                <br>{{ $letter->consignee->bank_address }}
                @if($letter->consignee->bank_city)
                    , {{ $letter->consignee->bank_city }}
                    @if($letter->consignee->bank_state)
                        , {{ $letter->consignee->bank_state }}
                    @endif
                @endif
            @endif
            {{-- @if($letter->consignee->bank_account_number)
                <br>Account #: {{ $letter->consignee->bank_account_number }}
            @endif
            @if($letter->consignee->bank_swift_code)
                <br>SWIFT: {{ $letter->consignee->bank_swift_code }}
            @endif --}}
        @else
            {!! nl2br(e($letter->notes ?: 'STATE BANK OF INDIA\nWHOLESALE BANKING OPERATIONS,\n(Importer bank details, if any)')) !!}
        @endif
    </div>

    <p class="mt-3">Please note all bank’s commissions and charges outside USA are for drawee’s account.</p>
    <p>Documents not to be released prior previous acceptance and/or full payment of our above draft(s) and/or invoice.</p>

    <div class="mt-3" style="text-align:right">
        <div>Yours faithfully,</div>
        <div class="title">{{ $setting->company_signature_text ?? $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC' }}</div>
        <div class="mt-2">__________________________</div>
        <div class="muted">
            {{ $letter->signer_name ?? '' }}
            @if($letter->signer_name && $letter->signer_title)
                ({{ $letter->signer_title }})
            @else
                (Authorised Signature)
            @endif
        </div>
    </div>
</body>
</html>
