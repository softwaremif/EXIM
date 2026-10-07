<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
        }

        .header .left,
        .header .right {
            width: 45%;
        }

        .title {
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            position: relative;
        }

        .title::after {
            content: "";
            display: block;
            width: 50%;
            margin: 10px auto;
            border-bottom: 1px solid black;
        }

        .content {
            margin: 20px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f5f5f5;
        }

        .total {
            text-align: right;
            margin-top: 20px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
        }

        .footer .line {
            border-top: 1px solid black;
            margin: 50px 0;
        }

        .footer .signature {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <div class="left">
            <strong>Company Name</strong><br>
            Address Line 1<br>
            Address Line 2<br>
            Phone: (123) 456-7890<br>
        </div>
        <div class="right" style="text-align: right;">
            <strong>Invoice</strong><br>
            Invoice Number: {{ $dtstyle->nobukti }}<br>
            Date: {{ \Carbon\Carbon::parse($dtstyle->tgl)->format('d/m/Y') }}<br>
        </div>
    </div>

    <!-- Title Section -->
    <div class="title">
        Invoice
    </div>

    <!-- Recipient Details -->
    <div class="content">
        <strong>To:</strong><br>
        nama penerima<br>
        alamat<br>
    </div>

    <!-- Table Section -->
    <table class="table">
        <thead>
            <tr>
                <th>MARKS & NOS</th>
                <th>DESCRIPTION</th>
                <th>JUMLAH</th>
                <th>UNIT PRICE</th>
                <th>AMOUNT</th>
            </tr>
        </thead>
        <tbody>
           @foreach($dtspbdt as $index => $dt)
            <tr>
                <td>{{ ++$index }} </td>
                <td>{{ $dt->noop}} </td>
                <td>{{ $dt->jmlsatuan}} </td>
                <td>{{ $dt->art}} </td>
                <td>{{ $dt->art}} </td>
            </tr>
           @endforeach
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="total">
        <strong>Total: IDR </strong><br>
        <em>Terbilang: </em>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <div class="line"></div>
        <div class="signature">
            Authorized Signature
        </div>
    </div>
</body>

</html>