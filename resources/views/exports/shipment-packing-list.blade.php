<table>
    <thead>
        <tr>
            <th>GIII PO NO.</th>
            <th>CUSTOMER PO</th>
            <th>STYLE NO.</th>
            <th>COLOR</th>
            <th>UNIT</th>
            <th colspan="2">CTN NO.</th>
            <th>TTL CTN</th>
            @foreach ($sizes as $label)
                <th>{{ $label }}</th>
            @endforeach
            <th>QTY/CTN</th>
            <th>TTL QTY</th>
            <th>GROSS WT (KGS)</th>
            <th>NET WT (KGS)</th>
            <th>TTL GROSS WT (KGS)</th>
            <th>TTL NET WT (KGS)</th>
            <th>MEASUREMENT (CM)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lines as $line)
            <tr>
                <td>{{ $line['POno'] }}</td>
                <td>{{ $line['customer_po'] }}</td>
                <td>{{ $line['style'] }}</td>
                <td>{{ $line['color'] }}</td>
                <td>{{ $line['unit'] }}</td>
                <td>{{ $line['ctn_from'] }}</td>
                <td>{{ $line['ctn_to'] }}</td>
                <td>{{ $line['ttl_ctn'] }}</td>
                @foreach ($sizes as $label)
                    <td>{{ $line['qty_per_size'][$label] ?? '' }}</td>
                @endforeach
                <td>{{ $line['qty_per_ctn'] }}</td>
                <td>{{ $line['ttl_qty'] }}</td>
                <td>{{ $line['gw'] }}</td>
                <td>{{ $line['nw'] }}</td>
                <td>{{ $line['ttl_gw'] }}</td>
                <td>{{ $line['ttl_nw'] }}</td>
                <td>{{ $line['meas'] }}</td>
            </tr>
        @endforeach

        {{-- baris kosong sebelum total --}}
        <tr>
            <td colspan="{{ 15 + count($sizes) }}"></td>
        </tr>

        <tr>
            <td>Total:</td>
            <td></td>
            <td></td>
            <td></td>
            <td>0</td>
            <td></td>
            <td></td>
            <td>{{ $totals['ttl_ctn'] }}</td>
            @foreach ($sizes as $label)
                <td></td>
            @endforeach
            <td></td>
            <td>{{ $totals['ttl_qty'] }}</td>
            <td>{{ $totals['gw'] }}</td>
            <td>{{ $totals['nw'] }}</td>
            <td>{{ $totals['ttl_gw'] }}</td>
            <td>{{ $totals['ttl_nw'] }}</td>
            <td></td>
        </tr>
    </tbody>
</table>
