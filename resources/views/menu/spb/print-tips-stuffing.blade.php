<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 40px;
        }

        .container {
            border: 1px solid black;
            padding: 20px;
            width: 100%;
        }

        .header {
            text-align: left;
        }

        .content {
            margin-top: 20px;
        }

        .signature {
            margin-top: 50px;
            text-align: center;
        }

        .signature div {
            display: inline-block;
            width: 30%;
        }
    </style>
</head>

<body>
    <div class="container">
        <p class="header">Karangjati, {{ $dtstyle->tanggal }}</p>
        <p>Tanda Terima,</p>

        <div class="content">
            <p><strong>Tips stuffing {{ $nama_buyer }}</strong></p>
            <!-- <p>No. Container / Truk: {{ $dtstyle->contno }} / {{ $dtstyle->notruk }}</p>
            <p><strong>Tujuan:</strong><br> {{ $dtstyle->gudnm }} <br> {{ $dtstyle->alamat }}</p>
            <p>Sebanyak: <strong>Rp. {{ number_format($dtstyle->tips, 0, '.', ',') }}</strong></p>
            <p>( {{ $terbilang }})</p> -->

            <table class="no-border" style="font-size: 12px;">
                <tr>
                    <td><strong>No. Container / Truk</strong></td>
                    <td>: {{ $dtstyle->contno }} / {{ $dtstyle->notruk }}</td>
                </tr>
                <tr>
                    <td><strong>Tujuan</strong></td>
                    <td>: {{ $dtstyle->gudnm }}</td>
                </tr>

                <tr>
                    <td> </td>
                    <td> &nbsp; {{ $dtstyle->alamat }}</td>
                </tr>

                <tr>
                    <td><strong>Sebanyak</strong></td>
                    <td>: Rp. {{ number_format($dtstyle->tips, 0, '.', ',') }}</td>
                </tr>

                <tr>
                    <td> </td>
                    <td>( {{ $terbilang }})</td>
                </tr>

            </table>
        </div>

        <div class="signature">
            <div>Mengetahui,<br><br><br>( ___________ )</div>
            <div>Penerima,<br><br><br>( ___________ )</div>
        </div>
    </div>
</body>

</html>