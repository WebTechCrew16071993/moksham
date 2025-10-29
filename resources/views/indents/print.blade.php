<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Indent #{{ $indent->indent_no }}</title>
    <style>
        @page { margin: 120px 30px 80px 30px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        header { position: fixed; top: -90px; left: 0; right: 0; height: 90px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 60px; text-align: center; font-size: 11px; color: #333; }
        .brand { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .brand img { height: 46px; }
        .brand-name { font-weight: bold; font-size: 16px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #333; padding: 6px; vertical-align: top; }
        .section-title { font-weight: bold; margin-top: 12px; }
        .pagenum:before { content: counter(page); }
        .pagecount:before { content: counter(pages); }
    </style>
</head>
<body>
<header>
    <div class="brand">
        <img src="{{ public_path('images/moksham-logo.png') }}" alt="Moksham Logo">
        <div class="brand-name">{{ $setting?->company_name }}</div>
    </div>
</header>

<footer>
    <div>{{ \App\Support\CompanyHelper::fullAddress($setting) }}</div>
    <div style="margin-top:4px;">(Page <span class="pagenum"></span>)</div>
    </footer>

<table class="grid">
    
 
            <div>Indent No.: <strong>{{ $indent->indent_no }}</strong></div>
            <div>Date: <strong>{{ optional($indent->indent_date)->format('m/d/Y') }}</strong></div>
            <div class="mt-3">

                <div><strong>To,</strong></div>
                <div style="margin-top:4px"><strong>{{ strtoupper($indent->consignee_name) }}</strong></div>
                <div>{{ $indent->consignee_address }}</div>
                <div>{{ $indent->consignee_city }}, {{ $indent->consignee_state }} {{ $indent->consignee_zip }}, {{ $indent->consignee_country }}.</div>
            </div>

    <div>Kind Attn.: <strong>{{ $indent->kind_attention }}</strong></div>
</table>

<p>Dear Sir,</p>
<p>
    Reference to our discussion and confirmation did {{ optional($indent->indent_date)->format('m/d/Y') }},
    we are pleased to confirm below order for M/s, <strong>{{ $indent->consignee_name }}</strong>,
    {{ $indent->consignee_city }}, {{ $indent->consignee_state }}, {{ $indent->consignee_country }}.
    </p>


<table class="grid" style="margin-top:10px;">
    <tr><th>Quality</th><td>{{ $indent->quality }}<br/>HS Code: <strong>{{ $indent->hsn_code }}</strong></td></tr>
    <tr><th>Origin</th><td>{{ $indent->origin }}</td></tr>
    <tr><th>Quantity</th><td>{{ $indent->quantity }}</td></tr>
    <tr><th>Moisture & Throw</th><td>{{ $indent->moisture_and_throw }}</td></tr>
    <tr><th>Price</th><td>{{ $indent->price }}</td></tr>
    <tr><th>Payment</th><td>{{ $indent->payment }}</td></tr>
    <tr><th>Shipment Schedule</th><td>{{ $indent->shipment_schedule }}</td></tr>
    <tr><th>Payload</th><td>{{ $indent->payload }}</td></tr>
    <tr><th>Shipping line</th><td>{{ $indent->shipping_line }}</td></tr>
    <tr><th>Discharge port</th><td>{{ $indent->discharge_port }}</td></tr>
    <tr><th>Final destination</th><td>{{ $indent->final_destination }}</td></tr>
    <tr>
        <td>
            <div><strong style="text-decoration: underline;">Shipper</strong></div>
            <div><strong>{{ strtoupper($indent->shipper_name) }}</strong></div>
            <div>{{ $indent->shipper_address }}</div>
            <div>{{ $indent->shipper_city }}, {{ $indent->shipper_state }} {{ $indent->shipper_zip }}, {{ $indent->shipper_country }}</div>
            <div>Email: {{ $indent->shipper_email }} | Phone: {{ $indent->shipper_phone }}</div>
        </td>
        <td>
            <div><strong style="text-decoration: underline;">Consignee / Invoice</strong></div>
            <div><strong>{{ strtoupper($indent->consignee_name) }}</strong></div>
            <div>{{ $indent->consignee_address }}</div>
            <div>{{ $indent->consignee_city }}, {{ $indent->consignee_state }} {{ $indent->consignee_zip }}, {{ $indent->consignee_country }}</div>
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
        <td style="width:50%;">
            <div><strong style="text-decoration: underline;">Shipping Bank</strong></div>
            <div>{{ $indent->shipper_bank_name }}</div>
            <div>{{ $indent->shipper_bank_address }}</div>
            <div>{{ $indent->shipper_bank_city }}, {{ $indent->shipper_bank_state }} {{ $indent->shipper_bank_zip }}, {{ $indent->shipper_bank_country }}</div>
            <div>A/C No.: {{ $indent->shipper_bank_account_number }}</div>
            <div>SWIFT NO.: {{ $indent->shipper_bank_swift_code }}</div>
            @if($indent->shipper_bank_routing_number)
                <div>ROUTING NO.: {{ $indent->shipper_bank_routing_number }}</div>
            @endif
        </td>
        <td style="width:50%;">
            <div><strong style="text-decoration: underline;">Consignee Bank</strong></div>
            <div>{{ $indent->consignee_bank_name }}</div>
            <div>{{ $indent->consignee_bank_address }}</div>
            <div>{{ $indent->consignee_bank_city }}, {{ $indent->consignee_bank_state }} {{ $indent->consignee_bank_zip }}, {{ $indent->consignee_bank_country }}</div>
            <div>A/C No.: {{ $indent->consignee_bank_account_number }}</div>
            <div>SWIFT : {{ $indent->consignee_bank_swift_code }}</div>
            @if($indent->consignee_bank_ifsc_code)
                <div>IFSC : {{ $indent->consignee_bank_ifsc_code }}</div>
            @endif
        </td>
    </tr>
    <tr><th>Release type of OBL</th><td><strong style="color:#c00;">{{ strtoupper($indent->release_type_of_obl) }}</strong></td></tr>
</table>



<div style="page-break-before: always;"></div>

<div style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div style="font-weight:bold;">This Agreement shall be governed by and construed in accordance with the laws of United States of America.</div>
    <div>Date : {{ optional($indent->indent_date)->format('m/d/Y') }}</div>
</div>

<h4 class="section-title">Other Terms :</h4>
<div>{!! $indent->other_terms !!}</div>

<h4 class="section-title">Claims :</h4>
<div>{!! $indent->claims !!}</div>

<h4 class="section-title">Remarks :</h4>
<div>{!! $indent->remarks !!}</div>

<div class="row" style="margin-top:40px; display:flex; justify-content:space-between;">
    <div class="col-lg-6">
        @if($setting?->company_signed_logo)
            <div style="margin-top:8px;">
                <img src="{{ public_path('storage/'.ltrim($setting->company_signed_logo, '/')) }}" alt="signature" height="60">
            </div>
        @endif
        <div>Authorized Sign. of Indenter / Shipper</div>
    </div>
    <div class="col-lg-6">
        @if($indent->consignee_signature_path)
            <div style="margin-top:8px;">
                <img src="{{ public_path('storage/'.ltrim($indent->consignee_signature_path, '/')) }}" alt="consignee signature" height="60">
            </div>
        @endif
        <div>Authorized Sign. of Consignee</div>
    </div>
</div>
</body>
</html>
