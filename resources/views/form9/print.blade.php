<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>FORM 9</title>
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
    <?php $f=$form9; ?>
    <?php
        // Local helpers to render values with graceful fallbacks
        $disp = function ($v, $fallback = 'N.A.') {
            if (is_numeric($v)) {
                return (string) $v;
            }
            $t = trim((string)($v ?? ''));
            return $t !== '' ? $t : $fallback;
        };
        $date = function ($v, $fallback = 'N.A.') {
            return $v ? $v->format('d/m/Y') : $fallback;
        };
    ?>
    <div class="heading">FORM 9</div>
    <div class="subheading">[See rule - 15 (5) and 16 (5)]<br>TRANSBOUNDARY MOVEMENT- MOVEMENT DOCUMENT</div>

    <table>
        <tr>
            <th style="width:6%">S.No</th>
            <th style="width:34%">Description</th>
            <th style="width:60%">Details to be furnished by the exporter or importer</th>
        </tr>
        <tr>
            <td class="center">1</td>
            <td>(i) Exporter (Name & Address)</td>
            <td><pre>{{ $disp($f->exporter_name_address) }}
{{ $disp($f->exporter_contact_person) }}
{{ $disp($f->exporter_phone) }}
{{ $disp($f->exporter_email) }}</pre></td>
        </tr>
        <tr>
            <td class="center"></td>
            <td>(ii) Waste Generator (Name & Address)</td>
            <td><pre>{{ $disp($f->generator_name_address) }}
{{ $disp($f->generator_contact_person) }}
{{ $disp($f->generator_phone) }}
{{ $disp($f->generator_email) }}</pre>
            <div class="small">Site of Generation: {{ $disp($f->site_of_generation) }}</div></td>
        </tr>
        <tr>
            <td class="center">2</td>
            <td>Importer / Recycler (Name & Address)</td>
            <td><pre>{{ $disp($f->importer_name_address) }}
{{ $disp($f->importer_contact_person) }}
{{ $disp($f->importer_phone) }}
{{ $disp($f->importer_email) }}</pre></td>
        </tr>
        <tr>
            <td class="center">3</td>
            <td>Corresponding to applicant Ref. No.</td>
            <td>{{ $disp($f->applicant_ref_no) }}</td>
        </tr>
        <tr>
            <td class="center"></td>
            <td>Movement subject to single / multiple</td>
            <?php $mtMap = ['single' => 'Single', 'multiple' => 'Multiple']; $mt = $mtMap[$f->movement_type ?? ''] ?? null; ?>
            <td>{{ $disp($mt) }}</td>
        </tr>
        <tr>
            <td class="center">4</td>
            <td>Bill of lading (attach copy)</td>
            <td>{{ $disp($f->bill_of_lading) }}</td>
        </tr>
        <tr>
            <td class="center">5</td>
            <td>Carriers</td>
            <td>
                <?php
                    $carriers = $form9->relationLoaded('carriers') ? $form9->carriers : $form9->carriers()->get();
                    $byType = [
                        'first' => $carriers->firstWhere('carrier_type','first'),
                        'second' => $carriers->firstWhere('carrier_type','second'),
                        'last' => $carriers->firstWhere('carrier_type','last'),
                    ];
                    $labels = [
                        'first' => '5(a) 1st Carrier',
                        'second' => '5(b) 2nd Carrier',
                        'last' => '5(c) Last Carrier',
                    ];
                ?>
                <?php foreach ($byType as $key => $c): ?>
                    <div class="small label" style="margin-top:6px;">{{ $labels[$key] }}</div>
                    <?php if($c): ?>
                        <pre class="small">{{ $disp($c->name_address) }}
Registration Number: {{ $disp($c->registration_no) }}
Identity of Means of Transport: {{ $disp($c->transport_identity) }}
Date of Transfer: {{ $date($c->transfer_date) }}
Signature of Carrier's representative: {{ $disp($c->signature) }}</pre>
                    <?php else: ?>
                        <div class="small">N.A.</div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <td class="center">6</td>
            <td>Disposer (Name, Address)</td>
            <td>
                <pre>{{ $disp($f->disposer_name_address) }}</pre>
                <div class="small">Contact person: {{ $disp($f->disposer_contact_person) }}</div>
                <div class="small">Actual site of disposal: {{ $disp($f->actual_site_of_disposal) }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">7</td>
            <td>Method(s) of Recovery</td>
            <td>
                <div>Method: {{ $disp($f->method_of_recovery) }}</div>
                <div>R Code: {{ $disp($f->r_code) }}</div>
                <div>Technology employed: {{ $disp($f->technology_employed) }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">8</td>
            <td>Designation and chemical composition of the waste</td>
            <td>{{ $disp($f->waste_designation_composition) }}</td>
        </tr>
        <tr>
            <td class="center">9</td>
            <td>Physical characteristics</td>
            <td>{{ $disp($f->physical_characteristics) }}</td>
        </tr>
        <tr>
            <td class="center">10</td>
            <td>Actual quantity Kg/Lt</td>
            <td>{{ $f->actual_quantity_kgs !== null ? number_format((float)$f->actual_quantity_kgs, 3, '.', ','). ' KGS' : 'N.A.' }}</td>
        </tr>
        <tr>
            <td class="center">11</td>
            <td>Waste identification Code</td>
            <td>
                <div>Base No.: {{ $disp($f->basel_no) }}</div>
                <div>UN No.: {{ $disp($f->un_no) }}</div>
                <div>ITC (HS): {{ $disp($f->itc_hs) }}</div>
                <div>Customs Code (HS): {{ $disp($f->customs_code_hs) }}</div>
                <div>Other: {{ $disp($f->other_codes) }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">12</td>
            <td>OECD Classification</td>
            <td>{{ $disp($f->oecd_classification) }} {{ $disp($f->oecd_color) }} {{ $disp($f->oecd_number) }}</td>
        </tr>
        <tr>
            <td class="center">13</td>
            <td>Packaging Type / Number</td>
            <td>{{ $disp($f->packaging_type) }} / {{ $disp($f->packaging_number) }}</td>
        </tr>
        <tr>
            <td class="center">14</td>
            <td>UN Classification</td>
            <td>
                <div>UN Shipping Name: {{ $disp($f->un_shipping_name) }}</div>
                <div>UN Identification No.: {{ $disp($f->un_identification_no) }}</div>
                <div>UN Class: {{ $disp($f->un_class) }}</div>
                <div>H Number: {{ $disp($f->h_number) }}</div>
                <div>Y Number: {{ $disp($f->y_number) }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">15</td>
            <td>Special handling requirements</td>
            <td>{{ $disp($f->special_handling_requirements) }}</td>
        </tr>
        <tr>
            <td class="center">16</td>
            <td>Actual date of Shipment</td>
            <td>{{ $date($f->actual_shipment_date) }}</td>
        </tr>
        <tr>
            <td class="center">17</td>
            <td>Exporter's declaration</td>
            <td>
                <div class="small">{{ $disp($f->exporter_declaration) }}</div>
                <table style="width:100%; border:0; margin-top:8px;">
                    <tr>
                        <td style="border:0; width:40%" class="small">Signature</td>
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
                        <td style="border:0;">{{ $disp($f->exporter_signature_name) }}</td>
                    </tr>
                    <tr>
                        <td style="border:0;" class="small">Date</td>
                        <td style="border:0;">{{ $date($f->exporter_declaration_date) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="center">18</td>
            <td>Shipment received by Importer/Recycler</td>
            <td>
                <div class="small">Quantity received: {{ $disp($f->quantity_received_importer) }}</div>
                <div class="small">Date: {{ $date($f->date_received_importer) }}</div>
                <div class="small">Signature: {{ $disp($f->signature_received_importer) }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">19</td>
            <td>Shipment received at Recycler</td>
            <td>
                <div class="small">Quantity received: {{ $disp($f->quantity_received_recycler) }}</div>
                <div class="small">Quantity accepted: {{ $disp($f->quantity_accepted_recycler) }}</div>
                <div class="small">Date: {{ $date($f->date_received_recycler) }}</div>
                <div class="small">Signature: {{ $disp($f->signature_received_recycler) }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">20</td>
            <td>Approximate date of recycling</td>
            <td>{{ $date($f->approximate_recycling_date) }}</td>
        </tr>
        <tr>
            <td class="center">21</td>
            <td>Method of recycling</td>
            <td>{{ $disp($f->method_of_recycling) }}</td>
        </tr>
        <tr>
            <td class="center">22</td>
            <td>Recycler certification</td>
            <td>
                <div class="small">{{ $disp($f->recycler_certification) }}</div>
                <div class="small">Date: {{ $date($f->recycler_certification_date) }}</div>
                <div class="small">Signature: {{ $disp($f->recycler_certification_signature) }}</div>
            </td>
        </tr>
        <tr>
            <td class="center">23</td>
            <td>Specific Conditions on Consenting to the Movement</td>
            <td>{{ $disp($f->specific_conditions) }}</td>
        </tr>
    </table>

    <?php
        $defaultNotes = "(1) Attach list, if more than one; (2) Enter X in appropriate box; (3) See codes reverse; (x) Immediately contact Competent Authority; (4) If more than three carriers, attach information as required Sr. No. 5.";
        $notesText = trim($f->notes ?? '') !== '' ? $f->notes : $defaultNotes;
    ?>
    <div style="margin-top:6px;"><pre class="small">{{ $notesText }}</pre></div>

    <div style="page-break-before: always;"></div>
    <div class="heading">List of abbreviations used in the Movement Document<br><span class="small">Recovery Operations (*)</span></div>
    <?php
        $defaultOps = [
            ['code' => 'R1', 'description' => 'Use as fuel (other than in direct incineration) or other means to generate energy'],
            ['code' => 'R2', 'description' => 'Solvent reclamation /regeneration'],
            ['code' => 'R3', 'description' => 'Recycling/reclamation of organic substances which are not used as solvents'],
            ['code' => 'R4', 'description' => 'Recycling/reclamation of metals and metal compounds'],
            ['code' => 'R5', 'description' => 'Recycling/reclamation of other inorganic materials'],
            ['code' => 'R6', 'description' => 'Regeneration of acids or bases'],
            ['code' => 'R7', 'description' => 'Recovery of components used for pollution abatement'],
            ['code' => 'R8', 'description' => 'Recovery of components from catalysts'],
            ['code' => 'R9', 'description' => 'Used oil re-refining or other reuses of previously used oil'],
            ['code' => 'R10', 'description' => 'Land treatment resulting in benefit to agriculture or ecological improvement'],
            ['code' => 'R11', 'description' => 'Uses of residual materials obtained from any of the operations numbered R1 to R10'],
            ['code' => 'R12', 'description' => 'Exchange of wastes for submission to any of the operations numbered R1 to R11'],
            ['code' => 'R13', 'description' => 'Accumulation of material intended for any operation numbered R1 to R12'],
        ];
    ?>
    <table style="border:1px solid #000; margin-bottom: 14px;">
        <?php foreach ($defaultOps as $row): ?>
            <tr>
                <td style="width:10%; border:1px solid #000; padding:6px;"><strong>{{ $row['code'] }}</strong></td>
                <td style="border:1px solid #000; padding:6px;">{{ $row['description'] }}</td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php
        $motMap = [
            'R' => 'R = Road',
            'T' => 'T = Train/Rail',
            'S' => 'S = Sea',
            'A' => 'A = Air',
            'W' => 'W = Inland Water ways',
        ];
        $motDisplay = $motMap[$f->means_of_transport ?? 'S'] ?? ($f->means_of_transport ?? 'S');
    ?>
    <table style="margin-top: 6px; border:1px solid #000;">
        <tr>
            <th style="width:33%;">Means of Transport (Sr. No. 5)</th>
            <th style="width:33%;">Packaging Types (Sr. No. 13)</th>
            <th style="width:34%;">Physical Characteristics (Sr. No. 9)</th>
        </tr>
        <tr>
            <td style="border:1px solid #000; padding:6px;" class="small">
                R = Road<br>
                T = Train/Rail<br>
                S = Sea<br>
                A = Air<br>
                W = Inland Water ways<br>
                {{-- <div style="margin-top:6px;"><strong>Selected:</strong> {{ $motDisplay }}</div> --}}
            </td>
            <td style="border:1px solid #000; padding:6px;" class="small">
                1. Drum<br>
                2. Wooden barrel<br>
                3. Jerrican<br>
                4. Box<br>
                5. Bag<br>
                6. Composite packaging<br>
                7. Pressure receptacle<br>
                8. Bulk<br>
                9. Other (Specify)
                {{-- <div style="margin-top:6px;"><strong>Selected:</strong> {{ $disp($f->packaging_type) }} ({{ $disp($f->packaging_number) }})</div> --}}
            </td>
            <td style="border:1px solid #000; padding:6px;" class="small">
                1. Powdery/powder<br>
                2. Solid<br>
                3. Viscous/paste<br>
                4. Sludgy<br>
                5. Liquid<br>
                6. Gaseous<br>
                7. Other (specify)
                {{-- <div style="margin-top:6px;"><strong>Selected:</strong> {{ $disp($f->physical_characteristics) }}</div> --}}
            </td>
        </tr>
    </table>

    <div style="margin-top: 18px;" class="heading">H NUMBER (Sr. No. 14) & UN CLASS (Sr. No. 14)</div>
    <table border="1" cellspacing="0" cellpadding="5" width="100%" style="font-size: 11px; font-family: Arial, sans-serif;">
        <thead>
            <tr>
                <th width="10%">UN Class</th>
                <th width="15%">H Number</th>
                <th width="75%">Designation</th>
            </tr>
        </thead>
        <tbody>
            <!-- Previous rows -->
            <tr><td>1</td><td>H 1</td><td>Explosive</td></tr>
            <tr><td>3</td><td>H 3</td><td>Inflammable liquids</td></tr>
            <tr><td>4.1</td><td>H 4.1</td><td>Inflammable solids</td></tr>
            <tr><td>4.2</td><td>H 4.2</td><td>Constituents or wastes liable to spontaneous combustion</td></tr>
            <tr><td>4.3</td><td>H 4.3</td><td>Constituents or wastes which, in contact with water emit inflammable gases</td></tr>
            <tr><td>5.1</td><td>H 5.1</td><td>Oxidizing</td></tr>
            <tr><td>5.2</td><td>H 5.2</td><td>Organic peroxides</td></tr>
            <tr><td>6.1</td><td>H 6.1</td><td>Poisonous (acute)</td></tr>
            <tr><td>6.2</td><td>H 6.2</td><td>Infectious wastes</td></tr>
            <tr><td>8</td><td>H 8</td><td>Corrosives</td></tr>
            <tr><td>9</td><td>H 10</td><td>Liberation of toxic gases in contact with air or water</td></tr>
    
            {{-- INSERT THE UPDATED CODE BELOW --}}
            <tr><td>9</td><td>H 11</td><td>Toxic (delayed or chronic)</td></tr>
            <tr><td>9</td><td>H 12</td><td>Ecotoxic</td></tr>
            <tr><td>9</td><td>H 13</td><td>Capable by any means, after disposal of yielding another material (e.g. leachate), which possesses any of the characteristics listed above.</td></tr>
    
            <tr><td colspan="3" style="height: 15px; border: 1px solid black;"></td></tr>
    
            <tr>
                <td colspan="3" style="
                    border: 1px solid black;
                    font-style: italic;
                    font-size: 10px;
                    padding: 8px;
                    text-align: left;
                    background-color: #f9f9f9;
                ">
                    Y numbers (Sr. No. 13) refer to categories of waste listed in Annexure I and II of the Basel Convention, as well as more detailed information can be found in an instruction manual available from the Secretariat of the Basel Convention.
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
