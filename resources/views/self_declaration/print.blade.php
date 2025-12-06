@php
    $setting = $setting ?? \App\Models\CompanySetting::first();

    // Prepare Address Lines
    $lines = [];
    if (!empty($setting->company_address)) $lines[] = $setting->company_address;
    if (!empty($setting->company_address2)) $lines[] = $setting->company_address2;
    $cityStatePinParts = [];
    if (!empty($setting->company_city)) $cityStatePinParts[] = $setting->company_city;
    if (!empty($setting->company_state)) $cityStatePinParts[] = $setting->company_state;
    if (!empty($setting->company_pincode)) $cityStatePinParts[] = $setting->company_pincode;
    if (!empty($cityStatePinParts)) $lines[] = implode(', ', $cityStatePinParts);
    if (!empty($setting->company_country)) $lines[] = $setting->company_country;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Self-Declaration Cum Undertaking Certificate</title>
    <style>
        @page { margin: 50px 50px; }

        * {
            font-size: 12px;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px !important;
            color: #000;
            line-height: 1.3;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            text-align: left;
        }

        /* Helper Classes */
        .bold { font-weight: bold; }
        .text-center { text-align: center; }
        .no-border { border: none !important; }
        .no-border td { border: none !important; }

        /* Main Title */
        .main-title {
            text-align: center;
            font-weight: bold;
            font-size: 20px !important;
            margin-bottom: 15px;
        }

        /* Top Grid Layout */
        .row-wrapper {
            width: 100%;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .col-left {
            width: 49%;
        }
        .col-right {
            width: 49%;
        }

        /* Spacer to align Right Importer Box with Left Importer Box */
        /* Adjusted height to account for the Invoice box on the left */
        .spacer {
            height: 35px;
            width: 100%;
        }

        /* Container Numbers Layout */
        .container-wrapper {
            width: 100%;
            margin-top: 15px;
            overflow: hidden;
        }

        /* Certification List (Roman Numerals) */
        .cert-table {
            margin-top: 15px;
            width: 100%;
            border: none;
        }
        .cert-table td {
            border: none;
            padding: 3px 0;
        }
        .roman-col {
            width: 40px;
            vertical-align: top;
        }

        /* Footer Elements */
        .declaration-text {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .signature-block {
            text-align: right;
            margin-bottom: 30px;
            font-weight: normal;
        }
        .date-block {
            text-align: left;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="main-title">Self-Declaration Cum Undertaking Certificate</div>

    {{-- TOP SECTION --}}
    <table style="border: none; margin-bottom: 15px;">
        <tr>
            <td style="border: none; padding: 0 5px 0 0; width: 50%;">
                <table style="margin-bottom: 10px; width: 100%;">
                    <tr>
                        <td width="70%" class="bold">Certificate Number :</td>
                        <td width="30%">{{ $doc->certificate_number ?? '' }}</td>
                    </tr>
                </table>

                <table style="margin-bottom: 10px; width: 100%;">
                    <tr>
                        <td width="70%" class="bold">Invoice Number :</td>
                        <td width="30%">{{ $doc->invoice_no ?? '' }}</td>
                    </tr>
                </table>

                <table style="margin-bottom: 0px; width: 100%;">
                    <tr>
                        <td class="bold">Details of Importer :</td>
                    </tr>
                    <tr>
                        <td style="height: 110px;">
                            {{ $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC' }}<br>
                            {!! nl2br(e(implode("\n", $lines))) !!}
                        </td>
                    </tr>
                </table>

            </td>
            <td style="border: none; padding: 0 0 0 5px; width: 50%;">
                <table style="margin-bottom: 10px; width: 100%;">
                    <tr>
                        <td width="70%" class="bold">Date of Issue :</td>
                        <td width="30%">{{ \Illuminate\Support\Carbon::parse($doc->issue_date ?? now())->format('d/m/Y') }}</td>
                    </tr>
                </table>

                <table style="margin-bottom: 0px; width: 100%;">
                    <tr>
                        <td class="bold">Details of Importer :</td>
                    </tr>
                    <tr>
                        <td style="height: 145px;">
                            @if(optional($doc->invoice)->consignee)
                                @php($c = $doc->invoice->consignee)
                                @if(!empty($c->name))
                                    {{ $c->name }}<br>
                                @endif
                                @if(!empty($c->address))
                                    {!! nl2br(e($c->address)) !!}<br>
                                @endif
                                @if(!empty($c->gstin ?? $c->gst_no))
                                    GSTIN : {{ $c->gstin ?? $c->gst_no }}<br>
                                @endif
                                @if(!empty($c->iec_code ?? $c->ice_code))
                                    IEC : {{ $c->iec_code ?? $c->ice_code }}<br>
                                @endif
                                @if(!empty($c->pan))
                                    PAN : {{ $c->pan }}<br>
                                @endif
                                @if(!empty($c->email))
                                    E-mail : {{ $c->email }}
                                @endif
                            @else
                                {!! nl2br(e($doc->importer_details ?? '')) !!}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="margin-bottom:15px">
    <table>
        <tr>
            <td colspan="3" class="bold">Details of Import :</td>
        </tr>
        <tr>
            <th width="10%" class="text-center">Sr. No.</th>
            <th width="70%" class="text-center">Description of Goods</th>
            <th width="20%" class="text-center">Total Quantity</th>
        </tr>
        <tr>
            <td class="text-center">01</td>
            <td class="text-center">{{ $doc->goods_description ?? 'WASTEPAPER OCC 11 HS CODE : 47079000' }}</td>
            <td class="text-center">{{ number_format((float)($doc->total_quantity_kgs ?? 0), 3) }} KGS</td>
        </tr>
    </table>
    </div>

    <table style="border: none; margin-bottom: 15px;">
        <tr>
            <td style="border: none; padding: 10px 10px 0 0; width: 50%;">
                <table>
                    <tr>
                        <td colspan="3" class="bold">Container Numbers :</td>
                    </tr>
                    <tr>
                        <th width="15%" class="text-center">Sr. No.</th>
                        <th width="50%" class="bold">Container Numbers</th>
                        <th width="35%" class="text-center bold">Quantity</th>
                    </tr>
                    @for($i = 0; $i < 6; $i++)
                        <tr>
                            <td class="text-center">{{ sprintf('%02d', $i + 1) }}.</td>
                            <td>{{ isset($col1[$i]) ? ($col1[$i]['container_no'] ?? '') : '' }}</td>
                            <td class="text-center">{{ isset($col1[$i]) ? ($col1[$i]['qty'] ?? '') : '' }}</td>
                        </tr>
                    @endfor
                </table>
            </td>
            <td style="border: none; padding: 10px 0 0 10px; width: 50%;">
                <table>
                    <tr>
                        <td colspan="3" class="bold">Container Numbers :</td>
                    </tr>
                    <tr>
                        <th width="15%" class="text-center">Sr. No.</th>
                        <th width="50%" class="bold">Container Numbers</th>
                        <th width="35%" class="text-center bold">Quantity</th>
                    </tr>
                    @for($i = 0; $i < 6; $i++)
                        <tr>
                            <td class="text-center">{{ sprintf('%02d', $i + 7) }}.</td>
                            <td>{{ isset($col2[$i]) ? ($col2[$i]['container_no'] ?? '') : '' }}</td>
                            <td class="text-center">{{ isset($col2[$i]) ? ($col2[$i]['qty'] ?? '') : '' }}</td>
                        </tr>
                    @endfor
                </table>
            </td>
        </tr>
    </table>
     
    {{-- CERTIFICATION TEXT --}}
    <div style="margin-top: 10px;font-size:12px;">
        After due inspection i/we hereby certify that
    </div>

    <table class="cert-table" style="margin:5px 0 0 0;">
        <tr>
            <td class="roman-col">V.</td>
            <td>The consignment is actually waste paper as per the internationally acceptable parameters for such material.</td>
        </tr>
        <tr>
            <td class="roman-col">VI.</td>
            <td>There is no putrefiable organic matter in this consignment.</td>
        </tr>
        <tr>
            <td class="roman-col">VII.</td>
            <td>The approximate content of non paper material is less than 5%.</td>
        </tr>
        <tr>
            <td class="roman-col">VIII.</td>
            <td>No municipal solid waste or Hazardous waste is part of this consignment.</td>
        </tr>
    </table>

    <div class="declaration-text">
        I/We hereby declare that the particulars and statements made in this certificate are true and correct and nothing has.
    </div>

    {{-- FOOTER --}}
    <div class="signature-block">
        {{ $setting->company_signature_text ?? $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC.' }}
    </div>

    <div>
        <b>DATE:</b> {{ \Illuminate\Support\Carbon::parse($doc->issue_date ?? now())->format('d-M-y') }}
    </div>

</body>
</html>
