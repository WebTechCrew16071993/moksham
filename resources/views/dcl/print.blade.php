@php($setting = $setting ?? \App\Models\CompanySetting::first())
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documentary Collection Letter</title>
    <style>
        @page {
            margin: 50px 60px 50px 60px; /* Margins */
        }
        body {
            font-family: "Times New Roman", serif;
            font-size: 14px !important;
            color: #000;
            line-height: 1.3;
            margin: 0;
        }
        
        /* Layout Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }
        td {
            vertical-align: top;
            padding: 0;
        }

        /* Helpers */
        .bold { font-weight: bold; }
        .text-right { text-align: right; }
        
        /* Header Specifics */
        .header-right {
            text-align: right;
            padding-top: 50px; 
        }
        
        /* Spacing utilities */
        .mb-1 { margin-bottom: 5px; }
        .mt-4 { margin-top: 40px; }
        
        /* Sections */
        .subject-line {
            font-weight: bold;
            margin-top: 40px;
            margin-bottom: 20px;
        }
        
        .bank-details {
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .footer {
            margin-top: 60px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <table>
        <tr>
            <td width="65%">
                <img src="{{ public_path('images/moksham-logo.png') }}" style="height:56px; display:block; margin-bottom: 8px;">
                
                <div style="font-size:12px; text-transform: uppercase;">{{ $setting?->company_address }}</div>
                <div style="font-size:12px; text-transform: uppercase;">{{ trim(($setting?->company_city.' '.$setting?->company_state.' '.$setting?->company_zip)) }} {{ $setting?->company_country }}</div>
                @if (isset($setting?->company_email))
                    <div style="font-size:12px; text-transform: uppercase;">E-MAIL : {{ strtoupper($setting?->company_email) }}</div>
                @endif
            </td>

            <td width="35%" class="header-right">
                <div style="font-size:12px; text-align: left" class="bold">Documents for Invoice No. : {{ $letter->invoice?->invoice_no }}</div>
                <div style="font-size:12px; text-align: left" class="bold">{{ $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC.' }}</div>
                <div style="font-size:12px; text-align: left" class="bold">SA's Contract No. : {{ $letter->sa_contract_no }}</div>
        
                <div style="font-size:12px; padding-top:30px; text-align: left;"><span class="bold">Date:</span> {{ \Illuminate\Support\Carbon::parse($letter->letter_date ?? now())->format('M d,Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="subject-line" style="font-size:16px;">
        RE : Documentary collection on our customer {{ strtoupper($letter->consignee?->name) }} In {{ strtoupper($letter->consignee?->country ?? 'INDIA') }}
    </div>

    <div class="mb-1 bold" style="font-size:16px;">Dear Sirs,</div>

    <p style="font-size:16px;">
        Please find attached the whole set of documents for our a/m customer under our invoice number <br>
        <span class="bold">{{ $letter->invoice?->invoice_no }}</span> for <span class="bold">USD {{ number_format($letter->amount_usd, 2) }}</span>
    </p>

    <p style="font-size:16px;">Please present these documents for acceptance/collection through:</p>

    <div class="bank-details" style="font-size:16px;">
        @if($letter->consignee && ($letter->consignee->bank_name || $letter->consignee->bank_account_number))
            <div class="bold">{{ $letter->consignee->bank_name }}</div>
            @if($letter->consignee->bank_address)
                <div>{{ $letter->consignee->bank_address }}</div>
                @if($letter->consignee->bank_city)
                    <div>
                        {{ $letter->consignee->bank_city }}
                        @if($letter->consignee->bank_state)
                            , {{ $letter->consignee->bank_state }}
                        @endif
                    </div>
                @endif
            @endif
        @else
            <div style="font-size:16px;" class="bold">STATE BANK OF INDIA</div>
            <div style="font-size:16px;">WHOLESALE BANKING OPERATIONS,</div>
            <div style="font-size:16px;">Sahakari Jin Road Branch, Uma Complex,</div>
            <div style="font-size:16px;">Plot No 9-10, Rajmehal Society, Sahkari Jin Road,</div>
            <div style="font-size:16px;">Himatnagar - 383 001 GUJARAT – (INDIA)</div>
        @endif
    </div>

    <p style="font-size:16px;margin-top: 20px;">Please note all bank’s commissions and charges outside USA are for drawee’s account.</p>
    
    <p style="font-size:16px;">Documents not to be released prior previous acceptance and/or full payment of our above draft (s) <br> and/or invoice.</p>

    <table>
        <tr>
            <td style="width: 70%;">

            </td>
            <td>
                <div class="footer" style="text-align: center;font-size:16px;">
                    <div>Yours faithfully,</div>
                    <div class="bold" style="font-size:16px;margin-top: 2px;text-transform: uppercase;">{{ $setting->company_signature_text ?? $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC.' }}</div>
                    
                    <div style="height: 60px;"></div>
                    
                    <div>{{ $letter->signer_name ?? 'Mr. NIK PATEL' }}({{ $letter->signer_title ?? ' C.O.O.' }})</div>
                </div>
            </td>
        </tr>
    </table>

    <div style="font-size:16px;text-decoration: underline;position: fixed;bottom:120px;left:0;">Encl. Ment</div>

</body>
</html>