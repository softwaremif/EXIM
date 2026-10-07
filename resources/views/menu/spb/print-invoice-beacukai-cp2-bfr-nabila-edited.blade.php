<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Beacukai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
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

        /* .title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            padding-top: 20px;
        } */

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
            font-size: 10px;
            text-transform: uppercase;
            padding-top: -90px;
        }

        .bawahInv {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            /* padding-top: -90px; */
        }

        .separator {
            border-bottom: 2px solid black;
            width: 250px;
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

        .footer {
            text-align: right;
            margin-top: 90px;
        }

        .footer .line {
            border-top: 1px solid black;
            margin: 1px 0;
            width: 30%;
            margin-left: auto;
            margin-right: 0;
            /* position: relative; */
        }

        .footer .signature {
            font-size: 14px;
            /* position: absolute; */
            top: -10px;
            /* Atur jarak vertikal agar teks tepat di tengah garis */
            /* left: 100%; */
            /* transform: translateX(-50%); */
            /* Posisikan teks di tengah horizontal */
            background: white;
            /* Tambahkan latar belakang putih agar garis tidak menutupi teks */
            padding: 0 19px;
            /* Opsional: Beri sedikit ruang di sekitar teks */
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
    <!-- <div class="title">
        INVOICE
        <div class="separator"></div>
        <div style="font-size: 12px;">(INVOICE NO: {{ $dt_inv->noinv ?? '-'}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; TGL {{ $dt_inv->tglinv ?? '-'}})</div>
    </div> -->

    <div class="title">
        INVOICE
        <div class="separator"></div>
    </div>

    <div class="title2">
        <div class="bawahInv">
        (INVOICE NO: {{ $dt_inv->noinv ?? '-'}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; TGL {{ $dt_inv->tglinv ?? '-'}})</span>
        </div>
    </div>

    <!-- Recipient Section -->
    <div class="recipient">
        <div style="font-size: 10px;">SOLD BY ORDER & FOR ACCOUNT & RISK OF</div>
        <div>
            <div style="font-size: 10px;">MESSRS:</div><br>
            <div style="font-size: 10px;">
                {{ $dtstyle->gudnm}} <br>
                {{ $dtstyle->alamat}}
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <table style="margin-top: 20px;">
        <thead>
            <tr style="font-size: 12px;">
                <td width="15%" align="center">MARKS & NOS</td>
                <td width="40%">DESCRIPTION</td>
                <td width="15%" align="center">JUMLAH</td>
                <td width="15%" align="center">UNIT PRICE</td>
                <td width="15%" align="center">AMOUNT</td>
            </tr>
        </thead>
        <tbody style="font-size: 12px;">
           
            @php
            $totalAmount = 0;
            $totalPcs = 0;
            $index = 0;

            @endphp

            @foreach($grouped as $noop => $group)
            <?php

            $total_jmlsatuan = $group->sum('jmlsatuan');
            $cleanNoop = trim($noop);
            // $getSIG = DB::connection('mysql_sig')
            //     ->table('ord')
            //     ->select('ordpk', 'ordno', 'prodes', 'artno')
            //     ->where('ordno', 'like', "%" . $cleanNoop . "%")
            //     ->first();

            // $ordno = $getSIG ? $getSIG->ordno : 'N/A';
            // $prodes = $getSIG ? $getSIG->prodes : 'N/A';
            // $artno = $getSIG ? $getSIG->artno : 'N/A';

            $getBdown = DB::connection('mysql_sig')
            ->table('bdown')
            ->select('bdownpk', 'ordpk', 'opno', 'style')
            ->where('opno', 'like', "%" . $cleanNoop . "%")
            ->first();
        
            if($getBdown) {
                $getOrd = DB::connection('mysql_sig')
                    ->table('ord')
                    ->select('ordpk', 'ordno', 'artno', 'prodes')
                    // ->where('artno', 'like', "%" . $getBdown->style . "%")
                    ->where('ordno', 'like', "%" . $getBdown->opno . "%")
                    ->orderBy('ordpk', 'DESC')
                    ->first();
            
                $prodes = $getOrd ? $getOrd->prodes : 'N/A';
                $artno = $getOrd ? $getOrd->artno : 'N/A';
            } else {
                // $prodes = 'N/A';
                // $artno = 'N/A';

                $getSIG = DB::connection('mysql_sig')
                ->table('ord')
                ->select('ordpk', 'ordno', 'prodes', 'artno')
                ->where('ordno', 'like', "%" . $cleanNoop . "%")
                ->first();

                $ordno = $getSIG ? $getSIG->ordno : 'N/A';
                $prodes = $getSIG ? $getSIG->prodes : 'N/A';
                $artno = $getSIG ? $getSIG->artno : 'N/A';
            }
            ?>

            <tr>
                <td align="center">{{ ++$index }}</td>
                <td>
                    {{ $prodes }}<br>
                    STYLE: {{ $artno }}
                </td>
                <td align="center">{{ number_format($total_jmlsatuan, 0, ',', ',') }} PCS</td>
                <td align="center">{{ number_format($dt_inv->priceppn, 2, '.', ',') }}</td>
                <td align="center">{{ number_format($total_jmlsatuan * $dt_inv->priceppn, 2, '.', ',') }}</td>

                <?php
                $totalAmount += $total_jmlsatuan * $dt_inv->priceppn;
                $totalPcs += $total_jmlsatuan;
                ?>
            </tr>
            @endforeach
        </tbody>

        <tfoot style="font-size: 12px;">
            <tr>
                <td colspan="2" align="center">Total</td>
                <td align="center">
                    {{ number_format($totalPcs, 0, ',', ','), }} PCS
                </td>
                <td>

                </td>
                <td align="center">
                <!-- <?php
                
                ?>
                    {{ number_format($totalAmount, 2, ',', ','), }} -->

                    <?php
                    $Terbilang2 = number_format($totalAmount, 2, '.', '');
                    ?>
                    USD <br>{{ number_format($Terbilang2, 2, '.', ',');}}
                </td>
            </tr>
        </tfoot>
    </table>


    <div class="total" style="font-size: 12px;">
        <?php
        // $formattedTotal = number_format($totalAmount, 2, '.', ',');
        $terbilang = convertToWords($Terbilang2);
        echo "SAY TOTAL: $terbilang";
        ?>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <div class="line"></div>
        <div class="signature" style="font-size: 12px;">AUTHORIZED SIGNATURE</div>
    </div>

</body>

</html>