<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Indent #{{ $indent->indent_no }}</title>
    <style>
        @page {
            size: A4;
            margin: 100px 60px 100px 60px;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 14px !important; /* Set default font size to 12px */
            line-height: 1.15;
        }
        
        /* Header & Footer Positioning */
        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 120px;
            text-align: center;
        }
        footer {
            position: fixed;
            bottom: -130px;
            left: -60px;
            width: calc(100% + 120px);
            right: 0;
            height: 90px;
            text-align: center;
            font-size: 14px;
            padding-bottom: 5px;
        }

        /* Helper Classes */
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .text-red { color: #dd2b1c; }

        .text-red-in b, .text-red-in strong { color: #dd2b1c; font-size:16px !important; }
        .underline { text-decoration: underline; }
        
        /* Main Grid Table */
        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 14px !important;
        }
        table.grid > tbody > tr > th,
        table.grid > tbody > tr > td {
            border: 1px solid #000;
            padding: 2px 8px; /* Adjusted padding for tighter look */
            vertical-align: top;
            text-align: left;
        }
        
        /* Column Widths for the main data section */
        .col-label { padding: 4px 8px !important; width: 30%; }
        .col-value { padding: 4px 8px 4px 12px !important; width: 70%; }

        /* Nested Tables for Split Rows (Shipper/Consignee) 
           This ensures the vertical divider is exactly in the middle (50%) 
           regardless of the top section's column width. */
        table.nested {
            width: 100%;
            border-collapse: collapse;
            margin: -6px -9px; /* Negative margin to fill the parent cell perfectly */
            width: calc(100% + 18px);
        }
        table.nested td {
            border: none;
            border-right: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
            width: 50%;
        }
        table.nested td:last-child {
            border-right: none;
        }

        /* Section Titles */
        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 14px !important;
            text-decoration: underline;
        }
        
        /* Page Numbers */
        .pagenum:before { content: counter(page); }
        
        /* Signature Table */
        .signature-table { width: 100%; margin-top: 40px; border: none; }
        .signature-table td { border: none; vertical-align: bottom; }

        .inner-m0 > * {
            padding-left:18px !important;
            margin: 0 !important;
            margin-top: 2px !important;
            width: 100% !important;
        }

        .inner-ul3 ol,
        .inner-ul2 ol,
        .inner-ul1 ol {
            counter-reset: item;
            list-style: none !important;
            padding-left: 0 !important;   
        }

        .inner-ul3 li,
        .inner-ul2 li,
        .inner-ul1 li {
            counter-increment: item;
            position: relative;
            padding-left: 24px;
            list-style: none !important;   
            padding-bottom: 2px;
        }

        .inner-ul3 li,
        .inner-ul2 li {
            padding-left: 26px;
            padding-bottom: 5px;
        }

        .inner-ul1 li::before {
            content: "(" counter(item) "). ";
            position: absolute;
            left: 0;
        }

        .inner-ul2 li::before {
            content: counter(item) "). ";
            position: absolute;
            left: 0;
        }

        .inner-ul3 li::before {
            content: counter(item) ". ";
            position: absolute;
            left: 0;
        }

        .page-number {
            position: fixed;
            bottom: 10px;
            right: 10px;
        }
    </style>
</head>
<body>

<header>
    <div>
        <img src="{{ public_path('images/moksham-logo.png') }}" alt="Moksham Logo" style="height: 66px;object-fit: contain;">
    </div>
</header>



<div class="content">
    
    <div style="margin-bottom: 15px;">
        <div style="margin-bottom: 0px;">Indent No.: {{ $indent->indent_no }}</div>
        <div>Date: {{ optional($indent->indent_date)->format('m/d/Y') }}</div>
    </div>

    <div style="margin-bottom: 15px;">
        <div>To,</div>
        <div class="font-bold uppercase" style="margin-top: 2px;">{{ $indent->consignee_name }}</div>
        <div>{{ $indent->consignee_address }},</div>
        <div>{{ $indent->consignee_city }}, {{ $indent->consignee_state }},</div>
        <div>{{ $indent->consignee_country }}, {{ $indent->consignee_zip }}.</div>
    </div>

    <div class="font-bold" style="margin-bottom: 15px;">
        Kind Attn. : <span class="font-bold">{{ $indent->kind_attention }}</span>
    </div>

    <p style="text-align: justify; margin-top: 0;margin-bottom: 10px;">
        Dear Sir, <br />
        Reference to our discussion and confirmation did {{ optional($indent->indent_date)->format('m/d/Y') }},
        we are pleased to confirm below order <br /> for M/s, <strong>{{ $indent->consignee_name }}</strong>,
        {{ $indent->consignee_city }}, {{ $indent->consignee_state }}, <span style="text-transform: uppercase;">{{ $indent->consignee_country }}.</span> 
    </p>

    <table class="grid grid2">
        <tr>
            <td class="col-label">Quality</td>
            <td class="col-value">
                <div class="font-bold">{{ $indent->quality }}</div>
                <div>HS Code: {{ $indent->hsn_code }}</div>
            </td>
        </tr>
        <tr>
            <td class="col-label">Origin</td>
            <td class="col-value" style="text-transform: uppercase;">{{ $indent->origin }}</td>
        </tr>
        <tr>
            <td class="col-label">Quantity</td>
            <td class="col-value font-bold">{{ $indent->quantity }}</td>
        </tr>
        <tr>
            <td class="col-label">Moisture & Throw</td>
            <td class="col-value">{{ $indent->moisture_and_throw }}</td>
        </tr>
        <tr>
            <td class="col-label">Price</td>
            <td class="col-value font-bold">{{ $indent->price }}</td>
        </tr>
        <tr>
            <td class="col-label">Payment</td>
            <td class="col-value">{{ $indent->payment }}</td>
        </tr>
        <tr>
            <td class="col-label">Shipment Schedule</td>
            <td class="col-value">{{ $indent->shipment_schedule }}</td>
        </tr>
        <tr>
            <td class="col-label">Payload</td>
            <td class="col-value">{{ $indent->payload }}</td>
        </tr>
        <tr>
            <td class="col-label">Shipping Line</td>
            <td class="col-value">{{ $indent->shipping_line }}</td>
        </tr>
        <tr>
            <td class="col-label">Discharge Port</td>
            <td class="col-value">{{ $indent->discharge_port }}</td>
        </tr>
        <tr>
            <td class="col-label">Final Destination</td>
            <td class="col-value">{{ $indent->final_destination }}</td>
        </tr>
        
        <tr>
            <td style="padding: 8px;">
                <div class="underline font-bold" style="margin-bottom: 2px;">Shipper</div>
                <div class="uppercase">{{ $indent->shipper_name }}</div>
                <div>{{ $indent->shipper_address }}</div>
                <div>{{ $indent->shipper_city }} {{ $indent->shipper_zip }} {{ $indent->shipper_country }}.</div>
            </td>
            <td style="padding: 8px 8px 8px 12px;">
                <div class="underline font-bold" style="margin-bottom: 2px;">Consignee / Invoice</div>
                <div class="font-bold uppercase">{{ $indent->consignee_name }}</div>
                <div>{{ $indent->consignee_address }},</div>
                <div>{{ $indent->consignee_city }}, {{ $indent->consignee_state }}, {{ $indent->consignee_country }},</div>
                <div>{{ $indent->consignee_zip }}, INDIA.</div>
                
                @if($indent->consignee_iec)
                    <div>IEC: {{ $indent->consignee_iec }}</div>
                @endif
                @if($indent->consignee_gstin)
                    <div>GSTIN : {{ $indent->consignee_gstin }}</div>
                @endif
                @if($indent->consignee_pan)
                    <div>PAN No. : {{ $indent->consignee_pan }}</div>
                @endif
                @if($indent->consignee_email)
                    <div>E-mail : {{ $indent->consignee_email }}</div>
                @endif
            </td>
        </tr>

        <tr>
            <td style="padding: 8px;">
                <div class="underline font-bold" style="margin-bottom: 2px;">Shipping Bank</div>
                <div class="uppercase">{{ $indent->shipper_bank_name }}</div>
                <div>{{ $indent->shipper_bank_address }}</div>
                <div>{{ $indent->shipper_bank_city }}, {{ $indent->shipper_bank_state }} {{ $indent->shipper_bank_zip }}.</div>
                <div>A/C No. : {{ $indent->shipper_bank_account_number }}</div>
                <div>SWIFT NO. : {{ $indent->shipper_bank_swift_code }}</div>
                @if($indent->shipper_bank_routing_number)
                    <div>ROUTING NO. : {{ $indent->shipper_bank_routing_number }}</div>
                @endif
            </td>
            <td style="padding: 8px 8px 8px 12px;">
                <div class="underline font-bold" style="margin-bottom: 2px;">Consignee Bank</div>
                <div class="uppercase">{{ $indent->consignee_bank_name }}</div>
                <div>{{ $indent->consignee_bank_address }}</div>
                <div>{{ $indent->consignee_bank_city }}, {{ $indent->consignee_bank_state }} - {{ $indent->consignee_bank_zip }}. {{ $indent->consignee_bank_country }}</div>
                <div>A/C No. : {{ $indent->consignee_bank_account_number }}</div>
                <div>SWIFT : {{ $indent->consignee_bank_swift_code }}</div>
                @if($indent->consignee_bank_ifsc_code)
                    <div>IFSC : {{ $indent->consignee_bank_ifsc_code }}</div>
                @endif
            </td>
        </tr>
        
        <tr>
            <td class="col-label font-bold">Release Type Of OBL</td>
            <td class="col-value"><strong class="text-red">{{ strtoupper($indent->release_type_of_obl) }}</strong></td>
        </tr>
    </table>

    <div style="margin-top: 10px; padding-left: 8px; position: relative;">
        <div style="margin:0;" class="font-bold">OTHER TERMS :</div>
        <div class="text-red-in inner-m0 inner-ul1" style="margin: 0px;">{!! $indent->other_terms !!}</div> 
        
    </div>
    <div class="page-number" style="text-align: right;">(Page 01)</div>

    <div style="page-break-before: always;"></div>

    <div style="margin-top: 10px; margin-bottom: 20px; width: 100%;">
        <div style="float: left; width: 100%;font-size: 16px !important;line-height: 1.25;">This Agreement shall be governed by and construed in accordance with the laws of United States <br /> of America.</div>
    </div>
    <div style="text-align: right;margin-top: 10px;margin-bottom: 15px;">Date : {{ optional($indent->indent_date)->format('m/d/Y') }}</div>

    <div style="font-size:14px !important;">
        <div class="font-bold" style="margin-bottom: 2px;">Claim :</div>
        <div class="inner-m0 inner-ul2" style="margin-left: 0px;">{!! $indent->claims !!}</div>
    </div>

    <div style="margin-top: 30px;">
        <div class="font-bold" style="margin-bottom: 2px;">Remarks :</div>
        <div class="inner-m0 inner-ul3" style="margin-left: 0px;">{!! $indent->remarks !!}</div>
    </div>

    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: left;vertical-align: bottom;">
                <div style="margin-bottom: 50px;">Thanking You Yours Sincerely,</div>
                
                @if($setting?->company_signed_logo)
                    <div style="margin-bottom: 5px;">
                        <img src="{{ public_path('storage/'.ltrim($setting->company_signed_logo, '/')) }}" alt="signature" height="60">
                    </div>
                @endif
                <div style="border-top: 1px solid transparent;">Authorized Sign. of Indenter / Shipper</div>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: bottom;">
                @if($indent->consignee_signature_path)
                    <div style="margin-bottom: 5px;">
                        <img src="{{ public_path('storage/'.ltrim($indent->consignee_signature_path, '/')) }}" alt="consignee signature" height="60">
                    </div>
                @else
                    <div style="height: 60px;"></div> @endif
                <div style="text-align: right; width: 100%;display:block">Authorized Sign. of Consignee</div>
            </td>
        </tr>
    </table>

    <div class="page-number" style="text-align: right;">(Page 02)</div>

</div>

<footer>
    <div>
        <img src="{{ public_path('images/footer-img.jpeg') }}" alt="Moksham Logo" style="width:100%; object-fit: contain;">
    </div>
</footer>

</body>
</html>