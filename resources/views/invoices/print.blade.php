<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Invoice #{{ $invoice->invoice_no }}</title>
    <style>
        @page {
            margin: 30px 60px 30px 60px; 
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            color: #000;
            line-height: 1.15;
        }
        
        /* Utility Classes */
        .bold { font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }
        .uppercase { text-transform: uppercase; }
        .text-red { color: #dd2b1c !important; }
        .text-blue { color: #0070c0 !important; }
        .bg-gray { background-color: #e6e6e6; }

        .no-break { page-break-inside: avoid; }
        .page-break { page-break-before: always; }
        
        /* Table Styles */
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 2px 4px; vertical-align: middle; }
        th { font-weight: bold; }
        
        /* Borders */
        .border-top { border-top: 1px solid #000; }
        .border-all td, .border-all th, .border-all { border: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }
        .border-right { border-right: 1px solid #000; }
        .border-left { border-left: 1px solid #000; }
        .no-border { border: none !important; }
        .no-border-top { border-top: none !important; }
        .no-border-bottom { border-bottom: none !important; }
        .no-border-right { border-right: none !important; }
        .no-border-left { border-left: none !important; }

        /* Specific Styles */
        .box-header { font-weight: bold; text-align: left; padding-left: 5px; }
        
        /* Page 2 Footer Sticky Logic */
        .page-2-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        /* Footer for Page Numbers (All Pages) */
        footer {
            position: fixed;
            bottom: -20px;
            left: 0; right: 0;
            height: 20px;
            text-align: center;
            font-size: 11px;
        }
        .pagenum:before { content: counter(page); }

        .h-1 {
            height: 22px !important;
        }
        .h-2 {
            height: 23px !important;
        }
    </style>
</head>
<body>

<footer>
    Page <span class="pagenum"></span> of 2
</footer>

<table style="margin-bottom: 10px;">
    <tr>
        <td style="width: 55%; vertical-align: top; padding-left: 0;">
            @php
                $logoPath = isset($asPdf) && $asPdf
                    ? public_path('images/moksham-logo.png')
                    : asset('images/moksham-logo.png');
            @endphp
            <img src="{{ $logoPath }}" alt="Moksham Logo" style="height: 42px; display: block; margin-bottom: 5px;">
            <div style="font-size: 12px; line-height: 1.2;">
                7511 BARKSTONE LANE<br>
                RICHMOND TX 77469 USA
            </div>
        </td>
        <td style="width: 45%; vertical-align: bottom; padding-right: 0;">
            <div class="bold" style="margin-bottom: 2px; font-size: 13px;">COMMERCIAL INVOICE</div>
            <table class="border-all">
                <tr>
                    <td class="bg-gray" style="width: 35%;">OBL REF:</td>
                    <td>{{ $invoice->obl_ref }}</td>
                </tr>
                <tr>
                    <td class="bg-gray">DATE:</td>
                    <td>{{ optional($invoice->date)->format('m/d/Y') }}</td>
                </tr>
                <tr>
                    <td class="bg-gray">EMPLOYEE:</td>
                    <td class="uppercase">{{ $invoice->employee }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="border-all" style="border-bottom: none;">
    <tr>
        <td style="width: 44%; padding: 0;border-bottom: none; border-right: 1px solid #000; vertical-align: top;">
            <div class="bold border-bottom bg-gray box-header">SHIPPER</div>
            <div style="padding: 5px; height: 75px;">
                <div class="bold">{{ strtoupper($setting?->company_name) }}</div>
                <div>{{ $setting?->company_address }},</div>
                <div>{{ $setting?->company_city }},{{ $setting?->company_state }} {{ $setting?->company_zip }}</div>
            </div>
        </td>
        <td style="width: 56%; padding: 0;border-bottom: none; vertical-align: top;">
            <table style="width: 100%;border:none !important;">
                <tr>
                    <td class="bold border-bottom border-right no-border-top no-border-left bg-gray h-1" style="width: 30%;">INVOICE NO.</td>
                    <td class="border-bottom no-border-top no-border-right" style="padding-left: 5px;">{{ $invoice->invoice_no }}</td>
                </tr>
                <tr>
                    <td class="bold border-bottom border-right no-border-top no-border-left bg-gray h-1">BOOKING NO.</td>
                    <td class="border-bottom no-border-right" style="padding-left: 5px;">{{ $invoice->shipment?->booking_no }}</td>
                </tr>
                <tr>
                    <td class="bold border-bottom border-right no-border-top no-border-left bg-gray h-1">CARRIER:</td>
                    <td class="border-bottom no-border-right" style="padding-left: 5px;">{{ $invoice->shipment?->carrier }}</td>
                </tr>
                <tr>
                    <td class="bold border-right no-border-top no-border-bottom no-border-left bg-gray h-1">VESSEL / VOYAG:</td>
                    <td class="no-border-right no-border-bottom" style="padding-left: 5px;">{{ $invoice->shipment?->vessel }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="border-all" style="border-top: 1px solid #000;">
    <tr>
        <td style="width: 44%; padding: 0; vertical-align: top;">
            <div class="bold border-bottom bg-gray box-header">BILL TO</div>
            <div style="padding: 5px; height: 60px;">
                <div class="bold">{{ $invoice->consignee?->name }}</div>
                <div style="white-space: pre-line;">{!! $invoice->consignee?->address !!}</div>
                <div>{{ $invoice->consignee?->city }},{{ $invoice->consignee?->state }}-{{ $invoice->consignee?->zip }}</div>
            </div>
            <table style="width: 100%;border:none !important; border-top: 1px solid #000 !important;">
                <tr>
                    <td class="bold border-right border-bottom bg-gray no-border-left" style="width: 30%;">NOTIFY:</td>
                    <td class="border-bottom uppercase no-border-right">{{ $invoice->notify ?? 'SAME AS CNEE' }}</td>
                </tr>
                <tr>
                    <td class="bold border-right border-bottom bg-gray no-border-left">CONTACT:</td>
                    <td class="border-bottom uppercase no-border-right">{{ $invoice->packingList?->contact }}</td>
                </tr>
                <tr>
                    <td class="bold border-right bg-gray no-border-left no-border-bottom">PHONE:</td>
                    <td class="no-border-right no-border-bottom">{{ $invoice->packingList?->phone }}</td>
                </tr>
            </table>
        </td>
        <td style="width: 56%; padding: 0; vertical-align: top;">
            <table style="width: 100%;border:none !important;">
                <tr>
                    <td class="bold border-right border-bottom no-border-top no-border-left bg-gray h-2" style="width: 30%;">RETURN DATE:</td>
                    <td class="center border-right border-bottom no-border-top" style="width: 20%;">{{ optional($invoice->return_date)->format('m/d/Y') }}</td>
                    <td class="bold border-right border-bottom no-border-top bg-gray h-2" style="width: 30%;">CUT OFF DATE:</td>
                    <td class="center border-bottom no-border-top no-border-right" style="width: 20%;">{{ optional($invoice->cut_off_date)->format('m/d/Y') }}</td>
                </tr>
                <tr>
                    <td class="bold border-right border-bottom no-border-left bg-gray h-2">DEPT. EST:</td>
                    <td class="center border-right border-bottom h-2">{{ optional($invoice->dept_est)->format('m/d/Y') }}</td>
                    <td class="bold border-right border-bottom bg-gray h-2">ARRIVAL:</td>
                    <td class="center border-bottom no-border-right h-2">{{ optional($invoice->arrival)->format('m/d/Y') }}</td>
                </tr>
                <tr>
                    <td class="bold border-right border-bottom no-border-left bg-gray h-2">SI CUT OFF:</td>
                    <td class="center border-right border-bottom h-2">{{ $invoice->si_cut_off ?? '' }}</td>
                    <td class="bold border-right border-bottom bg-gray h-2">DESTINATION:</td>
                    <td class="center border-bottom uppercase  no-border-right h-2">{{ $invoice->f_dest ?? $invoice->packingList?->destination }}</td>
                </tr>
                <tr>
                    <td class="bold border-right border-bottom no-border-left bg-gray h-2">ORIGIN:</td>
                    <td class="center border-right border-bottom uppercase h-2">{{ $invoice->origin ?? 'CHICAGO' }}</td>
                    <td class="bold border-right border-bottom bg-gray h-2">F. DEST:</td>
                    <td class="center border-bottom uppercase  no-border-right h-2">{{ $invoice->f_dest ?? $invoice->packingList?->destination }}</td>
                </tr>
                 <tr>
                    <td class="bold border-right bg-gray no-border-bottom no-border-left" style="height: 25px;">QUOTATION:</td>
                    <td colspan="3" class="no-border-bottom no-border-right" style="height: 25px;"></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="border-all" style="margin-top: 0px; border-top:none; border-bottom: none;">
    <thead>
        <tr class="bg-gray">
            <th class="center no-border-top border-right" style="width: 14%;">NO. OF CONT.</th>
            <th class="center no-border-top border-right" style="width: 14%;">WEIGHT</th>
            <th class="center no-border-top border-right" style="width: 44%;">DESCRIPTION OF COMMODITIES</th>
            <th class="center no-border-top border-right" style="width: 14%;">RATE</th>
            <th class="center no-border-top" style="width: 14%;">AMOUNT</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="center border-right" style="height: 300px; padding-top: 10px;vertical-align: top;">{{ $invoice->no_of_cont }}</td>
            <td class="center border-right" style="padding-top: 10px;vertical-align: top;">{{ number_format((float) $invoice->weight_mt, 3) }}MT</td>
            <td class="center border-right" style="padding-top: 10px;vertical-align: top;">
                <!-- <div style="margin-bottom: 3px;">WASTEPAPER OCC 11</div>
                <div style="margin-bottom: 3px;">MOISTURECONTAIN: LESS THAN 12%</div>
                <div style="margin-bottom: 3px;">( HS CODE: 47079000 )</div>
                <div style="margin-bottom: 3px;">({{ number_format((float) $invoice->weight_mt * 1000, 0, '', '') }} Kgs)</div> -->
                <div style="white-space: pre-line;line-height: 1.2;">{!! nl2br(e($invoice->description)) !!}</div>
            </td>
            <td class="center border-right" style="padding-top: 10px;vertical-align: top;">${{ number_format((float) $invoice->rate_mt, 2) }}<br>{{ $invoice->rate_note }}</td>
            <td class="center" style="padding-top: 10px;vertical-align: top;">${{ number_format((float) $invoice->amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<table class="border-all" style="border-top: 0px solid #000 !important;">
    <tr>
        <td class="no-border-top" style="width: 42%; vertical-align: middle; padding-left: 10px;text-align: center;">
            <span class="bold no-border-right">PAYMENT TERMS: </span> 
            <span class="bold no-border-right text-red uppercase">{{ $invoice->payment_terms }}</span>
        </td>
        <td class="no-border-top no-border-left" style="width: 58%; padding: 0;">
            <table style="width: 100%; border: none !important;">
                <tr>
                    <td class="bold center no-border-left border-bottom border-right no-border-top bg-gray" style="width: 33%;">TOTALS</td>
                    <td class="bold center no-border-top border-bottom border-right bg-gray" style="width: 33%;">ADVANCE</td>
                    <td class="bold center no-border-top text-red border-bottom no-border-right bg-gray" style="width: 33%;">AMOUNT DUE NOW</td>
                </tr>
                <tr>
                    <td class="bold center no-border-left no-border-bottom border-right" style="padding: 5px;">${{ number_format((float) $invoice->amount, 2) }}</td>
                    <td class="bold center no-border-bottom border-right" style="padding: 5px;">{{ number_format((float) ($invoice->advance ?? 0.00), 2) }}</td>
                    <td class="bold center no-border-bottom no-border-right" style="padding: 5px;">${{ number_format((float) $invoice->amount_due, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="border-all" style="margin-top: 5px;">
    <tr><td class="bold bg-gray box-header border-bottom">REMARKS</td></tr>
    @if($invoice->remarks)
    <tr><td style="padding: 4px; border-bottom: none;">{!! nl2br(e($invoice->remarks)) !!}</td></tr>
    @endif
    
    <tr>
        <td style="padding: 0;border:none !important;">
            <table style="width: 100%;border:none !important;">
                <tr>
                    <td class="no-border-left no-border-bottom no-border-right" style="width: 40%; vertical-align: top; padding: 5px;">
                        <div class="bold" style="margin-bottom: 8px;">BANK DETAILS:</div>
                        <div style="margin-bottom: 2px;"><span class="bold">A/C NAME:</span> {{ strtoupper($setting?->company_name) }}</div>
                        <div style="margin-bottom: 2px;"><span class="bold">BANK NAME:</span> {{ $setting?->bank_name }}</div>
                        <div><span class="bold">BANK ADD.:</span> {{ $setting?->bank_address }}, {{ $setting?->bank_city }}, <br> {{ $setting?->bank_state }} {{ $setting?->bank_zip }}.</div>
                    </td>
                    
                    <td class="no-border-left no-border-right no-border-bottom" style="width: 30%; vertical-align: top; padding-top: 25px;">
                        <div class="bold" style="margin-bottom: 2px;"><span class="bold">A/C NO. :</span> {{ $setting?->bank_account_number }}</div>
                        <div class="bold" style="margin-bottom: 2px;"><span class="bold">SWIFT NO:</span> {{ $setting?->bank_swift_code }}</div>
                        <div class="bold"><span class="bold">ROUTING NO. :</span> {{ $setting?->bank_routing_number }}</div>
                    </td>
                    
                    {{-- <td class="no-border-left no-border-bottom no-border-right" style="width: 30%; vertical-align: top; text-align: center; padding-top: 5px;">
                        <div style="margin-bottom: 10px; color: #555; font-size: 11px;">
                            For and on Behalf of<br>
                            <span style="color: #444; font-size: 13px;">{{ $setting?->company_name }}.</span><br>
                            <span style="font-size: 11px;">Authorised Signature(s)</span>
                        </div>
                        @php
                            $sigPath = isset($asPdf) && $asPdf
                                ? public_path('images/signature.png')
                                : asset('images/signature.png');
                        @endphp
                        <img src="{{ $sigPath }}" alt="Signature" style="height: 45px;">
                    </td> --}}
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="page-break"></div>

<table style="margin-bottom: 20px;">
    <tr>
        <td>
             @php
                $logoPath = isset($asPdf) && $asPdf
                    ? public_path('images/moksham-logo.png')
                    : asset('images/moksham-logo.png');
            @endphp
            <img src="{{ $logoPath }}" alt="Moksham Logo" style="height: 42px;">
        </td>
    </tr>
</table>

<div class="center" style="font-style: italic; margin-bottom: 20px; font-size: 13px;">Terms & Condition</div>

@php
    $tc = $invoice->terms_conditions;
    $tcHasHtml = is_string($tc) && $tc !== strip_tags($tc);
@endphp
<div style="font-size: 12px; margin-bottom: 25px; line-height: 1.4;">
    {!! $tcHasHtml ? $tc : nl2br(e($tc)) !!}
</div>

<div class="bold uppercase" style="margin-bottom: 5px;">ADDITIONAL APPLICABLE CHARGES:</div>
<div style="font-size: 12px; line-height: 1.4;">
    ** Rollover fee: $250 per containers, + terminal rehandling as per the line.<br>
    ** BL correction fee: $75 per BL after 2 amendments.<br>
    ** OBL Courier Fee: Domestic $30.00, International $90.00
</div>

{{-- <div style="margin-top: 60px; text-align: right;">
    <div class="center" style="display: inline-block; width: 220px; margin-right: 30px;">
        <div style="color: #666;font-size: 10px; margin-bottom: 20px;">
            For and on Behalf of<br>
            <span class="bold" style="font-size: 12px;">{{ $setting?->company_name }}.</span><br>
            <span style="font-size: 11px;">Authorised Signature(s)</span>
        </div>
        <img src="{{ $sigPath }}" alt="Signature" style="height: 50px;">
    </div>
</div> --}}

<div class="page-2-footer">
    <div style="border-bottom: 2px solid #000; margin-bottom: 15px;"></div>
    
    <div class="center" style="font-size: 12px;">
        IF YOU HAVE ANY QUESTION PLEASE REACH US AT:<br>
        <a href="mailto:{{ $setting?->company_email }}" class="text-blue bold" style="text-decoration: underline;text-transform:uppercase;">{{ $setting?->company_email ?? 'MOKSHAMUSA108@GMAIL.COM' }}</a>
    </div>
    <div class="center" style="font-size: 12px;margin-top: 5px;">
        PLEASE CONTACT US WITHIN 24 HRS FOR ANY NECESSARY CORRECTION.
    </div>
    <div class="center bold text-blue" style="margin-top: 15px; font-style: italic; font-size: 13px; margin-bottom: 30px;">
        THANK YOU FOR YOUR BUSINESS ....!
    </div>
</div>

</body>
</html>