<?php

namespace App\Http\Controllers\Api\master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Gudang;
use App\Models\Jang;
use App\Models\Ang;
use App\Models\Fwd;
use App\Models\Emkl;
use App\Models\Byr;

class M_GudangController extends Controller
{
    public function get_gudang()
    {
        $data = DB::table('gud')->orderBy('gudnm', 'asc')->get()->toArray();
        $data = array_map(function ($item) {
            $item->gudnm = trim($item->gudnm);
            return $item;
        }, $data);

        return json_encode($data);

        // $page = $request->input('page') ?? '1';
        // $rows = $request->input('rows') ?? '100';
        // $searchByInput = $request->searchByInput;
        // $sortlistByName = $request->sortlistByName;

        // $data_gudang = DB::table('gud')->select('gudpk', 'gudid', 'gudnm', 'alamat');

        // if (!empty($searchByInput)) {
        //     $concatenatedValue = DB::raw("CONCAT(
        //         COALESCE(gudid, ''),
        //         COALESCE(gudnm, '')
        //     )");
        //     $data_gudang = $data_gudang->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        // }

        // if ($sortlistByName == 11) {
        //     $data_gudang = $data_gudang->orderBy('gudnm', 'ASC');
        // } else if ($sortlistByName == 12) {
        //     $data_gudang = $data_gudang->orderBy('gudnm', 'DESC');
        // } else {
        //     $data_gudang = $data_gudang->orderBy('gudnm', 'ASC');
        // }


        // $AllDataCmt = $data_gudang->get();

        // $offset = ($page - 1) * $rows;
        // $data_gudang = $data_gudang->skip($offset)->take($rows)->get();

        // $result = array();
        // $result['total'] = $AllDataCmt->count();
        // $result['page'] = $page;
        // $result['rows'] = $rows;
        // $result['offset'] = $offset;
        // $row = array();
        // $index = $offset + 1;

        // foreach ($data_gudang as $d) {

        //     $row[] = array(
        //         'index' => $index,
        //         'gudpk' => $d->gudpk,
        //         'gudid' => $d->gudid,
        //         'gudnm' => $d->gudnm ?? '-',
        //         'alamat' => $d->alamat ?? '-',
        //     );
        //     $index++;
        // }
        // $result = array_merge($result, array('rows' => $row));
        // return json_encode($result);
    }

    public function get_jang()
    {
        $data = DB::table('jang')->orderBy('jangnm', 'asc')->get()->toArray();
        $data = array_map(function ($item) {
            $item->jangnm = trim($item->jangnm);
            return $item;
        }, $data);

        return json_encode($data);
    }

    public function get_ang()
    {

        $data = DB::table('ang')->orderBy('angnm', 'asc')->get()->toArray();
        $data = array_map(function ($item) {
            $item->angnm = trim($item->angnm);
            return $item;
        }, $data);

        return json_encode($data);
    }

    public function get_fwd()
    {
        // $data = DB::table('fwd')->orderBy('fwdnm', 'asc')->get();
        // // $data = DB::table('fwd')
        // // ->selectRaw("TRIM(fwdnm) as fwdnm")
        // // ->orderBy('fwdnm', 'asc')
        // // ->get();

        // return json_encode($data);

        $data = DB::table('fwd')->orderBy('fwdnm', 'asc')->get()->toArray();
        $data = array_map(function ($item) {
            $item->fwdnm = trim($item->fwdnm); // Menghapus spasi di awal & akhir
            return $item;
        }, $data);

        return json_encode($data);
    }

    public function get_emkl()
    {
        $data = DB::table('emkl')->orderBy('emklnm', 'asc')->get()->toArray();
        $data = array_map(function ($item) {
            $item->emklnm = trim($item->emklnm);
            return $item;
        }, $data);

        return json_encode($data);
    }

    public function get_gudang2(Request $request)
    {
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        $sortlistByName = $request->sortlistByName;

        $data_gudang = DB::table('gud')->select('gudpk', 'gudid', 'gudnm', 'alamat');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(gudid, ''),
                COALESCE(gudnm, '')
            )");
            $data_gudang = $data_gudang->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        if ($sortlistByName == 11) {
            $data_gudang = $data_gudang->orderBy('gudnm', 'ASC');
        } else if ($sortlistByName == 12) {
            $data_gudang = $data_gudang->orderBy('gudnm', 'DESC');
        } else {
            $data_gudang = $data_gudang->orderBy('gudnm', 'ASC');
        }


        $AllDataCmt = $data_gudang->get();

        $offset = ($page - 1) * $rows;
        $data_gudang = $data_gudang->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_gudang as $d) {

            $row[] = array(
                'index' => $index,
                'gudpk' => $d->gudpk,
                'gudid' => $d->gudid,
                'gudnm' => $d->gudnm ?? '-',
                'alamat' => $d->alamat ?? '-',
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function detail_gudang($gudpk)
    {
        $dt_gudang = Gudang::find($gudpk);

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $dt_gudang,
        ]);
    }

    public function store_gudang(Request $request)
    {

        $dt_gudang = new Gudang;
        $dt_gudang->gudid = $request->gudid;
        $dt_gudang->gudnm = $request->gudnm;
        $dt_gudang->alamat = $request->alamat;
        $dt_gudang->save();

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully',
            'data' => $dt_gudang,
        ]);
    }

    public function update_gudang($gudpk, Request $request)
    {
        $dt_gudang = Gudang::find($gudpk);
        $dt_gudang->gudid = $request->gudid;
        $dt_gudang->gudnm = $request->gudnm;
        $dt_gudang->alamat = $request->alamat;
        $dt_gudang->update();

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $dt_gudang,
        ]);
    }

    public function get_ang2(Request $request)
    {

        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        $sortlistByName = $request->sortlistByName;

        $data_ang = DB::table('ang')->select('angpk', 'angid', 'angnm');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(angid, ''),
                COALESCE(angnm, '')
            )");
            $data_ang = $data_ang->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        if ($sortlistByName == 11) {
            $data_ang = $data_ang->orderBy('angnm', 'ASC');
        } else if ($sortlistByName == 12) {
            $data_ang = $data_ang->orderBy('angnm', 'DESC');
        } else {
            $data_ang = $data_ang->orderBy('angnm', 'ASC');
        }


        $AllData = $data_ang->get();

        $offset = ($page - 1) * $rows;
        $data_ang = $data_ang->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllData->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_ang as $d) {

            $row[] = array(
                'index' => $index,
                'angpk' => $d->angpk,
                'angid' => $d->angid,
                'angnm' => $d->angnm,
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function detail_ang($angpk)
    {
        $dt_ang = Ang::find($angpk);

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $dt_ang,
        ]);
    }

    public function store_ang(Request $request)
    {

        $dt_ang = new Ang;
        $dt_ang->angid = $request->angid;
        $dt_ang->angnm = $request->angnm;
        $dt_ang->save();

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully',
            'data' => $dt_ang,
        ]);
    }

    public function update_ang($angpk, Request $request)
    {
        $dt_ang = Ang::find($angpk);
        $dt_ang->angid = $request->angid;
        $dt_ang->angnm = $request->angnm;
        $dt_ang->update();

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $dt_ang,
        ]);
    }

    public function get_jang2(Request $request)
    {

        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        $sortlistByName = $request->sortlistByName;

        $data_jang = DB::table('jang')->select('jangpk', 'jangid', 'jangnm');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(jangid, ''),
                COALESCE(jangnm, '')
            )");
            $data_jang = $data_jang->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        if ($sortlistByName == 11) {
            $data_jang = $data_jang->orderBy('jangnm', 'ASC');
        } else if ($sortlistByName == 12) {
            $data_jang = $data_jang->orderBy('jangnm', 'DESC');
        } else {
            $data_jang = $data_jang->orderBy('jangnm', 'ASC');
        }


        $AllData = $data_jang->get();

        $offset = ($page - 1) * $rows;
        $data_jang = $data_jang->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllData->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_jang as $d) {

            $row[] = array(
                'index' => $index,
                'jangpk' => $d->jangpk,
                'jangid' => $d->jangid,
                'jangnm' => $d->jangnm,
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function detail_jang($jangpk)
    {
        $dt_jang = Jang::find($jangpk);

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $dt_jang,
        ]);
    }

    public function store_jang(Request $request)
    {

        $dt_jang = new Jang;
        $dt_jang->jangid = $request->jangid;
        $dt_jang->jangnm = $request->jangnm;
        $dt_jang->save();

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully',
            'data' => $dt_jang,
        ]);
    }

    public function update_jang($jangpk, Request $request)
    {
        $dt_jang = Jang::find($jangpk);
        $dt_jang->jangid = $request->jangid;
        $dt_jang->jangnm = $request->jangnm;
        $dt_jang->update();

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $dt_jang,
        ]);
    }

    public function get_emkl2(Request $request)
    {
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        $sortlistByName = $request->sortlistByName;

        $data_emkl = DB::table('emkl')->select('emklpk', 'emklid', 'emklnm', 'alamat', 'kontak', 'telepon');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(emklid, ''),
                COALESCE(emklnm, ''),
                COALESCE(alamat, ''),
                COALESCE(kontak, ''),
                COALESCE(telepon, '')

            )");
            $data_emkl = $data_emkl->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        if ($sortlistByName == 11) {
            $data_emkl = $data_emkl->orderBy('emklnm', 'ASC');
        } else if ($sortlistByName == 12) {
            $data_emkl = $data_emkl->orderBy('emklnm', 'DESC');
        } else {
            $data_emkl = $data_emkl->orderBy('emklnm', 'ASC');
        }


        $AllData = $data_emkl->get();

        $offset = ($page - 1) * $rows;
        $data_emkl = $data_emkl->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllData->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_emkl as $d) {

            $row[] = array(
                'index' => $index,
                'emklpk' => $d->emklpk,
                'emklid' => $d->emklid,
                'emklnm' => $d->emklnm,
                'alamat' => $d->alamat ?? '-',
                'kontak' => $d->kontak ?? '-',
                'telepon' => $d->telepon ?? '-',
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function detail_emkl($jangpk)
    {
        $dt_emkl = Emkl::find($jangpk);

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $dt_emkl,
        ]);
    }

    public function store_emkl(Request $request)
    {

        $dt_emkl = new Emkl;
        $dt_emkl->emklid = $request->emklid;
        $dt_emkl->emklnm = $request->emklnm;
        $dt_emkl->alamat = $request->alamat;
        $dt_emkl->kontak = $request->kontak;
        $dt_emkl->telepon = $request->telepon;
        $dt_emkl->save();

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully',
            'data' => $dt_emkl,
        ]);
    }

    public function update_emkl($emklpk, Request $request)
    {
        $dt_emkl = Emkl::find($emklpk);
        $dt_emkl->emklid = $request->emklid;
        $dt_emkl->emklnm = $request->emklnm;
        $dt_emkl->alamat = $request->alamat;
        $dt_emkl->kontak = $request->kontak;
        $dt_emkl->telepon = $request->telepon;
        $dt_emkl->update();

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $dt_emkl,
        ]);
    }


    public function get_fwd2(Request $request)
    {
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        $sortlistByName = $request->sortlistByName;

        $data_fwd = DB::table('fwd')->select('fwdpk', 'fwdid', 'fwdnm', 'kontak');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(fwdid, ''),
                COALESCE(fwdnm, ''),
                COALESCE(kontak, '')
            )");
            $data_fwd = $data_fwd->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        if ($sortlistByName == 11) {
            $data_fwd = $data_fwd->orderBy('fwdnm', 'ASC');
        } else if ($sortlistByName == 12) {
            $data_fwd = $data_fwd->orderBy('fwdnm', 'DESC');
        } else {
            $data_fwd = $data_fwd->orderBy('fwdnm', 'ASC');
        }


        $AllData = $data_fwd->get();

        $offset = ($page - 1) * $rows;
        $data_fwd = $data_fwd->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllData->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_fwd as $d) {

            $row[] = array(
                'index' => $index,
                'fwdpk' => $d->fwdpk,
                'fwdid' => $d->fwdid,
                'fwdnm' => $d->fwdnm ?? '-',
                'kontak' => isset($d->kontak) && trim($d->kontak) !== '' ? $d->kontak : '-',
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function detail_fwd($fwdpk)
    {
        $dt_fwd = Fwd::find($fwdpk);

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $dt_fwd,
        ]);
    }

    public function store_fwd(Request $request)
    {

        $dt_fwd = new Fwd;
        $dt_fwd->fwdid = $request->fwdid;
        $dt_fwd->fwdnm = $request->fwdnm;
        $dt_fwd->kontak = $request->kontak;
        $dt_fwd->save();

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully',
            'data' => $dt_fwd,
        ]);
    }

    public function update_fwd($fwdpk, Request $request)
    {
        $dt_fwd = Fwd::find($fwdpk);
        $dt_fwd->fwdid = $request->fwdid;
        $dt_fwd->fwdnm = $request->fwdnm;
        $dt_fwd->kontak = $request->kontak;
        $dt_fwd->update();

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $dt_fwd,
        ]);
    }

    public function get_buyer(Request $request)
    {
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        $sortlistByName = $request->sortlistByName;

        $data_byr = DB::table('byr')->select('byrpk', 'byrid', 'byrnm', 'negara', 'alamattxt');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(byrid, ''),
                COALESCE(byrnm, ''),
                COALESCE(negara, ''),
                COALESCE(alamattxt, '')
            )");
            $data_byr = $data_byr->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        if ($sortlistByName == 11) {
            $data_byr = $data_byr->orderBy('byrnm', 'ASC');
        } else if ($sortlistByName == 12) {
            $data_byr = $data_byr->orderBy('byrnm', 'DESC');
        } else {
            $data_byr = $data_byr->orderBy('byrnm', 'ASC');
        }


        $AllData = $data_byr->get();

        $offset = ($page - 1) * $rows;
        $data_byr = $data_byr->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllData->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_byr as $d) {

            $row[] = array(
                'index' => $index,
                'byrpk' => $d->byrpk,
                'byrid' => $d->byrid,
                'byrnm' => $d->byrnm ?? '-',
                'alamattxt' => isset($d->alamattxt) && trim($d->alamattxt) !== '' ? $d->alamattxt : '-',
                'negara' => isset($d->negara) && trim($d->negara) !== '' ? $d->negara : '-',
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function store_byr(Request $request)
    {
        $getByr = DB::connection('mysql_sig')
            ->table('byr')
            ->select('byrpk', 'byrid', 'byrnm')
            ->where('byrnm', 'like', "%" . $request->byrnm . "%")
            ->first();


        $dt_byr = new Byr;
        $dt_byr->byrid = $getByr->byrid ?? $request->byrid;
        $dt_byr->byrnm = $getByr->byrnm ?? $request->byrnm;
        $dt_byr->alamattxt = $request->alamattxt;
        $dt_byr->negara = $request->negara;
        $dt_byr->byrpkgis = $getByr->byrpk ?? null;
        $dt_byr->save();


        return response()->json([
            'success' => true,
            'message' => 'Data created successfully',
            'data' => $dt_byr,
        ]);
    }

    public function detail_byr($byrpk)
    {
        $dt_byr = Byr::find($byrpk);

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $dt_byr,
        ]);
    }

    public function update_byr($byrpk, Request $request)
    {
        // Cari data di tabel byr dari database mysql_sig (jika memang diperlukan)
        $getByr = DB::connection('mysql_sig')
            ->table('byr')
            ->select('byrpk', 'byrid', 'byrnm')
            ->where('byrnm', 'like', "%" . $request->byrnm . "%")
            ->first();

        // Cari data yang akan diupdate di database utama
        $dt_byr = Byr::find($byrpk);

        if (!$dt_byr) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
            ], 404);
        }

        // Jika ditemukan di mysql_sig, update field sesuai hasil pencarian
        if ($getByr) {
            $dt_byr->byrid = $getByr->byrid;
            $dt_byr->byrnm = $getByr->byrnm;
            $dt_byr->byrpkgis = $getByr->byrpk ?? null;
        } else {
            $dt_byr->byrid = $request->byrid;
            $dt_byr->byrnm = $request->byrnm;
        }

        // Update data lainnya dari request
        $dt_byr->alamattxt = $request->alamattxt;
        $dt_byr->negara = $request->negara;
        $dt_byr->save(); // Gunakan save() daripada update() pada instance model

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $dt_byr,
        ]);
    }
}
