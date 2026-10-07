<table>
    <thead>
        <!-- Header Informasi Umum -->
        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;"> SEMARANG, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;"> &nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;"> No. &nbsp;&nbsp;&nbsp;&nbsp; : MIF/ /KEU/2025</td>
        </tr>
        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;"> Perihal &nbsp;&nbsp;&nbsp;&nbsp; :  TAGIHAN</td>
        </tr>
        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;"> &nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;"> TO:</td>
        </tr>
        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;"> {{ $gudnm }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;"> &nbsp;</td>
        </tr>

        @php
            $totalAmount = 0;
            $totalPcs = 0;
        @endphp

        @foreach($dt_kwitansi as $dt)
            @php
                $qty = intval(str_replace(',', '', $dt['qty'])); // Hapus koma dan ubah ke integer
                $pricedt = floatval(str_replace(',', '', $dt['pricedt'])); // Hapus koma dan ubah ke float
                $totalAmount += $qty * $pricedt; // Hitung total amount
                $totalPcs += $qty; // Hitung total pcs
            @endphp
        @endforeach

        @php
            $TakeAll = $totalAmount * (($dt_kwitansi[0]['ppn'] ?? 11) / 100) + $totalAmount; // Hitung total termasuk PPN
            $Terbilang2 = number_format($TakeAll, 3, '.', ''); // Format angka untuk terbilang
            $terbilang = convertToWords($Terbilang2); // Konversi ke terbilang
        @endphp

        <!-- <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">DITERIMA DARI</th>
            <th width="30px;" colspan="5" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">: {{ $gudnm }}</th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">USD SAY</th>
            <th width="30px;" colspan="5" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">
                ** US DOLLARS {{ $terbilang }} **
            </th>
        </tr> -->

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
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">MPO</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">STYLE</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">UNIT</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">PROSES</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">QUANTITY <br> (PCS)</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">UNIT PRICE <br> (USD)</th>
            <th width="20px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">AMOUNT <br> (USD)</th>
        </tr>

        <!-- Data Tabel -->
        @foreach($dt_kwitansi as $dt)
        <tr>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['mpo'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['style'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">PCE</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">JAHIT</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['qty'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['pricedt'] }}</td>
            <td style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ $dt['amount'] }}</td>
        </tr>
        @endforeach

        <tr>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- Total -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ number_format($totalPcs, 0, ',', ',') }}</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ number_format($totalAmount, 3, '.', ',') }}</th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- PPN -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">PPN {{ $dt_kwitansi[0]['ppn'] ?? 11 }}%</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">
                {{ number_format(($totalAmount * (($dt_kwitansi[0]['ppn'] ?? 11) / 100)), 3, '.', ',') }}
            </th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- Total Akhir -->
        <tr>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">
                {{ number_format($TakeAll, 3, '.', ',') }}
            </th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <tr>
            <th style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;">USD DOLLAR {{ $terbilang }} **</th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <tr>
            <th width="30px;" colspan="5" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">KINDLY TRANSFER THE AMOUNT TO OUR ACCOUNT :</th>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">SEMARANG, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</th>
        </tr>

        <tr>
            <th width="30px;" colspan="5" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">PT. MORICH INDO FASHION</th>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">PT. MORICH INDO FASHION</th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 9px; font-weight: bold;">BANK NEGARA INDONESIA (TBK)</th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 9px; font-weight: bold;">KANTOR CABANG KARANGAYU</th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 9px; font-weight: bold;">JL.JEND.SUDIRMAN NO.195</th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 9px; font-weight: bold;">SEMARANG</th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 9px; font-weight: bold;">SWIFT CODE:BNINIDJAKRU</th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 9px; font-weight: bold;">A/C NO. :   &nbsp;&nbsp;&nbsp;&nbsp; --- ( USD )</th>
        </tr>


        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;"> &nbsp;&nbsp;&nbsp;&nbsp; --- ( IDR )</th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">( JAP AGUS KARUNA  )</th>
        </tr>

    </tbody>
</table>