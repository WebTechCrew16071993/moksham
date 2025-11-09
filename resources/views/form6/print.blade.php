<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>FORM 6</title>
    <style>
        @page { margin: 15px 20px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .center { text-align: center; }
        .heading { text-align: center; font-weight: bold; font-size: 18px; margin-bottom: 6px; }
        .subheading { text-align: center; font-size: 11px; margin-bottom: 8px; }
        .small { font-size: 10px; }
        .label { font-weight: bold; }
        pre { white-space: pre-line; margin: 0; font-family: inherit; }
    </style>
</head>
<body>
    <?php $f=$form6; ?>
    <div class="heading">FORM 6</div>
    <div class="subheading">[See rules 13(2), 13 (10) and 14 (5)]<br>TRANSBOUNDARY MOVEMENT - MOVEMENT DOCUMENT</div>

    <table>
        <tr>
            <th style="width:6%">S.No</th>
            <th style="width:34%">Description</th>
            <th style="width:60%">Details to be furnished by the exporter or importer</th>
        </tr>
        <tr>
            <td class="center">1</td>
            <td>Exporter (Name and Address)<br>Contact Person<br>Tele, Fax and Email</td>
            <td>
                <pre>{{ $f->exporter_name_address }}
{{ $f->exporter_contact_person }}
{{ $f->exporter_phone }}
{{ $f->exporter_email }}</pre>
            </td>
        </tr>
        <tr>
            <td class="center">2</td>
            <td>Generator(s) of the waste (Name and Address)<br>Contact Person<br>Tele, Fax and Email<br>Site of Generation</td>
            <td>
                <pre>{{ $f->generator_name_address }}
{{ $f->generator_contact_person }}
{{ $f->generator_phone }}
{{ $f->generator_email }}</pre>
                <div class="small">Site of Generation: {{ $f->site_of_generation }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">3</td>
            <td>Importer or Actual user (Name and Address)<br>Contact Person<br>Tele, Fax and Email</td>
            <td>
                <pre>{{ $f->importer_name_address }}
{{ $f->importer_contact_person }}
{{ $f->importer_phone }}
{{ $f->importer_email }}</pre>
            </td>
        </tr>
        <tr>
            <td class="center">4</td>
            <td>Trader (Name and Address)<br>Contact Person<br>Tele, Fax and Email<br>Details of actual user (Name, Address, Telephone and Email)</td>
            <td>
                <pre>{{ $f->trader_name_address }}
{{ $f->trader_contact_person }}
{{ $f->trader_phone }}
{{ $f->trader_email }}</pre>
                <div>Details of actual user: {{ $f->actual_user_details }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">5</td>
            <td>Corresponding to applicant Ref No., if any</td>
            <td>{{ $f->applicant_ref_no }}</td>
        </tr>
        <tr>
            <td class="center">6</td>
            <td>Bill of lading (attach copy)</td>
            <td>{{ $f->bill_of_lading }}</td>
        </tr>
        <tr>
            <td class="center">7</td>
            <td>Country of Import / Export</td>
            <td>{{ $f->country_of_import }} / {{ $f->country_of_export }}</td>
        </tr>
        <tr>
            <td class="center">8</td>
            <td>General Description of waste</td>
            <td>
                <div>(a) Quantity: {{ number_format((float)$f->quantity_kgs, 3, '.', ',') }} KGS</div>
                <div>(b) Physical Characteristics: {{ $f->physical_characteristics }}</div>
                <div>(c) Chemical composition of waste (attach details), where applicable: {{ $f->chemical_composition }}</div>
                <div>(d) Basel No.: {{ $f->basel_no }}</div>
                <div>(e) UN Shipping Name: {{ $f->un_shipping_name }}</div>
                <div>(f) UN Class: {{ $f->un_class }}</div>
                <div>(g) UN No.: {{ $f->un_no }}</div>
                <div>(h) H Number: {{ $f->h_number }}</div>
                <div>(i) Y Number: {{ $f->y_number }}</div>
                <div>(j) ITC (HS): {{ $f->itc_hs }}</div>
                <div>(k) Customs Code (H.S.): {{ $f->customs_code_hs }}</div>
                <div>(l) Other (specify): {{ $f->other_codes }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">9</td>
            <td>Type of packages<br>Number</td>
            <td>{{ $f->package_type }}<br>{{ $f->package_number }}</td>
        </tr>
        <tr>
            <td class="center">10</td>
            <td>Special handling requirements including emergency provision in case of accidents</td>
            <td>{{ $f->special_handling_requirements }}</td>
        </tr>
        <tr>
            <td class="center">11</td>
            <td>Movement subject to single / multiple consignment</td>
            <td>
                <div>{{ strtoupper($f->movement_type ?? 'SINGLE') }}</div>
                @if(($f->movement_type ?? 'single') === 'multiple')
                    <table style="width:100%; border-collapse: collapse; margin-top:6px;">
                        <tr>
                            <td style="width:40%; border:1px solid #000; padding:4px;">(a) Expected dates of each shipment or expected frequency of the shipments</td>
                            <td style="border:1px solid #000; padding:4px;">{{ $f->expected_shipment_dates ?: 'N.A.' }}</td>
                        </tr>
                        <tr>
                            <td style="width:40%; border:1px solid #000; padding:4px;">(b) Estimated total quantity and quantities for each individual shipment</td>
                            <td style="border:1px solid #000; padding:4px;">{{ $f->estimated_quantities ?: 'N.A.' }}</td>
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
        <tr>
            <td class="center">12</td>
            <td>Transporter of waste (Name and Address)
                <div class="small">Registration number</div>
                <div class="small">Means of transport (road, rail, inland waterway, sea, air)</div>
                <div class="small">Date of Transfer</div>
                <div class="small">Signature of Carrier's representative</div>
            </td>
            <td>
                <div class="small">{{ $f->transporter_name_address }}</div>
                <div class="small">{{ $f->transporter_registration_no }}</div>
                <div class="small">{{ strtoupper($f->means_of_transport) }}</div>
                <div class="small">{{ $f->date_of_transfer?->format('d/m/Y') }}</div>
                <div class="small">{{ $f->carrier_signature }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">13</td>
            <td>Exporter's declaration for hazardous and other waste</td>
            <td>
                <div class="small">{{ $f->exporter_declaration }}</div>
                <div>Signature</div>
                <div style="height:30px;">{{ $f->exporter_signature_name }}</div>
                <div>Name</div>
                <div>{{ $f->exporter_signature_name }}</div>
                <div>Date</div>
                <div>{{ $f->exporter_signature_date?->format('d/m/Y') }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">14</td>
            <td>Shipment received by importer/actual user/trader
                <div class="small">Quantity received</div>
                <div class="small">Signature</div>
                <div class="small">Name</div>
                <div class="small">Date</div>
            </td>
            <td>
                <div class="small">{{ $f->quantity_received_kgs }}</div>
                <div class="small">{{ $f->importer_signature_name }}</div>
                <div class="small">{{ $f->importer_signature_name }}</div>
                <div class="small">{{ $f->importer_signature_date?->format('d/m/Y') }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">15</td>
            <td>Corresponding to applicant Ref No., if any<br>R code*<br>Technology employed (Attached details if necessary)</td>
            <td>
                <div>{{ $f->importer_applicant_ref_no }}</div>
                <div>{{ $f->r_code }}</div>
                <div>{{ $f->technology_employed }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">16</td>
            <td>I certify that nothing other than declared goods covered as per these rules is intended to be imported in the above referred consignment and will be recycled/utilized. Signature<br>Date</td>
            <td>
                <div style="height:30px;">{{ $f->importer_certification_signature }}</div>
                <div>{{ $f->importer_certification_date?->format('d/m/Y') }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">17</td>
            <td>Specific conditions on consenting to the movement if applicable</td>
            <td>{{ $f->specific_conditions }}</td>
        </tr>
    </table>
    <?php
        $defaultNotes = "(1) Attach list, if more than one; (2) Select appropriate option; (3) Immediately contact competent authority in case of any\nemergency; (4) If more than one transporter carriers, attach information as required in SL. No. 12";
        $notesText = trim($f->notes ?? '') !== '' ? $f->notes : $defaultNotes;
    ?>
    <div style="margin-top:6px;"><pre class="small">{{ $notesText }}</pre></div>

    <div style="page-break-before: always;"></div>
    <div class="heading">List of abbreviations used in the Movement Document<br><span class="small">Recovery Operations (*)</span></div>
    <?php
        $defaultOps = [
            ['code' => 'R1', 'description' => 'Use as a fuel (other than in direct incineration) or other means to generate energy.'],
            ['code' => 'R2', 'description' => 'Solvent reclamation/regeneration.'],
            ['code' => 'R3', 'description' => 'Recycling/reclamation of organic substances which are not used as solvents.'],
            ['code' => 'R4', 'description' => 'Recycling/reclamation of metals and metal compounds.'],
            ['code' => 'R5', 'description' => 'Recycling/reclamation of other inorganic materials.'],
            ['code' => 'R6', 'description' => 'Regeneration of acids or bases.'],
            ['code' => 'R7', 'description' => 'Recovery of components used for pollution abatement.'],
            ['code' => 'R8', 'description' => 'Recovery of components from catalysts.'],
            ['code' => 'R9', 'description' => 'Used oil re-refining or other reuses of previously used oil.'],
            ['code' => 'R10', 'description' => 'Land treatment resulting in benefit to agriculture or ecological improvement'],
            ['code' => 'R11', 'description' => 'Uses of residual materials obtained from any of the operations numbered R1 to R10'],
        ];
        $ops = is_array($f->recovery_operations) && count($f->recovery_operations) > 0
            ? array_map(function($row) {
                return ['code' => $row['code'] ?? '', 'description' => $row['description'] ?? ''];
              }, $f->recovery_operations)
            : $defaultOps;
    ?>
    <table style="border:1px solid #000;">
        <?php foreach ($ops as $row): ?>
            <tr>
                <td style="width:10%; border:1px solid #000; padding:6px;"><strong>{{ $row['code'] }}</strong></td>
                <td style="border:1px solid #000; padding:6px;">{{ $row['description'] }}</td>
            </tr>
        <?php endforeach; ?>
    </table>

    <table style="margin-top: 24px; border:0">
        <tr>
            <td style="width:55%; border:0"></td>
            <td style="width:45%; border:0">
                <table style="width:100%; border:0">
                    <tr>
                        <td style="border:0; text-align:left; width:40%" class="small">Signature</td>
                        <td style="border:0; width:60%">
                            <div style="height:60px;">
                                @if(!empty($f->exporter_signature_image_path))
                                    <img src="{{ public_path('storage/'.ltrim($f->exporter_signature_image_path,'/')) }}" style="max-height:60px; max-width:100%;" />
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border:0;" class="small">Name</td>
                        <td style="border:0;">{{ $f->exporter_signature_name }}</td>
                    </tr>
                    <tr>
                        <td style="border:0;" class="small">Date</td>
                        <td style="border:0;">{{ $f->exporter_signature_date?->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="margin-top: 18px; width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:50%; border:1px solid #000; padding:6px"><span class="label">Place</span><br>{{ $f->signature_place }}</td>
            <td style="width:50%; border:1px solid #000; padding:6px"><span class="label">Designation</span><br>{{ $f->signature_designation }}</td>
        </tr>
    </table>
</body>
</html>
