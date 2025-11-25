@php
    // Load company settings
    $setting = $setting ?? \App\Models\CompanySetting::first();

    // Default DRAWEE values
    $displayName = $boe->drawee_name ?? '';
    $displayAddress = $boe->drawee_address ?? '';
    $details = [];

    // Get consignee data if available
    $consignee = $boe->invoice->consignee ?? null;

    if ($consignee) {
        if (empty($displayName)) {
            $displayName = $consignee->name ?? '';
        }

        if (empty($displayAddress)) {
            $displayAddress = $consignee->address ?? '';
        }

        if (!empty($consignee->gstin ?? $consignee->gst_no)) {
            $details['GSTIN'] = $consignee->gstin ?? $consignee->gst_no;
        }

        if (!empty($consignee->pan)) {
            $details['PAN'] = $consignee->pan;
        }

        if (!empty($consignee->ice_code)) {
            $details['ICE'] = $consignee->ice_code;
        }

        if (!empty($consignee->email)) {
            $details['Email'] = $consignee->email;
        }
    }
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill of Exchange</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        .muted { color:#555; }
        .row { display:flex; justify-content:space-between; }
        .box { border:2px solid #000; padding:16px; }
        .title { font-size: 14px; font-weight: bold; margin-top: 12px; }
        .mt-2 { margin-top:8px; }
        .mt-3 { margin-top:12px; }
        .underline { text-decoration: underline; }
    </style>
</head>

<body>
    <div class="box">

        {{-- HEADER ROW --}}
        <div class="row">
            <div><strong>AMOUNT :</strong> USD {{ number_format($boe->amount_usd, 2) }}</div>
            <div>
                <div><strong>REF. No. :</strong> {{ $boe->ref_no }}</div>
                <div><strong>DATE :</strong> {{ \Illuminate\Support\Carbon::parse($boe->issue_date ?? now())->format('d/m/Y') }}</div>
                <div><strong>PLACE OF ISSUE :</strong> {{ $boe->place_of_issue }}</div>
            </div>
        </div>

        {{-- INTRO TEXT --}}
        <div class="mt-3">
            AT DP / (AT SIGHT) pay against this <span class="underline">sole</span> bill of exchange to the order of
            <strong>{{ $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC' }} ({{ $setting?->company_country ?? 'USA' }})</strong>
            the sum of (<strong>{{ strtoupper($boe->amount_in_words) }}</strong>)
            Value received & charge the same to account of
        </div>

        <div class="row mt-3">

            {{-- LEFT SIDE: DRAWEE --}}
            <div style="width:48%">
                <div class="title">DRAWEE :</div>

                @if($displayName || $displayAddress || !empty($details))

                    @if($displayName)
                        <strong>{{ $displayName }}</strong><br>
                    @endif

                    @if($displayAddress)
                        {!! nl2br(e($displayAddress)) !!}
                    @endif

                    @if(!empty($details))
                        <div style="margin-top: 8px;">
                            <table style="width:100%; border-collapse: collapse;">
                                @foreach($details as $label => $value)
                                    <tr>
                                        <td style="width: 80px; padding: 2px 0;"><strong>{{ $label }}:</strong></td>
                                        <td style="padding: 2px 0;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif

                @endif
            </div>

            {{-- RIGHT SIDE --}}
            <div style="width:48%; text-align:right">
                <div class="title">{{ $setting->company_signature_text ?? 'MOKSHAM EXPORT IMPORT LLC' }}</div>
                <div class="mt-3">__________________________</div>
                <div class="muted">(Authorised Signature)</div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="mt-3 muted" style="font-size:11px;">
            FOR COLLECTION ONLY - PAY TO THE ORDER OF ANY BANK - WITHOUT RECOURSE - ENDORSED BY
            {{ $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC' }}.
        </div>

    </div>
</body>
</html>
