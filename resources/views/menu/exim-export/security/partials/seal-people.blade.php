{{-- Petugas seal: diisi lewat scan ID card karyawan (QR berisi NRK); nama & department dari HRD. Scanner ada di index.blade.php --}}
@foreach ($sealRoles as $col => $label)
    @include('menu.exim-export.security.partials.person-slot', ['name' => $col . '_nrk', 'label' => $label, 'person' => $s->seal_people[$col] ?? null, 'required' => true])
@endforeach
