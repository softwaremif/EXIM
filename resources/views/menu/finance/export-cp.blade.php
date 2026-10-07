<table>
    <thead>

        @php
            $totalAmount = 0;
            $totalPcs = 0;
        @endphp

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
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> MIF/012A  /KEU/2025</td>
        </tr>

        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> &nbsp;</td>
        </tr>

        @foreach($dt_kwitansi as $dt)
            @php
                $qty = intval(str_replace(',', '', $dt['qty']));
                $priceppn = floatval(str_replace(',', '', $dt['priceppn']));

                $totalAmount += $qty * $priceppn;
                $totalPcs += $qty;
            @endphp
        @endforeach

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">DITERIMA DARI</th>
            <th width="30px;" colspan="5" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">: {{ $dt['gudnm'] }}</th>
        </tr>



        <?php
            $TakeAll = $totalAmount * (($dt['ppn'] ?? 11) / 100) + $totalAmount;
            $Terbilang2 = number_format($TakeAll, 3, '.', '');
        ?>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">USD SAY</th>
         
            <th width="30px;" colspan="5" style="font-family: Arial; text-align: left; font-size: 11px; font-weight: bold;">
            <!-- <?php
            $terbilang = convertToWords($Terbilang2);
            echo "** US DOLLARS $terbilang **";
            ?> -->
            nilai
            </th>
        </tr>

        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> &nbsp;</td>
        </tr> 
        
        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;"> &nbsp;</td>
        </tr>

        <tr>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">KONTRAK KERJA</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">INVOICE NO.</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">STYLE</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">QUANTITY <br> (PCS)</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">UNIT PRICE <br> (USD)</th>
            <th width="20px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">AMOUNT <br> (USD)</th>
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
     

        @foreach($dt_kwitansi as $dt)
        <tr>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['nokk'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['noinv'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['style'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['qty'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['priceppn'] }}</td>
            <td style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ $dt['amount'] }}</td>
        </tr>
        @php
        $qty = intval(str_replace(',', '', $dt['qty'])); // Hapus koma, lalu ubah ke integer
        $priceppn = floatval(str_replace(',', '', $dt['priceppn'])); // Pastikan nilai numerik

        $totalAmount += $qty * $priceppn;
        $totalPcs += $qty;
        @endphp
        @endforeach
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ number_format($totalPcs, 0, ',', ','), }}</th>
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

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold">PPN {{ $dt['ppn'] }}%</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold">{{ number_format(($totalAmount * (($dt['ppn'] ?? 11) / 100)), 3, '.', ',') }}</th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
        </tr>

        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" align="center"></th>
            <th width="13px;" align="center"></th>
            @php
            $TakeAll = $totalAmount * (($dt_inv->ppn ?? 11) / 100) + $totalAmount;
            $Terbilang2 = number_format($TakeAll, 3, '.', '');
            @endphp
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ number_format($TakeAll, 3, '.', ',');}}</th>
        </tr>

     

        <tr>
            <td colspan="6" style="font-family: Arial; text-align: center; font-size: 12px; font-weight: bold;">
            <?php
            $terbilang = convertToWords($Terbilang2);
            echo "** US DOLLARS $terbilang **";
            ?>
            </td>
        </tr>
    </tbody>
</table>