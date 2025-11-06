<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Certificate of Origin #{{ $certificate->document_no }}</title>
    <style>
        @page { margin: 30px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11.5px; color:#000; }
        header { text-align: center; margin-bottom: 8px; }
        .title { text-align: center; font-weight: bold; font-size: 18px; margin: 4px 0 10px; }
        .grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grid th, .grid td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .grid th { font-weight: 700; }
        .right { text-align: right; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .muted { color: #333; }
        .small { font-size: 10.5px; }
        .no-break { page-break-inside: avoid; }
        .sig-area { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; }
        .sig-box { width: 240px; height: 54px; border-bottom: 1px solid #000; text-align: right; position: relative; }
        .sig-box img { max-height: 48px; max-width: 100%; object-fit: contain; position: absolute; right: 0; bottom: 2px; }
        .sig-label { display: block; margin-top: 6px; font-size: 10px; text-align: right; }
        .stamp-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-top: 18px; }
        .stamp-space { width: 280px; height: 85px; }
        .text-grow { flex: 1; }
    </style>
</head>
<body>
<header>
    @php
        $logoPath = isset($asPdf) && $asPdf
            ? public_path('images/moksham-logo.png')
            : asset('images/moksham-logo.png');
    @endphp
    <img src="{{ $logoPath }}" alt="Moksham Logo" style="height:46px;">
</header>

<div class="title">CERTIFICATE OF ORIGIN</div>

<table class="grid" style="margin-bottom:6px;">
    <tr>
        <th style="width:55%">2. EXPORTER (Principal or seller-licensed and address including ZIP Code)</th>
        <th style="width:10%">ZIP CODE</th>
        <th style="width:17.5%">5. DOCUMENT NUMBER</th>
        <th style="width:17.5%">5A. B/L NUMBER</th>
    </tr>
    <tr>
        <td>
            <div style="font-weight:bold;">{{ strtoupper($setting?->company_name) }}</div>
            <div>{{ $setting?->company_address }}</div>
            @if($setting?->company_address_line2)
                <div>{{ $setting?->company_address_line2 }}</div>
            @endif
            <div>{{ $setting?->company_city }}, {{ $setting?->company_state }}, {{ $setting?->company_country }}</div>
        </td>
        <td class="center nowrap">{{ $setting?->company_zip }}</td>
        <td class="center nowrap">{{ $certificate->document_no }}</td>
        <td class="center nowrap">{{ $certificate->bl_no }}</td>
    </tr>
</table>

<table class="grid" style="margin-bottom:6px;">
    <tr>
        <th style="width:60%">3. CONSIGNED TO</th>
        <th style="width:40%">6. EXPORT REFERENCES</th>
    </tr>
    <tr>
        <td>
            @php
                $consignedBlock = trim((string) ($certificate->consigned_to ?? ''));
                if ($consignedBlock === '') {
                    $consignee = optional($certificate->shipment?->indent)->consignee_name;
                    $consigneeAddr = optional($certificate->shipment?->indent)->consignee_address;
                    $consignedBlock = trim(($consignee ? (strtoupper($consignee)."\n") : '') . ($consigneeAddr ?? ''));
                }
            @endphp
            <div style="white-space: pre-line;">{{ $consignedBlock }}</div>
        </td>
        <td class="small">{{ $certificate->export_references }}</td>
    </tr>
</table>

<table class="grid" style="margin-bottom:6px;">
    <tr>
        <th style="width:60%">4. NOTIFY PARTY/INTERMEDIATE CONSIGNEE (Name and Address)</th>
        <th style="width:40%">7. FORWARDING AGENT (Name and address - reference)</th>
    </tr>
    <tr>
        <td class="small" style="white-space: pre-line;">
            {{ $certificate->notify_party ?: optional($certificate->shipment?->indent)->notify_party }}
        </td>
        <td class="small">{{ $certificate->forwarding_agent }}</td>
    </tr>
</table>

<table class="grid no-break" style="margin-bottom:6px;">
    <tr>
        <th style="width:30%">8. POINT (STATE) OF ORIGIN OR FTZ NUMBER</th>
        <th style="width:30%">9. DOMESTIC ROUTING/EXPORT INSTRUCTIONS</th>
        <th style="width:40%">10. PRE-CARRIAGE BY</th>
    </tr>
    <tr>
        <td>{{ $certificate->origin_or_ftz ?: ($setting?->company_state . ', ' . $setting?->company_country) }}</td>
        <td>{{ $certificate->domestic_routing_instructions }}</td>
        <td>{{ $certificate->pre_carriage_by }}</td>
    </tr>
    <tr>
        <th>12. PRE-CARRIAGE BY</th>
        <th>13. PLACE OF RECEIPT BY PRE-CARRIER</th>
        <th>14. EXPORTING CARRIER</th>
    </tr>
    <tr>
        <td></td>
        <td>{{ $certificate->place_of_receipt }}</td>
        <td>{{ $certificate->exporting_carrier }}</td>
    </tr>
    <tr>
        <th>15. PORT OF LOADING/EXPORT</th>
        <th>16. AREA/PORT OF DISCHARGE</th>
        <th>17. PLACE OF DELIVERY BY ON-CARRIER</th>
    </tr>
    <tr>
        <td>{{ $certificate->port_of_loading }}</td>
        <td>{{ $certificate->port_of_discharge }}</td>
        <td>{{ $certificate->place_of_delivery_on_carrier }}</td>
    </tr>
    <tr>
        <th>18. FINAL DESTINATION (if oncarrier)</th>
        <th>11. TYPE OF MOVE</th>
        <th>11a. CONTAINERIZED (Vessel only)</th>
    </tr>
    <tr>
        <td>{{ optional($certificate->shipment?->indent)->final_destination }}</td>
        <td>{{ $certificate->type_of_move ?? 'Vessel, Containerized' }}</td>
        <td class="center">{{ $certificate->containerized ? 'Yes' : 'No' }}</td>
    </tr>
</table>

<table class="grid no-break" style="margin-bottom:6px;">
    <colgroup>
        <col style="width:25%" />
        <col style="width:10%" />
        <col style="width:40%" />
        <col style="width:12.5%" />
        <col style="width:12.5%" />
    </colgroup>
    <thead>
        <tr>
            <th>MARKS AND NUMBERS</th>
            <th class="center">NUMBER OF PACKAGES</th>
            <th class="center">DESCRIPTION OF COMMODITIES</th>
            <th class="center">GROSS WEIGHT (KG)</th>
            <th class="center">MEASUREMENT</th>
        </tr>
    </thead>
    <tbody>
        @php
            $rows = count($certificate->items) > 0 ? $certificate->items : collect(data_get($computed,'items', []));
            $containerNos = collect($rows)->map(fn($r) => is_object($r) ? $r->marks_numbers : ($r['marks_numbers'] ?? ''))->filter()->values();
            $weights = collect($rows)->map(fn($r) => (float) (is_object($r) ? $r->gross_weight_kg : ($r['gross_weight_kg'] ?? 0)))->filter()->values();
            $totalBales = $certificate->total_bales ?? data_get($computed,'totalBales');
            $totalWeightKg = (float) ($certificate->total_gross_weight_kg ?? data_get($computed,'totalWeightKg'));
            $containerSummary = $certificate->total_packages ?? data_get($computed,'containerCount');
            $shipSummary = data_get($computed,'containersSummary');
        @endphp
        <tr>
            <td style="white-space: pre-line;">{{ $containerNos->implode("\n") }}</td>
            <td class="center">{{ $totalBales ? ($totalBales.' BALES') : '' }}</td>
            <td>
                <div>{{ $shipSummary }}</div>
                <div><strong>{{ $certificate->description ?? 'WASTEPAPER OCC. 11' }}</strong></div>
                <div>TOTAL WEIGHT: {{ number_format($totalWeightKg, 3) }} KGS</div>
                <div>TOTAL BALES: {{ $totalBales }}</div>
            </td>
            <td class="right" style="white-space: pre-line;">
                {{ $weights->map(fn($w) => number_format($w, 3).' Kg')->implode("\n") }}
            </td>
            <td></td>
        </tr>
    </tbody>
</table>

<div class="no-break" style="margin-top:8px;">
    <div class="small">
        The undersigned, <strong>{{ strtoupper($setting?->company_name) }}</strong> (Owner or Agent), does hereby declare for the above named shipper, the goods as described above were shipped on the above date and consigned as indicated and are products of the <strong>UNITED STATES OF AMERICA</strong> dated at <strong>{{ $setting?->company_state }}</strong> on <strong>{{ optional($certificate->shipped_date)->format('d-F-Y') }}</strong>.
    </div>
    <div class="small" style="margin-top:6px;">
        Sworn to before me on <strong>{{ optional($certificate->sworn_date)->format('d-F-Y') }}</strong>
    </div>

    @php
        $ownerSig = $certificate->owner_signature_path;
        if ($ownerSig) {
            $ownerSig = isset($asPdf) && $asPdf
                ? public_path('storage/' . ltrim($ownerSig, '/'))
                : asset('storage/' . ltrim($ownerSig, '/'));
        }
        $chSig = $certificate->chamber_signature_path;
        if ($chSig) {
            $chSig = isset($asPdf) && $asPdf
                ? public_path('storage/' . ltrim($chSig, '/'))
                : asset('storage/' . ltrim($chSig, '/'));
        }
    @endphp

    <div class="sig-area" style="margin-top: 10px;">
        <div class="text-grow"></div>
        <div class="sig-box">
            @if(!empty($ownerSig))
                <img src="{{ $ownerSig }}" alt="Owner Signature">
            @endif
        </div>
    </div>
    <span class="sig-label">Signature of Owner or Agent</span>

    <div class="stamp-row">
        <div class="stamp-space">
            {{-- Reserved empty space for chamber stamp (no border) --}}
        </div>
        <div class="text-grow medium">
            The  ________________________________________________________________  a recognized Chamber of Commerce under the laws of the State of ____________________________________ certifies in reliance on the exporter's representation and not on the basis of independent verification, that to the best of its knowledge and belief, the products named in this document originated in the United States of America.
        </div>
        <div style="width: 240px;">
            <div class="sig-box">
                @if(!empty($chSig))
                    <img src="{{ $chSig }}" alt="Chamber Signature">
                @endif
            </div>
            <span class="sig-label">Authorized Signature</span>
        </div>
    </div>
</div>

</body>
</html>
