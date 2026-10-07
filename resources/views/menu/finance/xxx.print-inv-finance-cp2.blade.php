<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Finance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 2px 0;
        }
        .invoice-info {
            width: 100%;
            margin-bottom: 20px;
        }
        .invoice-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-info td {
            vertical-align: top;
            padding: 5px;
        }
        .invoice-info .left {
            width: 50%;
        }
        .invoice-info .right {
            width: 50%;
            text-align: right;
        }
        .details {
            width: 100%;
            margin-bottom: 20px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }
        .details th, .details td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }
        .details th {
            background-color: #f0f0f0;
        }
        .totals {
            width: 100%;
            margin-top: 10px;
            text-align: right;
        }
        .totals table {
            width: auto;
            margin-left: auto;
        }
        .totals td {
            padding: 5px 10px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
        }
        .signature {
            text-align: right;
            margin-top: 30px;
            padding-right: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>INVOICE</h1>
            <p>PT. MORICH INDO FASHION</p>
            <p>JL. Raya Karangjati KM.25 Desa Gembongan - Klepu SEMARANG INDONESIA</p>
            <p>Phone: (0298) - 525105 (6 Lines) | Fax: (0298) - 525110</p>
        </div>

        <div class="invoice-info">
            <table>
                <tr>
                    <td class="left">
                        <strong>MESSRS :</strong><br>
                        PT. EAGLE NICE INDONESIA<br>
                        JL. RAYA SERANG KM 70 KIBIN - SERANG<br>
                        INDONESIA
                    </td>
                    <td class="right">
                        <strong>NO :</strong> 0201/AKB/MIF/2024<br>
                        <strong>DD :</strong> 04 NOVEMBER 2024
                    </td>
                </tr>
            </table>
        </div>

        <div class="details">
            <table>
                <thead>
                    <tr>
                        <th>MPO</th>
                        <th>STYLE</th>
                        <th>DESCRIPTIONS</th>
                        <th>QTY</th>
                        <th>UNIT PRICE</th>
                        <th>AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>MNIK2410-0720B</td>
                        <td>IM7151</td>
                        <td rowspan="2">M NK TCH WVN FZ JACKET<br>KK NO. 2024/07/K/YO/014</td>
                        <td>489 PCS</td>
                        <td>USD 5.74</td>
                        <td>USD 2,806.86</td>
                    </tr>
                    <tr>
                        <td>MNIK2410-0915B</td>
                        <td>IM7151</td>
                        <td>631 PCS</td>
                        <td>USD 5.74</td>
                        <td>USD 3,621.94</td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right;">Subtotal</td>
                        <td>USD 6,428.80</td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right;">PPN 11%</td>
                        <td>USD 707.17</td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right;"><strong>Total</strong></td>
                        <td><strong>USD 7,135.97</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="totals">
            <p><strong>** US DOLLARS SEVEN THOUSAND ONE HUNDRED THIRTY FIVE POINT NINETY SEVEN ONLY **</strong></p>
        </div>

        <div class="footer">
            <p>Kurs Pajak: 15,574.00</p>
            <p><strong>OUR BANK DETAILS:</strong></p>
            <p>PT BANK NEGARA INDONESIA (Tbk)<br>
                KANTOR CABANG KARANGJATI<br>
                JL. REND. SUDARSONO 155 SEMARANG<br>
                AC NAME: PT MORICH INDO FASHION<br>
                SWIFT CODE: BNINIDJAXXX<br>
                A/C NO: 0290430494
            </p>
        </div>

        <div class="signature">
            <p>AGUS KARUNA</p>
            <p>Authorized Signature</p>
        </div>
    </div>
</body>
</html>
