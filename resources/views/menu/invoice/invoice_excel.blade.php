<table>
    <thead>
        <tr>
            <th>Est Ship</th>
            <th>Products</th>
            <th>Buyer</th>
            <th>Tgl PEB</th>
            <th>PEB</th>
            <th>Style</th>
            <th>#OP</th>
            <th>PO</th>
            <th>No.Invoice</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Amount</th>
            <th>Remark</th>
        </tr>
    </thead>
        <tbody>
                    @php $i=1; $tqyt = 0; $tjmlflyer = 0; $tamount = 0; @endphp
                    @foreach($dtinvs as $key => $p)
                    @php 
                        $price = $p->price;
                        $qty = $p->qty;
                        if($p->price > 0.01){
                            $amountAwal = (int)$price * (int)$qty;
                             $amount = (float)$price * (float)$qty;
                        }else{
                            $amount = 0.01;
                        }
                        $amount2 = $amount;

                        $op = DB::connection('mysql_sig')->table('ord')
                        ->select('ordno')
                        ->where('ordpk', $p->ordpk)
                        ->first();

                        $ordno = $op->ordno ?? '-';
                        
                    @endphp
                    <tr>
                        <td align="center">{{$p->sendate}}</td>
                        <td align="center">{{$p->itemnm}}</td>
                        <td align="center">{{$p->brand}}</td>
                        <td align="center"></td>
                        <td align="center"></td>
                        <td align="center">{{$p->style}}</td>
                        <td align="center">{{$ordno}}</td>
                        <td align="center">{{$p->pono}}</td>
                        <td align="center">{{$p->noinvs}}</td>
                        <td align="center">{{$p->qty}}</td>
                        <td align="center">{{$price}}</td>
                        <td align="center">{{$amount2}}</td>
                        <td align="center">{{$p->nos}}</td>
                    </tr>
                    @endforeach
    </tbody>
</table>