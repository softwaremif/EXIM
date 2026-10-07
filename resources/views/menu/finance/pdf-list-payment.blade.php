<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Payment PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .signature {
            margin-top: 50px;
            text-align: left;
        }
    </style>
</head>

<body>
    
    <h2>LIST PAYMENT</h2>
    <h3>PT. MORICH INDO FASHION</h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Kontrak</th>
                <th>Tgl Invoice</th>
                <th>No Invoice</th>
                <th>No Faktur Pajak</th>
                <th>Amount</th>
                <th>Total Amount Invoice</th>
            </tr>
        </thead>
        <tbody>
            @php
            $totalAmount = 0;
            $totalAmountInv = 0;
            @endphp

            @foreach ($formattedData as $data)
            @php
            // Lebih aman pakai regex untuk parsing nilai
            $amount = $data['amountlistpay'];
            $totalInv = $data['totamountinv'];

            $totalAmount += $amount;
            $totalAmountInv += round($totalInv, 2);

            @endphp
            <tr>
                <td>{{ $data['index'] }}</td>
                <td>{{ $data['nokk'] }}</td>
                <td>{{ $data['tanggal'] }}</td>
                <td>{{ $data['noinv'] }}</td>
                <td>{{ $data['nofp'] }}</td>
                <td>${{ number_format($amount, 2, '.', ',') }}</td>
                <td>${{ number_format($totalInv, 2, '.', ',') }}</td>
            </tr>
            @endforeach

            <tr>
                <td colspan="5" style="text-align: center; font-weight: bold;">GRAND TOTAL</td>
                <td style="font-weight: bold;">${{ number_format(round($totalAmount, 2), 2, '.', ',') }}</td>
                <td style="font-weight: bold;">${{ number_format(round($totalAmountInv, 2), 2, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature" style="margin-top: 20px; text-align: left;">
        <p style="margin: 5px 0 0 13px;">PT. MORICH INDO FASHION</p>
        <br>
        <br>
        <br>
        <br>
        <br>

        <p style="margin: 5px 0 0 60px; line-height: 1;">Agus Karuna</p> <!-- Jarak lebih kecil -->
        <p style="margin: -2px 0 10 3px; line-height: 1;">____________________________</p> <!-- Angkat garis lebih dekat -->
        <p style="margin: 2px 0 0 47px;">Managing Director</p>
    </div>


</body>

</html>