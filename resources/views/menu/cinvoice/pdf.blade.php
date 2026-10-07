<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="{!! asset('public/css/images/morich.gif') !!}" />
  <title>PDF | Commercial Invoice</title>

  <style type="text/css">
    @page {
      margin: 30px;
      margin-left: 35px;
    }

    .header-company {
      font-family: 'Times New Roman', Times, serif;
      font-size: 7pt;
      font-style: italic;
    }

    .header-title {
      font-family: Arial, sans-serif;
      font-size: 7pt;
      text-decoration: underline;
      text-align: center;
    }

    .fs-5 {
      font-size: 5pt;
    }
    
    .fs-4 {
      font-size: 4pt;
    }

    .fw-bold {
      font-weight: bold;
    }

    .ff-arial {
      font-family: Arial, sans-serif !important;
    }

    .border-trb {
      border-top: 0.0001px solid #000;
      border-right: 0.0001px solid #000;
      border-bottom: 0.0001px solid #000;
    }
    
    .border-lrb {
      border-left: 0.0001px solid #000;
      border-right: 0.0001px solid #000;
      border-bottom: 0.0001px solid #000;
    }

    .border-lr {
      border-left: 0.0001px solid #000;
      border-right: 0.0001px solid #000;
    }
    
    .border-rb {
      border-right: 0.0001px solid #000;
      border-bottom: 0.0001px solid #000;
    }
    
    .border-r {
      border-right: 0.0001px solid #000;
    }
  </style>
</head>

<body>
  <div class="header">
    {{-- <table>
      <tr>
        <td class="header-company">PT MORICH INDO FASHION</td>
      </tr>
    </table>
    <table class="ff-arial">
      <tr>
        <td class="fw-bold fs-4" style="width: 50px;">address</td>
        <td class="fs-4">JL. RAYA KARANGJATI KM. 25, GEMBONGAN - BERGAS,</td>
      </tr>
      <tr>
        <td></td>
        <td class="fs-4">KAB SEMARANG - JAWA TENGAH</td>
      </tr>
      <tr>
        <td></td>
        <td class="fs-4">INDONESIA 50552</td>
      </tr>
      <tr>
        <td></td>
        <td class="fs-4">TELP. 62-298-525105</td>
      </tr>
    </table> --}}
    <div><img src="{!! asset('public/css/images/morich.jpg') !!}" width="500" height="35" style="margin-top:3%;margin-left:17%;"></div>
    <div style="margin-top:-5%;margin-left:2%;"><img src="{!! asset('public/css/images/morich.png') !!}" width="75" height="75"></div>
    <div style="margin-top:-7%;margin-left:1%;text-align:center;">
        <p style="font-size:13px;">JL. RAYA KARANGJATI KM. 25 DESA GEMBONGAN - BERGAS, SEMARANG - INDONESIA<br>
        PHONE : 0298 - 523523 (6 LINES) FAX. 0298 - 522095<br>
        www.morichindofashion.com</p>
    </div>
    <hr style="border: none;border-top: 2px solid black;margin-top:2%;"></hr>
    <table style="width: 100%;">
      <tr>
        <td class="header-title">{{ $judul }}</td>
      </tr>
    </table>
  </div>

  <div class="content" style="margin-top: 15px;">
    <table style="width: 100%;table-layout: fixed;">
      <tr style="vertical-align: top; text-align: left;">
        <td style="width: 60%;">
          <table style="width: 100%;table-layout: fixed;">
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 15%;">BUYER</td>
              <td class="fs-5 ff-arial" style="width: 55%;">{!! nl2br(e($dbnote->alamatbyr)) !!}</td>
              <td class="fs-5 ff-arial" style="width: 30%;"></td>
            </tr>
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 15%;">CONTACT</td>
              <td class="fs-5 ff-arial" style="width: 55%;">{!! nl2br(e($dbnote->kontakbyr)) !!}</td>
              <td class="fs-5 ff-arial" style="width: 30%;"></td>
            </tr>
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 15%;">SHIPTO</td>
              <td class="fs-5 ff-arial" style="width: 55%;">{!! nl2br(e($shipto->alamat ?? null)) !!}</td>
              <td class="fs-5 ff-arial" style="width: 30%;"></td>
            </tr>
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 15%;">CONTACT</td>
              <td class="fs-5 ff-arial" style="width: 55%;">{!! nl2br(e($shipto->kontakship ?? null)) !!}</td>
              <td class="fs-5 ff-arial" style="width: 30%;"></td>
            </tr>
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 15%;">BILL TO</td>
              <td class="fs-5 ff-arial" style="width: 55%;">{!! nl2br(e($billnm)) !!} <br> {!! nl2br(e($dbnote->alamatbill)) !!}</td>
              <td class="fs-5 ff-arial" style="width: 30%;"></td>
            </tr>
            <!-- <tr><br></tr> -->
            <tr style="text-align: left;">
              <td class="fs-5 ff-arial" style="width: 15%;">SHIPPER</td>
              <td class="fs-5 ff-arial" style="width: 55%;">{!! nl2br(e($shipper)) !!}</td>
              <td class="fs-5 ff-arial" style="width: 30%;"></td>
            </tr>
            <tr><td style="padding-top: 5px;"></td></tr>
            <tr style="text-align: center;">
              <td class="fs-5 ff-arial" style="width: 15%;"></td>
              <td class="fs-5 ff-arial" style="width: 55%; border: 0.01px solid #000;">{!! nl2br(e($dbnote->dbnm)) !!}</td>
              <td class="fs-5 ff-arial" style="width: 30%;"></td>
            </tr>
          </table>
        </td>
        <td style="width: 5%;"></td>
        <td style="width: 35%;">
          <table style="width: 100%;table-layout: fixed;">
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 25%">{{ $judulinv }}</td>
              <td class="fs-5 ff-arial" style="width: 75%">{{ $dbnote->dbno }}</td>
            </tr>
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 25%">DATE</td>
              <td class="fs-5 ff-arial" style="width: 75%">{{ \Carbon\Carbon::parse($dbnote->tgldb)->format('M d, y') }}</td>
            </tr>
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 25%">SXS PO NO</td>
              <td class="fs-5 ff-arial" style="width: 75%;">{{ $pobyr }}</td>
            </tr>
            <tr><br></tr>
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 25%">SEASON</td>
              <td class="fs-5 ff-arial" style="width: 75%">{{ $dbnote->season }}</td>
            </tr>
            <!-- <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 25%">SBU</td>
              <td class="fs-5 ff-arial" style="width: 75%"></td>
            </tr> -->
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 25%">BRAND</td>
              <td class="fs-5 ff-arial" style="width: 75%">{{ $dbnote->brand }}</td>
            </tr>
            <tr style="vertical-align: top; text-align: left;">
              <td class="fs-5 ff-arial" style="width: 25%">HK Invoice</td>
              <td class="fs-5 ff-arial" style="width: 75%"></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <table style="border-collapse: collapse;width: 100%;table-layout: fixed;">
      <thead style="background-color: rgb(186, 186, 186)">
        <tr style="border: 0.0001px solid #000;">
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 8%;">PO</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 8%;">Style</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 10%;">COLOR</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 8%;">DESC</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 8%;">Product</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 8%;">Destination</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 8%;">SEND DATE</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 5%;">Qty</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 5%;">Unit</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 8%;">Unit Price<br>US$</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 8%;">Total Amount<br>US$</td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;width: 10%;">AWB SHIPPING #</td>
        </tr>
      </thead>
      <tbody>
        @foreach ($invsdt['row'] as $d)
          <tr style="border: 0.0001px solid #000;">
            <td class="fs-5 ff-arial" style="text-align: left;border-right: 0.0001px solid #000;">{{ $d['po'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: left;border-right: 0.0001px solid #000;">{{ $d['style'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: left;border-right: 0.0001px solid #000;">{{ $d['color'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: left;border-right: 0.0001px solid #000;">{{ $d['desc'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">{{ $d['product'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">{{ $d['destination'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">{{ $d['senddate'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">{{ $d['qty'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">{{ $d['unit'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">{{ $d['price'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">{{ $d['amount'] ?? null }}</td>
            <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">{{ $d['shipno'] ?? null }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="border: 0.0001px solid #000;">
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
          <td style="border-right: 0.0001px solid #000;height: 10px;"></td>
        </tr>
        @if(!empty($dbnote->bankcharge))
        <tr style="border: 0.0001px solid #000;">
          <td style="border-right: 0.0001px solid #000;"></td>
          <td style="border-right: 0.0001px solid #000;"></td>
          <td style="border-right: 0.0001px solid #000;"></td>
          <td style="border-right: 0.0001px solid #000;"></td>
          <td style="border-right: 0.0001px solid #000;"></td>
          <td style="border-right: 0.0001px solid #000;"></td>
          <td class="fs-5 ff-arial" style="text-align: left;border-right: 0.0001px solid #000;">BANK CHARGE</td>
          <td style="border-right: 0.0001px solid #000;"></td>
          <td style="border-right: 0.0001px solid #000;"></td>
          <td style="border-right: 0.0001px solid #000;"></td>
          <td class="fs-5 ff-arial" style="text-align: center;border-right: 0.0001px solid #000;">${{ $dbnote->bankcharge }}</td>
          <td style="border-right: 0.0001px solid #000;"></td>
        </tr>
        @endif
        <tr>
          <td class="fs-5 ff-arial" style="text-align: left;">PAYMENT DUE<br>DATE</td>
          <td class="fs-5 ff-arial" style="text-align: center;">{{ \Carbon\Carbon::parse($paydate)->format('M d, y') }}</td>
          <td style="border-bottom: 0.0001px solid #000;"></td>
          <td style="border-bottom: 0.0001px solid #000;"></td>
          <td class="fs-5 ff-arial" style="text-align: left;">TOTAL</td>
          <td></td>
          <td></td>
          <td></td>
          <td style="border: 0.0001px solid #000;"></td>
          <td style="border: 0.0001px solid #000;"></td>
          <td class="fs-5 ff-arial" style="text-align: center;border: 0.0001px solid #000;">${{ $invsdt['tamount'] }}</td>
          <td class="fs-5 ff-arial" style="text-align: center;border: 0.0001px solid #000;"></td>
          <!-- <td></td> -->
        </tr>
      </tfoot>
    </table>
    <br>
    <table style="border-collapse: collapse;width: 100%;table-layout: fixed;">
      <tr>
        <td style="width: 10%"></td>
        <td class="fs-5 fw-bold" style="width: 12%;border: 0.0001px solid #000;">Pay to the Order of:</td>
        <td class="border-trb" style="width: 30%;"></td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td class="fs-5 fw-bold border-lr" style="width: 12%;">Beneficiary:</td>
        <td class="fs-5 fw-bold border-r" style="width: 30%;">PT MORICH INDO FASHION</td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td class="fs-5 fw-bold border-lr" style="width: 12%;">Address of Beneficiary:</td>
        <td class="fs-5 fw-bold border-r" style="width: 30%;">
          JL. RAYA KARANGJATI KM.25, GEMBONGAN - BERGAS, <br>
          KAB SEMARANG - JAWA TENGAH, INDONESIA 50552 <br>
          TELP. 62-298-525105
        </td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td class="fs-5 fw-bold border-lr" style="width: 12%;">Advising Bank:</td>
        <td class="fs-5 fw-bold border-r" style="width: 30%;">PT. Bank Negara Indonesia (Persero) tbk</td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td class="border-lr" style="width: 12%;"></td>
        <td class="fs-5 fw-bold border-r" style="width: 30%;">Karangayu Branch,</td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td class="fs-5 fw-bold border-lr" style="width: 12%;">Address of advising Bank:</td>
        <td class="fs-5 fw-bold border-r" style="width: 30%;">Jl.Jend.Sudirman No. 195, 50149 Ph : (024) 7605502</td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td class="border-lr" style="width: 12%;"></td>
        <td class="fs-5 fw-bold border-r" style="width: 30%;">SEMARANG - INDONESIA</td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td class="fs-5 fw-bold border-lr" style="width: 12%;">Account:</td>
        <td class="fs-5 fw-bold border-r" style="width: 30%;">0029042494</td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td class="fs-5 fw-bold border-lr" style="width: 12%;">Swift No.:</td>
        <td class="fs-5 fw-bold border-r" style="width: 30%;">BNINIDJAKRU</td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="width: 10%"></td>
        <td style="width: 12%;border: 0.0001px solid #000;height: 5px;"></td>
        <td style="width: 30%;border: 0.0001px solid #000;"></td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
      <tr>
        <td style="height: 5px;"></td>
        <td style="height: 5px;"></td>
        <td style="height: 5px;"></td>
        <td style="height: 5px;"></td>
      </tr>
      <tr>
        <td class="fs-5" style="width: 10%">PREPARED BY</td>
        <td class="fs-5" style="width: 12%;">{{ $dbnote->prepareby }}</td>
        <td style="width: 30%;"></td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>

      <tr>
        <td class="fs-5" style="width: 10%"></td>
        <td class="fs-5" style="width: 12%;">        
          @if(Session::get('userpk') == 12)
            <img src="{!! asset('public/ttd/ega.jpg') !!}" alt="" style="width:80%;">
          @endif
        </td>
        <td style="width: 30%;"></td>
        <td style="width: 25%"></td>
        <td style="width: 28%"></td>
      </tr>
    </table>
  </div>

</body>

</html>
