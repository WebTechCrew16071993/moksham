<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Packing List #{{ $packingList->id }}</title>
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
        .title { text-align: right; font-weight: bold; font-size: 18px; margin: 4px 0 8px; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
    </head>
<body>
<header>
    <div class="brand">
        @php
            $logoPath = isset($asPdf) && $asPdf
                ? public_path('images/moksham-logo.png')
                : asset('images/moksham-logo.png');
        @endphp
        <img src="{{ $logoPath }}" alt="Moksham Logo">
        {{-- <div class="brand-name">{{ strtoupper($setting?->company_name) }}</div> --}}
    </div>
</header>

<footer>
    <div>{{ \App\Support\CompanyHelper::fullAddress($setting) }}</div>
    <div style="margin-top:4px;">(Page <span class="pagenum"></span>)</div>
</footer>

<div class="title">PACKING LIST</div>

<table class="grid" style="margin-bottom:8px;">
    <tr>
        <th style="width:22%">BKG #</th>
        <td style="width:28%">{{ $packingList->shipment?->booking_no }}</td>
        <th style="width:22%">DATE</th>
        <td style="width:28%">{{ optional($packingList->date)->format('m/d/Y') }}</td>
    </tr>
    <tr>
        <th>EMPLOYEE</th>
        <td>{{ $packingList->employee }}</td>
        <th></th>
        <td></td>
    </tr>
    
</table>
<table class="grid">
    <tr>
        <th colspan="2">SHIPPER</th>
        <th colspan="2">&nbsp;</th>
    </tr>
    <tr>
        <td colspan="2" style="width:60%;">
            <div style="font-weight:bold;">{{ strtoupper($setting?->company_name) }}</div>
            <div>{{ $setting?->company_address }}</div>
            @if($setting?->company_address_line2)
                <div>{{ $setting?->company_address_line2 }}</div>
            @endif
            <div>{{ $setting?->company_city }}, {{ $setting?->company_state }} {{ $setting?->company_zip }}, {{ $setting?->company_country }}</div>
        </td>
        <td colspan="2" style="width:40%;">
            <table class="grid" style="width:100%;">
                <tr>
                    <th style="width:42%">INVOICE NO.</th>
                    <td style="width:58%">{{ $invoice?->invoice_no }}</td>
                </tr>
                <tr>
                    <th>BOOKING NO.</th>
                    <td>{{ $packingList->shipment?->booking_no }}</td>
                </tr>
                <tr>
                    <th>CARRIER</th>
                    <td>{{ $packingList->shipment?->carrier }}</td>
                </tr>
                <tr>
                    <th>SHIP DATE</th>
                    <td>{{ optional($packingList->ship_date)->format('m/d/Y') }}</td>
                </tr>
                <tr>
                    <th>ARRIVAL DATE</th>
                    <td>{{ optional($packingList->arrival_date)->format('m/d/Y') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="grid" style="margin-top:10px;">
    <tr>
        <th>ORIGIN</th><td>{{ $packingList->origin }}</td>
        <th></th><td></td>
    </tr>
    <tr>
        <th>DESTINATION</th><td>{{ $packingList->destination }}</td>
        <th>ORDER NO</th><td>{{ $packingList->order_no }}</td>
    </tr>
    <tr>
        <th>SHIP IN</th><td>{{ $packingList->ship_in }}</td>
        <th>CONTACT</th><td>{{ $packingList->contact }}</td>
    </tr>
    <tr>
        <th>CONTAINER NO:</th>
        @if($packingList->container_no_summary) 
            <td>{{ $packingList->container_no_summary }}</td>
        @else
            <td>{{ $containerCount }} x {{ strtoupper($packingList->ship_in) }}</td>
        @endif
        <th>PHONE</th><td>{{ $packingList->phone }}</td>
    </tr>
</table>

<table class="grid" style="margin-top:12px;">
    <thead>
    <tr>
        <th class="center">CONTAINER NO.</th>
        <th class="center">SEAL NO.</th>
        <th class="center">DESCRIPTION</th>
        <th class="center">NO. OF BALES</th>
        <th class="center">WEIGHT (LBS)</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($packingList->items as $item)
        <tr>
            <td>{{ $item->container_no }}</td>
            <td>{{ $item->seal_no }}</td>
            <td>{{ $item->description }}</td>
            <td class="center">{{ $item->no_of_bales }}</td>
            <td class="right">{{ number_format((float) $item->weight_lbs, 0) }}</td>
        </tr>
    @endforeach
    @for ($i = $packingList->items->count(); $i < 8; $i++)
        <tr>
            <td>&nbsp;</td><td></td><td></td><td></td><td></td>
        </tr>
    @endfor
    </tbody>
</table>

<table class="grid" style="margin-top:10px;">
    <tr>
        <th style="width:22%">TOTAL BALES</th>
        <td style="width:28%">{{ $totalBales }}</td>
        <th style="width:22%">DESCRIPTION</th>
        <td style="width:28%">{{ $descriptionSummary }}</td>
    </tr>
    <tr>
        <th>TOTAL WEIGHT IN LBS</th>
        <td>{{ number_format($totalWeightLbs, 0) }}</td>
        <th>IN KG</th>
        <td style="font-weight:bold; text-align:right;">{{ number_format($totalWeightKg, 2) }}</td>
    </tr>
</table>

<div style="margin-top:18px; text-align:center;">
    IF YOU HAVE ANY QUESTION PLEASE REACH US AT: <strong><a href="mailto:{{ $setting?->company_email }}">{{ $setting?->company_email }}</a></strong><br>
    PLEASE CONTACT US WITHIN 24 HRS FOR ANY NECESSARY CORRECTION.
    <div style="margin-top:8px;"><em>THANK YOU FOR YOUR BUSINESS ...!</em></div>
    </div>

</body>
</html>
