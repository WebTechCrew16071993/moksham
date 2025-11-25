@php

$setting = $setting ?? \App\Models\CompanySetting::first();

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
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            color: #000;
            margin: 20px;
            line-height: 1.4;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px;
        }
        th, td { 
            border: 1px solid #000; 
            padding: 8px; 
            vertical-align: top;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .noborder, .noborder td, .noborder tr { 
            border: none; 
        }
        .section-title { 
            text-align: center; 
            font-weight: bold; 
            font-size: 16px; 
            margin-bottom: 15px;
            text-decoration: underline;
        }
        .company-name {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-top: 30px;
            margin-bottom: 20px;
        }
        .declaration-text {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .two-column-container {
            width: 100%;
            margin-top: 10px;
        }
        .column-left {
            width: 48%;
            float: left;
            margin-right: 2%;
        }
        .column-right {
            width: 48%;
            float: right;
            margin-left: 2%;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="section-title">Self-Declaration Cum Undertaking Certificate</div>

    {{-- Certificate and Date Info --}}
    <table>
        <tr>
            <td width="50%"><strong>Certificate Number :</strong> {{ $doc->certificate_number ?? 'N/A' }}</td>
            <td width="50%"><strong>Date of Issue :</strong> {{ \Illuminate\Support\Carbon::parse($doc->issue_date ?? now())->format('d/m/Y') }}</td>
        </tr>
    </table>
    
    <table>
        <tr>
            <td width="50%">
                <strong>Invoice Number :</strong><br>
                {{ $doc->invoice_no ?? 'N/A' }}
            </td>
        </tr>
    </table>

    {{-- Invoice and Importer Details --}}
    <table>
        <tr>
            <td width="50%">
                <strong>Details of Exporter :</strong><br>
                <strong>{{ $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC' }}</strong><br>
             
                {!! nl2br(e(implode("\n", $lines))) !!}
            </td>
            <td width="50%" style="vertical-align: top;">
                <strong>Details of Importer :</strong><br>
                @if(optional($doc->invoice)->consignee)
                    @php($c = $doc->invoice->consignee)
                    @if(!empty($c->name))
                        <strong>{{ $c->name }}</strong><br>
                    @endif
                    @if(!empty($c->address))
                        {!! nl2br(e($c->address)) !!}<br>
                    @endif
                    @if(!empty($c->gstin ?? $c->gst_no))
                        <strong>GSTIN:</strong> {{ $c->gstin ?? $c->gst_no }}<br>
                    @endif
                    @if(!empty($c->iec_code ?? $c->ice_code))
                        <strong>IEC:</strong> {{ $c->iec_code ?? $c->ice_code }}<br>
                    @endif
                    @if(!empty($c->pan))
                        <strong>PAN:</strong> {{ $c->pan }}<br>
                    @endif
                    @if(!empty($c->email))
                        <strong>E-mail:</strong> {{ $c->email }}
                    @endif
                @else
                    {!! nl2br(e($doc->importer_details ?? 'N/A')) !!}
                @endif
            </td>
        </tr>
        </table>

    {{-- Details of Import --}}
    <table>
        <tr>
            <th colspan="3" style="text-align: center;">Details of Import :</th>
        </tr>
        <tr>
            <th width="10%" style="text-align: center;">Sr. No.</th>
            <th width="60%">Description of Goods</th>
            <th width="30%">Total Quantity</th>
        </tr>
        <tr>
            <td style="text-align: center;">01</td>
            <td>{{ $doc->goods_description ?? 'WASTEPAPER OCC 11 HS CODE : 47079000' }}</td>
            <td>{{ number_format((float)($doc->total_quantity_kgs ?? 0), 3) }} KGS</td>
        </tr>
    </table>

    {{-- Container Numbers in Two Columns --}}
    <div class="two-column-container clearfix">
        <div class="column-left">
            <table>
                <tr>
                    <th colspan="3" style="text-align: center;">Container Numbers :</th>
                </tr>
                <tr>
                    <th width="15%" style="text-align: center;">Sr. No.</th>
                    <th width="55%">Container Numbers</th>
                    <th width="30%">Quantity</th>
                </tr>
                @for($i = 0; $i < 6; $i++)
                    <tr>
                        <td style="text-align: center;">{{ sprintf('%02d', $i + 1) }}.</td>
                        <td>{{ isset($col1[$i]) ? ($col1[$i]['container_no'] ?? '') : '' }}</td>
                        <td>{{ isset($col1[$i]) ? ($col1[$i]['qty'] ?? '') : '' }}</td>
                    </tr>
                @endfor
            </table>
        </div>
        
        <div class="column-right">
            <table>
                <tr>
                    <th colspan="3" style="text-align: center;">Container Numbers :</th>
                </tr>
                <tr>
                    <th width="15%" style="text-align: center;">Sr. No.</th>
                    <th width="55%">Container Numbers</th>
                    <th width="30%">Quantity</th>
                </tr>
                @for($i = 0; $i < 6; $i++)
                    <tr>
                        <td style="text-align: center;">{{ sprintf('%02d', $i + 7) }}.</td>
                        <td>{{ isset($col2[$i]) ? ($col2[$i]['container_no'] ?? '') : '' }}</td>
                        <td>{{ isset($col2[$i]) ? ($col2[$i]['qty'] ?? '') : '' }}</td>
                    </tr>
                @endfor
            </table>
        </div>
    </div>

    <div style="clear: both; margin-top: 15px;">
        <p style="margin-bottom: 10px;"><strong>After due inspection i/we hereby certify that</strong></p>
        <table class="noborder">
            <tr>
                <td style="width: 5%; padding: 4px 0;">V.</td>
                <td style="padding: 4px 0;">The consignment is actually waste paper as per the internationally acceptable parameters for such material.</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">VI.</td>
                <td style="padding: 4px 0;">There is no putrefiable organic matter in this consignment.</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">VII.</td>
                <td style="padding: 4px 0;">The approximate content of non paper material is less than 5%.</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">VIII.</td>
                <td style="padding: 4px 0;">No municipal solid waste or Hazardous waste is part of this consignment.</td>
            </tr>
        </table>
    </div>

    <div class="declaration-text">
        I/We hereby declare that the particulars and statements made in this certificate are true and correct and nothing has.
    </div>

    <div class="company-name">
        {{ $setting->company_signature_text ?? $setting->company_name ?? 'MOKSHAM EXPORT IMPORT LLC.' }}
    </div>

    <div style="margin-top: 20px;">
        <strong>DATE:</strong> {{ \Illuminate\Support\Carbon::parse($doc->issue_date ?? now())->format('d-M-y') }}
    </div>
</body>
</html>