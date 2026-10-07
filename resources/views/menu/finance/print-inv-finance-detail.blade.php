<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Finance</title>
    <style>
        @page {
            margin: 20px;
            /* Margin semua sisi */
            margin-top: 35px;
            /* Atur margin atas jika berbeda */
            margin-bottom: 25px;
            margin-left: 25px;
            margin-right: 25px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid black;
        }

        .header td {
            border: none;
            vertical-align: top;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 22px;
            text-transform: uppercase;
            padding-top: 20px;
        }

        .title2 {
            text-align: right;
            /* font-weight: bold; */
            font-size: 12px;
            text-transform: uppercase;
            padding-top: -90px;
        }

        .bawahInv {
            text-align: right;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            /* padding-top: -90px; */
        }

        .separator {
            border-bottom: 0.5px solid black;
            width: 280px;
            /* Lebar garis, bisa disesuaikan */
            margin: 3px auto;
            /* Otomatis rata tengah */
        }

        .recipient,
        .address {
            margin-top: 20px;
            font-size: 14px;
        }

        .total {
            margin-top: 10px;
            /* font-weight: bold; */
            text-align: left;
        }


        .total2 {
            margin-top: 17px;
            font-weight: bold;
            text-align: right;
            padding: 0 30px;
        }

        .footer {
            text-align: right;
            margin-top: -192px;
        }

        .footer .line {
            border-top: 1px solid black;
            margin: 1px 0;
            width: 26%;
            margin-left: auto;
            margin-right: 0;
        }

        .footer .signatureName {
            font-size: 14px;
            top: -10px;
            padding: 0 50px;
        }

        .footer .signature {
            font-size: 14px;
            top: -10px;
            /* background: white; */
            padding: 0 30px;
        }

        .footer2 {
            margin-top: 20px;
            text-align: left;
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <table class="header">
        <!-- <tr>
            <td align="left" width="50%" style="font-size: 12px;">
                <strong>PT. MORICH INDO FASHION</strong><br>
                Jl. Raya Karangjati KM. 25<br>
                Desa Gembongan - Bergas<br>
                KAB. SEMARANG JAWA TENGAH
            </td>
            <td align="right" width="50%" style="font-size: 10px;">
                PHONE: (0298) - 523523 (6 LINES)<br>
                FAX: (0298) - 522095
            </td>
        </tr> -->

        <div><img src="{!! asset('public/css/images/morich.jpg') !!}" width="500" height="35" style="margin-top:3%;margin-left:17%;"></div>
            <div style="margin-top:-5%;margin-left:2%;"><img src="{!! asset('public/css/images/morich.png') !!}" width="75" height="75"></div>
            <div style="margin-top:-7%;margin-left:1%;text-align:center;">
                <p style="font-size:13px;">JL. RAYA KARANGJATI KM. 25 DESA GEMBONGAN - BERGAS, SEMARANG - INDONESIA<br>
                PHONE : 0298 - 523523 (6 LINES) FAX. 0298 - 522095<br>
                www.morichindofashion.com</p>
            </div>
            <hr style="border: none;border-top: 2px solid black;margin-top:2%;"></hr>
    </table>
    <br>
    <!-- Title Section -->
    <div class="title">
        INVOICE
        <div class="separator"></div>
    </div>

    <div class="title2">
        <div class="bawahInv">
            NO: {{ $dt_inv->noinv ?? '-'}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            DD. {{ $dt_inv->tglinv ?? '-'}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span style="font-size: 11px;">Semarang, {{ $dt_inv->tglinv ?? '-'}}</span>
        </div>
    </div>



    <br>
    <table border="1" width="100%;" style="font-size: 10px;">
        <thead>
            <tr>
                <td width="100px" rowspan="7">
                    <div>
                        <div>MESSRS:</div>
                        <div>
                            {{ $dtstyle->gudnm}} <br>
                            {{ $dtstyle->alamat}}
                        </div>
                    </div>
                </td>
                <td width="100px"> CONT / ORD.NO :</td>
            </tr>

            <tr>
                <td width="100px">SHIPPED PER :</td>
            </tr>

            <tr>
                <td width="100px">SAILING ON / ABT :</td>
            </tr>

            <tr>
                <td width="100px"></td>
            </tr>

            <tr>
                <td>TO :</td>
            </tr>

            <tr>
                <td></td>
            </tr>

            <tr>
                <td></td>
            </tr>
        </thead>
    </table>
    <table border="0">
        <thead>
            <tr style="text-align: left; font-size: 10px;">
                <td>L/C NO : </td>
                <td>ISSUED BY :</td>
                <tD>TERM :</tD>
            </tr>
        </thead>
    </table>

    <!-- Table Section -->
    <table>
        <thead>
            <tr style="font-size: 12px;">
                <td width="15%" align="center">MPO</td>
                <td width="15%" align="center">STYLE</td>
                <td width="40%" align="center">DESCRIPTIONS</td>
                <td width="10%" align="center">QTY</td>
                <td width="18%" align="center">UNIT PRICE</td>
                <td width="18%" align="center">AMOUNT</td>
            </tr>
        </thead>
        <tbody style="font-size: 12px;">

            @php
            $totalAmount = 0;
            $totalPcs = 0;
            @endphp

            <tr>
                <td colspan="6" align="center" style="font-weight: bold;">KK NO. {{ $dt_inv->nokk ?? '-'}}</td>
            </tr>

            @foreach($dt_invoicedt as $data)
            <?php

            $getBdown = DB::connection('mysql_sig')
                        ->table('bdown')
                        ->select('bdown.bdownpk', 'bdown.ordpk', 'bdown.poref')
                        ->where('bdown.poref', 'like', "%" . $data->mpo . "%")
                        ->first();

            // $getArtProdes = DB::connection('mysql_sig')
            //         ->table('ord')
            //         ->select('ordpk', 'ordno', 'artno', 'prodes')
            //         ->where('ordpk', $getBdown->ordpk)
            //         ->first();

            if (!is_null($getBdown) && is_object($getBdown)) {
                $getArtProdes = DB::connection('mysql_sig')
                    ->table('ord')
                    ->select('ordpk', 'ordno', 'artno', 'prodes')
                    ->where('ordpk', $getBdown->ordpk)
                    ->first();
            }

            ?>
            <tr>
                <td align="center">{{ $data->mpo }}</td>

                <td align="center">{{ $data->mstyle ?? 'N/A' }}</td>
                <td align="center">{{ $data->mprodes ?? 'N/A' }}</td>

                <td align="center">{{ number_format($data->qty, 0, ',', ',') }} PCS</td>
                <td style="text-align: center; position: relative;">
                    @if($dt_inv->cur == 1)
                    <span style="position: absolute; left: 1; top:12;">USD</span>
                    @else
                    <span style="position: absolute; left: 1; top:12;">IDR</span>
                    @endif
                    {{ number_format($data->pricedt, 2, '.', ',') }}
                </td>
                <td>
                    {{ number_format($data->qty * $data->pricedt, 3, '.', ',') }}
                </td>
                <?php
                $totalAmount += $data->qty * $data->pricedt;
                $totalPcs += $data->qty;
                ?>
            </tr>
            @endforeach
        </tbody>

        <tfoot style="font-size: 12px;">
            <tr>
                <td colspan="6" align="center"> &nbsp;</td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: right;">SUBTOTAL</td>

                <td>
                    @if($dt_inv->cur == 1)
                    <span>USD</span>
                    @else
                    <span>IDR</span>
                    @endif
                    {{ number_format($totalAmount, 3, '.', ',') }}
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: right;">DPP NILAI LAIN</td>
                <td>
                    @if($dt_inv->cur == 1)
                        <span>USD</span>
                    @else
                        <span>IDR</span>
                    @endif
                    {{ number_format(($totalAmount * (11/12)), 3, '.', ',') }}
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: right;">PPN {{ $dt_inv->ppn }} %</td>
                <td>
                    @if($dt_inv->cur == 1)
                        <span>USD</span>
                    @else
                        <span>IDR</span>
                    @endif
                    <!-- {{ number_format(($totalAmount * (($dt_inv->ppn ?? 11) / 100)), 3, '.', ',') }} -->
                    {{ number_format(($totalAmount * (11 / 100)), 3, '.', ',') }}
                </td>
            </tr>

            <tr>
                <td colspan="3" align="center">Total</td>

                <td align="center">
                    {{ number_format($totalPcs, 0, ',', ','), }} PCS
                </td>

                <td> </td>

                <td>
                    <?php
                        $TakeAll = $totalAmount * 1.11;
                        $Terbilang2 = round($TakeAll, 2);
                    ?>

                    @if($dt_inv->cur == 1)
                        <span>USD</span>
                    @else
                        <span>IDR</span>
                    @endif

                    <?= number_format($Terbilang2, 2, '.', ','); ?>
                </td>
            </tr>
        </tfoot>
    </table>

     @if($dt_inv->cur == 1)
    <div class="total" style="font-size: 12px;">
        <?php
        $terbilang = convertToWords($Terbilang2);
        echo "** $terbilang **";
        ?>
    </div>
    @else
    <div class="total" style="font-size: 12px;">
        <?php
        
        $terbilang = App\Helpers\FormatRupiahHelper::terbilang($Terbilang2) . " Rupiah";
        echo "** $terbilang **";
        ?>
    </div>
    @endif
    
    <div class="total2" style="font-size: 12px;">
        Semarang, {{ $dt_inv->tglinv ?? '-'}}
    </div>

    <br>
    @if($dt_inv->cur == 1)
    <div style="margin-top:10px; width: 17%; border: 1px solid black; padding: 8px; font-size: 12px; height: 20px;">
        Kurs Pajak: {{ isset($dt_inv->kursp) ? number_format($dt_inv->kursp, 0, '.', ',') : 'N/A' }}
    </div>
    @else
    <div style="margin-top:10px; width: 17%; border: 1px solid black; padding: 8px; font-size: 12px; height: 20px;">
        Kurs Pajak:  N/A
    </div>
    @endif

    <div class="footer2" style="font-size: 12px;">
        <p><strong><u>OUR BANK DETAILS:</u></strong></p>
        <p>
            Bank Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: PT BANK NEGARA INDONESIA (TBK) <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;KANTOR CABANG KARANGAYU <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;JL. JEND. SUDIRMAN 195 <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SEMARANG <br>
            A/C Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: PT MORICH INDO FASHION <br>
            Swift Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: BNINIDJAKRU <br>
            A/C No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 0029042494
        </p>
    </div>

    <div class="footer">
        <div class="signatureName" style="font-size: 12px;">
            @if($dtstyle->gudpk == 165)
            <strong>Maya Regawati</strong>
            @else
            <strong>Agus Karuna</strong>
            @endif
        </div>
        <div class="line"></div>
        <div class="signature" style="font-size: 12px;"><strong>Authorized Signature</strong></div>
    </div>

</body>

</html>