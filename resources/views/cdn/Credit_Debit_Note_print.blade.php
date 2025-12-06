
<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Credit Note #{{ $indent->indent_no ?? 'CN/02' }}</title>
    <style>
        @page {
            size: A4;
            margin: 100px 60px 100px 60px;
        }
        body {
            font-family: "Arial", sans-serif !important;
            font-size: 12px !important;
            line-height: 1.2;
            color: #000;
        }

        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 90px;
            text-align: center;
        }
        footer {
            position: fixed;
            bottom: -130px;
            left: -60px;
            width: calc(100% + 120px);
            right: 0;
            height: 90px;
            text-align: center;
            font-size: 12px;
            padding-bottom: 5px;
        }

        /* Helper Classes */
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-red { color: red; }
        .uppercase { text-transform: uppercase; }

        /* Main Table Styling */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000; /* Outer border */
        }
        
        /* Cell Styling */
        table.main-table td, 
        table.main-table th {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }

        /* Specific Layout Tweaks */
        .header-title {
            font-size: 16px;
            font-weight: bold;
            padding: 8px;
            text-align: center;
        }
        
        .address-block {
            line-height: 1.4;
        }

        .particulars-header {
            text-align: center;
            text-transform: uppercase;
            padding: 5px;
        }

        .sub-header {
            text-align: center;
            padding: 5px;
        }

        /* Column Widths */
        .details-col { width: 55%; }
        .sr-col { width: 8%; text-align: center; }
        .amount-col { width: 18%; }
        
        /* Signature Area */
        .signature-block {
            padding-top: 20px;
            padding-bottom: 10px;
            text-align: right;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>
<header>
    <div>
        <img src="{{ public_path('images/moksham-logo.png') }}" alt="Moksham Logo" style="height: 55px;">
    </div>
</header>
<table class="main-table">
    <tr>
        <td colspan="4" class="header-title">CREDIT NOTE</td>
    </tr>

    <tr>
        <td colspan="2" class="address-block" style="width: 65%;">
            <div>Credit Note No. : {{ $indent->indent_no ?? 'CN/02' }}</div>
            <br>
            <div>AMBITION PAPER TECH PVT. LTD.</div>
            <div>Polt No.106,Village Sadani Muvadi, Majar Talod</div>
            <div>Road, Gandari Chowkdi, Taluka-Prantij, Sabarkantha,</div>
            <div>Gujarat - 383 205, INDIA.</div>
            <div>IEC: AAUCA0892M, GSTIN : 24AAUCA0892M1Z3</div>
            <div>PAN No : AAUCA0892M</div>
            <br><br><br>
            <div>Your Account has been credited as under :</div>
        </td>

        <td colspan="2" style="width: 35%;">
            <div>Date : 17/11/2025</div>
            <div>Against Invoice : 1005</div>
        </td>
    </tr>

    <tr>
        <td colspan="4" class="particulars-header">PARTICULARS</td>
    </tr>

    <tr>
        <td colspan="4" class="sub-header">We have Credited to your account, As per following detail</td>
    </tr>

    <tr>
        <th style="font-weight: normal;" class="sr-col">Sr. No.</th>
        <th style="font-weight: normal;" class="details-col text-center">Details</th>
        <th style="font-weight: normal;" class="amount-col text-center">Amount (USD)</th>
        <th style="font-weight: normal;" class="amount-col text-center">Amount Credit</th>
    </tr>

    <tr >  
        <td class="text-center">1.</td>
        <td style="height: 100px;">
            We are issuing a Credit Note for the Difference in Qty. LESS
            As Per Not Order, Qty. Weight Short ,Poor Quality
        </td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td colspan="2" class="font-bold text-center">
            Charges Incurred Due to Document Submission Delay From Your Side
        </td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td class="text-center">2.</td>
        <td style="height: 160px;">
            BOOKING NO. 31555951<br>
            B/L-NO. HLCUBSC2509AVNU6
        </td>
        <td class="text-right" style="vertical-align: top;">
            $ 11,864.00
        </td>
        <td class="text-right" style="vertical-align: top;">
            1,632.00
        </td>
    </tr>

    <tr>
        <td colspan="2" class="text-right font-bold" style="font-size: 16px;">TOTAL CREDIT AMOUNT</td>
        <td class="text-right font-bold text-red" style="font-size: 16px;">$ 10,232.00</td>
        <td class="text-right font-bold" style="font-size: 16px;">0.00</td>
    </tr>

    <tr>
        <td colspan="4" class="signature-block">
            <div class="font-bold">MOKSHAM EXPORT IMPORT LLC</div>
            <div style="height: 50px; margin: 5px 0;">
                 <img src="{{ public_path('images/signature.png') }}" alt="Signature" style="height: 40px;">
            </div>
            <div>Authorised Signatory</div>
        </td>
    </tr>
</table>
<footer>
    <div>
        <img src="{{ public_path('images/footer-img.jpeg') }}" alt="Moksham Logo" style="width:100%; object-fit: contain;">
    </div>
</footer>
</body>
</html>