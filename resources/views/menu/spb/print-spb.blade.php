<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pengantar Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header,
        .footer {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        /* th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        } */

        .no-border {
            border: none;
        }

        .kop-container {
            width: 100%;
            /* border-bottom: 2px solid black; */
            margin-bottom: 3px;
        }

        .kop-logo {
            width: 15%;
            text-align: center;
            vertical-align: middle;
        }

        .kop-logo img {
            width: 60px;
            height: auto;
        }

        .kop-text {
            width: 85%;
            vertical-align: middle;
            padding-left: 1px;
        }

        .kop-text h2 {
            margin: 0;
            font-size: 18px;
        }

        .kop-text p {
            margin: 0.3px 0;
            font-size: 12px;
        }

        .container {
            width: 100%;
            /* border-collapse: collapse; */
        }

        .left-column {
            width: 65%;
            vertical-align: top;
        }

        .right-column {
            width: 35%;
            vertical-align: top;
        }

        td {
            padding: 5px;
        }

        .judul-surat {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .separator {
            border-bottom: 0.5px solid black;
            width: 280px;
            /* Lebar garis, bisa disesuaikan */
            margin: 3px auto;
            /* Otomatis rata tengah */
        }

        .subjudul {
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <table class="kop-container">
        <tr>
            <td class="kop-logo">
                <img src="{!! asset('public/css/images/morich.png') !!}" alt="Logo Perusahaan">
            </td>
            <td class="kop-text">
                <h2>PT. MORICH INDO FASHION</h2>
                <p>Jl. Raya Karangjati KM. 25 Desa Gembongan, Bergas,</p>
                <p>SEMARANG 50552 INDONESIA</p>
                <p>Telp. 62-298-523523 Fax. 62-298-522095</p>
                <p>TaxId: 1.287.855.9-505</p>
            </td>
        </tr>
    </table>

    <table class="container">
        <tr>
            <td colspan="2" class="judul-surat">
                SURAT PENGANTAR BARANG
                <div class="separator"></div>

            </td>
        </tr>

        <tr>
            <td colspan="2" class="subjudul">
                No. {{ $dtstyle->nobukti }}, {{ $dtstyle->tanggal }}
            </td>
        </tr>
        <br>
        <tr>
            <td class="left-column">
                <table>
                    <tr>
                        <td>Jenis Angkutan</td>
                        <td><strong>: {{ $dtstyle->jangnm }}</strong></td>
                    </tr>
                    <tr>
                        <td>Nama Perusahaan Angkutan</td>
                        <td><strong>: {{ $dtstyle->angnm }}</strong></td>
                    </tr>
                    <tr>
                        <td>Data Angkutan</td>
                        <td>
                            <strong>
                                :
                                @if($dtstyle->contno != null)
                                CONT NO. {{ $dtstyle->contno }}
                                @endif
                                <br> &nbsp;
                                @if($dtstyle->sealno != null)
                                SEAL NO. {{ $dtstyle->sealno }}
                                @endif
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>No. Truk</td>
                        <td><strong>: {{ $dtstyle->notruk }}</strong></td>
                    </tr>
                    <tr>
                        <td>Booking Forwarder</td>
                        <td><strong>: {{ $dtstyle->fwdnm }}</strong></td>
                    </tr>
                </table>
            </td>

            <td class="right-column">
                <table>
                    <tr>
                        <td>Alamat Gudang:</td>
                    </tr>
                    <tr>
                        <td>
                            <strong>
                                {{ $dtstyle->gudnm}}
                                <br> {{ $dtstyle->alamat}}
                                <br>
                                <br> {!! $dtstyle->att !!}
                            </strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table border="1">
        <tr>
            <th rowspan="2" align="center">Nomor O/P</th>
            <th rowspan="2" align="center">Order No.</th>
            <th rowspan="2" align="center">Nama Pembeli</th>
            <th rowspan="2" align="center">Negara Tujuan</th>
            <th colspan="2" align="center">Jml (Kuantitas)</th>
            <th rowspan="2" align="center">Berat Bruto <br>( Kg )</th>
            <th rowspan="2" align="center">Kubikasi <br>M3</th>
            <th rowspan="2" align="center">Booking Number</th>
        </tr>
        <tr>
            <th>PCS/DZ</th>
            @if($dtstyle->gudpk == 165)
            <th>PACK</th>
            @else
            <th>CARTON</th>
            <!-- <th>PACKAGE</th> -->
            @endif
        </tr>

        <tbody>

            @php
            $totalBerat = 0;
            $totalPcs = 0;
            $totalKarton = 0;
            $totalKubikasi = 0;
            @endphp

            @foreach($dtspbdt as $dt)
            <tr>
                <td align="center">{{ $dt->noop }}</td>
                <td align="center">{{ $dt->orderno }}</td>
                <td align="center">{{ $dt->byrnm }}</td>
                <td align="center">{{ $dt->negara ?? '' }}</td>
                <td align="center">{{ number_format($dt->jmlsatuan, 0, '.', ','),  }}</td>
                <td align="center">{{ number_format($dt->jmlkarton, 0, '.', ','),  }}</td>
                <td align="center">{{ number_format($dt->berat, 2, '.', ','), }}</td>
                <td align="center">{{ $dt->kubikasi }}</td>
                <td align="center">{{ $dt->smarks }} </td>

                <?php
                $totalPcs += $dt->jmlsatuan;
                $totalKarton += $dt->jmlkarton;
                $totalBerat += $dt->berat;
                $totalKubikasi += $dt->kubikasi;
                ?>
            </tr>

            @endforeach
        </tbody>
        <tfoot style="font-size: 12px;">
            <tr>
                <td colspan="4" align="center"><strong> Total </strong></td>
                <td align="center"><strong> {{ number_format($totalPcs, 0, ',', ','), }} </strong></td>
                <td align="center"><strong> {{ number_format($totalKarton, 0, ',', ','), }} </strong></td>
                <td align="center"><strong> {{ number_format($totalBerat, 2, '.', ','), }} </strong></td>
                <td align="center"><strong> {{ number_format($totalKubikasi, 3, '.', ''), }} </strong></td>
                <td> </td>
            </tr>
        </tfoot>
    </table>

    <br>
    <br>



    <table class="no-border" style="font-size: 12px;">
        <tr>
            <td align="center">Disetujui oleh:</td>
            <td align="center">Diketahui oleh:</td>
            <td align="center">Driver:</td>
            <td align="center">Telah diterima dalam keadaan <br> BAIK & SESUAI <br> oleh Pihak Gudang</td>
        </tr>

        <tr>
            <td align="center">PT. MORICH INDO FASHION <br>EXIM Dept.</td>
            <td align="center">PT. MORICH INDO FASHION <br>Security</td>
            <td> </td>
            <td> </td>
        </tr>

        <br>
        <br>

        <tr>
            <td align="center">(__________)</td>
            <td align="center">(__________)</td>
            <td align="center">({{ $dtstyle->angnm}})</td>
            <td align="center">(__________)</td>

        </tr>
    </table>

    <p style="margin-top: 20px; font-size: 12px;">EMKL : {{ $dtstyle->emklnm ?? ''}}</p>

</body>

</html>