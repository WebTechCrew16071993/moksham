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

        // Labels exactly matching the image provided
        if (!empty($consignee->gstin ?? $consignee->gst_no)) {
            $details['GSTIN. :'] = $consignee->gstin ?? $consignee->gst_no;
        }

        if (!empty($consignee->ice_code)) {
            $details['IEC CODE :'] = $consignee->ice_code;
        }

        if (!empty($consignee->pan)) {
            $details['PAN NO. :'] = $consignee->pan;
        }

        if (!empty($consignee->email)) {
            $details['E-mail :'] = $consignee->email;
        }
    }
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill of Exchange</title>
    <style>
        @page { margin: 100px 60px; }
        
        body { 
            font-family: 'Times New Roman', serif; 
            font-size: 15px !important; 
            color: #000; 
            line-height: 1.15;
        }

        /* The main outer border box */
        .box { 
            border: 1px solid #000; 
            padding: 25px; 
            min-height: 400px;
            max-width:600px;
            margin: auto;
        }

        /* Layout Utilities */
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .bold { font-weight: bold; }
        .text-right { text-align: right; }
        
        /* Spacing helpers */
        .mb-1 { margin-bottom: 5px; }
        .mb-4 { margin-bottom: 25px; }
        
        /* Middle Content Paragraph */
        .content-para {
            margin-top: 40px;
            margin-bottom: 25px;
            line-height: 1.5; /* Increased line height to match the airy look of the image */
        }

        /* Signature Section specific styling */
        .signature-cell {
            vertical-align: middle; 
            padding-left: 20px;
        }
        .signature-block {
            width: 100%;
            text-align: center;
            margin-top: 30px; /* Pushes it down to align nicely with address */
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 60px; /* Gap for signature */
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            text-transform: uppercase;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="box">

        {{-- HEADER SECTION --}}
        <table style="padding-bottom: 25px;">
            <tr>
                {{-- Left: Amount --}}
                <td style="width: 50%;">
                    <span class="bold">AMOUNT :</span> USD {{ number_format($boe->amount_usd, 2) }}
                </td>

                {{-- Right: Ref, Date, Place --}}
                <td style="width: 50%;">
                    <table style="width: auto; float: right;">
                        <tr>
                            <td><span class="bold">REF. No. :</span> {{ $boe->ref_no }}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 2px;"><span class="bold">DATE :</span> {{ \Illuminate\Support\Carbon::parse($boe->issue_date ?? now())->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 2px;"><span class="bold">PLACE OF ISSUE :</span> {{ $boe->place_of_issue }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- MIDDLE TEXT SECTION --}}
        <div style="font-size:16px !important" class="content-para">
            AT DP / (AT SIGHT) pay against this <span class="bold">sole</span> bill of exchange to the order of<br>
            {{ $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC' }}({{ $setting?->company_country ?? 'USA' }})<br>
            the sum of (<span class="bold">USD {{ ucwords(strtolower($boe->amount_in_words)) }}</span>)<br>
            Value received & charge the same to account of
        </div>

        {{-- DRAWEE AND SIGNATURE SECTION --}}
        <table>
            <tr>
                {{-- Left Column: DRAWEE details --}}
                <td style="width: 55%;">
                    <div class="bold mb-1">DRAWEE :</div>
                    
                    @if($displayName)
                        <div class="mb-0" style="font-size:14px !important">{{ $displayName }}</div>
                    @endif

                    @if($displayAddress)
                        <div class="mb-0" style="font-size:14px !important">{!! nl2br(e($displayAddress)) !!}</div>
                    @endif

                    @if(!empty($details))
                        @foreach($details as $label => $value)
                            <div style="font-size:14px !important">{{ $label }} {{ $value }}</div>
                        @endforeach
                    @endif
                </td>

                {{-- Right Column: SIGNATURE details --}}
                <td class="signature-cell" style="width: 45%;">
                    <div class="signature-block">
                        <div class="bold" style="text-transform: uppercase; font-size: 14px;">{{ $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC.' }}</div>
                        
                        <div class="signature-line"></div>
                        
                        <div style="margin-top: 5px;">(Authorised Signature)</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- FOOTER SECTION --}}
        <div class="footer" style="font-size:13px !important">
            FOR COLLECTION ONLY - PAY TO THE ORDER OF ANY BANK - WITHOUT RECOURSE - ENDORSED BY {{ $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC' }}.
        </div>

    </div>
</body>
</html>