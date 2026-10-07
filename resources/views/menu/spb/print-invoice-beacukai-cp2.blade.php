<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Beacukai</title>
    <style>
        @page {
            margin: 20px;
            /* Margin semua sisi */
            margin-top: 35px;
            /* Atur margin atas jika berbeda */
            margin-bottom: 25px;
            margin-left: 5px;
            margin-right: 5px;
        }

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

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 22px;
            text-transform: uppercase;
            padding-top: 20px;
        }

        .title2 {
            text-align: right;
            font-size: 10px;
            text-transform: uppercase;
            padding-top: -90px;
        }

        .bawahInv {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }

        .separator {
            border-bottom: 2px solid black;
            width: 250px;
            margin: 3px auto;
        }

        .recipient,
        .address {
            margin-top: 20px;
            font-size: 14px;
        }

        .total {
            margin-top: 10px;
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
        }

        .footer .signature {
            font-size: 14px;
            top: -10px;
            background: white;
            padding: 0 19px;
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <table class="header">
        <div><img src="{!! asset('public/css/images/morich.jpg') !!}" width="500" height="35" style="margin-top:3%;margin-left:17%;"></div>
        <div style="margin-top:-5%;margin-left:2%;"><img src="{!! asset('public/css/images/morich.png') !!}" width="75" height="75"></div>
        <div style="margin-top:-7%;margin-left:1%;text-align:center;">
            <p style="font-size:13px;">JL. RAYA KARANGJATI KM. 25 DESA GEMBONGAN - BERGAS, SEMARANG - INDONESIA<br>
                PHONE : 0298 - 523523 (6 LINES) FAX. 0298 - 522095<br>
                www.morichindofashion.com</p>
        </div>
        <hr style="border: none;border-top: 2px solid black;margin-top:2%;">
        </hr>
    </table>
    <br>

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

    @if($dt_inv->cur == 1)
    <!-- Table Section -->
    <table style="margin-top: 20px;">
        <thead>
            <tr style="font-size: 12px;">
                <td width="15%" align="center">MARKS & NOS</td>
                <td width="40%">DESCRIPTION</td>
                <td width="15%" align="center">JUMLAH</td>
                @if($dt_inv->cur == 1)
                <td width="15%" align="center">AMOUNT/USD</td>
                @endif

                @if($dt_inv->cur == 1)
                    @if($dtstyle->gudpk == 172)
                        <td width="15%" align="center">AMOUNT/IDR</td>
                    @else
                        <td width="15%" align="center">AMOUNT/USD</td>
                    @endif
                @else
                <td width="15%" align="center">AMOUNT/IDR</td>
                @endif
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
            $cleanNoop = $noop = str_replace(' ', '', $noop);

            $getBdown = DB::connection('mysql_sig')
                ->table('bdown')
                ->leftjoin('ord', 'ord.ordpk', '=', 'bdown.ordpk')
                ->select('bdown.bdownpk', 'ord.ordpk', 'bdown.opno', 'bdown.style', 'ord.artno', 'ord.prodes')
                ->whereRaw("REPLACE(bdown.opno, ' ', '') LIKE ?", [$cleanNoop])
                ->first();

            if (!$getBdown) {
                $getOrd = DB::connection('mysql_sig')
                    ->table('ord')
                    ->select('ordpk', 'ordno', 'artno', 'prodes')
                    ->where('ordno', 'like', "%" . $cleanNoop . "%")
                    ->orderBy('ordpk', 'DESC')
                    ->first();

                $prodes = $getOrd ? $getOrd->prodes : 'N/A';
                $artno = $getOrd ? $getOrd->artno : 'N/A';
            } else {
                $prodes = $getBdown->prodes;
                $artno = $getBdown->artno;
                $opno = $getBdown->opno;
            }

            ?>

            <tr>
                <td align="center">{{ ++$index }}</td>
                <td>
                    <!-- {{ $prodes }}<br>
                    STYLE: {{ $artno }} -->

                    <!-- {{ $getSpbdt2->prodes ?? 'N/A' }}<br>
                    STYLE: {{ $getSpbdt2->buyer ?? 'N/A' }} -->

                           {{ $dt_invdt->mprodes ?? '' }}<br>
                    STYLE: {{ $dt_invdt->mstyle ?? '' }}

                </td>
                <td align="center">{{ number_format($total_jmlsatuan, 0, ',', ',') }} PCS</td>
                @if($dt_inv->cur == 1)
                <td align="center">{{ number_format($dt_inv->pricemat, 2, '.', ',') }} </td>
                @endif
                <td align="center">{{ number_format(optional($kursp)->kursp * optional($dt_inv)->pricemat, 2, '.', ',') }}</td>

                <?php
                $totalAmount += optional($kursp)->kursp * optional($dt_inv)->pricemat;
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

                @if($dt_inv->cur == 1)
                <td align="center">
                    {{ number_format($dt_inv->pricemat, 2, '.', ',') }}
                </td>
                @endif

                <td align="center">
                    <?php
                    $Terbilang2 = number_format($totalAmount, 2, '.', '');
                    ?>
                    {{ number_format($Terbilang2, 2, '.', ',');}}
                </td>
            </tr>
        </tfoot>
    </table>
    @else

    <table style="margin-top: 20px;">
        <thead>
            <tr style="font-size: 12px;">
                <td width="15%" align="center">MARKS & NOS</td>
                <td width="40%">DESCRIPTION</td>
                <td width="15%" align="center">JUMLAH</td>
                <td width="15%" align="center">AMOUNT/IDR</td>
            </tr>
        </thead>


        <tbody style="font-size: 12px;">

            @php
                $index = 0;
            @endphp

            @foreach($grouped as $noop => $group)
            <?php

                $total_jmlsatuan = $group->sum('jmlsatuan');
                $cleanNoop = $noop = str_replace(' ', '', $noop);

                $getBdown = DB::connection('mysql_sig')
                    ->table('bdown')
                    ->leftjoin('ord', 'ord.ordpk', '=', 'bdown.ordpk')
                    ->select('bdown.bdownpk', 'ord.ordpk', 'bdown.opno', 'bdown.style', 'ord.artno', 'ord.prodes')
                    ->whereRaw("REPLACE(bdown.opno, ' ', '') LIKE ?", [$cleanNoop])
                    ->first();

                if (!$getBdown) {
                    $getOrd = DB::connection('mysql_sig')
                        ->table('ord')
                        ->select('ordpk', 'ordno', 'artno', 'prodes')
                        ->where('ordno', 'like', "%" . $cleanNoop . "%")
                        ->orderBy('ordpk', 'DESC')
                        ->first();

                    $prodes = $getOrd ? $getOrd->prodes : 'N/A';
                    $artno = $getOrd ? $getOrd->artno : 'N/A';
                } else {
                    $prodes = $getBdown->prodes;
                    $artno = $getBdown->artno;
                    $opno = $getBdown->opno;
                }

            ?>

            <tr>
                <td align="center">{{ ++$index }}</td>
                <td>

                           {{ $dt_invdt->mprodes ?? '' }}<br>
                    STYLE: {{ $dt_invdt->mstyle ?? '' }}

                </td>
                <td align="center">{{ number_format($total_jmlsatuan, 0, ',', ',') }} PCS</td>
                <td align="center">{{ number_format($dt_inv->pricemat, 2, '.', ',') }}</td>
            </tr>
            @endforeach

        </tbody>

        <tfoot style="font-size: 12px;">
            <tr>
                <td colspan="2" align="center">Total</td>
                <td align="center">
                    {{ number_format($total_jmlsatuan, 0, ',', ','), }} PCS
                </td>


                <td align="center">
                    {{ number_format($dt_inv->pricemat, 2, '.', ',') }}
                </td>
            </tr>
        </tfoot>
    </table>
    @endif




    <div class="total" style="font-size: 12px;">
        {{ $dt_inv->nokk ?? ''}}
        <br> TGL {{ \Carbon\Carbon::parse($dt_inv->tglkk)->format('d-m-Y') }}

    </div>


    <!-- Footer Section -->
    <div class="footer">
        <div class="line"></div>
        <div class="signature" style="font-size: 12px;">AUTHORIZED SIGNATURE</div>
    </div>

</body>

</html>