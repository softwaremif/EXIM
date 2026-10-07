<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Finance</title>
    <style>
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
            font-size: 15px;
            text-transform: uppercase;
            padding-top: 20px;
        }

        .title2 {
            text-align: right;
            /* font-weight: bold; */
            font-size: 10px;
            text-transform: uppercase;
            padding-top: -90px;
        }

        .bawahInv {
            text-align: right;
            font-weight: bold;
            font-size: 10px;
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
            width: 30%;
            margin-left: auto;
            margin-right: 0;
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
        <tr>
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
        </tr>
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
            <span style="font-size: 8px;">Semarang, {{ $dt_inv->tglinv ?? '-'}}</span>
        </div>
    </div>


    <!-- Recipient Section -->
    <!-- <div class="recipient">
        <div style="font-size: 10px;">SOLD BY ORDER & FOR ACCOUNT & RISK OF</div>
        <div>
            <div style="font-size: 10px;">MESSRS:</div>
            <div style="font-size: 10px;">
                {{ $dtstyle->gudnm}} <br>
                {{ $dtstyle->alamat}}
            </div>
        </div>
    </div> -->
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
                <td width="40%">DESCRIPTION</td>
                <td width="15%" align="center">QTY</td>
                <td width="15%" align="center">UNIT PRICE</td>
                <td width="15%" align="center">AMOUNT</td>
            </tr>
        </thead>
        <tbody style="font-size: 12px;">

            @php
            $totalAmount = 0;
            $totalPcs = 0;
            @endphp

            <tr>
                <td colspan="6" align="center">KK NO. {{ $dt_inv->nokk ?? '-'}}</td>
            </tr>

            @foreach($grouped as $noop => $group)
            <?php
            $total_jmlsatuan = $group->sum('jmlsatuan');

            $cleanNoop = trim($noop);
            $getSIG = DB::connection('mysql_sig')
                ->table('ord')
                ->select('ordpk', 'ordno', 'prodes', 'artno')
                ->where('ordno', 'like', "%" . $cleanNoop . "%")
                ->first();

            $ordpk = $getSIG ? $getSIG->ordpk : 'N/A';
            $ordno = $getSIG ? $getSIG->ordno : 'N/A';
            $prodes = $getSIG ? $getSIG->prodes : 'N/A';
            $artno = $getSIG ? $getSIG->artno : 'N/A';

            $getBdown = DB::connection('mysql_sig')
                ->table('bdown')
                ->select('bdownpk', 'ordpk', 'poref', 'opno')
                ->where('ordpk',  $ordpk)
                // ->where('opno',  $ordpk)
                ->where('jenis', '=', 'DETAIL')
                ->where('poref', '!=', null)
                ->orderBy('bdownpk', 'DESC')
                ->first();

            $poref = $getBdown ? $getBdown->poref : 'N/A';
            ?>


            <tr>
                <td align="center">{{ $poref }}</td>
                <td>
                    {{ $artno }}<br>
                </td>

                <td>
                    {{ $prodes }}<br>
                </td>

                <td align="center">{{ number_format($total_jmlsatuan, 0, ',', ',') }} PCS</td>
                <td align="center">USD {{ number_format($priceppn, 2, '.', ',') }}</td>
                <td align="center">USD {{ number_format($total_jmlsatuan * $priceppn, 2, '.', ',') }}</td>

                <?php
                // Hitung totalAmount dan totalPcs sekali per grup
                $totalAmount += $total_jmlsatuan * $priceppn;
                $totalPcs += $total_jmlsatuan;
                ?>
            </tr>
            @endforeach
        </tbody>

        <tfoot style="font-size: 12px;">
            <tr>
                <td colspan="5" style="text-align: center;"></td>
                <td align="center"> USD <br>{{ number_format($totalAmount, 2, '.', ',') }}</td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: right;">PPN {{$dt_inv->ppn ?? '11'}}%</td>
                <td align="center"> USD <br>{{ number_format(($totalAmount * (($dt_inv->ppn ?? 11) / 100)), 2, '.', ',') }}</td>
            </tr>

            <tr>
                <td colspan="3" align="center">Total</td>
                <td align="center">
                    {{ number_format($totalPcs, 0, ',', ','), }} PCS
                </td>
                <td>

                </td>
                <td align="center">
                    <?php
                    $TakeAll = $totalAmount * (($dt_inv->ppn ?? 11) / 100) + $totalAmount;
                    $Terbilang2 = number_format($TakeAll, 2, '.', '');
                    ?>
                    USD <br>{{ number_format($TakeAll, 2, '.', ',');}}
                </td>
            </tr>
        </tfoot>
    </table>


    <div class="total" style="font-size: 12px;">
        <?php
        $terbilang = convertToWords($Terbilang2);
        echo "** US DOLLARS $terbilang **";
        ?>
    </div>

    <div class="total2" style="font-size: 12px;">
        Semarang, {{ $dt_inv->tglinv ?? '-'}}
    </div>

    <br>
    <!-- Footer Section -->


    <!-- <table style="width:20%;">
        <tr>
            <td align="left" style="font-size: 12px; height: 30px;">
                kurs pajak : {{ $dt_inv->kursp ?? 'N/A'}}
            </td>
        </tr>
    </table> -->

    <div style="margin-top:10px; width: 17%; border: 1px solid black; padding: 8px; font-size: 12px; height: 20px;">
    Kurs Pajak: {{ isset($dt_inv->kursp) ? number_format($dt_inv->kursp, 0, '.', ',') : 'N/A' }}

        <!-- {{ $dt_inv->kursp ?? 'N/A' }} -->
    </div>

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
        <div class="line"></div>
        <div class="signature" style="font-size: 12px;">AUTHORIZED SIGNATURE</div>
    </div>

</body>

</html>