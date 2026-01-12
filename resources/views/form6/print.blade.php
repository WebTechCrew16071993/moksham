<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>FORM 6</title>
    <style>
        @page { margin: 20px 60px; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px !important;
            color: #000;
            line-height: 1.15;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
            font-size: 12px !important;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        
        /* Header Styling */
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 16px !important;
            margin-bottom: 0px;
            text-transform: uppercase;
        }
        .header-sub {
            text-align: center;
            font-size: 12px !important;
            margin-bottom: 10px;
            font-weight: bold;
        }

        /* Column Widths based on images */
        .col-1 { width: 4%; text-align: center; } /* S.No */
        .col-2 { width: 44%; } /* Description */
        .col-3 { width: 52%; } /* Details */

        /* Utility for pre-formatted text that wraps */
        .pre-wrap {
            white-space: pre-wrap;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        /* Signature Image Sizing */
        .sig-img {
            max-height: 50px;
            max-width: 200px;
            display: block;
            margin-top: 5px;
        }
        
        .no-border-top { border-top: none; }
        .no-border-bottom { border-bottom: none; }
    </style>
</head>
<body>
    <?php $f = $form6; ?>

    <div class="header-title">FORM 6</div>
    <div class="header-sub">
        [See rules 13(2), 13 (10) and 14 (5)]<br>
        TRANSBOUNDARY MOVEMENT - MOVEMENT DOCUMENT
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-1 bold">S.No</th>
                <th class="col-2 bold">Description</th>
                <th class="col-3 bold">Details to be furnished by the exporter or importer</th>
            </tr>
            <tr>
                <th class="col-1 bold">(1)</th>
                <th class="col-2 bold">(2)</th>
                <th class="col-3 bold">(3)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="col-1">1.</td>
                <td>Exporter (Name and Address)<br>Contact Person<br>Tele, Fax and email</td>
                <td>
                    <div class="pre-wrap bold">{{ $f->exporter_name_address }}</div>
                    <div class="pre-wrap bold">{{ $f->exporter_contact_person }}</div>
                    <div class="pre-wrap bold">{{ $f->exporter_phone }}</div>
                    <div class="pre-wrap bold" style="color:#0070c0; text-decoration:underline;">{{ $f->exporter_email }}</div>
                </td>
            </tr>

            <tr>
                <td class="col-1" rowspan="2">2.</td>
                <td>Generator(s) of the waste (Name and Address) <sup>1</sup><br>Contact Person<br>Tele, Fax and email</td>
                <td>
                    <div class="pre-wrap bold">{{ $f->generator_name_address }}</div>
                    <div class="pre-wrap bold">{{ $f->generator_contact_person }}</div>
                    <div class="pre-wrap bold">{{ $f->generator_phone }}</div>
                    <div class="pre-wrap bold" style="color:#0070c0; text-decoration:underline;">{{ $f->generator_email }}</div>
                </td>
            </tr>
            <tr>
                <td>Site of Generation</td>
                <td>{{ $f->site_of_generation }}</td>
            </tr>

            <tr>
                <td class="col-1">3.</td>
                <td>Importer or Actual user (Name and Address)<br>Contact person<br>Tele, Fax and email</td>
                <td>
                    <div class="pre-wrap bold">{{ $f->importer_name_address }}</div>
                    <div class="pre-wrap bold">{{ $f->importer_contact_person }}</div>
                    <div class="pre-wrap bold">{{ $f->importer_phone }}</div>
                    <div class="pre-wrap bold"><a href="mailto:{{ $f->importer_email }}" style="color:#0070c0; text-decoration:underline;">{{ $f->importer_email }}</a></div>
                </td>
            </tr>

            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1">4.</td>
                <td>Trader (Name and Address)<br>Contact person<br>Tele, Fax and email</td>
                <td>
                    <div class="pre-wrap bold">{{ $f->trader_name_address }}</div>
                    <div class="pre-wrap bold">{{ $f->trader_contact_person }}</div>
                    <div class="pre-wrap bold">{{ $f->trader_phone }}</div>
                    <div class="pre-wrap">{{ $f->trader_email }}</div>
                </td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td>Details of actual user (Name, Address, Telephone and Email)</td>
                <td>
                    <div class="pre-wrap bold">{{ $f->actual_user_details }}</div>
                </td>
            </tr>

            <tr>
                <td class="col-1">5.</td>
                <td>Corresponding to applicant Ref No., if any</td>
                <td class="bold">{{ $f->applicant_ref_no }}</td>
            </tr>

            <tr>
                <td class="col-1">6.</td>
                <td>Bill of lading (attach copy)</td>
                <td>{{ (trim((string)($f->bill_of_lading ?? '')) !== '') ? $f->bill_of_lading : (optional($f->bl)->booking_no ?? '') }}</td>
            </tr>

            <tr>
                <td class="col-1">7.</td>
                <td>Country of Import / Export</td>
                <td class="bold" style="vertical-align:middle;padding-top:5px;padding-bottom:0;">
                    <div style="clear:both;width:100%;display:block;vertical-align:middle;">
                        <span style="width:49%;display:inline-block;margin:0;">{{ $f->country_of_import }}</span>
                        <span style="width:49%;display:inline-block;margin:0;">{{ $f->country_of_export }}</span>
                    </div>
                </td>
            </tr>

            <tr>
                <td style="border-bottom:none !important;" class="col-1">8.</td>
                <td>General Description of waste</td>
                <td></td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-bottom:none !important;">(a) Quantity</td>
                <td class="bold">{{ number_format((float)$f->quantity_kgs, 3, '.', '') }} KGS</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(b) Physical Characteristics</td>
                <td class="bold">{{ $f->physical_characteristics }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(c) Chemical composition of waste (attach details), where applicable</td>
                <td class="bold">{{ $f->chemical_composition }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(d) Basel No.</td>
                <td class="bold">{{ $f->basel_no }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(e) UN Shipping Name</td>
                <td class="bold">{{ $f->un_shipping_name }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(f) UN Class</td>
                <td class="bold">{{ $f->un_class }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(g) UN No</td>
                <td class="bold">{{ $f->un_no }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(h) H Number</td>
                <td class="bold">{{ $f->h_number }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(i) Y Number</td>
                <td class="bold">{{ $f->y_number }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(j) ITC (HS)</td>
                <td class="bold">{{ $f->itc_hs }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(k) Customs Code (H.S.)</td>
                <td class="bold">{{ $f->customs_code_hs }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td style="border-top:none !important;border-bottom:none !important;">(l) Other (specify)</td>
                <td class="bold">{{ $f->other_codes }}</td>
            </tr>

            <tr>
                <td style="border-bottom:none !important;" class="col-1">9.</td>
                <td>Type of packages</td>
                <td class="bold">{{ $f->package_type }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;" class="col-1"></td>
                <td>Number</td>
                <td class="bold">{{ $f->package_number }}</td>
            </tr>

            <tr>
                <td class="col-1">10.</td>
                <td>Special handling requirements including emergency provision in case of accidents</td>
                <td class="bold">{{ $f->special_handling_requirements }}</td>
            </tr>

            <tr>
                <td style="border-bottom:none !important;" class="col-1">11.</td>
                <td>Movement subject to single / multiple consignment</td>
                <td class="bold">{{ strtoupper($f->movement_type ?? 'SINGLE') }}</td>
            </tr>
            <tr>
                <td style="border-top:none !important;" class="col-1"></td>
                <td>
                    In case of multiple movement-<br>
                    (a) Expected dates of each shipment or expected frequency of the shipments<br>
                    (b) Estimated total quantity and quantities for each individual shipment
                </td>
                <td class="bold">
                    <br>
                    {{ $f->expected_shipment_dates ?: 'N.A.' }}<br><br>
                    {{ $f->estimated_quantities ?: 'N.A.' }}
                </td>
            </tr>
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <table>
        <thead>
            <tr>
                <th class="col-1 bold">(1)</th>
                <th class="col-2 bold">(2)</th>
                <th class="col-3 bold">(3)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border-bottom:none !important;" class="col-1">12.</td>
                <td>Transporter of waste (Name and Address) <sup>1</sup></td>
                <td style="height: 80px;">
                    <div class="pre-wrap bold">{{ $f->transporter_name_address }}</div>
                </td>
            </tr>
            <tr>
                <td style="border-bottom:none !important;border-top:none !important;" class="col-1"></td>
                <td>Registration number</td>
                <td class="bold">{{ $f->transporter_registration_no }}</td>
            </tr>
            <tr>
                <td style="border-bottom:none !important;border-top:none !important;" class="col-1"></td>
                <td>Means of transport (road, rail, inland waterway, sea, air) <sup>2</sup></td>
                <td class="bold">{{ $f->means_of_transport }}</td>
            </tr>
            <tr>
                <td style="border-bottom:none !important;border-top:none !important;" class="col-1"></td>
                <td>Date of Transfer</td>
                <td class="bold">{{ $f->date_of_transfer?->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="border-bottom:none !important;border-top:none !important;" class="col-1"></td>
                <td>Signature of Carrier's representative</td>
                <td style="height: 80px;">
                    <div class="pre-wrap">{{ $f->carrier_signature }}</div>
                </td>
            </tr>

            <tr>
                <td class="col-1">13.</td>
                <td>Exporter's declaration for hazardous and other waste:</td>
                <td class="bold">{{ $f->exporter_declaration }}</td>
            </tr>
            <tr>
                <td style="border-bottom:none;border-top:none" class="col-1"></td>
                <td style="border-bottom:none;border-top:none">
                    I certify that the information in Sl. Nos. 1 to 12 above are complete and correct to my best knowledge. I also certify that legally enforceable written contractual obligations have been entered into and are in force covering the transboundary movement regulations/rules.<br>
                </td>
                <td style="border-bottom:none;border-top:none">
                    
                </td>
            </tr>

            <tr>
                <td style="border-bottom:none;border-top:none"></td>
                <td style="border-bottom:none;border-top:none">Signature</td>
                <td style="border-bottom:none;border-top:none">
                    <div style="min-height: 40px;">
                        @if(!empty($f->exporter_signature_image_path))
                            <img src="{{ public_path('storage/'.ltrim($f->exporter_signature_image_path,'/')) }}" class="sig-img" />
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td style="border-bottom:none;border-top:none"></td>
                <td style="border-bottom:none;border-top:none">Name</td>
                <td style="border-bottom:none;border-top:none"><div class="bold">{{ $f->exporter_signature_name }}</div>
                    </td>
            </tr>
            <tr>
                <td style="border-bottom:none;border-top:none"></td>
                <td style="border-bottom:none;border-top:none">Date</td>
                <td style="border-bottom:none;border-top:none"><div class="bold">{{ $f->exporter_signature_date?->format('d/m/Y') }}</div></td>
            </tr>
            

            <tr>
                <td colspan="3" class="bold">TO BE COMPLETED BY IMPORTER (ACTUAL USER OR TRADER)</td>
            </tr>
            <tr>
                <td style="border-bottom:none;border-top:none" class="col-1">14.</td>
                <td style="border-bottom:none;border-top:none">
                    Shipment received by importer/actual user/trader<sup>2/3</sup><br>
                    <span style="text-decoration: underline;">Quantity received </span>
                </td>
                <td style="border-bottom:none;border-top:none">
                    <div class="bold">{{ $f->quantity_received_kgs }}</div>
                </td>
            </tr>

            <tr>
                <td style="border-bottom:none;border-top:none"></td>
                <td style="border-bottom:none;border-top:none" style="height: 40px;">Signature</td>
                <td style="border-bottom:none;border-top:none"></td>
            </tr>

            <tr>
                <td style="border-bottom:none;border-top:none"></td>
                <td style="border-bottom:none;border-top:none">Name</td>
                <td style="border-bottom:none;border-top:none"><div class="bold">{{ $f->importer_signature_name }}</div></td>
            </tr>

            <tr>
                <td style="border-bottom:none;border-top:none"></td>
                <td style="border-bottom:none;border-top:none">Date</td>
                <td style="border-bottom:none;border-top:none"><div class="bold">{{ $f->importer_signature_date?->format('d/m/Y') }}</div></td>
            </tr>

            <tr>
                <td style="border-bottom:none;" class="col-1">15.</td>
                <td style="border-bottom:none;">Corresponding to applicant Ref No., if any</td>
                <td style="border-bottom:none;" class="bold">{{ $f->importer_applicant_ref_no }}</td>
            </tr>
            <tr>
                <td style="border-bottom:none;border-top:none" class="col-1"></td>
                <td>R code*</td>
                <td class="bold">{{ $f->r_code }}</td>
            </tr>
            <tr>
                <td style="border-bottom:none;border-top:none" class="col-1"></td>
                <td>Technology employed (Attached details if necessary)</td>
                <td class="bold">{{ $f->technology_employed }}</td>
            </tr>

            <tr>
                <td style="border-bottom:none;" class="col-1">16.</td>
                <td style="border-bottom:none;border-top:none">
                    I certify that nothing other than declared goods covered as per these rules is intended to be imported in the above referred consignment and will be recycled /utilized.<br>
                    
                </td>
                <td style="border-bottom:none;border-top:none">
                </td>
            </tr>

            <!-- -->
            <tr>
                <td style="border-bottom:none;border-top:none"></td>
                <td style="border-bottom:none;border-top:none">Signature</td>
                <td style="border-bottom:none;border-top:none"><div style="min-height: 40px;">
                         @if(!empty($f->importer_signature_image_path))
                            <img src="{{ public_path('storage/'.ltrim($f->importer_signature_image_path,'/')) }}" class="sig-img" />
                        @endif
                    </div></td>
            </tr>

            <tr>
                <td style="border-bottom:none;border-top:none"></td>
                <td style="border-bottom:none;border-top:none">Date</td>
                <td style="border-bottom:none;border-top:none">
                    <div class="bold">{{ $f->importer_certification_date?->format('d/m/Y') }}</div>
                </td>
            </tr>
            <!-- -->

            <tr>
                <td class="col-1">17.</td>
                <td>SPECIFIC CONDITIONS ON CONSENTING TO THE MOVEMENT if applicable.</td>
                <td>{{ $f->specific_conditions ?: '(attach details)' }}</td>
            </tr>
            
            <tr>
                <td colspan="3" class="bold">
                    Notes:- <?php
                        $defaultNotes = "Notes:-(1) Attach list, if more than one; (2) Select appropriate option; (3) Immediately contact competent authority in case of any emergency; (4) If more than one transporter carriers, attach information as required in SL. No. 12";
                        $notesText = trim($f->notes ?? '') !== '' ? $f->notes : $defaultNotes;
                    ?>
                    {{ $notesText }}
                </td>
            </tr>
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <div style="padding: 20px 0;">
    <div class="center bold" style="margin-bottom: 5px;">List of abbreviations used in the Movement Document</div>
    <div class="center bold" style="margin-bottom: 20px;">Recovery Operations (*)</div>

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
            ['code' => 'R9', 'description' => 'Used oil re-refining or other reuses of previously used oil..'],
            ['code' => 'R10', 'description' => 'Land treatment resulting in benefit to agriculture or ecological improvement'],
            ['code' => 'R11', 'description' => 'Uses of residual materials obtained from any of the operations numbered R 1 to R 10'],
        ];
    ?>

    <table style="border: none;padding-bottom: 30px;">
        <?php foreach ($defaultOps as $row): ?>
            <tr>
                <td style="border: none; width: 50px; padding: 2px 0;" class="bold">{{ $row['code'] }}</td>
                <td style="border: none; padding: 2px 0;" class="bold">{{ $row['description'] }}</td>
            </tr>
        <?php endforeach; ?>
    </table>

    <table style="border: none;padding:0 0 30px;">
        <tr>
            <td style="border: none; width: 50%;">
                Date<br>
                <span class="bold">{{ $f->exporter_signature_date?->format('d/m/Y') }}</span>
            </td>
            <td style="border: none; width: 50%;">
                Signature<br>
                @if(!empty($f->exporter_signature_image_path))
                    <div style="min-height: 40px;">
                        <img src="{{ public_path('storage/'.ltrim($f->exporter_signature_image_path,'/')) }}" class="sig-img" />
                    </div>
                    @endif
                <div class="bold">{{ $f->exporter_signature_name }}</div>
            </td>
        </tr>
    </table>

    <table style="border: none;">
        <tr>
            <td style="border: none; width: 50%;">
                Place<br>
                <span class="bold">{{ $f->signature_place }}</span>
            </td>
            <td style="border: none; width: 50%;">
                Designation<br>
                <span class="bold">{{ $f->signature_designation }}</span>
            </td>
        </tr>
    </table>
</div>
</body>
</html>