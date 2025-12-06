<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>FORM 9</title>
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
            border: 1px solid #000; /* Outer border slightly thicker to match image appearance */
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .heading {
            text-align: center;
            font-weight: bold;
            font-size: 16px; /* Larger header */
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .subheading {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        /* Specific Column Widths based on visual inspection */
        .col-sno { width: 5%; text-align: center; }
        .col-desc { width: 40%; }
        .col-details { width: 55%; }

        /* Utilities */
        pre {
            white-space: pre-wrap;
            margin: 0;
            font-family: Arial, sans-serif; /* Ensure pre uses Arial */
            font-size: 12px !important;
        }
        .no-border-top { border-top: none; }
        .no-border-bottom { border-bottom: none; }
        
        /* For the nested look in carriers/declarations */
        .nested-label {
            margin-bottom: 2px;
        }

        .inner-bold * {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php $f=$form9; ?>
    <?php
        // Helpers
        $disp = function ($v, $fallback = '') {
            if (is_numeric($v)) { return (string) $v; }
            $t = trim((string)($v ?? ''));
            return $t !== '' ? $t : $fallback;
        };
        $date = function ($v, $fallback = '') {
            return $v ? $v->format('d/m/Y') : $fallback;
        };

        // Extract Carriers for Fixed Rows
        $carriers = $form9->relationLoaded('carriers') ? $form9->carriers : $form9->carriers()->get();
        $c1 = $carriers->firstWhere('carrier_type', 'first');
        $c2 = $carriers->firstWhere('carrier_type', 'second');
        $cL = $carriers->firstWhere('carrier_type', 'last');
    ?>

    <div class="heading">FORM 9</div>
    <div class="subheading">[See rule - 15 (5) and 16 (5)]</div>
    <div class="subheading" style="margin-bottom: 10px;">TRANSBOUNDARY MOVEMENT- MOVEMENT DOCUMENT</div>

    <table>
        <thead>
            <tr>
                <th class="col-sno bold">S.No</th>
                <th class="col-desc bold">Description</th>
                <th class="col-details bold">Details to be furnished by the exporter or importer</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="col-sno">1.</td>
                <td class="col-desc">(i) Exporter (Name & Address)</td>
                <td class="col-details bold">
                    <pre>{{ $disp($f->exporter_name_address) }}</pre>
                    <div>{{ $disp($f->exporter_contact_person) }}</div>
                    <div>{{ $disp($f->exporter_phone) }}</div>
                    <div style="color:#0070c0; text-decoration:underline;">{{ $disp($f->exporter_email) }}</div>
                </td>
            </tr>
            
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">(ii) Waste Generator (Name & Address)</td>
                <td class="col-details bold">
                    <pre>{{ $disp($f->generator_name_address) }}</pre>
                    <div>{{ $disp($f->generator_contact_person) }}</div>
                    <div>{{ $disp($f->generator_phone) }}</div>
                    <div style="color:#0070c0; text-decoration:underline;">{{ $disp($f->generator_email) }}</div>
                </td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Site of Generation)</td>
                <td class="col-details bold">{{ $disp($f->site_of_generation) }}</td>
            </tr>

            <tr>
                <td class="col-sno">2.</td>
                <td class="col-desc">Importer / Recycler (Name & Address)</td>
                <td class="col-details bold">
                    <pre>{{ $disp($f->importer_name_address) }}</pre>
                    <div>{{ $disp($f->importer_contact_person) }}</div>
                    <div>{{ $disp($f->importer_phone) }}</div>
                    <div style="color:#0070c0; text-decoration:underline;"><a href="mailto:{{ $disp($f->importer_email) }}"  style="color:#0070c0; text-decoration:underline;">{{ $disp($f->importer_email) }}</a></div>
                </td>
            </tr>

            <tr>
                <td style="border-bottom:none !important;" class="col-sno">3.</td>
                <td class="col-desc">Corresponding to applicant Ref. No.</td>
                <td class="col-details bold">{{ $disp($f->applicant_ref_no) }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Movement subject to single / multiple</td>
                <?php $mtMap = ['single' => 'Single', 'multiple' => 'Multiple']; $mt = $mtMap[$f->movement_type ?? ''] ?? null; ?>
                <td class="col-details bold">{{ $disp($mt) }}</td>
            </tr>

            <tr>
                <td class="col-sno">4.</td>
                <td class="col-desc">Bill of lading (attach copy)</td>
                <td class="col-details bold">{{ $disp($f->bill_of_lading) }}</td>
            </tr>

            <tr>
                <td style="border-bottom:none !important;" class="col-sno">5.</td>
                <td class="col-desc">(a) 1ST Carrier (Name & Address)</td>
                <td class="col-details bold">
                    @if($c1)
                        <pre>{{ $disp($c1->name_address) }}</pre>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Registration Number</td>
                <td class="col-details bold">{{ $c1 ? $disp($c1->registration_no, 'N.A.') : 'N.A.' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Tel./ Fax</td>
                <td class="col-details bold">N.A.</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Identity of Means of Transport <sup>(3)</sup></td>
                <td class="col-details bold">{{ $c1 ? $disp($c1->transport_identity) : '' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Date of Transfer</td>
                <td class="col-details bold">{{ $c1 ? $date($c1->transfer_date) : '' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Signature of Carrier's representative</td>
                <td style="height:70px" class="col-details bold">{{ $c1 ? $disp($c1->signature) : '' }}</td>
            </tr>

            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">(b) 2 ND Carrier (Name & Address)</td>
                <td class="col-details bold">
                    @if($c2)
                        <pre>{{ $disp($c2->name_address) }}</pre>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Registration Number</td>
                <td class="col-details bold">{{ $c2 ? $disp($c2->registration_no) : '' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Tel./ Fax</td>
                <td class="col-details bold"></td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Identity of Means of Transport <sup>(3)</sup></td>
                <td class="col-details bold">{{ $c2 ? $disp($c2->transport_identity) : '' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Date of Transfer</td>
                <td class="col-details bold">{{ $c2 ? $date($c2->transfer_date) : '' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Signature of Carrier's representative</td>
                <td style="height:70px" class="col-details bold">{{ $c2 ? $disp($c2->signature) : '' }}</td>
            </tr>

            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">(c) Last Carrier (Name & Address)</td>
                <td class="col-details bold">
                    @if($cL)
                        <pre>{{ $disp($cL->name_address) }}</pre>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Registration Number</td>
                <td class="col-details bold">{{ $cL ? $disp($cL->registration_no) : '' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Tel./ Fax</td>
                <td class="col-details bold"></td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Identity of Means of Transport <sup>(3)</sup></td>
                <td class="col-details bold">{{ $cL ? $disp($cL->transport_identity) : '' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Date of Transfer</td>
                <td class="col-details bold">{{ $cL ? $date($cL->transfer_date) : '' }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Signature of Carrier's representative</td>
                <td style="height:70px" class="col-details bold">{{ $cL ? $disp($cL->signature) : '' }}</td>
            </tr>

        </tbody>
    </table>

    <table>
        <tbody>
            <tr>
                <td style="border-bottom: none;" class="col-sno">6.</td>
                <td class="col-desc">Disposer (Name, Address)</td>
                <td class="col-details bold">
                    <pre>{{ $disp($f->disposer_name_address) }}</pre>
                </td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Contact Person</td>
                <td class="col-details bold">{{ $disp($f->disposer_contact_person, 'N.A.') }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Actual site of disposal</td>
                <td class="col-details bold">{{ $disp($f->actual_site_of_disposal, 'SAME AS JUST ABOVE') }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Tel./ Fax</td>
                <td class="col-details bold">N.A.</td>
            </tr>

            <tr>
                <td style="border-bottom: none;" class="col-sno">7.</td>
                <td class="col-desc">Method(s) of Recovery</td>
                <td class="col-details bold">{{ $disp($f->method_of_recovery) }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">R Code</td>
                <td class="col-details bold">{{ $disp($f->r_code) }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Technology employed*<br><span style="font-weight:normal; font-size:11px;">*(Attach details if necessary)</span></td>
                <td class="col-details bold">{{ $disp($f->technology_employed) }}</td>
            </tr>

            <tr>
                <td class="col-sno">8.</td>
                <td class="col-desc">Designation and chemical composition of the waste</td>
                <td class="col-details bold">{{ $disp($f->waste_designation_composition) }}</td>
            </tr>

            <tr>
                <td class="col-sno">9.</td>
                <td class="col-desc">Physical characteristics <sup>(3)</sup></td>
                <td class="col-details bold">{{ $disp($f->physical_characteristics) }}</td>
            </tr>

            <tr>
                <td class="col-sno">10.</td>
                <td class="col-desc">Actual quantity Kg/ Lit</td>
                <td class="col-details bold">{{ $f->actual_quantity_kgs !== null ? number_format((float)$f->actual_quantity_kgs, 3, '.', '') . ' KGS OF WASTEPAPER OCC 11' : 'N.A.' }}</td>
            </tr>

            <tr>
                <td style="border-bottom: none;" class="col-sno">11.</td>
                <td class="col-desc">Waste identification Code</td>
                <td class="col-details bold">N.A.</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Base No.</td>
                <td class="col-details bold">{{ $disp($f->basel_no) }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">OECD No.</td>
                <td class="col-details bold">{{ $disp($f->oecd_number, 'N.A.') }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">UN No.</td>
                <td class="col-details bold">{{ $disp($f->un_no, 'N.A.') }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">ITC (HS)</td>
                <td class="col-details bold">{{ $disp($f->itc_hs) }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">Customs Code (HS)</td>
                <td class="col-details bold">{{ $disp($f->customs_code_hs) }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Other (specify)</td>
                <td class="col-details bold">{{ $disp($f->other_codes, 'N.A.') }}</td>
            </tr>

            <tr>
                <td style="border-bottom: none;" class="col-sno">12.</td>
                <td class="col-desc">OECD Classification <sup>(2)</sup></td>
                <td class="col-details bold">N.A.</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">(a) Amber/ Red/ Other (attach details)</td>
                <td class="col-details bold">N.A.</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">(b) Number</td>
                <td class="col-details bold">N.A.</td>
            </tr>

            <tr>
                <td style="border-bottom: none;" class="col-sno">13.</td>
                <td class="col-desc">Packaging Type <sup>(3)</sup></td>
                <td class="col-details bold">
                    <table style="width:100%; border:none; margin:-4px;">
                        <tr>
                            <td style="border:none; width:50%;">Other (Specify)</td>
                            <td style="border:none; width:50%;">{{ $disp($f->packaging_type) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Number</td>
                <td class="col-details bold">{{ $disp($f->packaging_number) }}</td>
            </tr>

            <tr>
                <td style="border-bottom: none;" class="col-sno">14.</td>
                <td class="col-desc">UN Classification</td>
                <td class="col-details bold">N.A.</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">UN Shipping Name</td>
                <td class="col-details bold">{{ $disp($f->un_shipping_name, 'N.A.') }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">UN Identification No.</td>
                <td class="col-details bold">{{ $disp($f->un_identification_no, 'N.A.') }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">UN Class <sup>(3)</sup></td>
                <td class="col-details bold">{{ $disp($f->un_class, 'N.A.') }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top no-border-bottom"></td>
                <td class="col-desc">H Number <sup>(3)</sup></td>
                <td class="col-details bold">{{ $disp($f->h_number, 'N.A.') }}</td>
            </tr>
            <tr>
                <td class="col-sno no-border-top"></td>
                <td class="col-desc">Y Number</td>
                <td class="col-details bold">{{ $disp($f->y_number, 'N.A.') }}</td>
            </tr>

            <tr>
                <td class="col-sno">15.</td>
                <td class="col-desc">Special handling requirements</td>
                <td class="col-details bold">{{ $disp($f->special_handling_requirements, 'NOTHING SPECIFIC') }}</td>
            </tr>

            <tr>
                <td class="col-sno">16.</td>
                <td class="col-desc">Actual date of Shipment</td>
                <td class="col-details bold">{{ $date($f->actual_shipment_date) }}</td>
            </tr>

            <tr>
                <td class="col-sno">17.</td>
                <td colspan="2" style="padding: 5px;">
                    <div>Exporter's declaration:</div>
                    <div style="font-size: 11px; margin-bottom: 5px; text-align: justify;">
                        I certify that the information in Sr. No. 1 of 16 above is complete and correct to my best knowledge. I also certify that legally enforceable written contractual obligations have been entered into, that any applicable insurance or other financial guarantees are in force covering the transboundary movement and that all necessary authorizations have been received from the competent authorities of the States concerned.
                    </div>
                    <div>Date:</div>
                    <div class="bold">{{ $date($f->exporter_declaration_date) }}</div>
                    <div style="margin-top:5px;">Signature:</div>
                    <div style="height: 60px; text-align: center;">
                        @if(!empty($f->exporter_signature_image_path))
                            <img src="{{ public_path('storage/'.ltrim($f->exporter_signature_image_path,'/')) }}" style="max-height:60px; max-width:200px;" />
                        @endif
                    </div>
                    <div class="bold" style="\display:inline-block; margin-top:5px; padding-right:20px;">
                        {{ $disp($f->exporter_signature_name) }}
                    </div>
                </td>
            </tr>
        </tbody>
        </table>
        
        <div style="page-break-before: always;"></div>
        
        <table>
            <tbody>
                <tr>
                    <td colspan="3" class="bold center" style="background-color: #fff;">TO BE COMPLETED BY IMPORTER/ RECYCLER</td>
                </tr>

                <tr>
                    <td style="border-bottom: none;" class="col-sno">18.</td>
                    <td class="col-desc">
                        <div>Shipment received by Importer/ Recycler</div>
                    </td>
                    <td class="col-details">
                    </td>
                </tr>

                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td><div>Quantity received </div></td>
                    <td><div class="bold">{{ $disp($f->quantity_received_importer) }}</div></td>
                </tr>
                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td><div>Date </div></td>
                    <td><div class="bold">{{ $date($f->date_received_importer) }}</div></td>
                </tr>
                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td style="height:70px;"><div>Signature</div></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td><div>Name</div></td>
                    <td><div class="bold">{{ $disp($f->signature_received_importer) }}</div></td>
                </tr>

                <tr>
                    <td class="col-sno">19.</td>
                    <td class="col-desc">
                        <div>Shipment received at Recycler</div>
                    </td>
                    <td class="col-details">
                        
                    </td>
                </tr>

                <!---->
                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td>Quantity received at recycler</td>
                    <td><div class="bold">{{ $disp($f->quantity_received_recycler) }}</div></td>
                </tr>
                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td>Quantity received and accepted </td>
                    <td><div class="bold">{{ $disp($f->quantity_accepted_recycler) }}</div></td>
                </tr>
                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td>Date</td>
                    <td><div class="bold">{{ $date($f->date_received_recycler) }}</div></td>
                </tr>
                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td style="height:70px;">Signature</td>
                    <td><div class="bold">{{ $disp($f->signature_received_recycler) }}</div></td>
                </tr>
                <tr>
                    <td style="border-bottom: none;border-top:none;"></td>
                    <td>Name</td>
                    <td></td>
                </tr>
                <!---->

                <tr>
                    <td class="col-sno">20.</td>
                    <td class="col-desc">Approximate date of recycling</td>
                    <td class="col-details bold">{{ $date($f->approximate_recycling_date) }}</td>
                </tr>
                <tr>
                    <td class="col-sno">21.</td>
                    <td class="col-desc">Method of recycling</td>
                    <td class="col-details bold">{{ $disp($f->method_of_recycling) }}</td>
                </tr>
            </tbody>
        </table>
        <table style="padding-top:20px;">
            <tbody> 
                <tr>
                    <td class="col-sno">22.</td>
                    <td colspan="2" style="padding: 5px;">
                        <div>I certify that the Recycling of the wastes described above will be completed as per HW (M, H and TM) Rules.</div>
                        <table style="width:100%; border:none; margin-top:5px;">
                            <tr>
                                <td style="border:none; width:50%;">Date</td>
                                <td style="border:none; width:50%;">Signature</td>
                            </tr>
                            <tr>
                                <td style="border:none;height:70px;" class="bold">{{ $date($f->recycler_certification_date) }}</td>
                                <td style="border:none;height:70px;" class="bold">{{ $disp($f->recycler_certification_signature) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                
                <tr>
                    <td class="col-sno">21.</td> <td class="col-desc">Specific Conditions on Consenting to the Movement</td>
                    <td class="col-details bold">(Attach details) {{ $disp($f->specific_conditions) }}</td>
                </tr>
            </tbody>
        </table>

    <div style="page-break-before: always;"></div>

    <?php
        $defaultNotes = "(1) Attach list, if more than one; (2) Enter X in appropriate box; (3) See codes reverse (x) Immediately contact Competent Authority; (4) If more than three carriers, attach information as required Sr. No. 5.";
        $notesText = trim($f->notes ?? '') !== '' ? $f->notes : $defaultNotes;
    ?>
    <div style="font-weight:bold; margin-bottom:15px; font-size:11px;">Note: {{ $notesText }}</div>

    <div class="bold center" style="margin-bottom:5px;">List of abbreviations used in the notification</div>
    <div class="bold center" style="margin-bottom:5px;">RECOVERY OPERATIONS (Sr. No. 7)</div>
     
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
            ['code' => 'R11', 'description' => 'Uses of residual materials obtained from any of the operations numbered R1 to R10.'],
            ['code' => 'R12', 'description' => 'Exchange of wastes for submission to any of the operations numbered R1 to R11'],
            ['code' => 'R13', 'description' => 'Accumulation of material intended for any operation numbered R1 to R12.'],
        ];
    ?>
    <table style="border:none; margin-bottom: 20px;">
        <?php foreach ($defaultOps as $row): ?>
            <tr>
                <td style="border:none; width:50px; font-weight:bold; padding:2px;">{{ $row['code'] }}</td>
                <td style="border:none;font-weight:bold; padding:2px;">{{ $row['description'] }}</td>
            </tr>
        <?php endforeach; ?>
    </table>

    <table style="border: 1px solid #000;">
        <thead>
            <tr>
                <th style="width:33%; text-align:center;" class="bold">Means of Transport<br>(Sr. No. 5)</th>
                <th style="width:33%; text-align:center;" class="bold">Packaging Types<br>(Sr. No. 13)</th>
                <th style="width:34%; text-align:center;" class="bold">Physical Characteristics<br>(Sr. No. 9)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="margin-bottom:10px;"><strong>R = Road</strong></div>
                    <div style="margin-bottom:10px;"><strong>T = Train/Rail</strong></div>
                    <div style="margin-bottom:10px;"><strong>S = Sea</strong></div>
                    <div style="margin-bottom:10px;"><strong>A = Air</strong></div>
                    <div><strong>W = Inland Water ways</strong></div>
                </td>
                <td>
                    <div class="bold" style="margin-bottom:2px;">1. Drum</div>
                    <div class="bold" style="margin-bottom:2px;">2. Wooden barrel</div>
                    <div class="bold" style="margin-bottom:2px;">3. Jerrican</div>
                    <div class="bold" style="margin-bottom:2px;">4. Box</div>
                    <div class="bold" style="margin-bottom:2px;">5. Bag</div>
                    <div class="bold" style="margin-bottom:2px;">6. Composite packaging</div>
                    <div class="bold" style="margin-bottom:2px;">7. Pressure receptacle</div>
                    <div class="bold" style="margin-bottom:2px;">8. Bulk</div>
                    <div class="bold" style="margin-bottom:2px;">9. Other (Specify)</div>
                </td>
                <td>
                    <div class="bold" style="margin-bottom:4px;">1. Powdery/powder</div>
                    <div class="bold" style="margin-bottom:4px;">2. Solid</div>
                    <div class="bold" style="margin-bottom:4px;">3. Viscous/paste</div>
                    <div class="bold" style="margin-bottom:4px;">4. Sludgy</div>
                    <div class="bold" style="margin-bottom:4px;">5. Liquid</div>
                    <div class="bold" style="margin-bottom:4px;">6. Gaseous</div>
                    <div class="bold" style="margin-bottom:4px;">7. Other (specify)</div>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <table style="border: 1px solid #000;padding-top:20px;">
        <thead>
            <tr>
                <th colspan="3"><div style="text-align:center;" class="bold">H NUMBER (Sr. No. 14) & UN CLASS (Sr. No. 14)</div></th>
            </tr>
            
            <tr>
                <th style="width:15%; text-align: center;" class="bold">UN Class</th>
                <th style="width:15%; text-align: center;" class="bold">H Number</th>
                <th style="width:70%; text-align: center;" class="bold">Designation</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="bold">1</td><td class="bold">H 1</td><td class="bold">Explosive</td></tr>
            <tr><td class="bold">3</td><td class="bold">H 3</td><td class="bold">Inflammable liquids</td></tr>
            <tr><td class="bold">4.1</td><td class="bold">H 4.1</td><td class="bold">Inflammable solids</td></tr>
            <tr><td class="bold">4.2</td><td class="bold">H 4.2</td><td class="bold">Constituents or wastes liable to spontaneous combustion</td></tr>
            <tr><td class="bold">4.3</td><td class="bold">H 4.3</td><td class="bold">Constituents or wastes which, in contact with water emit inflammable gases</td></tr>
            <tr><td class="bold">5.1</td><td class="bold">H 5.1</td><td class="bold">Oxidizing</td></tr>
            <tr><td class="bold">5.2</td><td class="bold">H 5.2</td><td class="bold">Organic peroxides</td></tr>
            <tr><td class="bold">6.1</td><td class="bold">H 6.1</td><td class="bold">Poisonous (acute)</td></tr>
            <tr><td class="bold">6.2</td><td class="bold">H 6.2</td><td class="bold">Infectious wastes</td></tr>
            <tr><td class="bold">8</td><td class="bold">H 8</td><td class="bold">Corrosives</td></tr>
            <tr><td class="bold">9</td><td class="bold">H 10</td><td class="bold">Liberation of toxic gases in contact with air or water</td></tr>
            <tr><td class="bold">9</td><td class="bold">H 11</td><td class="bold">Toxic (delayed or chronic)</td></tr>
            <tr><td class="bold">9</td><td class="bold">H 12</td><td class="bold">Ecotoxic</td></tr>
            <tr><td class="bold">9</td><td class="bold">H 13</td><td class="bold">Capable by any means, after disposal of yielding another material<br>e.g. leachate, which possesses any of the characteristics listed above.</td></tr>
            
            <tr>
                <td colspan="3" style="padding: 5px; font-weight:bold;">
                    Y numbers (Sr. No. 13) refer to categories of waste listed in Annexure I and II of the Basel Convention, as well as more detailed information can be found in an instruction manual available from the Secretariat of the Basel Convention.
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>