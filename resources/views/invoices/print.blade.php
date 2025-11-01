<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Invoice #{{ $invoice->invoice_no }}</title>
    <style>
        @page { margin: 120px 30px 80px 30px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        header { position: fixed; top: -90px; left: 0; right: 0; height: 90px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 60px; text-align: center; font-size: 11px; color: #333; }
        .brand { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .brand img { height: 46px; }
        .brand-name { font-weight: bold; font-size: 16px; }
        .grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grid th, .grid td { border: 1px solid #333; padding: 6px; vertical-align: middle; }
        /* Keep labels on one line; allow values to wrap within their cell */
        .grid th { white-space: nowrap; font-weight: 700; }
        .grid td { white-space: normal; word-break: break-word; }
        /* Meta tables (top-right + schedule) a bit tighter */
        table.meta th, table.meta td { padding: 5px 6px; font-size: 11px; }
        /* Utility class for non-wrapping values */
        .nowrap { white-space: nowrap !important; }
        .section-title { font-weight: bold; margin-top: 12px; }
        .grid thead th { font-size: 11.5px; letter-spacing: .2px; }
        .spacer { height: 6px; }
        .no-break { page-break-inside: avoid; }
        .pagenum:before { content: counter(page); }
        .pagecount:before { content: counter(pages); }
        .title { text-align: center; font-weight: bold; font-size: 18px; margin: 8px 0 10px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .muted { color: #444; }
        .nowrap { white-space: nowrap; }
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
    </div>
</header>

<footer>
    <div>{{ \App\Support\CompanyHelper::fullAddress($setting) }}</div>
    <div style="margin-top:4px;">(Page <span class="pagenum"></span>)</div>
</footer>

<div class="title">COMMERCIAL INVOICE</div>

<table class="grid" style="margin-bottom:8px; width:45%; margin-left:auto;">
    <tr>
        <th style="width:30%">OBL REF:</th>
        <td style="width:20%" class="nowrap">{{ $invoice->obl_ref }}</td>
        <th style="width:30%">DATE:</th>
        <td style="width:20%" class="nowrap">{{ optional($invoice->date)->format('m/d/Y') }}</td>
    </tr>
    <tr>
        <th>EMPLOYEE:</th>
        <td>{{ $invoice->employee }}</td>
        <th>INVOICE NO.</th>
        <td>{{ $invoice->invoice_no }}</td>
    </tr>
    </table>

    <!-- Add a tiny spacer to ensure clear separation before next blocks -->
    <div class="spacer"></div>

</table>

<table class="grid" style="clear: both; margin-top:8px;">
    <tr>
        <th style="width:60%">SHIPPER</th>
        <th style="width:40%"></th>
    </tr>
    <tr>
        <td style="width:60%;">
            <div style="font-weight:bold;">{{ strtoupper($setting?->company_name) }}</div>
            <div>{{ $setting?->company_address }}</div>
            <div>{{ $setting?->company_city }}, {{ $setting?->company_state }} {{ $setting?->company_zip }}, {{ $setting?->company_country }}</div>
        </td>
        <td style="width:40%; padding:0;">
            <table class="grid meta no-break" style="width:100%;">
                <colgroup>
                    <col style="width:28%" />
                    <col style="width:22%" />
                    <col style="width:28%" />
                    <col style="width:22%" />
                </colgroup>
                <tr>
                    <th style="width:28%">INVOICE NO.</th>
                    <td style="width:22%">{{ $invoice->invoice_no }}</td>
                </tr>
                <tr>
                    <th style="width:28%">BOOKING NO.</th>
                    <td style="width:22%">{{ $invoice->shipment?->booking_no }}</td>
                </tr>
                <tr>
                    <th style="width:28%">CARRIER</th>
                    <td style="width:22%">{{ $invoice->shipment?->carrier }}</td>
                </tr>
                <tr>
                    <th style="width:28%">VESSEL / VOYAG.</th>
                    <td style="width:22%">{{ $invoice->shipment?->vessel }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="grid" style="margin-top:8px;">
    <tr>
        <th style="width:60%">BILL TO</th>
        <th style="width:40%"></th>
    </tr>
    <tr>
        <td style="width:60%;">
            <div style="font-weight:bold;">{{ $invoice->consignee?->name }}</div>
            <div>{{ $invoice->consignee?->address }}</div>
            <div class="muted">{{ $invoice->consignee?->city }}, {{ $invoice->consignee?->state }} {{ $invoice->consignee?->zip }}, {{ $invoice->consignee?->country }}</div>
        </td>
        <td style="width:40%; padding:0;">
            <table class="grid meta" style="width:100%;">
                <colgroup>
                    <col style="width:28%" />
                    <col style="width:22%" />
                    <col style="width:28%" />
                    <col style="width:22%" />
                </colgroup>
                <tr>
                    <th style="width:28%">RETURN DATE:</th>
                    <td style="width:22%" class="nowrap center">{{ optional($invoice->return_date)->format('m/d/Y') }}</td>
                    <th style="width:28%">CUT OFF DATE:</th>
                    <td style="width:22%" class="nowrap center">{{ optional($invoice->cut_off_date)->format('m/d/Y') }}</td>
                </tr>
                <tr>
                    <th style="width:28%">DEPT. EST:</th>
                    <td style="width:22%" class="nowrap center">{{ optional($invoice->dept_est)->format('m/d/Y') }}</td>
                    <th style="width:28%">ARRIVAL:</th>
                    <td style="width:22%" class="nowrap center">{{ optional($invoice->arrival)->format('m/d/Y') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="grid" style="margin-top:12px;">
    <tr>
        <td style="width:50%; padding:0;">
            <table class="grid" style="width:100%; table-layout: fixed;">
                <tr>
                    <th style="width:40%">NOTIFY:</th>
                    <td>{{ $invoice->notify ?? 'SAME AS CNEE' }}</td>
                </tr>
                <tr>
                    <th>CONTACT:</th>
                    <td>{{ $invoice->packingList?->contact }}</td>
                </tr>
                <tr>
                    <th>PHONE:</th>
                    <td>{{ $invoice->packingList?->phone }}</td>
                </tr>
            </table>
        </td>
        <td style="width:50%; padding:0;">
            <table class="grid" style="width:100%; table-layout: fixed;">
                <tr>
                    <th style="width:40%">ORIGIN:</th>
                    <td>{{ $invoice->origin ?? $invoice->packingList?->origin ?? $setting?->company_city }}</td>
                </tr>
                <tr>
                    <th>DESTINATION:</th>
                    <td>{{ $invoice->f_dest ?? $invoice->packingList?->destination }}</td>
                </tr>
                <tr>
                    <th>QUOTATION:</th>
                    <td>{{ $invoice->quotation ?? '' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Removed duplicate block to keep alignment exactly as reference -->

<!-- Extra spacer to avoid any perceived conflict above the commodities header -->
<div class="spacer"></div>

<table class="grid no-break" style="margin-top:14px;">
    <colgroup>
        <col style="width:16%" />
        <col style="width:14%" />
        <col style="width:40%" />
        <col style="width:15%" />
        <col style="width:15%" />
    </colgroup>
    <thead>
    <tr>
        <th class="center">NO. OF CONT.</th>
        <th class="center">WEIGHT</th>
        <th class="center">DESCRIPTION OF COMMODITIES</th>
        <th class="center nowrap">RATE M/T-</th>
        <th class="center nowrap">AMOUNT</th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td class="center">{{ $invoice->no_of_cont }}</td>
            <td class="center">{{ number_format((float) $invoice->weight_mt, 3) }}MT</td>
            <td style="white-space: pre-line; text-align:center;">{!! nl2br(e($invoice->description)) !!}</td>
            <td class="right">{{ $invoice->rate_mt !== null ? '$'.number_format((float) $invoice->rate_mt, 2) : '' }}</td>
            <td class="right">{{ $invoice->amount !== null ? '$'.number_format((float) $invoice->amount, 2) : '' }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <th class="right">TOTALS</th>
            <td class="right">{{ $invoice->amount !== null ? '$'.number_format((float) $invoice->amount, 2) : '' }}</td>
        </tr>
    </tbody>
</table>

<table class="grid" style="margin-top:10px;">
    <tr>
        <th style="width:22%">PAYMENT TERMS:</th>
        <td style="width:28%">{{ $invoice->payment_terms }}</td>
        <th style="width:22%" class="right">ADVANCE</th>
        <td class="right" style="width:28%">{{ number_format((float) ($invoice->advance ?? 0), 2) }}</td>
    </tr>
    <tr>
        <th class="right" colspan="3">AMOUNT DUE NOW</th>
        <td class="right">{{ $invoice->amount_due !== null ? number_format((float) $invoice->amount_due, 2) : '' }}</td>
    </tr>
</table>

<div class="section-title">REMARKS</div>
@php
    $remarks = $invoice->remarks;
    $remarksHasHtml = is_string($remarks) && $remarks !== strip_tags($remarks);
@endphp
<div style="min-height: 60px;">
    {!! $remarksHasHtml ? $remarks : nl2br(e($remarks)) !!}
</div>

<div class="section-title">BANK DETAILS</div>
<div style="min-height: 80px;">
    <div><strong>A/C NAME:</strong> {{ strtoupper($setting?->company_name) }}</div>
    <div><strong>BANK NAME:</strong> {{ $setting?->bank_name }}</div>
    <div><strong>BANK ADD.:</strong> {{ $setting?->bank_address }}, {{ $setting?->bank_city }}, {{ $setting?->bank_state }} {{ $setting?->bank_zip }}, {{ $setting?->bank_country }}</div>
    <div style="display:flex; gap:24px;">
        <div><strong>A/C NO.:</strong> {{ $setting?->bank_account_number }}</div>
        <div><strong>SWIFT NO.:</strong> {{ $setting?->bank_swift_code }}</div>
        <div><strong>ROUTING NO.:</strong> {{ $setting?->bank_routing_number }}</div>
    </div>
</div>

<div class="section-title">Terms & Conditions</div>
@php
    $tc = $invoice->terms_conditions;
    $tcHasHtml = is_string($tc) && $tc !== strip_tags($tc);
@endphp
<div style="min-height: 140px;">
    {!! $tcHasHtml ? $tc : nl2br(e($tc)) !!}
</div>

</body>
</html>
