<table>
    @foreach ($tables as $table)
        <tr>
            <th>OP</th>
            <th>No PO</th>
            <th>STYLE</th>
            <th>COLOR</th>
            <th>COLOR CODE</th>
            <th>IDENTIFIER</th>
            <th>Case From -To (No Carton)</th>
            <th>LINE</th>
            <th>Total PCS/Carton</th>
            @foreach ($table['sizes'] as $label)
                <th>{{ $label }}</th>
            @endforeach
            <th>Total Carton</th>
            <th>Total PCS</th>
        </tr>

        @foreach ($table['groups'] as $group)
            <tr>
                <td>{{ $table['OP'] }}</td>
                <td>{{ $table['POno'] }}</td>
                <td>{{ $table['style'] }}</td>
                <td>{{ $table['color'] }}</td>
                <td>{{ $table['color_code'] }}</td>
                <td>{{ $group['is_singleton'] ? '►' : '' }}</td>
                <td>{{ $group['case_range'] }}</td>
                <td>{{ $group['line'] }}</td>
                <td>{{ $group['pcs_per_carton'] }}</td>
                @foreach ($table['sizes'] as $index => $label)
                    <td>{{ ($group['qty_per_size'][$index] ?? 0) > 0 ? $group['qty_per_size'][$index] : '' }}</td>
                @endforeach
                <td>{{ $group['total_carton'] }}</td>
                <td>{{ $group['total_pcs'] }}</td>
            </tr>
        @endforeach

        {{-- baris kosong sebagai jarak antar tabel PO --}}
        <tr><td></td></tr>
        <tr><td></td></tr>
    @endforeach
</table>