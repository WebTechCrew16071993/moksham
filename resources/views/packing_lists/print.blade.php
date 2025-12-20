<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Packing List #{{ $packingList->id }}</title>
    <style>
        @page {
            size: A4;
            margin: 60px 60px 40px 60px;
        }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 12px !important; 
            line-height: 1.3;
            color: #000;
        }
        
        /* Layout Helpers */
        .w-full { width: 100%; }
        .text-bold { font-weight: bold; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right; }
        .uppercase { text-transform: uppercase; }
        .bg-gray { background-color: #e6e6e6; } /* Matches the grey headers in image */

        /* Header Section */
        .header-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .logo-box {
            width: 50%;
            float: left;
            vertical-align: top;
            padding-top: 38px;
        }
        .logo-box img {
            max-width: 250px;
            height: auto;
        }
        .header-info-box {
            width: 40%;
            float: right;
        }
        .page-title {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        /* Tables */
        table.grid {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000; /* Thicker outer border */
            margin-bottom: -1px; /* Collapse double borders between tables if needed */
        }
        table.grid th, table.grid td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.grid th {
            font-weight: bold;
            text-align: left;
        }

        /* Specific Table Adjustments */
        .shipper-address {
            vertical-align: top !important;
            padding-top: 5px;
            height: 54px; /* Force height for address block */
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom:0;
            left:0;
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            width: 100%;
        }
        .footer a {
            color: #0070c0; /* Blue link color from image */
            text-decoration: underline;
            font-weight: bold;
        }
        .thank-you {
            color: #0070c0;
            font-style: italic;
            font-weight: bold;
            margin-top: 20px;
            font-size: 14px;
        }
        .page-num {
            margin-top: 20px;
        }
    </style>
</head>
<body>

{{-- Header Section: Logo Left, Title & Table Right --}}
<div class="header-container">
    <div class="logo-box">
        @php
            $logoPath = isset($asPdf) && $asPdf
                ? public_path('images/moksham-logo.png')
                : asset('images/moksham-logo.png');
        @endphp
        <img src="{{ $logoPath }}" alt="Moksham Export Import LLC">
    </div>
    
    <div class="header-info-box">
        <div class="page-title">PACKING LIST</div>
        <table class="grid">
            <tr>
                <th class="bg-gray" style="width: 35%;">BKG #</th>
                <td>{{ $packingList->shipment?->booking_no }}</td>
            </tr>
            <tr>
                <th class="bg-gray">DATE:</th>
                <td>{{ optional($packingList->date)->format('n/j/Y') }}</td>
            </tr>
            <tr>
                <th class="bg-gray">EMPLOYEE:</th>
                <td>{{ $packingList->employee }}</td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>

{{-- Main Details Table --}}
{{-- 
    Structure based on image:
    Col 1 (Labels Left) | Col 2 (Values Left) | Col 3 (Labels Right) | Col 4 (Values Right)
    Shipper Address spans Col 1-2 and Rows 2-5
--}}
<table class="grid" style="margin-top: 10px;">
    {{-- Row 1 --}}
    <tr>
        <th colspan="2" class="bg-gray" style="width: 50%;">SHIPPER</th>
        <th class="bg-gray" style="width: 17%;">INVOICE NO.</th>
        <td style="width: 33%;">{{ $invoice?->invoice_no }}</td>
    </tr>
    
    {{-- Row 2 --}}
    <tr>
        {{-- Address Block: Spans 2 Columns and 4 Rows to match right side height --}}
        <td colspan="2" rowspan="3" class="shipper-address">
            <div class="text-bold uppercase">{{ $setting?->company_name }}</div>
            <div class="uppercase">{{ $setting?->company_address }}</div>
            @if($setting?->company_address_line2)
                <div class="uppercase">{{ $setting?->company_address_line2 }}</div>
            @endif
            <div class="uppercase">{{ $setting?->company_city }}, {{ $setting?->company_state }} {{ $setting?->company_zip }} USA.</div>
        </td>
        <th class="bg-gray">BOOKING NO.</th>
        <td>{{ $packingList->shipment?->booking_no }}</td>
    </tr>

    {{-- Row 3 --}}
    <tr>
        <th class="bg-gray">CARRIER:</th>
        <td>{{ $packingList->shipment?->carrier }}</td>
    </tr>

    {{-- Row 4 --}}
    <tr>
        <th class="bg-gray">SHIP DATE:</th>
        <td>{{ optional($packingList->ship_date)->format('m/d/Y') }}</td>
    </tr>

    {{-- Row 6 --}}
    <tr>
        <th class="bg-gray" style="width: 17%;">ORIGIN:</th>
        <td style="width: 33%;">{{ $packingList->origin }}</td>
        <th class="bg-gray">ARRIVAL DATE:</th>
        <td>{{ optional($packingList->arrival_date)->format('m/d/Y') }}</td>
    </tr>

    {{-- Row 7 --}}
    <tr>
        <th class="bg-gray">DESTINATION</th>
        <td style="text-transform: uppercase;">{{ $packingList->destination }}</td>
        
        <th class="bg-gray">ORDER NO:</th>
        <td>{{ $packingList->order_no }}</td>
    </tr>

    {{-- Row 8 --}}
    <tr>
        <th class="bg-gray">SHIP IN:</th>
        <td style="text-transform: uppercase;">{{ $packingList->ship_in }}</td>
        
        <th class="bg-gray">CONTACT:</th>
        <td>{{ $packingList->contact }}</td>
    </tr>

    {{-- Row 9 --}}
    <tr>
        <th class="bg-gray">CONTAINER NO:</th>
        @if($packingList->container_no_summary) 
            <td>{{ $packingList->container_no_summary }}</td>
        @else
            <td>{{ $containerCount }} X{{ strtoupper($packingList->ship_in) }}</td>
        @endif
        {{-- Empty cells on right to complete the grid if needed, or merge --}}
        
        <th class="bg-gray">PHONE:</th>
        <td>{{ $packingList->phone }}</td>
    </tr>
</table>

{{-- Items Table --}}
<table class="grid" style="margin-top: 15px;">
    <thead>
        <tr>
            <th class="text-center bg-gray" style="width: 17%;padding-left:5px;padding-right:5px;">CONTAINER NO.</th>
            <th class="text-center bg-gray" style="width: 12%;padding-left:5px;padding-right:5px;">SEAL NO.</th>
            <th class="text-center bg-gray" style="width: 48%;">DESCRIPTION</th>
            <th class="text-center bg-gray" style="width: 9%;">BALES</th>
            <th class="text-center bg-gray" style="width: 14%;">WEIGHT</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($packingList->items as $item)
        <tr>
            <td class="text-center">{{ $item->container_no }}</td>
            <td class="text-center">{{ $item->seal_no }}</td>
            <td class="text-center">{{ $item->description }}</td>
            <td class="text-center">{{ $item->no_of_bales }}</td>
            <td class="text-center">{{ number_format((float) $item->weight_lbs, 0) }}</td>
        </tr>
    @endforeach
    {{-- Fill empty rows to mimic the image look --}}
    <!-- @for ($i = $packingList->items->count(); $i < 9; $i++)
        <tr>
            <td>&nbsp;</td><td></td><td></td><td></td><td></td>
        </tr>
    @endfor -->
    </tbody>
</table>

{{-- Totals Section --}}
<table class="grid" style="margin-top: 15px;">
    <thead>
        <tr>
            <th class="text-center bg-gray" style="width: 17%;">TOTAL BALES</th>
            <th class="text-center bg-gray" style="width: 60%;">DESCRIPTION</th>
            <th class="text-center bg-gray" style="width: 23%;">TOTAL WEIGHT IN LBS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">{{ $totalBales }}</td>
            <td class="text-center">{{ $descriptionSummary }}</td>
            <td class="text-center">{{ number_format($totalWeightLbs, 0) }}</td>
        </tr>
    </tbody>
</table>

{{-- IN KG Small Table (Aligned Right) --}}
<div style="width: 100%; display: flex; justify-content: flex-end; margin-top: 15px;">
    <table class="grid" style="width: 38%; margin-left: auto;">
        <tr>
            <th class="text-center bg-gray" style="width: 40%;">IN KG</th>
            <td class="text-center text-bold">{{ number_format($totalWeightKg, 2) }}</td>
        </tr>
    </table>
</div>

{{-- Footer --}}
<div class="footer">
    <div>
        IF YOU HAVE ANY QUESTION PLEASE REACH US AT: 
        <a href="mailto:{{ $setting?->company_email }}">{{ strtoupper($setting?->company_email) }}</a>
    </div>
    <div>PLEASE CONTACT US WITHIN 24 HRS FOR ANY NECESSARY CORRECTION.</div>
    
    <div class="thank-you">THANK YOU FOR YOUR BUSINESS ....!</div>
    
    <div class="page-num">Page <span class="pagenum">1</span> of <span class="pagecount">1</span></div>
</div>

</body>
</html>