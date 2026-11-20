{{-- Halaman yang ditampilkan service worker (GateAppController::serviceWorker) kalau sinyal putus --}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline · Security Gate</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; box-sizing: border-box; background: #f4f3ef; color: #16191c; font: 16px/24px "IBM Plex Sans", system-ui, sans-serif; text-align: center; }
        @media (prefers-color-scheme: dark) { body { background: #121416; color: #f2f1ec; } }
        h1 { margin: 0 0 8px; font-size: 20px; }
        p { margin: 0 0 20px; color: #52575d; }
        button { min-height: 48px; padding: 0 24px; border: 0; border-radius: 10px; background: #f2b705; color: #16191c; font: 600 16px/20px inherit; cursor: pointer; }
    </style>
</head>
<body>
    <div>
        <h1>Tidak ada sinyal</h1>
        <p>Security Gate belum bisa terhubung ke server. Periksa Wi-Fi, lalu coba lagi.</p>
        <button type="button" onclick="location.reload()">Coba lagi</button>
    </div>
</body>
</html>
