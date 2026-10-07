<table>
    <thead>
        <!-- Header Informasi Umum -->
        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> PT. MORICH INDO FASION</td>
        </tr>
        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 10px; font-weight: bold;"> Jl.Raya Karangjati KM 25, Desa Gembongan, Bergas</td>
        </tr>
        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 10px; font-weight: bold;"> KAB. SEMARANG </td>
        </tr>
        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> &nbsp;</td>
        </tr>
        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 16px; font-weight: bold;"> KWITANSI</td>
        </tr>
        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> MIF/  /KEU/2025</td>
        </tr>
        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> &nbsp;</td>
        </tr>

        <!-- Hitung Total Amount dan Terbilang -->
        @php
            $totalAmount = 0;
            $totalPcs = 0;
        @endphp

        @foreach($dt_kwitansi as $dt)
            @php
                $qty = intval(str_replace(',', '', $dt['qty'])); // Hapus koma dan ubah ke integer
                $priceppn = floatval(str_replace(',', '', $dt['priceppn'])); // Hapus koma dan ubah ke float
                $totalAmount += $qty * $priceppn; // Hitung total amount
                $totalPcs += $qty; // Hitung total pcs
            @endphp
        @endforeach

        @php
            $TakeAll = $totalAmount * (($dt_kwitansi[0]['ppn'] ?? 11) / 100) + $totalAmount; // Hitung total termasuk PPN
            $Terbilang2 = number_format($TakeAll, 3, '.', ''); // Format angka untuk terbilang
            $terbilang = convertToWords($Terbilang2); // Konversi ke terbilang
        @endphp

        <!-- Informasi Diterima Dari -->
        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">DITERIMA DARI</th>
            <th width="30px;" colspan="5" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">: {{ $gudnm }}</th>
        </tr>

        <!-- Informasi USD SAY -->
        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">USD SAY</th>
            <th width="30px;" colspan="5" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">
                ** US DOLLARS {{ $terbilang }} **
            </th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="20px;" align="center"></th>
        </tr>

    </thead>

    <tbody>
        <!-- Header Tabel -->
        <tr>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">KONTRAK KERJA</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">INVOICE NO.</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">STYLE</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">QUANTITY <br> (PCS)</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">UNIT PRICE <br> (USD)</th>
            <th width="20px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">AMOUNT <br> (USD)</th>
        </tr>

        <!-- Data Tabel -->
        @foreach($dt_kwitansi as $dt)
        <tr>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['nokk'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['noinv'] }}</td>
            <!-- <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['style'] }}</td> -->
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;"> </td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['qty'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['priceppn'] }}</td>
            <td style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ $dt['amount'] }}</td>
        </tr>
        @endforeach

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- Total -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ number_format($totalPcs, 0, ',', ',') }}</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ number_format($totalAmount, 3, '.', ',') }}</th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- PPN -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">PPN {{ $dt_kwitansi[0]['ppn'] ?? 11 }}%</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">
                {{ number_format(($totalAmount * (($dt_kwitansi[0]['ppn'] ?? 11) / 100)), 3, '.', ',') }}
            </th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- Total Akhir -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">
                {{ number_format($TakeAll, 3, '.', ',') }}
            </th>
        </tr>
    </tbody>
</table>