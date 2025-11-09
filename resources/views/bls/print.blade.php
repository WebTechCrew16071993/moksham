<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Bill of Lading</title>
    <style>
        @page { margin: 20px 25px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        .heading { text-align: center; font-weight: bold; font-size: 16px; margin: 5px 0 10px; text-transform: uppercase; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .label { font-weight: bold; width: 28%; }
        .muted { color: #333; }
        .right { text-align: right; }
        .center { text-align: center; }
        .small { font-size: 10px; }
        .block-title { font-weight: bold; text-transform: uppercase; }
        .no-border td, .no-border th { border: none; }
        .mt-6 { margin-top: 6px; }
        .mt-10 { margin-top: 10px; }
        .mb-10 { margin-bottom: 10px; }
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

    <div class="heading">Bill of Lading</div>

    <table class="grid">
        <tr>
            <td style="width:50%">
                <div class="block-title">Shipper:</div>
                <div class="muted">
                    <div>{{ $shipperName }}</div>
                    @foreach($shipperAddrLines as $line)
                        @if($line) <div>{{ $line }}</div> @endif
                    @endforeach
                    <div>PH {{ $shipperPhone }}</div>
                    <div>EMAIL: {{ $shipperEmail }}</div>
                </div>
            </td>
            <td style="width:50%">
                <table class="grid" style="border:0;">
                    <tr>
                        <td style="border:0; width:50%">
                            <div class="block-title">Consignee's Party:</div>
                            <div class="muted" style="white-space: pre-line;">{{ $consigneeBlock }}</div>
                            @if($bl->consignee_iec)<div>IEC: {{ $bl->consignee_iec }}</div>@endif
                            @if($bl->consignee_gstin)<div>GSTIN: {{ $bl->consignee_gstin }}</div>@endif
                            @if($bl->consignee_pan)<div>PAN: {{ $bl->consignee_pan }}</div>@endif
                            @if($bl->consignee_email)<div>E-mail: {{ $bl->consignee_email }}</div>@endif
                        </td>
                        <td style="border:0; width:50%">
                            <div class="block-title">Notify Party:</div>
                            <div class="muted" style="white-space: pre-line;">{{ $notifyBlock }}</div>
                            @if($bl->notify_party_iec)<div>IEC: {{ $bl->notify_party_iec }}</div>@endif
                            @if($bl->notify_party_gstin)<div>GSTIN: {{ $bl->notify_party_gstin }}</div>@endif
                            @if($bl->notify_party_pan)<div>PAN: {{ $bl->notify_party_pan }}</div>@endif
                            @if($bl->notify_party_email)<div>E-mail: {{ $bl->notify_party_email }}</div>@endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="grid mt-10">
        <tr>
            <td class="label">Port Of Loading</td><td>{{ $bl->port_of_loading }}</td>
            <td class="label">Origin</td><td>{{ $bl->origin }}</td>
        </tr>
        <tr>
            <td class="label">Net Weight (KGS)</td><td class="right">{{ number_format((float)$bl->net_weight_kgs, 3, '.', ',') }}</td>
            <td class="label">Destination</td><td>{{ $bl->destination }}</td>
        </tr>
        <tr>
            <td class="label">Cargo Value</td><td>{{ ($bl->currency ?? 'USD') }} {{ number_format((float)$bl->cargo_value, 2) }}</td>
            <td class="label">Booking No</td><td>{{ $bl->booking_no }}</td>
        </tr>
        <tr>
            <td class="label">Packaging Types</td><td>{{ $bl->packaging_type }}</td>
            <td class="label">Ship In</td><td>{{ $bl->ship_in }}</td>
        </tr>
        <tr>
            <td class="label">Type of Document</td><td>{{ $bl->document_type }}</td>
            <td class="label">No of Container</td><td>{{ $bl->no_of_containers }} {{ $bl->container_type }}</td>
        </tr>
    </table>

    <table class="grid mt-10">
        <thead>
            <tr>
                <th>Container No.</th>
                <th>Seal No.</th>
                <th>Commodity Details</th>
                <th>No. of Bales</th>
                <th class="right">Weight KGS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($containerRows as $row)
                <tr>
                    <td>{{ $row->container_no }}</td>
                    <td>{{ $row->seal_no }}</td>
                    <td>{{ $row->commodity }}</td>
                    <td class="center">{{ $row->no_of_bales }}</td>
                    <td class="right">{{ number_format((float)$row->weight_kgs, 3, '.', ',') }}</td>
                </tr>
            @endforeach
            @for($i=count($containerRows); $i<5; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="grid mt-10">
        <tr>
            <td class="label">Total Bales</td><td class="center">{{ $bl->total_bales }}</td>
            <td class="label">Description</td><td>{{ $bl->commodity_description }}</td>
            <td class="label">Total Weight in KGS</td><td class="right">{{ number_format((float)$bl->net_weight_kgs, 3, '.', ',') }}</td>
        </tr>
        <tr>
            <td class="label">In MTS</td><td class="center">{{ number_format((float)$bl->net_weight_mts, 3, '.', ',') }}</td>
            <td colspan="4"></td>
        </tr>
    </table>

    <div class="mt-10 small center">
        @if(!$asPdf)
            <em>Preview — use the PDF action to download/print</em>
        @endif
        <div class="mt-6">If you have any question please reach us at: <strong>{{ $shipperEmail }}</strong></div>
        <div>Please contact us within 24 hrs for any necessary correction.</div>
        <div class="mt-6"><strong>Thank you for your business!</strong></div>
    </div>
</body>
</html>
