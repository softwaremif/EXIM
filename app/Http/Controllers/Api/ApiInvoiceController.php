<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\Invsdt;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InvsdtImport;
use Str;

class ApiInvoiceController extends Controller
{
    public function DeleteExcel($invspk)
    {
        $delinvsdt = Invsdt::where('invspk', $invspk)->delete();

        return $delinvsdt;
    }
    public function UploadExcel(Request $request, $invspk)
    {
        $request->validate([
            'uploadfile' => 'required|file|mimes:xls,xlsx|max:10240',
        ]);

        // Cek apakah file ada
        if (!$request->hasFile('uploadfile')) {
            return response()->json(['success' => false, 'message' => 'No file received by server.'], 400);
        }

        // Simpan file
        $files = $request->file('uploadfile');
        $original_name = $files->getClientOriginalName();
        $ext = strtolower($files->getClientOriginalExtension());
        $current_time = Carbon::now()->format('H-i-s');
        $image_full_name = $invspk . '-' . $current_time . '.' . Str::slug(pathinfo($original_name, PATHINFO_FILENAME)) . '.' . $ext;
        $destinationPath = public_path('upload/invsdt/');

        // Pastikan direktori ada
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $files->move($destinationPath, $image_full_name);
        $filePath = $destinationPath . $image_full_name;

        try {
            Excel::import(new InvsdtImport($invspk), $filePath);
        } catch (\Exception $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'File uploaded and imported successfully.'], 200);
    }
}
