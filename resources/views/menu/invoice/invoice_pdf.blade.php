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
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
            max-width: 200px;
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
            page-break-after: always;
        }

        thead {
            display: table-header-group;
        }

        /* Content style */
        .content {
            margin-top: 5%;
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

        .wrap-text {
            width: 300px; /* Atur lebar maksimal */
            word-wrap: break-word; /* Memastikan kata panjang turun ke bawah */
            overflow-wrap: break-word; /* Alternatif agar kata panjang dipisah */
            white-space: normal; /* Izinkan teks turun ke bawah */
        }
    </style>

    <body>
        <!-- <div class="header"> -->
            <!-- <div>PT MORICH INDO FASHION<br>JL. Raya Karangjati Km.25<br>Desa Gembongan - Bergas<br>SEMARANG - INDONESIA</div>
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
            <div class="custom-hr"></div> -->
            <div><img src="{!! asset('public/css/images/morich.jpg') !!}" width="500" height="35" style="margin-top:3%;margin-left:17%;"></div>
            <div style="margin-top:-5%;margin-left:2%;"><img src="{!! asset('public/css/images/morich.png') !!}" width="75" height="75"></div>
            <div style="margin-top:-7%;margin-left:1%;text-align:center;">
                <p style="font-size:13px;">JL. RAYA KARANGJATI KM. 25 DESA GEMBONGAN - BERGAS, SEMARANG - INDONESIA<br>
                PHONE : 0298 - 523523 (6 LINES) FAX. 0298 - 522095<br>
                www.morichindofashion.com</p>
            </div>
            <hr style="border: none;border-top: 2px solid black;margin-top:2%;"></hr>
        <!-- </div> -->
        @if($nilai == '1') 
            <div style="font-size:16px; font-weight:700; margin-top:2%; text-align:center;">
                INVOICE
            </div>
        @else 
            <div style="font-size:16px; font-weight:700; margin-top:2%; text-align:center;">
                PACKING LIST
            </div>
        @endif

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
                <div style="text-align:right;margin-top:-5%;"> DATE INV. : {{ $invs->tglinvs }}</div>
                <div style="text-align:right;"> SEND DATE : {{ $invs->sendate }}</div>
                <br>
                <div>SHIPPER : </div>
                <div>PT MORICH INDO FASHION</div>
                <div>JL RAYA KARANGJATI KM.25</div>
                <div>DESA GEMBONGAN SEMARANG</div>
                <div>CENTRAL JAVA 50552 INDONESIA</div>
                <br>
                <div>M.I.D CODE : IDMORIND25SEM</div>
                <br>
                <div>SOLD BY ORDER & FOR ACCOUNT & RISK OF<br>MESSRS</div>
                <br>
                <div>{{ $invs->shiptonm }}</div>
                <div class="wrap-text">{{ $invs->alamat }}</div>
                <div>{{ $invs->negara }}</div>
                <div>Telp. {{ $telepon }}</div>
                <div style="border: 1px solid #000000;width:25%;">Acc : {{ $invs->accno }}</div>
            </div>
            <div style="margin-left:51%;margin-top:-18%;">
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
                            <td width="125"> {{ $invs->shipby }} #{{ $invs->shipno }}</td>
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
                            <td width="125"> {{ strtoupper($kontakship) }}</td>
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
                        <th colspan="8">DESCRIPTION</th>
                        <th colspan="2">QUANTITY</th>
                        <th colspan="1" rowspan="2">UNIT PRICE</th>
                        <th colspan="1" rowspan="2">AMOUNT</th>
                    </tr>
                    <tr>
                        <th>STATUS</th>
                        <th>STYLE</th>
                        <th>PO</th>
                        <th>ITEMS</th>
                        <th>SAMPLE TYPE</th>
                        <th>HTS CODE</th>
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
                        $price = $p->price;
                        $qty = $p->qty;
                      //  if($p->price > 0.01){
                            $amount = (double)$price * (double)$qty;
                        //}else{
                        //    $amount = 0.01;
                        //}
                        //$amount2 = "$ ".number_format($amount, 2,",",".");
                        //$price2 = "$ ".$price;
                        $tamount += $amount;
                       // if($tamount < 1.00){
                        //    $tamount2 = 1.00;
                       // }else{
                        //    $tamount2 = $tamount;
                        //}
                    @endphp
                    {{-- <tr class="{{ (($key + 1) % 20 == 0) ? 'page-break' : '' }}"> --}}
                    <tr class="">
                        <td align="center">{{$p->nos}}</td>
                        <td align="center">{{$p->style}}</td>
                        <td align="center">{{$p->pono}}</td>
                        <td align="center">{{$p->itemnm}}</td>
                        <td align="center">{{$p->samplenm}}</td>
                        <td align="center">{{$p->hscode}}</td>
                        <td align="center">{{$p->colnm}}</td>
                        <td align="center">{{$p->fabnm}}</td>
                        <td align="center">{{$p->sizenm}}</td>
                        <td align="center">{{$p->qty}}</td>
                        <td align="center">{{ "$ ".$price }}</td>
                        <td align="center">{{ "$ ".$amount ?? '-' }}</td>
                    </tr>
                </tbody>
                        @endforeach
                    <tr>
                        <td colspan="9" style="">TOTAL</td>
                        <td align="center">{{ $tqyt }}</td>
                        <td style=""></td>
                        <td align="center">{{ "$ ".number_format($tamount, 2) ?? '-' }}</td>
                    </tr>
                    <tr>
                    <td align="left" colspan="12">GW : {{ $invs->GW }} || Sizebox : {{ $invs->sizebox }} || Volume : {{ $invs->volume }} || NW : {{ $invs->nw }} || Packaging : {{ $invs->packtype }} || Qty Packaging : {{ $invs->jmlpack }}</td>
                    </tr>
                </table>
            </div>

            @if(Session::get('userpk') == 12)
                <div style="margin-top:5%; margin-left:80%; width:100px;text-align:center;">
                    @if(Session::get('userpk') == 12)
                    <img src="{!! asset('public/ttd/ttd_mas_eko.png') !!}" alt="" style="width:100%;">
                    @endif
                    <div>Eko Budi Cahyono</div>
                    <div style="border-top:1px solid #000000;">Authorize Signature</div>
                </div>
            @else
                <div style="margin-top:12%; margin-left:80%; width:100px;text-align:center;">
                    <div>Eko Budi Cahyono</div>
                    <div style="border-top:1px solid #000000;">Authorize Signature</div>
                </div>
            @endif
    </body>

</html>