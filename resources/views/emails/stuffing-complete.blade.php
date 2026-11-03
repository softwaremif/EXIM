<!doctype html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body style="margin:0;padding:24px;background:#F5F6F8;font-family:Arial,Helvetica,sans-serif;color:#101828;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:760px;margin:0 auto;background:#FFFFFF;border:1px solid #EAECF0;border-radius:8px;">
        <tr>
            <td style="padding:24px;">
                @if ($originalTo)
                    <div style="margin-bottom:16px;padding:8px 12px;background:#FFF4E5;color:#B98900;font-size:12px;border-radius:6px;">
                        Mode uji &mdash; email ini seharusnya dikirim ke <b>{{ $originalTo }}</b>
                    </div>
                @endif

                <p style="margin:0 0 12px;font-size:14px;">Halo {{ $pic->login }},</p>
                <p style="margin:0 0 16px;font-size:14px;line-height:1.5;">
                    Shipment berikut <b>sudah 100% stuffing</b> (semua carton sudah discan masuk container),
                    tetapi statusnya masih <b>Confirm</b>. Mohon segera lengkapi No. Seal container dan Upload foto terkait,
                    agar status berubah menjadi <b>Complete</b>.
                </p>

                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#F9FAFB;text-align:left;color:#475467;">
                            <th style="border-bottom:1px solid #EAECF0;">ID Shipplan</th>
                            <th style="border-bottom:1px solid #EAECF0;">OP / Buyer</th>
                            <th style="border-bottom:1px solid #EAECF0;">Container belum seal</th>
                            <th style="border-bottom:1px solid #EAECF0;text-align:center;">Stuffing</th>
                            <th style="border-bottom:1px solid #EAECF0;">Closing Due</th>
                            <th style="border-bottom:1px solid #EAECF0;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shipments as $s)
                            <tr>
                                <td style="border-bottom:1px solid #EAECF0;font-weight:bold;">{{ $s['idship'] }}</td>
                                <td style="border-bottom:1px solid #EAECF0;">
                                    {{ $s['op'] ?: '-' }}<br>
                                    <span style="color:#667085;font-size:12px;">{{ $s['buyer'] ?: '-' }}</span>
                                </td>
                                <td style="border-bottom:1px solid #EAECF0;">{{ implode(', ', $s['unsealed']) ?: '-' }}</td>
                                <td style="border-bottom:1px solid #EAECF0;text-align:center;color:#12A150;font-weight:bold;">
                                    {{ number_format($s['loaded']) }} / {{ number_format($s['total']) }} ctn
                                </td>
                                <td style="border-bottom:1px solid #EAECF0;">{{ $s['closetime'] ?: '-' }}</td>
                                <td style="border-bottom:1px solid #EAECF0;">
                                    <a href="{{ $s['url'] }}" style="color:#2F6FED;text-decoration:none;">Buka</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p style="margin:20px 0 0;font-size:12px;color:#98A2B3;">
                    Email otomatis dari sistem EXIM. Pengingat dikirim maksimal 1x sehari per shipment
                    selama status masih Confirm.
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
