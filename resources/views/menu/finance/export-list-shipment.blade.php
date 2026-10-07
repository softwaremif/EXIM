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


        <!-- Hitung Total Amount dan Terbilang -->
        @php
            $totalAmount = 0;
            $totalPcs = 0;
        @endphp

        @foreach($dt_export as $dt)
            @php $dt = (object) $dt; @endphp

            @php
                $qty = floatval(preg_replace('/[^\d.]/', '', $dt->totqty));
                $pricedt = floatval(preg_replace('/[^\d.]/', '', $dt->pricedt));
                $totalAmount += $qty * $pricedt; // Hitung total amount
                $totalPcs += $qty; // Hitung total pcs
            @endphp
        @endforeach
    </thead>

    <tbody>
        <!-- Header Tabel -->
        <tr>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">Tanggal</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">INVOICE NO.</th>
            <th width="30px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">NO SPB</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">GUDANG</th>
            <th width="13px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">QTY</th>
            <th width="20px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">PRICE</th>
            <th width="20px;" style="font-family: Arial; text-align: center; font-size: 11px; font-weight: bold;">TOTAL</th>
        </tr>

        <!-- Data Tabel -->
        @foreach($dt_export as $dt)
        <tr>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['tanggal'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['noinv'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['nobukti'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['gudang'] }}</td>
            <td style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ $dt['totqty'] }}</td>
            <td style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ $dt['pricedt'] }}</td>
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
        </tr>

        <!-- Total -->
        <tr>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="30px;" align="center"></th>
            <th width="13px;" style="font-family: calibri; text-align: center; font-size: 11px; font-weight: bold;">{{ number_format($totalPcs, 0, ',', ',') }}</th>
            <th width="13px;" align="center"></th>
            <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ number_format($totalAmount, 2, '.', ',') }}</th>
            <!-- <th width="20px;" style="font-family: calibri; text-align: right; font-size: 11px; font-weight: bold;">{{ number_format(round($totalAmount), 0, '.', ',') }}</th> -->
        </tr>
    </tbody>
</table>