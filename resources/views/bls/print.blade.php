<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Bill of Lading</title>
    <style>
        @page { margin: 60px 60px; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13px !important;
            color: #000;
            line-height: 1.15;
        }
        .heading {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin: 5px 0 15px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grid th, .grid td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        /* Specific Label Styles to match Image Inconsistency */
        .label-left { font-weight: bold; width: 20%; }
        .label-mid { font-weight: bold; width: 18%; text-transform: uppercase; }
        
        .header-bg { background-color: #e6e6e6; font-weight: bold; text-align: center; }
        
        .text-red { color: #dd2b1c !important; }
        .text-blue { color: #0070c0 !important; }
        .italic { font-style: italic; }
        
        .right { text-align: right; }
        .center { text-align: center; }
        
        .mt-10 { margin-top: 15px; }
        .mt-20 { margin-top: 20px; }
        .mt-6 { margin-top: 6px; }
        
        .block-title { font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        
        /* Helper for stacking text in headers */
        .stack { display: block; }
        
        /* Empty row height */
        .h-row { height: 18px; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php
        $indent = optional($bl->shipment)->indent;
        $shipperName = optional($setting)->company_name ?? 'MOKSHAM EXPORT IMPORT LLC';
        $shipperAddrLines = array_filter([
            optional($setting)->company_address,
            trim((optional($setting)->company_city.' '.optional($setting)->company_state.' '.optional($setting)->company_zip)),
            optional($setting)->company_country,
        ]);
        $shipperPhone = optional($setting)->company_phone ?? '201-982-4039';
        $shipperEmail = optional($setting)->company_email ?? 'Mokshamusa108@gmail.com';

        $consigneeBlock = $bl->consignee_details ?: ($indent?->consignee_name.'\n'.$indent?->consignee_address);
        $notifyBlock = $bl->notify_party_details ?: $consigneeBlock;
        $containerRows = $bl->items;
    ?>

    {{-- Top Parties Table (2x2 Grid based on image) --}}
    <table class="grid" style="font-size: 14px;">
        <tr>
            <td width="50%" style="height: 100px;">
                <div class="block-title" style="margin-bottom: 0px;">SHIPPER:</div>
                <div class="bold">{{ $shipperName }}</div>
                @foreach($shipperAddrLines as $line)
                    @if($line) <div style="text-transform: uppercase;">{{ $line }}</div> @endif
                @endforeach
                <div>PH {{ $shipperPhone }}</div>
                <div>EMAIL:{{ $shipperEmail }}</div>
            </td>
            <td width="50%">
                {{-- Top right cell is empty in image --}}
            </td>
        </tr>
        <tr>
            <td>
                <div class="block-title" style="margin-bottom: 10px;">CONSIGNEES PARTY:</div>
                <div style="white-space: pre-line;">{{ $consigneeBlock }}</div>
                <div class="mt-6">
                    @if($bl->consignee_iec)<div>IEC: {{ $bl->consignee_iec }}</div>@endif
                    @if($bl->consignee_gstin)<div>GSTIN: {{ $bl->consignee_gstin }}</div>@endif
                    @if($bl->consignee_pan)<div>PAN No. : {{ $bl->consignee_pan }}</div>@endif
                    @if($bl->consignee_email)<div>E-mail: <span class="text-blue" style="text-decoration: underline;">{{ $bl->consignee_email }}</span></div>@endif
                </div>
            </td>
            <td>
                <div class="block-title" style="margin-bottom: 10px;">NOTIFY PARTY:</div>
                <div style="white-space: pre-line;">{{ $notifyBlock }}</div>
                <div class="mt-6">
                    @if($bl->notify_party_iec)<div>IEC: {{ $bl->notify_party_iec }}</div>@endif
                    @if($bl->notify_party_gstin)<div>GSTIN: {{ $bl->notify_party_gstin }}</div>@endif
                    @if($bl->notify_party_pan)<div>PAN No. : {{ $bl->notify_party_pan }}</div>@endif
                    @if($bl->notify_party_email)<div>E-mail: <span class="text-blue" style="text-decoration: underline;">{{ $bl->notify_party_email }}</span></div>@endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Middle Details Table --}}
    <table class="grid mt-10">
        <tr>
            <td style="width: 20%" class="bold">Port Of Loading</td>
            <td style="text-transform: uppercase;width: 22%">{{ $bl->port_of_loading }}</td>
            <td style="width: 22%" class="bold uppercase">ORIGIN:</td>
            <td style="text-transform: uppercase;width: 22%">{{ $bl->origin }}</td>
            <td></td>
        </tr>
        <tr>
            <td class="bold">Net Weight KGS</td><td class="bold" style="text-transform: uppercase;">{{ number_format((float)$bl->net_weight_kgs, 3, '.', '') }} (KGS)</td>
            <td class="bold uppercase">DESTINATION</td><td style="text-transform: uppercase;">{{ $bl->destination }}</td>
            <td></td>
        </tr>
        <tr>
            <td class="bold">Cargo Value</td><td style="text-transform: uppercase;">{{ ($bl->currency ?? '$') }}{{ number_format((float)$bl->cargo_value, 2) }}</td>
            <td class="bold uppercase">BOOKING NO:</td><td style="text-transform: uppercase;">{{ $bl->booking_no }}</td>
            <td></td>
        </tr>
        <tr>
            <td class="bold">Packaging Types</td><td style="text-transform: uppercase;">{{ $bl->packaging_type }}</td>
            <td class="bold uppercase">SHIP IN</td><td style="text-transform: uppercase;">{{ $bl->ship_in }}</td>
            <td></td>
        </tr>
        <tr>
            <td class="bold">Type of Document</td><td style="text-transform: uppercase;" class="bold text-red">{{ $bl->document_type }}</td>
            <td class="bold uppercase">NO OF CONTAINER</td><td style="text-transform: uppercase;">{{ $bl->no_of_containers }} {{ $bl->container_type }}</td>
            <td></td>
        </tr>
    </table>

    {{-- Container Table --}}
    <table class="grid mt-20">
        <thead>
            <tr class="header-bg uppercase">
                <th style="width: 18%">CONTAINER NO.</th>
                <th style="width: 10%">SEAL NO.</th>
                <th style="width: 37%">COMMODITY DETAILS.</th>
                <th style="width: 10%">NO. OF BALES</th>
                <th style="width: 25%">WEIGHT<br>KGS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($containerRows as $row)
                <tr>
                    <td class="center">{{ $row->container_no }}</td>
                    <td class="center">{{ $row->seal_no }}</td>
                    <td class="center">{{ $row->commodity }}</td>
                    <td class="center">{{ $row->no_of_bales }}</td>
                    <td class="center">{{ number_format((float)$row->weight_kgs, 3, '.', '') }}</td>
                </tr>
            @endforeach
            <!-- {{-- Fill rows to match image height (approx 10 total slots) --}}
            @for($i=count($containerRows); $i<10; $i++)
                <tr>
                    <td class="h-row">&nbsp;</td>
                    <td class="h-row">&nbsp;</td>
                    <td class="h-row">&nbsp;</td>
                    <td class="h-row">&nbsp;</td>
                    <td class="h-row">&nbsp;</td>
                </tr>
            @endfor -->
        </tbody>
    </table>

    {{-- Totals Table --}}
    <table class="grid mt-10">
        <tr class="header-bg bold uppercase">
            <td style="width: 18%">TOTAL BALES</td>
            <td style="width: 57%">DESCRIPTION</td>
            <td style="width: 25%" style="padding-left:0;padding-right:0;">TOTAL WEIGHT IN KGS</td>
        </tr>
        <tr>
            <td class="center">{{ $bl->total_bales }}</td>
            <td class="center uppercase">{{ $bl->commodity_description }}</td>
            <td class="center bold">{{ number_format((float)$bl->net_weight_kgs, 3, '.', '') }}</td>
        </tr>
    </table>

    {{-- IN MTS Floating Table --}}
    <table class="grid mt-10" style="border-top: 0; width: 100%;">
        <tr>
            <td style="width:57%; border:0;"></td>
            <td style="width: 18%" class="header-bg bold center">IN MTS</td>
            <td style="width: 25%" class="center bold">{{ number_format((float)$bl->net_weight_mts, 6, '.', '') }}</td>
        </tr>
    </table>

    <div class="mt-10 center" style="margin-top: 20px;">
        @if(!$asPdf)
            <div style="margin-bottom: 10px; font-size: 10px;"><em>Preview Mode</em></div>
        @endif
        
        <div class="footer">
            <div style="margin-bottom: 8px;">
                <span class="uppercase">IF YOU HAVE ANY QUESTION PLEASE REACH US AT:</span> 
                <a href="mailto:{{ $shipperEmail }}" class="text-blue bold" style="text-decoration: underline; text-transform: uppercase;">{{ $shipperEmail }}</a>
            </div>
            <div class="uppercase" style="margin-bottom: 15px;">PLEASE CONTACT US WITHIN 24 HRS FOR ANY NECESSARY CORRECTION.</div>
            
            <div class="text-blue italic bold uppercase" style="color: #0070c0;">THANK YOU FOR YOUR BUSINESS ....!</div>
        </div>
    </div>
</body>
</html>