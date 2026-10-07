<!DOCTYPE html>
<html>

<head>
    <title>PDF Invoice | Morich Indo Fashion</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"> -->
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>

<body>
    <style type="text/css">
        body {
            font-family: "Arial Narrow";
            font-size: 10px;
            line-height: 1.5;
        }

        @page {
            margin: 20px;
            /* Margin semua sisi */
            margin-top: 35px;
            /* Atur margin atas jika berbeda */
            margin-bottom: 25px;
            margin-left: 25px;
            margin-right: 25px;
        }

        .hor-minimalist-a {
            font-family: "Arial Narrow";
            font-size: 10px;
            background: #fff;
            border-collapse: collapse;
            text-align: left;
            width: auto;
            height: auto;
        }

        .hor-minimalist-a th {
            font-size: 10px;
            font-weight: bolder;
            padding: 2px 2px;
            text-align: center;
            border-bottom: 1px solid #6678b1;
            background-color: #dadada;
        }

        .hor-minimalist-a td {
            border-bottom: 1px solid #6678b1;
            font-size: 10px;
        }

        .hor-minimalist-a tbody tr:hover td {
            font-weight: normal;
        }

        table tr td,
        table tr th {
            font-size: 13px;
        }

        .font-judul {
            font-size: 15px;
            font-weight: 800;
            list-style: 24px;
        }

        .border-1 {
            border: 1px solid red;
        }

        /* Header style */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;

        }

        /* Spacer for additional margin on page 2 and onwards */
        .page-break {
            page-break-before: always;
            /* Forces new page */
            margin-top: 12%;
            /* Adjust this value for the desired margin */
        }

        /* Content style */
        .content {
            margin-top: 13%;
            /* Adjust for header space */
        }

        .custom-hr {
            height: 1px;
            /* Ketebalan garis */
            background-color: #000;
            /* Warna garis */
            margin: 0px 0;
            /* Jarak atas dan bawah */
            width: 100%;
            /* Panjang garis */
            border: none;
            /* Menghilangkan border */
        }

        .hidden-border {
            border-bottom: none !important;
            border-left: none !important;
            border-right: none !important;
        }
    </style>

    <body>
        <div class="header">
            <div>PT MORICH INDO FASHION<br>JL. Raya Karangjati Km.25<br>Desa Gembongan - Bergas<br>SEMARANG - INDONESIA</div>
            <div style="margin-left:85%;margin-top:-15%;">
                <table>
                    <tr>
                        <td style="font-size:10px;padding:0px;">Phone</td>
                        <td style="font-size:10px;padding:0px;">: (0298) - 523523</td>
                    </tr>
                    <tr style="font-size:10px;">
                        <td style="font-size:10px;padding:0px;">Fax</td>
                        <td style="font-size:10px;padding:0px;">: (0298) - 522095</td>
                    </tr>
                </table>
            </div>
            <div style="font-size:16px;font-weight:700;margin-top:5%;text-align:center;">INVOICE</div>
            <div class="custom-hr"></div>
        </div>

        <div class="content">
            <div>
                @php
                    $nomor = $invs->noinvs;
                    $parts = explode('/', $nomor);
                    $angka = (int) $parts[0];
                    $angkaFormatted = str_pad($angka, 4, '0', STR_PAD_LEFT);
                    $noinvoice = $angkaFormatted . '/' . $parts[1] . '/' . $parts[2];
                @endphp
                <div>NO : {{ $noinvoice }}</div>
                <br>
                <div>SOLD BY ORDER & FOR ACCOUNT & RISK OF<br>MESSRS</div>
                <br>
                <div>{{ $invs->shiptonm }}</div>
                <div>{{ $invs->alamat }}</div>
                <div>{{ $invs->negara }}</div>
                <div>Telp. {{ $invs->telepon }}</div>
                <div style="border: 1px solid #000000;width:25%;">Acc : {{ $invs->accno }}</div>
            </div>
            <div style="margin-left:51%;margin-top:-70%;">
                <div style="text-align:center"> DATE : {{ $invs->tglinvs }}</div>
                <br>
                <div>
                    <table border="1" class="hor-minimalist-a">
                        <tr>
                            <td width="125">CONTR/ORD.NO</td>
                            <td width="15" style="border-right: 1px solid #ffffff;border-left: 1px solid #ffffff;">:</td>
                            <td width="125"></td>
                        </tr>
                        <tr>
                            <td width="125">SHIPPED PER</td>
                            <td width="15" style="border-right: 1px solid #ffffff;border-left: 1px solid #ffffff;">:</td>
                            <td width="125"> {{ $invs->shipno }}</td>
                        </tr>
                        <tr>
                            <td width="125">SAILING ON/ABT</td>
                            <td width="15" style="border-right: 1px solid #ffffff;border-left: 1px solid #ffffff;">:</td>
                            <td width="125"> </td>
                        </tr>
                        <tr>
                            <td width="125">FROM</td>
                            <td width="15" style="border-right: 1px solid #ffffff;border-left: 1px solid #ffffff;">:</td>
                            <td width="125"> {{ strtoupper($invs->funm) }}</td>
                        </tr>
                        <tr>
                            <td width="125">TO</td>
                            <td width="15" style="border-right: 1px solid #ffffff;border-left: 1px solid #ffffff;">:</td>
                            <td width="125"> {{ strtoupper($invs->kontakship) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <br><br><br>
            <!-- <div>
                <table style="width:100%;">
                    <tr>
                        <td style="width:50%;font-size:10px;">L/C NO :</td>
                        <td style="width:50%;font-size:10px;">ISSUE BY :</td>
                    </tr>
                </table>
            </div> -->
            <table style="width:100%;" border="1" class="hor-minimalist-a">
                <thead>
                    <tr>
                        <th colspan="6">DESCRIPTION</th>
                        <th colspan="2">QUANTITY</th>
                        <th colspan="1" rowspan="2">UNIT PRICE</th>
                        <th colspan="1" rowspan="2">AMOUNT</th>
                    </tr>
                    <tr>
                        <th>STYLE</th>
                        <th>PO</th>
                        <th>ITEMS</th>
                        <th>SAMPLE TYPE</th>
                        <th>COLOR</th>
                        <th>FABRIC CONTENT</th>
                        <th>SIZE</th>
                        <th>PCS</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i=1; $tqyt = 0; $tjmlflyer = 0; $tamount = 0; @endphp
                    @foreach($dtinvs as $key => $p)
                    @php 
                        $tqyt += (int)$p->qty; 
                        $tjmlflyer += (int)$p->jmlflyer;
                        $tamount += (int)$p->amount;
                    @endphp
                    <tr>
                        <td align="center">{{$p->style}}</td>
                        <td align="center">{{$p->pono}}</td>
                        <td align="center">{{$p->itemnm}}</td>
                        <td align="center">{{$p->samplenm}}</td>
                        <td align="center">{{$p->colnm}}</td>
                        <td align="center">{{$p->fabnm}}</td>
                        <td align="center">{{$p->sizenm}}</td>
                        <td align="center">{{$p->qty}}</td>
                        <td align="center">{{ "$".number_format($p->price, 2) ?? '-'}}</td>
                        <td align="center">{{ "$".number_format($p->amount, 2) ?? '-'}}</td>
                    </tr>

                    @if (($key + 1) % 20 == 0) <!-- Adjust based on rows per page -->
                    <!-- Add spacer for new page -->
                </tbody>
            </table>
            <div class="page-break">
                <table style="width:100%;" border="1" class="hor-minimalist-a">
                    <thead>
                        <tr>
                            <!-- <th colspan="1" rowspan="2">MARKS & NOS</th> -->
                            <th colspan="6">DESCRIPTION</th>
                            <th colspan="2">QUANTITY</th>
                            <th colspan="1" rowspan="2">UNIT PRICE</th>
                            <th colspan="1" rowspan="2">AMOUNT</th>
                        </tr>
                        <tr>
                            <th>STYLE</th>
                            <th>PO</th>
                            <th>ITEMS</th>
                            <th>SAMPLE TYPE</th>
                            <th>COLOR</th>
                            <th>FABRIC CONTENT</th>
                            <th>SIZE</th>
                            <th>PCS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @endif
                        @endforeach
                    </tbody>
                    <tr>
                        <td colspan="7" style="">TOTAL</td>
                        <td align="center">{{ $tqyt }}</td>
                        <td style=""></td>
                        <td align="center">{{ "$".number_format($tamount, 2) ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td align="left">GW : {{ $invs->GW }}</td>
                        <td align="left">Sizebox : {{ $invs->sizebox}}</td>
                        <td colspan="10"></td>
                    </tr>
                </table>
            </div>
            <div style="margin-top:20%; margin-left:80%; width:100px;text-align:center;">
                <div>Eko Budi Cahyono</div>
                <div style="border-top:1px solid #000000;">Authorize Signature</div>
            </div>

    </body>

</html>