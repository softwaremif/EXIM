<table>
    <thead>
        <!-- Header Informasi Umum -->
        <tr>
            <td colspan="1" style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;">  KARANGJATI, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>

        <tr>
        </tr>

        <tr>
            <td colspan="1" style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;"> TO:</td>
        </tr>
        <tr>
            <td colspan="1" style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;"> {{ $gudnm }} </td>
        </tr>

        <tr>
            <td colspan="3" style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;"> {{ $alamat }}</td>
        </tr>

        <tr>
            <td colspan="9" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> &nbsp;</td>
        </tr>

        <tr>
            <td colspan="9" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> DEBIT NOTE</td>
        </tr>

        <tr>
            <td colspan="9" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> MIF/  /KEU/2025</td>
        </tr>
        <tr>
            <td colspan="9" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> &nbsp;</td>
        </tr>
        <tr>
            <td colspan="9" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> BEING CHARGE OF MAKLON</td>
        </tr>

        @php
            $totalAmount = 0;
            $totalPcs = 0;
        @endphp

        @foreach($dt_kwitansi as $dt)
            @php
                $qty = intval(str_replace(',', '', $dt['qty'])); 
                $pricedt = floatval(str_replace(',', '', $dt['pricedt'])); 
                $totalAmount += $qty * $pricedt; 
                $totalPcs += $qty; 
            @endphp
        @endforeach
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="20px;" align="center"></th>
        </tr>

    </thead>

    <tbody style="border: 1px;">
        <!-- Header Tabel -->
        <tr>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">MPO</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">KONTRAK KERJA</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">INVOICE NO.</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">STYLE</th>
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
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['nokk'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['noinv'] }}</td>
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
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- Total -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;"> {{ number_format($totalPcs, 0, ',', ',') }}</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;"> {{ number_format($totalAmount, 3, '.', ',') }}</th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">PPH 2%</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;"> 
                {{ number_format($totalAmount * 0.2, 3, '.', ',') }}
            </th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">
                 <!-- total amount - total pph = hasil -->
                  @php
                    $totalPPh = $totalAmount * 0.2; 
                    $hasil = $totalAmount - $totalPPh;
                  @endphp
                 {{ number_format($hasil, 3, '.', ',') }}
                </th> 
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- PPN -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">PPN {{ $dt_kwitansi[0]['ppn'] ?? 11 }}%</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">
                <!-- {{ number_format(($totalAmount * (($dt_kwitansi[0]['ppn'] ?? 11) / 100)), 3, '.', ',') }} -->
                  <!-- totalAmoun * 11% = hasil ppn 12% -->
                  {{ number_format(($totalAmount * 0.11), 3, '.', ',') }}
                  </th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <!-- Total Akhir -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">
                {{ number_format($hasil + ($totalAmount * 0.11), 3, '.', ',') }}
                </th>
        </tr>

        @php
            $TakeAll = $hasil + ($totalAmount * 0.11); // Hitung dulu sebagai angka
            $Terbilang2 = number_format($TakeAll, 3, '.', ''); // Format hasil akhir
            $terbilang = convertToWords($Terbilang2); // Konversi ke kata
        @endphp


        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 10px; font-weight: bold;">USD SAY : {{ $terbilang }}</th>
        </tr>

        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">KINDLY TRANSFER THE AMOUNT TO OUR ACCOUNT :</th>
        </tr>
        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 9px; font-weight: bold;">PT.MORICH INDO FASHION</th>
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
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 9px; font-weight: bold;">            &nbsp;&nbsp;&nbsp;&nbsp; --- ( IDR )</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">PT.MORICH INDO FASHION</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">( JAP AGUS KARUNA  )</th>
        </tr>
    </tbody>
</table>