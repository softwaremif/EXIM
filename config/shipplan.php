<?php

return [

    // Notifikasi email: Stuffing sudah 100% tapi status shipment masih Confirm
    'stuffing_notif' => [
        // daftar email PIC (akses.user.email) yang diproses, pisah koma.
        // Kosong = semua PIC.
        'only_users' => array_values(array_filter(array_map('trim',
            explode(',', (string) env('STUFFING_NOTIF_ONLY_USERS', ''))))),

        // kalau diisi, SEMUA email dialihkan ke alamat ini (mode uji)
        'redirect_to' => env('STUFFING_NOTIF_REDIRECT_TO'),

        // CC tiap email ke alamat ini (monitoring), pisah koma. Kosong = tanpa CC.
        'cc' => array_values(array_filter(array_map('trim',
            explode(',', (string) env('STUFFING_NOTIF_CC', ''))))),
    ],

];
