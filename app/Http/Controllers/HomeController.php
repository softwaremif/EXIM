<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
//use Illuminate\Support\Collection;
use App\Models\stage;
use App\Models\progres;
use App\Models\group;
use App\Models\Size;
use App\Models\Jadwal;
use App\Models\Msize;
use App\Models\type;
use App\Models\Buyer;
use App\Helpers\MemoHelper;

class HomeController extends Controller
{

    public function index()
    {
        if (!Session::get('userpk')) {
            return redirect('/');
        } else {
            return view('pass');
        }
    }

    public function displaysr()
    {
        $nowd = Carbon::now();
        $ambilbulanIni = $nowd->format('m');
        $ambiltahunIni = $nowd->format('Y');

        // Untuk bulan sebelumnya
        $previousMonth = $nowd->copy()->subMonth();
        $ambilbulanLalu = $previousMonth->format('m');

        // Mendapatkan memo untuk bulan ini    
        $results = MemoHelper::getMemo($ambilbulanIni, $ambilbulanLalu, $ambiltahunIni);
        $semua_all = MemoHelper::getAllMemo($ambilbulanIni, $ambilbulanLalu, $ambiltahunIni);
        $notip = MemoHelper::getAllNotip();

        return view('display.list-sr.display_sr', compact('results', 'semua_all', 'notip'));
    }

    public function getDisplaySR(Request $request)
    {
        $tahun = date("Y");
        $today = date("Y-m-d");
        $reqAll = $request->all();
        $segments = $request->segments();

        $searchByInput = $request->searchByInput;
        $sortlistByDate = $request->sortlistByDate;
        $filterByStages = $request->filterByStages;
        $filterByProgress = $request->filterByProgress;
        $filterByBrands = $request->filterByBrands;
        $filterByYear = $request->filterByYear;
        $complete = $request->complete;
        $isActiveOrOnhold = $segments[1] ?? $request->isActiveOrOnhold;

        if (!empty($filterByYear)) $tahun = $filterByYear;

        $page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->leftJoin('material', 'material.statuspk', '=', 'status.statuspk')
            ->select(
                'jadwal.stspause',
                'jadwal.stsout',
                'status.remarkmd',
                'status.artwork',
                'status.cansts',
                'status.cekdate',
                'status.date',
                'status.dateupdt',
                'status.description',
                'status.foto',
                'status.grouppk',
                'status.opsi',
                'status.pattern',
                'status.pattern2',
                'status.paused',
                'status.progrespk',
                'status.reject',
                'status.sam',
                'status.samplenm',
                'status.season',
                'status.srpk',
                'status.stagepk',
                'stage.stagenm',
                'status.statuspk',
                'status.stsupdt2',
                'status.style',
                'status.techpack',
                'status.washing',
                'status.yy',
                'progres.progresnm',
                'group.groupnm',
                'jadwal.jadwalpk',
                'jadwal.receiptdate',
                'jadwal.finishsample',
                'jadwal.startsample',
                'jadwal.cutdate',
                'jadwal.matdate',
                'jadwal.trimdate',
                'jadwal.shipdate',
                'jadwal.pausedate',
                'jadwal.resumedate',
                'jadwal.realdate',
                'jadwal.assigndate',
                'jadwal.updtjadwal',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srno',
                'material.matpk',
                DB::raw('DATE_FORMAT(jadwal.finishsample2, "%d %b") as finishsample2'),
                DB::raw('DATE_FORMAT(jadwal.startsample2,"%d %b") as startsample2'),
                DB::raw('DATE_FORMAT(jadwal.cutdate2,"%d %b") as cutdate2'),
                DB::raw('DATE_FORMAT(jadwal.semblesdate1,"%d %b %Y") as semblesdate1'),
                DB::raw('DATE_FORMAT(jadwal.swashdate1,"%d %b %Y") as swashdate1'),
                DB::raw('SUM(size.qty) as qty')
            )
            ->where('status.buyerpk2', '>', 0)
            ->where('status.userpk', '>', 0)
            ->where('status.cansts', '=', 0)
            // ->where('status.aktif', '=', 0)
            ->whereYEAR('status.date', $tahun)
            ->groupBy('statuspk');

        if (!empty($searchByInput)) {
            $dtstyle = $dtstyle->where(DB::raw("CONCAT(request.srno,'',status.style,'',buyer.buyernm,'',status.season)"), 'like', '%' . $searchByInput . '%');
        }

        if (!empty($filterByStages)) {
            $dtstyle = $dtstyle->whereIn('status.stagepk', $filterByStages);
        }

        if (!empty($filterByProgress)) {
            $dtstyle = $dtstyle->whereIn('progres.progrespk', $filterByProgress);
        }

        if (!empty($filterByBrands)) {
            $dtstyle = $dtstyle->whereIn('buyer.buyerpk', $filterByBrands);
        }


        if ($sortlistByDate == 'oldestEntry') {
            if ($isActiveOrOnhold == 'active') {
                $dtstyle = $dtstyle->orderBy('jadwal.assigndate', 'ASC');
            } else {
                $dtstyle = $dtstyle->orderBy('status.date', 'ASC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC');
            }
        } elseif ($sortlistByDate == 'recentlyUpdated') {
            if ($isActiveOrOnhold == 'active') {
                $dtstyle = $dtstyle->orderBy('jadwal.updtjadwal', 'DESC')->orderBy('request.srno', 'DESC');
            } else {
                $dtstyle = $dtstyle->orderBy('status.dateupdt', 'DESC')->orderBy('request.srno', 'DESC');
            }
        } elseif ($sortlistByDate == 'dueDate') {
            $dtstyle = $dtstyle->orderBy('jadwal.shipdate', 'ASC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC');
        } else {
            if ($isActiveOrOnhold == 'active') {
                $dtstyle = $dtstyle->orderBy('jadwal.assigndate', 'DESC');
            } else {
                $dtstyle = $dtstyle->orderBy('status.date', 'DESC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC')->orderBy('jadwal.assigndate', 'DESC');
            }
        }

        $offset = ($page - 1) * $rows;

        $dtStyleWithoutCompleteMaterial = $dtstyle->get();

        $statusPKNotAllMaterialChecked = array();

        foreach ($dtStyleWithoutCompleteMaterial as $d) {
            $dtMatNotChecked = DB::table('material')
                ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
                ->where('material.statuspk', '=', $d->statuspk)
                ->count();

            $dtMatChecked = DB::table('material')
                ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
                ->where('material.statuspk', '=', $d->statuspk)
                ->where('material.cek', '=', 1)
                ->count();

            if ($dtMatNotChecked != $dtMatChecked) array_push($statusPKNotAllMaterialChecked, $d->statuspk);
        }

        if (count($statusPKNotAllMaterialChecked) > 0 && $complete == 1) {
            $dtStyleWithCompleteMaterial = $dtstyle->whereNotIn('status.statuspk', $statusPKNotAllMaterialChecked)->get();
            $dtstyleAll = $dtStyleWithCompleteMaterial;
        } else {
            $dtstyleAll = $dtStyleWithoutCompleteMaterial;
        }

        $dtstyle = $dtstyle->skip($offset)->take($rows)->get();

        $result = array();

        $dtstyleAllCount = $dtstyleAll->count();

        $result['total'] = $dtstyleAllCount;
        $result['reqAll'] = $reqAll;

        $row = array();

        foreach ($dtstyle as $d) {
            $qty = 0;
            $stagenm = $d->stagenm;
            if ($d->stagepk == 0) {
                $stagenm = $d->stagenm;
                $stagenm = $d->stagenm . "<br>";
            } else if ($d->stagepk == 8) {
                $stagenm = $d->samplenm;
                $stagenm = htmlspecialchars($d->samplenm, ENT_QUOTES, 'UTF-8') . "<br>";
            }

            $dtsize = DB::table('size')
                ->leftJoin('status', 'status.statuspk', '=', 'size.statuspk')
                ->select(DB::raw('SUM(size.qty) as qty'))
                ->where('size.statuspk', $d->statuspk)
                ->first();

            if (!empty($dtsize)) $qty = $dtsize->qty;

            $dtmatMax = DB::table('material')
                ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
                ->where('material.statuspk', '=', $d->statuspk)
                ->count();

            $dtmatMin = DB::table('material')
                ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
                ->where('material.statuspk', '=', $d->statuspk)
                ->where('material.cek', '=', 1)
                ->count();

            $dateInput = DB::table('material')
                ->select(DB::raw('DATE_FORMAT(material.cekdate, "%d %b") as MatCekDate'))
                ->where('material.statuspk', '=', $d->statuspk)
                ->orderBy('material.cekdate', 'DESC')
                ->get();

            $MatCekDate = null;

            if (!$dateInput->isEmpty()) {
                foreach ($dateInput->take(1) as $tglInput) {
                    $MatCekDate = $tglInput->MatCekDate;
                }
            }

            //File Tech Pack
            $FileTechPack = DB::table('filesr')
                ->select('filesrpk', 'statuspk', 'jnsfile', 'filenm as NamaFileTechPack')
                ->where('filesr.statuspk', '=', $d->statuspk)
                ->where('filesr.jnsfile', 'like', "%" . 'TECHPACK' . "%")
                ->where('filesr.filenm', '!=', '')
                ->orderBy('filesr.filesrpk', 'DESC')
                ->get();

            $NamaFileTechPack = 'N/A';

            if (!$FileTechPack->isEmpty()) {
                foreach ($FileTechPack->take(1) as $FileTechPackX) {
                    // $NamaFileTechPack = $FileTechPackX->NamaFileTechPack;
                    $fileUrl = asset('public/upload/tech/' . $FileTechPackX->NamaFileTechPack);
                    // $NamaFileTechPack = '<a href="' . $fileUrl . '" target="_blank" style="color:#359DD9;">' . $FileTechPackX->NamaFileTechPack . '</a>';
                    $NamaFileTechPack = '<a href="' . $fileUrl . '" onclick="window.open(this.href, \'_blank\'); return false;" style="color:#359DD9;">' . $FileTechPackX->NamaFileTechPack . '</a>';
                }
            }

            //File Buyer Pattern
            $FileBuyer = DB::table('filesr')
                ->select('filesrpk', 'statuspk', 'jnsfile', 'filenm as NamaFileBuyer')
                ->where('filesr.statuspk', '=', $d->statuspk)
                ->where('filesr.jnsfile', 'like', "%" . 'FILEBLOCK' . "%")
                ->orderBy('filesr.filesrpk', 'DESC')
                ->where('filesr.filenm', '!=', '')
                ->get();

            $NamaFileBuyer = 'N/A';

            if (!$FileBuyer->isEmpty()) {
                foreach ($FileBuyer->take(1) as $FileBuyerX) {
                    // $NamaFileBuyer = $FileBuyerX->NamaFileBuyer;
                    $fileUrl = asset('public/upload/pattern1/' . $FileBuyerX->NamaFileBuyer);
                    // $NamaFileBuyer = '<a href="' . $fileUrl . '" target="_blank" style="color:#359DD9;">' . $FileBuyerX->NamaFileBuyer . '</a>';
                    $NamaFileBuyer = '<a href="' . $fileUrl . '" onclick="window.open(this.href, \'_blank\'); return false;" style="color:#359DD9;">' . $FileBuyerX->NamaFileBuyer . '</a>';
                }
            }

            //File Sample Pattern
            $FilePattern = DB::table('filesr')
                ->select('filesrpk', 'statuspk', 'jnsfile', 'filenm as NamaFilePattern')
                ->where('filesr.statuspk', '=', $d->statuspk)
                ->where('filesr.jnsfile', 'like', "%" . 'FILEPATTER' . "%")
                ->where('filesr.filenm', '!=', '')
                ->orderBy('filesr.filesrpk', 'DESC')
                ->get();

            $NamaFilePattern = 'N/A';

            if (!$FilePattern->isEmpty()) {
                foreach ($FilePattern->take(1) as $FilePatternX) {
                    // $NamaFilePattern = $FilePatternX->NamaFilePattern;
                    $fileUrl = asset('public/upload/pattern2/' . $FilePatternX->NamaFilePattern);
                    // $NamaFilePattern = '<a href="' . $fileUrl . '" target="_blank" style="color:#359DD9;">' . $FilePatternX->NamaFilePattern . '</a>';
                    $NamaFilePattern = '<a href="' . $fileUrl . '" onclick="window.open(this.href, \'_blank\'); return false;" style="color:#359DD9;">' . $FilePatternX->NamaFilePattern . '</a>';
                }
            }


            if ($MatCekDate == null || $MatCekDate == "" || $MatCekDate == "0000-00-00") {
                $MatCekDate = "--";
            } else {
                $MatCekDate = date("d M Y", strtotime($MatCekDate));
            }

            if ($d->date == "" || $d->date == "0000-00-00") {
                $date = "--";
            } else {
                $date = date("d M Y", strtotime($d->date));
            }

            if ($d->trimdate == "" || $d->trimdate == "0000-00-00") {
                $trimdate = "--";
            } else {
                $trimdate = date("d M Y", strtotime($d->trimdate));
            }

            if ($d->cutdate == "" || $d->cutdate == "0000-00-00") {
                $cutdate = "--";
            } else {
                $cutdate = date("d M Y", strtotime($d->cutdate));
            }

            if ($d->startsample == "" || $d->startsample == "0000-00-00") {
                $sewdate = "--";
            } else {
                $sewdate = date("d M Y", strtotime($d->startsample));
            }

            if ($d->finishsample == "" || $d->finishsample == "0000-00-00") {
                $findate = "--";
            } else {
                $findate = date("d M Y", strtotime($d->finishsample));
            }

            if ($d->receiptdate == "" || $d->receiptdate == "0000-00-00") {
                $recdate = "--";
            } else {
                $recdate = date("d M Y", strtotime($d->receiptdate));
            }

            if ($d->shipdate == "" || $d->shipdate == "0000-00-00") {
                $shipdate = "--";
            } else {
                $shipdate = date("d M Y", strtotime($d->shipdate));
            }

            if ($d->pausedate == "" || $d->pausedate == "0000-00-00") {
                $pausedate = "--";
            } else {
                $pausedate = date("d M Y", strtotime($d->pausedate));
            }

            if ($d->realdate == "" || $d->realdate == "0000-00-00") {
                $realdate = "--";
            } else {
                $realdate = date("d M Y", strtotime($d->realdate));
            }

            if ($d->dateupdt == "" || $d->dateupdt == "0000-00-00") {
                $dateupdt = $date;
            } else {
                $dateupdt = date("d M Y", strtotime($d->dateupdt));
            }

            if ($d->shipdate == "" || $d->shipdate == "0000-00-00") {
                $duedate = "--";
            } else {
                $duedate = date("d/m", strtotime($d->shipdate));
            }

            if ($d->assigndate == "" || $d->assigndate == "0000-00-00") {
                $assigndate = $d->assigndate;
            } else {
                $assigndate = date("d M Y", strtotime($d->assigndate));
            }


            if ($sortlistByDate == 'recentlyUpdated') {
                if ($isActiveOrOnhold == 'active') {
                    $dateGroupField = $dateupdt;
                } else {
                    $dateGroupField = $d->updtjadwal;
                }
            } elseif ($sortlistByDate == 'dueDate') {
                $dateGroupField = $shipdate;
            } else {
                $dateGroupField = $assigndate;
            }

            $dateprog = "--";
            if ($d->progrespk == 1) {
                $dateprog = $date;
            } elseif ($d->progrespk == 2) {
                $dateprog = $trimdate;
            } elseif ($d->progrespk == 3) {
                $dateprog = $cutdate;
            } elseif ($d->progrespk == 4) {
                $dateprog = $sewdate;
            } elseif ($d->progrespk == 5) {
                $dateprog = $findate;
            } elseif ($d->progrespk == 6) {
                $dateprog = $recdate;
            } elseif ($d->progrespk == 7) {
                $dateprog = $pausedate;
            } elseif ($d->progrespk == 8) {
                $dateprog = $realdate;
            } elseif ($d->progrespk == 9) {
                $dateprog = $d->semblesdate1;
            } elseif ($d->progrespk == 10) {
                $dateprog = $d->swashdate1;
            }

            $NamaProgres = "";
            if ($d->progrespk == 1 || $d->progrespk == 2) {
                $NamaProgres = 'On Hold';
            } elseif ($d->progrespk == 3) {
                $NamaProgres = 'Cutting';
            } elseif ($d->progrespk == 4) {
                $NamaProgres = 'Sewing';
            } elseif ($d->progrespk == 5) {
                $NamaProgres = 'Finishing';
            } elseif ($d->progrespk == 6) {
                $NamaProgres = 'Handover';
            } elseif ($d->progrespk == 7 &&  $d->stspause == 1 || $d->progrespk == 7 &&  $d->stspause == 2) {
                $NamaProgres = 'On Hold';
            } elseif ($d->progrespk == 7 &&  $d->stspause == 3) {
                $NamaProgres = 'Cutting';
            } elseif ($d->progrespk == 7 &&  $d->stspause == 4) {
                $NamaProgres = 'Sewing';
            } elseif ($d->progrespk == 8 &&  $d->stsout == 1 || $d->progrespk == 8 &&  $d->stsout == 2) {
                $NamaProgres = 'On Hold';
            } elseif ($d->progrespk == 8 &&  $d->stsout == 3) {
                $NamaProgres = 'Cutting';
            } elseif ($d->progrespk == 8 &&  $d->stsout == 4) {
                $NamaProgres = 'Sewing';
            } elseif ($d->progrespk == 9 || $d->progrespk == 7 &&  $d->stspause == 9) {
                $NamaProgres = 'Embellishment';
            } elseif ($d->progrespk == 10 || $d->progrespk == 7 &&  $d->stspause == 10) {
                $NamaProgres = 'Washing';
            }


            //Start Stage History Column
            if ($d->pattern2 <> "") {
                $pattern = "<img src='public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $pattern = "<img src='public/css/images/abu.png' width='15px' height='15px'>";
            }

            if ($d->yy <> "") {
                $yy = "<img src='public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $yy = "<img src='public/css/images/abu.png' width='15px' height='15px'>";
            }

            if ($d->sam <> "") {
                $sam = "<img src='public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $sam = "<img src='public/css/images/abu.png' width='15px' height='15px'>";
            }

            if ($d->trimdate <> "") {
                $trimdate = "<img src='public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $trimdate = "<img src='public/css/images/abu.png' width='15px' height='15px'>";
            }
            //End Stage History Column

            $gambar = $d->foto;
            $folderPath = public_path('foto');
            $arrow_right = "<img src='public/css/images/Arrow2.png' width='15px' height='15px'>";

            $hasil_gambar = "<img src='public/css/no-img.png' width='65px' height='65px'>";
            if ($gambar && file_exists($folderPath . '/' . $gambar)) {
                $hasil_gambar = "<img src='public/foto/$gambar' width='65px' height='65px'>";
            }

            if ($d->reject > 0) {
                $foto = "<div style='font-weight: 700; font-size: 14px; line-height:18px; color:#FF6B00; margin-top: -13%;'>R1</div>
					     <div style='margin-top:0%'>$hasil_gambar</div>";
            } else if ($d->cansts > 0) {
                $foto = "<span style='font-weight: 700;font-size: 18px;line-height: 24px;color:rgba(0, 0, 0, 0.38);'>Cancel</span>$hasil_gambar";
            } else {
                $foto = "$hasil_gambar";
            }

            $sr_text = "$d->srno";
            $margin_top = '-15%';
            if (!empty($d->opsi)) $sr_text = "$d->srno [$d->opsi]";
            if ($d->reject > 0) $margin_top = '-13%';

            $padding_top = '0px';
            if ($isActiveOrOnhold == 'active') $padding_top = '15px';

            $styledt = "
			<div style='width:60px;height:60px;padding-top:$padding_top'>$foto</div>
			<div style='margin-left:25%;margin-top:$margin_top;font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
			SR $sr_text<br>
			<span style='color:#000000;cursor:default;font-weight: 400;font-size: 14px;line-height: 18px;'>$d->style - Qty: $qty - Due: $duedate</span>
			</div>
			<div style='margin-left:25%;font-family: Arial;font-style: normal;font-weight: 500;font-size: 11px;line-height: 14px;color:#00000061'> 
			<a href='javascript:void(0)' style='text-decoration:none; color:#00000061';cursor:default;' title='$d->buyernm, $d->season - $d->description' class='easyui-tooltip'>$d->buyernm, $d->season - $d->description</a> 
			</div><br>
			";

            if ($d->cansts > 0) {
                $stage = "<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
				<a href='javascript:void(0)' style='color:rgba(0, 0, 0, 0.38);text-decoration:none;cursor:default;' title='$stagenm' class='easyui-tooltip'>$stagenm</a>
				<span style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $date </span></div>
				<div style='color:rgba(0, 0, 0, 0.38);font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 12px; line-height: 16px;'>
				Patern $pattern	YY $yy SMV $sam	TC $trimdate</div>";

                $datemat = "<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dtmatMin/$dtmatMax </div>
				<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $MatCekDate </div>";

                $progres = "
				<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'>$NamaProgres </div>
				<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $dateprog </div>";

                $plandate = "<div style='color:rgba(0, 0, 0, 0.38);'>$shipdate</div>";
                $act = "";
                // $act = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 20px;line-height: 26px;color:#000000'>&#10247;</div>";

            } else {
                $stage = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
				<a href='javascript:void(0)' style='text-decoration:none;color:#000000;cursor:default;' title='$stagenm' class='easyui-tooltip'>$stagenm</a>
				<span style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color:rgba(0, 0, 0, 0.6);'> $date </span></div>
				<div style='font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 12px; line-height: 16px;'>
				Patern $pattern	YY $yy SMV $sam TC $trimdate</div>";

                $datemat = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dtmatMin/$dtmatMax </div>
				<div style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $MatCekDate </div>";

                $progres = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'>$NamaProgres </div>
				<div style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $dateprog </div>";

                $plandate = "<div style=''>$shipdate</div>";
                // $act = "";
                // $act = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 20px;line-height: 26px;color:#000000'>&#10247;</div>";
                $act = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 20px;line-height: 26px;color:#000000''>
                <a href='detail/development/$d->statuspk/$d->srpk/$d->stagepk' style='text-decoration:none; font-family: Arial;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;color:#000000''> $arrow_right </a>
                </div>";
            }

            $btnProgress = '';
            if ($d->cansts == 1) {
                $btnProgress = '<button style="padding: 2px 4px;text-align:center; border-radius:5px; border:0px solid #000000; font-weight:300; font-size:14px; color:#FFFFFF; background-color:#EF3B42;">Cancelled</button>';
            } else {
                if ($d->progrespk == 1) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:left; border-radius:4px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#BDBDBD;">On hold</button>';
                } elseif ($d->progrespk == 2) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:left; border-radius:4px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#BDBDBD;">On hold</button>';
                } elseif ($d->progrespk == 3) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                } elseif ($d->progrespk == 4) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                } elseif ($d->progrespk == 5) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                } elseif ($d->progrespk == 6) {
                    $btnProgress = '<button style="width:72px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#19B715;">Complete</button>';
                } elseif ($d->progrespk == 7) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#FF6B00;">Pause</button>';
                } elseif ($d->progrespk == 8) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#FF6B00;">Transfer Out</button>';
                } elseif ($d->progrespk == 9) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                } elseif ($d->progrespk == 10) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                }
            }


            if ($dateGroupField == $today) {
                $hari = "Today";
            } else {
                $hari = $dateGroupField;
            }

            $today2 = Carbon::parse($today)->format('d M 20y');
            if ($d->date == $today2) {
                $dateCreate = "Today";
            } else {
                $dateCreate = Carbon::parse($d->date)->format('d M 20y');
            }

            $row[] = array(
                'statuspk' => $d->statuspk,
                'qty' => $qty,
                'grouppk' => $d->grouppk,
                'buyerpk' => $d->buyerpk,
                'srpk' => $d->srpk,
                'stagepk' => $d->stagepk,
                'jadwalpk' => $d->jadwalpk,
                'reject' => $d->reject,
                'cansts' => $d->cansts,
                'groupnm' => $d->groupnm,
                'foto' => $foto,
                'style' => $styledt,
                'dstyle' => $d->style,
                'stage' => $stage,
                'datemat' => $datemat,
                'progres' => $progres,
                'progrespk' => $d->progrespk,
                'pausedate' => $d->pausedate,
                'resumedate' => $d->resumedate,
                'plandate' => $plandate,
                'techpack' => $d->techpack,
                'pattern2' => $d->pattern2,
                'trimdate' => $d->trimdate,
                'cutdate' => $d->cutdate,
                'paused' => $d->paused,
                'material' => $d->matpk,
                'status' => $btnProgress,
                'stsupdt' => $d->stsupdt2,
                'hari' => $hari,
                'techpackfile' => $NamaFileTechPack,
                'buyerpattern' => $NamaFileBuyer,
                'samplepattern' => $NamaFilePattern,
                'dateCreate' => $dateCreate,
                'act' => $act,
            );
        }

        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    function detailSRMobile($statuspk, $srpk, $stagepk)
    {
        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('qa', 'qa.statuspk', '=', 'status.statuspk')
            ->leftJoin('dsmv', 'dsmv.statuspk', '=', 'status.statuspk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->select(
                'status.ketreject',
                'status.description',
                'stage.stagenm',
                'status.progrespk',
                'progres.progresnm',
                'status.statuspk',
                'jadwal.jadwalpk',
                'status.foto',
                'status.foto2',
                'status.foto3',
                'status.artwork',
                'status.washing',
                'status.topstitch',
                'qa.ctt',
                'status.elastic',
                'status.bobin',
                'status.overlock',
                'status.mobtape',
                'status.pattern2',
                'jadwal.patterndate',
                'jadwal.trimdate2',
                'jadwal.patterndate2',
                'jadwal.emblesdate2',
                'jadwal.stichdate',
                'jadwal.washdate2',
                'jadwal.cutdate2',
                'jadwal.startsample2',
                'jadwal.finishsample2',
                'jadwal.receiptdate2',
                'status.keterangan',
                'status.cansts',
                'status.construction',
                'dsmv.totsmv',
                'dsmv.finishing',
                'status.ketwash',
                DB::raw('SUM(dsmv.machine+dsmv.manual+dsmv.finishing) as totsec'),
                DB::raw('DATE_FORMAT(status.date, "%d %M %Y") as date'),
                DB::raw('DATE_FORMAT(status.cekdate, "%d %M %Y") as cekdate'),
                DB::raw('DATE_FORMAT(jadwal.receiptdate, "%d %M %Y") as receiptdate'),
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %M %Y") as finishsample'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %M %Y") as startsample'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %M %Y") as cutdate'),
                DB::raw('DATE_FORMAT(jadwal.matdate,"%d %M %Y") as matdate'),
                DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %M %Y") as shipdate'),
                DB::raw('DATE_FORMAT(jadwal.pausedate, "%d %M %Y") as pausedate'),
                DB::raw('DATE_FORMAT(jadwal.realdate, "%d %M %Y") as realdate'),
                DB::raw('SUM(size.qty) as qty'),
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.foto',
                'status.samplenm',
                'status.style',
                'status.season',
                'status.statuspk',
                'status.instruksi',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srpk',
                'request.srno',
                'status.srpk',
                'status.samplenm',
                'status.stagepk',
                'status.opsi',
                'status.userpk',
                'status.paused',
                'status.progrespk',
                'status.reallocate',
                'jadwal.stsout',
                'jadwal.stspause',
                'jadwal.resumedate',
                'jadwal.progsew'
            )
            ->where('status.statuspk', '=', $statuspk)
            ->first();

        $data_uri4 = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('qa', 'qa.statuspk', '=', 'status.statuspk')
            ->leftJoin('dsmv', 'dsmv.statuspk', '=', 'status.statuspk')
            ->select(
                'status.description',
                'stage.stagenm',
                'status.progrespk',
                'progres.progresnm',
                'status.statuspk',
                'jadwal.jadwalpk',
                'jadwal.trimdate',
                'status.foto',
                'status.foto2',
                'status.foto3',
                'status.artwork',
                'status.washing',
                'status.topstitch',
                'qa.ctt',
                'status.elastic',
                'status.bobin',
                'status.overlock',
                'status.mobtape',
                'status.pattern2',
                'jadwal.patterndate',
                'jadwal.cutdate2',
                'jadwal.startsample2',
                'jadwal.finishsample2',
                'jadwal.receiptdate2',
                'status.keterangan',
                'status.cansts',
                'status.construction',
                'dsmv.totsmv',
                'dsmv.finishing',
                'status.ketwash',
                DB::raw('SUM(dsmv.machine+dsmv.manual+dsmv.finishing) as totsec'),
                DB::raw('DATE_FORMAT(status.date, "%d %M %Y") as date'),
                DB::raw('DATE_FORMAT(status.cekdate, "%d %M %Y") as cekdate'),
                DB::raw('DATE_FORMAT(jadwal.receiptdate, "%d %M %Y") as receiptdate'),
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %M %Y") as finishsample'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %M %Y") as startsample'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %M %Y") as cutdate'),
                DB::raw('DATE_FORMAT(jadwal.matdate,"%d %M %Y") as matdate'),
                DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %M %Y") as shipdate'),
                DB::raw('DATE_FORMAT(jadwal.pausedate, "%d %M %Y") as pausedate'),
                DB::raw('DATE_FORMAT(jadwal.realdate, "%d %M %Y") as realdate'),
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.foto',
                'status.samplenm',
                'status.style',
                'status.season',
                'status.statuspk',
                'status.instruksi',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srpk',
                'request.srno',
                'status.srpk',
                'status.samplenm',
                'status.stagepk',
                'status.opsi',
                'status.userpk',
                'status.paused',
                'status.progrespk',
                'status.reallocate'
            )
            ->where('status.statuspk', '=', $statuspk)
            ->first();

        $material = DB::table('material')
            ->where('material.statuspk', '=', $statuspk)
            ->count();

        $checked = DB::table('material')
            ->where('material.statuspk', '=', $statuspk)
            ->where('material.cek', '=', 1)
            ->count();

        $data_size = Size::where('statuspk', '=', $statuspk)->get();

        $for_button = DB::table('status')
            ->leftJoin('jadwal', 'status.statuspk', '=', 'jadwal.statuspk')
            ->select('status.statuspk', 'status.progrespk', 'status.washing', 'jadwal.progcut', 'jadwal.progemb', 'jadwal.progsew', 'jadwal.progwash')
            ->where('status.statuspk', '=', $statuspk)->first();

        $ii = (NULL);

        $kelnmall = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.matnm',
                'material.kelnm',
            )
            ->where('material.kelnm', '=', $ii)
            ->where('material.statuspk', '=', $statuspk)
            ->orderBy('material.matnm', 'ASC')
            ->count();

        $fabric = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.matnm',
                'material.kelnm',
            )
            ->where('material.kelnm', 'LIKE', 'FABRIC')
            ->where('material.statuspk', '=', $statuspk)
            ->orderBy('material.matnm', 'ASC')
            ->count();


        $zipper = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.matnm',
                'material.kelnm',
            )
            ->where('material.kelnm', 'LIKE', 'ZIPPER')
            ->where('material.statuspk', '=', $statuspk)
            ->orderBy('material.matnm', 'ASC')
            ->count();


        $kelnmNew = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.matnm',
                'material.kelnm',
            )
            ->where('material.statuspk', '=', $statuspk)
            ->where('material.kelnm', '<>', "FABRIC")
            ->where('material.kelnm', '<>', "ZIPPER")
            ->orderBy('material.matnm', 'ASC')
            ->count();

        $data_jadwal = Jadwal::where('statuspk', '=', $statuspk)->first();

        $keterangan_stage = DB::table('status')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select('status.statuspk', 'stage.stagepk', 'stage.stagenm', 'status.keterangan')
            ->where('status.stagepk', '<>', '')
            ->where('status.srpk', '=', $dtstyle->srpk)
            ->orderBy('status.statuspk', 'asc')
            ->groupBy('statuspk')->get();


        $comment = DB::table('qa')
            ->leftJoin('user', 'user.userpk', '=', 'qa.userpk')
            ->leftJoin('fu', 'fu.fupk', '=', 'user.fupk')
            ->where('statuspk', '=', $statuspk)
            ->orderBy('qa.qapk', 'desc')
            ->get();

        $comments = DB::table('qa')
            ->select('qa.qapk', 'qa.reject', 'qa.ctt', 'reason.reasonnm', 'qa.filectt')
            ->leftJoin('qadt', 'qa.qapk', '=', 'qadt.qapk')
            ->leftJoin('reason', 'qadt.reasonpk', '=', 'reason.reasonpk')
            ->where('qa.statuspk', '=', $statuspk)
            ->orderBy('qa.qapk', 'desc')
            ->get();

        $keterangan = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select(
                'status.statuspk',
                'stage.stagenm',
                'status.stagepk',
                'status.samplenm',
                'status.keterangan',
                'status.buyerpk2',
                'request.srpk',
                'request.srno',
                'status.srpk',
                'status.opsi'
            )
            ->where('status.srpk', '=', $dtstyle->srpk)
            ->where('status.opsi', '=', $dtstyle->opsi)
            ->orderBy('status.statuspk', 'desc')
            ->groupBy('statuspk')->get();

        $gambar = $dtstyle->foto;
        $gambar2 = $dtstyle->foto2;
        $gambar3 = $dtstyle->foto3;
        $folderPath = public_path('foto');

        $pause  = DB::table('status')
            ->select('paused')
            ->where('statuspk', '=', $statuspk)
            ->get();

        $reallocate  = DB::table('status')
            ->select('reallocate')
            ->where('statuspk', '=', $statuspk)
            ->get();

        $dt_stage = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.stagepk', '>', '0')
            ->where('status.srpk', '=', $dtstyle->srpk)
            ->where('status.opsi', '=', $dtstyle->opsi)
            ->orderBy('status.statuspk', 'desc')
            ->get();

        $Loop_Opsi = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.stagepk', '>', '0')
            ->where('status.srpk', '=', $dtstyle->srpk)
            ->where('status.aktif', '=', 0)
            ->orderBy('status.opsi', 'asc')
            ->get();

        $countproses = DB::table('detproses')
            ->where('statuspk', '=', $statuspk)
            ->orderBy('timeproses', 'DESC')
            ->count();

        $critical = DB::table('detproses')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->orderBy('timeproses', 'DESC')
            ->limit(5)
            ->get();

        $dmsv2 = DB::table('dsmv')
            ->select('totsmv')
            ->where('statuspk', '=', $statuspk)
            ->first();

        // MD
        $dt_stage = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select('status.statuspk', 'stage.stagepk', 'stage.stagenm', 'status.srpk', 'status.samplenm')
            ->where('status.srpk', '=', $srpk)
            ->groupBy('stage.stagepk')
            ->orderBy('status.stagepk', 'DESC')
            ->get();

        $dt_stage2 = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select('stage.stagepk')
            ->where('status.statuspk', '=', $statuspk)
            ->orderBy('status.statuspk', 'DESC')
            ->first();

        if ($dt_stage2) {
            $dt_stage2_stagepk = $dt_stage2->stagepk;
        } else {
            $dt_stage2_stagepk = 0;
        }

        $dt_opsi = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select('status.statuspk', 'status.opsi', 'status.srpk', 'status.stagepk')
            ->where('status.stagepk', '=', $dt_stage2_stagepk)
            ->where('status.srpk', '=', $srpk)
            ->orderBy('status.opsi', 'ASC')
            ->get();

        $count_file_techpack = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%TECHPACK%')
            ->count();

        $file_multi_techpack = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%TECHPACK%')
            ->select(
                'filesrpk',
                'statuspk',
                'jnsfile',
                'filenm',
                DB::raw('DATE_FORMAT(dateupload, "%d/%m/%y, %H:%i") as dateupload')
            )
            ->get();

        $file_multi_block = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%FILEBLOCK%')
            ->select(
                'filesrpk',
                'statuspk',
                'jnsfile',
                'filenm',
                DB::raw('DATE_FORMAT(dateupload, "%d/%m/%y, %H:%i") as dateupload')
            )
            ->get();

        $count_file_block = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%FILEBLOCK%')
            ->count();


        $file_multi_sample = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%FILEPATTER%')
            ->select(
                'filesrpk',
                'statuspk',
                'jnsfile',
                'filenm',
                DB::raw('DATE_FORMAT(dateupload, "%d/%m/%y, %H:%i") as dateupload')
            )
            ->get();

        $count_file_sample = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%FILEPATTER%')
            ->count();

        $count_character = DB::table('status')->select('construction')->where('statuspk', '=', $statuspk)->get();
        $total_characters = 0; // Inisialisasi variabel untuk menyimpan total karakter

        if ($count_character === 0) {
            echo "Tidak ada data yang ditemukan.";
        } else {
            $total_characters = DB::table('status')->select('construction')->where('statuspk', '=', $statuspk)->sum(DB::raw('LENGTH(construction)'));
        }

        $count_pass = DB::table('qa')
            // ->leftJoin('user', 'user.userpk', '=', 'qa.userpk')
            // ->leftJoin('fu', 'fu.fupk', '=', 'user.fupk')
            ->where('statuspk', '=', $statuspk)
            ->whereNotNull('reject',)
            ->count();

        return view('display.list-sr.PageDetailMobile', compact(
            'count_pass',
            'count_file_techpack',
            'file_multi_techpack',
            'count_file_block',
            'file_multi_block',
            'count_file_sample',
            'file_multi_sample',
            'total_characters',
            'dt_opsi',
            'dmsv2',
            'critical',
            'countproses',
            'Loop_Opsi',
            'data_uri4',
            'dt_stage',
            'keterangan_stage',
            'keterangan',
            'comment',
            'data_jadwal',
            'pause',
            'reallocate',
            'dtstyle',
            'material',
            'statuspk',
            'data_size',
            'fabric',
            'zipper',
            'checked',
            'kelnmall',
            'kelnmNew',
            'gambar',
            'gambar2',
            'gambar3',
            'folderPath',
            'for_button',
            'comments'
        ));
    }


    function detailSR($statuspk, $srpk, $stagepk)
    {
        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('qa', 'qa.statuspk', '=', 'status.statuspk')
            ->leftJoin('dsmv', 'dsmv.statuspk', '=', 'status.statuspk')
            ->select(
                'user.userpk',
                'request.srpk',
                'request.srno',
                'status.srpk',
                'status.description',
                'stage.stagenm',
                'status.progrespk',
                'progres.progresnm',
                'status.statuspk',
                'jadwal.jadwalpk',
                'status.foto',
                'status.foto2',
                'status.foto3',
                'status.artwork',
                'status.washing',
                'status.topstitch',
                'qa.ctt',
                'status.elastic',
                'status.bobin',
                'status.overlock',
                'status.mobtape',
                'status.pattern2',
                'jadwal.patterndate',
                'jadwal.cutdate2',
                'jadwal.startsample2',
                'jadwal.finishsample2',
                'jadwal.receiptdate2',
                'jadwal.trimdate2',
                'jadwal.patterndate2',
                'jadwal.emblesdate1',
                'jadwal.emblesdate2',
                'jadwal.washdate1',
                'jadwal.washdate2',
                'jadwal.stichdate',
                'status.keterangan',
                'status.ketreject',
                'status.reject',
                'status.cansts',
                'status.construction',
                'dsmv.totsmv',
                'dsmv.finishing',
                'status.ketwash',
                'status.samplenm',
                'status.stagepk',
                DB::raw('DATE_FORMAT(status.date, "%d %M %Y") as date'),
                DB::raw('DATE_FORMAT(status.cekdate, "%d %M %Y") as cekdate'),
                DB::raw('DATE_FORMAT(jadwal.receiptdate, "%d %M %Y") as receiptdate'),
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %M %Y") as finishsample'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %M %Y") as startsample'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %M %Y") as cutdate'),
                DB::raw('DATE_FORMAT(jadwal.matdate,"%d %M %Y") as matdate'),
                DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %M %Y") as shipdate'),
                DB::raw('DATE_FORMAT(jadwal.patterndate, "%d %M %Y") as patterndate'),
                DB::raw('DATE_FORMAT(jadwal.pausedate,"%d %M %Y") as pausedate'),
                DB::raw('DATE_FORMAT(jadwal.realdate,"%d %M %Y") as realdate'),
                DB::raw('DATE_FORMAT(jadwal.trimdate, "%d %M %Y") as trimdate'),
                DB::raw('DATE_FORMAT(jadwal.resumedate, "%d %M %Y") as resumedate'),
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.style',
                'status.season',
                'status.instruksi',
                'buyer.buyerpk',
                'buyer.buyernm',
                'status.samplenm',
                'status.stagepk',
                'jadwal.trimdate',
                'status.paused',
                'status.reallocate',
                'status.grouppk',
                DB::raw('SUM(dsmv.machine+dsmv.manual+dsmv.finishing) as totsec'),
                'status.opsi',
                'jadwal.stsout',
                'jadwal.stspause',
                'status.remarkmd',
            )
            ->where('status.statuspk', '=', $statuspk)
            ->first();

        // MD
        $dt_stage = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select(
                DB::raw('GROUP_CONCAT(status.statuspk) as statuspks'),
                'stage.stagepk',
                'stage.stagenm',
                'status.srpk',
                'status.samplenm'
            )
            ->where('status.srpk', '=', $srpk)
            ->whereNotNull('status.stagepk')
            ->groupBy('stage.stagepk', 'status.samplenm')
            ->orderBy('status.statuspk', 'DESC')
            ->get();

        $sumSize = DB::table('size')->where('statuspk', '=', $statuspk)->sum('qty');

        $countStage = $dt_stage->count();

        $dt_opsi_options = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select('status.statuspk', 'status.opsi', 'status.srpk', 'status.stagepk')
            ->whereNotNull('status.opsi')
            ->where('status.stagepk', '=', $dtstyle->stagepk)
            ->where('status.srpk', '=', $srpk)
            ->where('status.statuspk', '=', $statuspk)
            ->groupBy('status.statuspk', 'status.opsi')
            ->get();

        //Fabric cunsumtion
        $tahun = date("Y") - 2;
        $data_first = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->select(
                'status.iefile',
                'status.date',
                'stage.stagenm',
                'progres.progresnm',
                'jadwal.jadwalpk',
                'jadwal.matdate',
                'jadwal.shipdate',
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.foto',
                'status.foto2',
                'status.foto3',
                'status.samplenm',
                'status.date',
                'status.style',
                'status.season',
                'status.statuspk',
                'buyer.buyerpk',
                'buyer.buyernm',
                'status.buyerpk2',
                'status.topstitch',
                'status.elastic',
                'status.description',
                'status.bobin',
                'status.overlock',
                'status.mobtape',
                'jadwal.cutdate',
                'jadwal.matdate',
                'jadwal.startsample',
                'jadwal.finishsample',
                'request.srno',
                'status.srpk',
                'status.opsi'
            )
            ->WhereYear('status.date', '>=', $tahun)
            ->Where('status.buyerpk2', '>', 0)
            ->Where('status.statuspk', '=', $statuspk)
            ->first();

        $dt_stage1a = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.stagepk', '>', '0')
            ->where('status.srpk', '=', $data_first->srpk)
            ->where('status.opsi', '=', $data_first->opsi)
            ->orderBy('status.statuspk', 'desc')
            ->get();


        $dt_stage1b = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.srpk', '=', $data_first->srpk)
            ->orderBy('status.statuspk', 'ASC')
            ->limit(1)
            ->get();

        $dt_stage2a = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.stagepk', '>', '0')
            ->where('status.srpk', '=', $data_first->srpk)
            ->orderBy('status.statuspk', 'ASC')
            ->limit(1)
            ->first();

        $dt_stage2b = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.stagepk', '>', '0')
            ->where('status.srpk', '=', $data_first->srpk)
            ->orderBy('status.statuspk', 'DESC')
            ->limit(1)
            ->first();

        $dt_yy1 = DB::table('material')
            ->select(DB::raw('SUM(myy) as myy'))
            ->where('statuspk', '=', $dt_stage2a->statuspk)
            ->groupBY('statuspk')
            ->get();

        $dt_yy2 = DB::table('material')
            ->select(DB::raw('SUM(myy) as myy'))
            ->where('statuspk', '=', $dt_stage2b->statuspk)
            ->groupBY('statuspk')
            ->get();
        // akhir fabric consumtion

        $material = DB::table('material')
            ->where('material.statuspk', '=', $statuspk)
            ->count();

        $checked = DB::table('material')
            ->where('material.statuspk', '=', $statuspk)
            ->where('material.cek', '=', true)
            ->count();

        $Goals = null;
        if ($material == $checked) {
            $Goals = '';
            $Goals1 = DB::table('jadwal')->where('statuspk', $statuspk)->update(['matdate' => NOW()]);
        } else {
            $Goals1 = DB::table('jadwal')->where('statuspk', $statuspk)->update(['matdate' => null]);
        }

        $data_size = Size::where('statuspk', '=', $statuspk)->get();

        $ii = (NULL);
        $all = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.kelnm',
                'material.matnm',
            )
            ->where('material.kelnm', '=', $ii)
            ->where('material.statuspk', '=', $statuspk)
            ->orderBy('material.matnm', 'ASC')
            ->count();

        $fabric = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.kelnm',
                'material.matnm',
            )
            ->where('material.kelnm', 'LIKE', 'FABRIC')
            ->where('material.statuspk', '=', $statuspk)
            ->orderBy('material.matnm', 'ASC')
            ->count();

        $zipper = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.kelnm',
                'material.matnm',
            )
            ->where('material.kelnm', 'LIKE', 'ZIPPER')
            ->where('material.statuspk', '=', $statuspk)
            ->orderBy('material.matnm', 'ASC')
            ->count();

        $kelnmNew = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.matnm',
                'material.kelnm',
            )
            ->where('material.statuspk', '=', $statuspk)
            ->where('material.kelnm', '<>', "FABRIC")
            ->where('material.kelnm', '<>', "ZIPPER")
            ->orderBy('material.matnm', 'ASC')
            ->count();

        $data_jadwal = Jadwal::where('statuspk', '=', $statuspk)->first();

        $comment = DB::table('qa')
            ->leftJoin('user', 'user.userpk', '=', 'qa.userpk')
            ->leftJoin('fu', 'fu.fupk', '=', 'user.fupk')
            ->where('statuspk', '=', $statuspk)
            ->orderBy('qa.qapk', 'desc')
            ->get();

        $comments = DB::table('qa')
            ->select('qa.qapk', 'qa.reject', 'qa.ctt', 'reason.reasonnm', 'qa.filectt')
            ->leftJoin('qadt', 'qa.qapk', '=', 'qadt.qapk')
            ->leftJoin('reason', 'qadt.reasonpk', '=', 'reason.reasonpk')
            ->where('qa.statuspk', '=', $statuspk)
            ->orderBy('qa.qapk', 'desc')
            ->get();

        $keterangan = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select(
                'status.statuspk',
                'stage.stagenm',
                'status.stagepk',
                'status.samplenm',
                'status.keterangan',
                'status.buyerpk2',
                'request.srpk',
                'request.srno',
                'status.srpk'
            )
            ->where('status.srpk', '=', $dtstyle->srpk)
            ->orderBy('status.statuspk', 'desc')
            ->groupBy('statuspk')->get();

        $keterangan2 = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select(
                'status.statuspk',
                'stage.stagenm',
                'status.stagepk',
                'status.samplenm',
                'status.keterangan',
                'status.buyerpk2',
                'status.srpk',
                'request.srno',
                'status.srpk',
                'status.opsi'
            )
            ->where('status.srpk', '=', $dtstyle->srpk)
            ->where('status.opsi', '=', $dtstyle->opsi)
            ->where('status.stagepk', '=', $stagepk)
            ->orderBy('status.statuspk', 'desc')
            ->groupBy('statuspk')->get();

        $gambar = $dtstyle->foto;
        $gambar2 = $dtstyle->foto2;
        $gambar3 = $dtstyle->foto3;
        $folderPath = public_path('foto');

        $pause2  = DB::table('status')
            ->select('paused')
            ->where('statuspk', '=', $statuspk)
            ->get();

        $dmsv2 = DB::table('dsmv')
            ->select('totsmv')
            ->where('statuspk', '=', $statuspk)
            ->first();

        $reallocate2  = DB::table('status')
            ->select('reallocate')
            ->where('statuspk', '=', $statuspk)
            ->get();

        $keterangan_stage = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->select(
                'request.srpk',
                'request.srno',
                'status.srpk',
                'status.statuspk',
                'stage.stagepk',
                'stage.stagenm',
                'status.keterangan'
            )
            ->where('request.srno', '=', $dtstyle->srno)
            ->where('status.stagepk', '<>', '')
            ->orderBy('status.statuspk', 'desc')
            ->groupBy('statuspk')
            ->get();

        $kelallnew = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->select(
                'status.statuspk',
                'material.matnm',
                'material.kelnm',
            )
            ->where('material.statuspk', '=', $statuspk)
            ->where('material.kelnm', '<>', "FABRIC")
            ->Where('material.kelnm', '<>', "ZIPPER")
            ->orderBy('material.matnm', 'ASC')
            ->count();

        $pause = DB::table('status')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->select(
                'status.paused',
                DB::raw('DATE_FORMAT(jadwal.pausedate, "%d %M %Y") as pausedate'),
            )
            ->where('status.statuspk', '=', $statuspk)
            ->get();


        $reallocate = DB::table('status')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->select(
                'status.reallocate',
            )
            ->where('status.statuspk', '=', $statuspk)
            ->get();

        $count_character = DB::table('status')->select('construction')->where('statuspk', '=', $statuspk)->get();
        $total_characters = 0; // Inisialisasi variabel untuk menyimpan total karakter

        if ($count_character === 0) {
            echo "Tidak ada data yang ditemukan.";
        } else {
            $total_characters = DB::table('status')->select('construction')->where('statuspk', '=', $statuspk)->sum(DB::raw('LENGTH(construction)'));
        }

        $count_remarkmd = DB::table('status')->select('remarkmd')->where('statuspk', '=', $statuspk)->get();
        $total_remarkmd = DB::table('status')->select('remarkmd')->where('statuspk', '=', $statuspk)->sum(DB::raw('LENGTH(remarkmd)'));

        $critical = DB::table('detproses')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->orderBy('timeproses', 'DESC')
            ->limit(5)
            ->get();
        // $count_critical = $countproses->count();

        $countproses = DB::table('detproses')
            ->where('statuspk', '=', $statuspk)
            ->orderBy('timeproses', 'DESC')
            ->count();

        $file_multi_techpack = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%TECHPACK%')
            ->where('filenm', '!=', '')
            ->select(
                'filesrpk',
                'statuspk',
                'jnsfile',
                'filenm',
                DB::raw('DATE_FORMAT(dateupload, "%d/%m/%y, %H:%i") as dateupload')
            )
            ->get();

        $count_file_techpack = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%TECHPACK%')
            ->where('filenm', '!=', '')
            ->count();

        $file_multi_block = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%FILEBLOCK%')
            ->where('filenm', '!=', '')
            ->select(
                'filesrpk',
                'statuspk',
                'jnsfile',
                'filenm',
                DB::raw('DATE_FORMAT(dateupload, "%d/%m/%y, %H:%i") as dateupload')
            )
            ->get();

        $count_file_block = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%FILEBLOCK%')
            ->where('filenm', '!=', '')
            ->count();


        $file_multi_sample = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%FILEPATTER%')
            ->where('filenm', '!=', '')
            ->select(
                'filesrpk',
                'statuspk',
                'jnsfile',
                'filenm',
                DB::raw('DATE_FORMAT(dateupload, "%d/%m/%y, %H:%i") as dateupload')
            )
            ->get();

        $count_file_sample = DB::table('filesr')
            ->where('statuspk', '=', $dtstyle->statuspk)
            ->where('jnsfile', 'LIKE', '%FILEPATTER%')
            ->where('filenm', '!=', '')
            ->count();

        $nowd = Carbon::now();
        $ambilbulanIni = $nowd->format('m');
        $ambiltahunIni = $nowd->format('Y');

        // Untuk bulan sebelumnya
        $previousMonth = $nowd->copy()->subMonth();
        $ambilbulanLalu = $previousMonth->format('m');

        $results = MemoHelper::getMemo($ambilbulanIni, $ambilbulanLalu, $ambiltahunIni);
        $semua_all = MemoHelper::getAllMemo($ambilbulanIni, $ambilbulanLalu, $ambiltahunIni);
        $notip = MemoHelper::getAllNotip();

        return view('display.list-sr.detail-sr', compact(
            'results',
            'semua_all',
            'notip',
            'file_multi_techpack',
            'file_multi_block',
            'file_multi_sample',
            'count_file_techpack',
            'count_file_block',
            'count_file_sample',
            'sumSize',
            'countproses',
            'critical',
            'total_characters',
            'total_remarkmd',
            'keterangan_stage',
            'keterangan',
            'comment',
            'data_jadwal',
            'pause',
            'reallocate',
            'dtstyle',
            'material',
            'statuspk',
            'data_size',
            'all',
            'fabric',
            'zipper',
            'checked',
            'kelallnew',
            'kelnmNew',
            'gambar',
            'gambar2',
            'gambar3',
            'folderPath',
            'dt_stage',
            'dt_opsi_options',
            'data_first',
            'dt_stage1a',
            'dt_stage1b',
            'dt_stage2a',
            'dt_stage2b',
            'dt_yy1',
            'dt_yy2',
            'comments',
        ));
    }

    function detailHistory($statuspk)
    {
        $tahun = date("Y") - 2;

        DB::table('status')->where('statuspk', $statuspk)->first();
        $id = $statuspk;

        return redirect('detail/development/' . $statuspk . '/history/' . $id . '/tracking');
    }


    function detailTracking($statuspk, $id)
    {
        $tahun = date("Y") - 2;

        $data_first = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->select(
                'status.iefile',
                'status.date',
                'stage.stagenm',
                'progres.progresnm',
                'jadwal.jadwalpk',
                'jadwal.matdate',
                'jadwal.shipdate',
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.foto',
                'status.foto2',
                'status.foto3',
                'status.samplenm',
                'status.date',
                'status.style',
                'status.season',
                'status.statuspk',
                'buyer.buyerpk',
                'buyer.buyernm',
                'status.buyerpk2',
                'status.topstitch',
                'status.elastic',
                'status.description',
                'status.bobin',
                'status.overlock',
                'status.mobtape',
                'jadwal.cutdate',
                'jadwal.matdate',
                'jadwal.startsample',
                'jadwal.finishsample',
                'status.srpk',
                'request.srno',
                'status.srpk',
                'status.stagepk',
                'status.opsi'
            )
            ->WhereYear('status.date', '>=', $tahun)
            ->Where('status.buyerpk2', '>', 0)
            ->Where('status.statuspk', '=', $statuspk)
            ->first();

        $data_size = Size::where('statuspk', '=', $statuspk)->orderBy('sizepk', 'ASC')->get();

        $ii = (NULL);
        $kelnmall = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.matnm',
                'material.kelnm',
            )
            ->where('material.kelnm', '=', $ii)
            ->where('material.statuspk', '=', $statuspk)
            ->orderBy('material.matnm', 'ASC')
            ->count();

        $fabric = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.kelnm',
            )
            ->where('material.statuspk', '=', $id)
            ->where('material.kelnm', 'LIKE', 'FABRIC')
            ->count();

        $zipper = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.kelnm',
            )
            ->where('material.kelnm', 'LIKE', 'ZIPPER')
            ->where('material.statuspk', '=', $id)
            ->count();

        //copyan dr sample
        $dt_stage = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.stagepk', '>', '0')
            ->where('status.srpk', '=', $data_first->srpk)
            ->where('status.opsi', '=', $data_first->opsi)
            ->orderBy('status.statuspk', 'desc')
            ->get();

        $dt_stage1a = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.srpk', '=', $data_first->srpk)
            ->orderBy('status.statuspk', 'ASC')
            ->limit(1)
            ->get();

        //copyan dr sample
        $dt_stage1 = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.stagepk', '>', '0')
            ->where('status.srpk', '=', $data_first->srpk)
            ->orderBy('status.statuspk', 'ASC')
            ->limit(1)
            ->first();

        //copyan dr sample
        $dt_stage2 = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->where('status.stagepk', '>', '0')
            ->where('status.srpk', '=', $data_first->srpk)
            ->orderBy('status.statuspk', 'DESC')
            ->limit(1)
            ->first();

        $dt_smv1 = DB::table('dsmv')
            ->where('statuspk', '=', $dt_stage1->statuspk)
            ->get();

        $dt_smv2 = DB::table('dsmv')
            ->where('statuspk', '=', $dt_stage2->statuspk)
            ->get();

        $dt_yy1 = DB::table('material')
            ->select(DB::raw('SUM(myy) as myy'))
            ->where('statuspk', '=', $dt_stage1->statuspk)
            ->groupBY('statuspk')
            ->get();

        $dt_yy2 = DB::table('material')
            ->select(DB::raw('SUM(myy) as myy'))
            ->where('statuspk', '=', $dt_stage2->statuspk)
            ->groupBY('statuspk')
            ->get();

        foreach ($dt_stage as $dt) {
            $datas = "<a href='{{ url('/detail/development/'.$data_first->statuspk.'/history/'.$dt->statuspk.'/tracking')}}'";
        }

        $datas = (request()->segment(5) == $dt->statuspk);

        $data_kita = DB::table('jadwal')
            ->Where('jadwal.statuspk', '=', $id)
            ->first();

        $for_smv = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('dsmv', 'dsmv.statuspk', '=', 'status.statuspk')
            ->select(
                'status.statuspk',
                'request.srpk',
                'request.srno',
                'status.srpk',
                'dsmv.totsmv',
                'dsmv.dsmvpk',
                DB::raw('(SUM(dsmv.machine+dsmv.manual+dsmv.finishing) / 3) as totsec'),
                'dsmv.finishing',
                'dsmv.machine',
                'dsmv.manual',
                'status.stagepk',
                'stage.stagenm'
            )
            ->where('material.kelnm', 'LIKE', 'FABRIC')
            ->where('request.srno', '=', $data_first->srno)
            ->orderBy('status.statuspk', 'desc')
            ->groupBy('statuspk')->get();

        $hasil_smv_terakhir = DB::table('dsmv')
            ->leftJoin('status', 'status.statuspk', '=', 'dsmv.statuspk')
            ->select(
                'dsmv.totsmv',
                DB::raw('SUM(dsmv.machine+dsmv.manual+dsmv.finishing) as totsec')
            )
            ->where('status.srpk', '=', $data_first->srpk)
            ->orderBy('status.statuspk', 'desc')
            ->groupBy('status.statuspk')->get();

        $fortable = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->select('status.statuspk', 'material.matpk', 'material.matnm', 'material.unit', 'material.spec', 'material.qty', 'material.meff', 'material.myy', 'stage.stagenm')
            ->where('material.kelnm', 'LIKE', 'FABRIC')
            ->where('request.srpk', '=', $data_first->srpk)
            ->groupBy('material.matnm')
            ->orderBy('material.matnm', 'ASC')
            ->get();


        $dt_stage2 = DB::table('status')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('material', 'material.statuspk', '=', 'status.statuspk')
            ->where('request.srno', '=', $data_first->srno)
            ->orderBy('status.statuspk', 'desc')
            ->get();

        $kelnmNew = DB::table('material')
            ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('supplier', 'supplier.suppk', '=', 'material.suppk')
            ->leftJoin('cur', 'cur.curpk', '=', 'material.curpk')
            ->select(
                'status.statuspk',
                'material.kelnm',
            )
            ->where('material.statuspk', '=', $statuspk)
            ->where('material.kelnm', '<>', "FABRIC")
            ->where('material.kelnm', '<>', "ZIPPER")
            ->count();

        $gambar = $data_first->foto;
        $folderPath = public_path('foto');


        return view('display.list-sr.tracking', compact(
            'kelnmall',
            'for_smv',
            'hasil_smv_terakhir',
            'fortable',
            'data_kita',
            'id',
            'dt_stage',
            'dt_stage1a',
            'dt_stage1',
            'dt_stage2',
            'dt_smv1',
            'dt_smv2',
            'dt_yy1',
            'dt_yy2',
            'data_first',
            'statuspk',
            'data_size',
            'fabric',
            'zipper',
            'kelnmNew',
            'gambar',
            'folderPath'
        ));
    }

    function detailSes(Request $request, $statuspk)
    {
        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('dsmv', 'dsmv.statuspk', '=', 'status.statuspk')
            ->leftJoin('doperator', 'doperator.statuspk', '=', 'status.statuspk')
            ->select(
                'status.description',
                'stage.stagenm',
                'status.progrespk',
                'progres.progresnm',
                'status.statuspk',
                'jadwal.jadwalpk',
                'status.foto',
                'status.artwork',
                'status.washing',
                'status.topstitch',
                'status.elastic',
                'status.bobin',
                'status.overlock',
                'status.mobtape',
                'status.pattern2',
                'jadwal.patterndate',
                'status.keterangan',
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.foto',
                'status.samplenm',
                'status.style',
                'status.season',
                'status.instruksi',
                'buyer.buyerpk',
                'buyer.buyernm',
                'status.buyerpk2',
                'status.iefile',
                'status.foto2',
                'status.foto3',
                'dsmv.machine',
                'dsmv.manual',
                'dsmv.finishing',
                'dsmv.totsmv',
                'dsmv.t100',
                'dsmv.t75',
                'doperator.operator',
                'doperator.helper',
                'doperator.finishopr',
                'doperator.totopr',
                'request.srpk',
                'request.srno',
                'status.opsi',
                'status.stagepk',
                DB::raw('DATE_FORMAT(status.date, "%d %M %Y") as date'),
                DB::raw('DATE_FORMAT(status.cekdate, "%d %M %Y") as cekdate'),
                DB::raw('DATE_FORMAT(jadwal.receiptdate, "%d %M %Y") as receiptdate'),
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %M %Y") as finishsample'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %M %Y") as startsample'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %M %Y") as cutdate'),
                DB::raw('DATE_FORMAT(jadwal.matdate,"%d %M %Y") as matdate'),
                DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %M %Y") as shipdate'),
            )
            ->where('status.statuspk', '=', $statuspk)
            ->where('status.statuspk', $statuspk)
            ->first();


        if ($dtstyle->opsi != '') {
            $dt_stage = DB::table('status')
                ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
                ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
                ->where('status.srpk', '=', $dtstyle->srpk)
                ->where('status.opsi', 'LIKE', $dtstyle->opsi)
                ->orderBy('status.statuspk', 'desc')
                ->get();

            foreach ($dt_stage as $dt) {
                $datas = "<a href='ie/notif/'. $statuspk . '/stage/'. $dt->statuspk> </a>";
            }

            $datas = (request()->segment(5) ==  $dt->statuspk);
        } else {
            $dt_stage = DB::table('status')
                ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
                ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
                ->where('status.srpk', '=', $dtstyle->srpk)
                ->orderBy('status.statuspk', 'desc')
                ->get();

            foreach ($dt_stage as $dt) {
                $datas = "<a href='ie/notif/'. $statuspk . '/stage/'. $dt->statuspk> </a>";
            }
            $datas = (request()->segment(5) ==  $dt->statuspk);
        }



        $datas = (request()->segment(5) ==  $dt->statuspk);

        $data_ku = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('doperator', 'doperator.statuspk', '=', 'status.statuspk')
            ->leftJoin('dsmv', 'dsmv.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->select(
                'status.description',
                'stage.stagenm',
                'status.progrespk',
                'status.statuspk',
                'status.foto',
                'status.artwork',
                'status.washing',
                'status.topstitch',
                'status.elastic',
                'status.bobin',
                'status.overlock',
                'status.mobtape',
                'status.pattern2',
                'status.keterangan',
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.foto',
                'status.samplenm',
                'status.style',
                'status.season',
                'status.instruksi',
                'buyer.buyerpk',
                'buyer.buyernm',
                'status.buyerpk2',
                'status.iefile',
                'dsmv.machine',
                'dsmv.manual',
                'dsmv.finishing',
                'dsmv.totsmv',
                'dsmv.t100',
                'dsmv.t75',
                'doperator.operator',
                'doperator.helper',
                'doperator.finishopr',
                'doperator.totopr',
                'request.srno',
                'request.srpk',
                'status.srpk',
                'status.stagepk',
                'status.opsi'
            )
            ->where('status.statuspk', '=', $statuspk)
            ->first();

        $machine = $data_ku->machine;
        $manual = $data_ku->manual;
        $finishing = $data_ku->finishing;
        $totalSMV = $machine + $manual + $finishing;

        $t100 = $data_ku->t100;
        $t75 = $data_ku->t75;

        $machine1 = substr($machine, 0, strpos($machine, "."));
        $manual1 = substr($manual, 0, strpos($manual, "."));
        $finishing1 = substr($finishing, 0, strpos($finishing, "."));
        $t1001 = substr($t100, 0, strpos($t100, "."));
        $t751 = substr($t75, 0, strpos($t75, "."));

        $gambar = $dtstyle->foto;
        $folderPath = public_path('foto');

        $count_ie = DB::table('detproses')
            ->where('statuspk', '=', $statuspk)
            ->count();

        return view('display.list-sr.see_engineering_sheet', compact(
            'totalSMV',
            'count_ie',
            'gambar',
            'folderPath',
            'data_ku',
            'dt_stage',
            'statuspk',
            'dtstyle',
            'manual1',
            'machine1',
            'finishing1',
            't1001',
            't751'
        ));
    }


    public function style()
    {
        $nowd = Carbon::now();
        $ambilbulanIni = $nowd->format('m');
        $ambiltahunIni = $nowd->format('Y');

        // Untuk bulan sebelumnya
        $previousMonth = $nowd->copy()->subMonth();
        $ambilbulanLalu = $previousMonth->format('m');

        $results = MemoHelper::getMemo($ambilbulanIni, $ambilbulanLalu, $ambiltahunIni);
        $semua_all = MemoHelper::getAllMemo($ambilbulanIni, $ambilbulanLalu, $ambiltahunIni);
        $notip = MemoHelper::getAllNotip();

        return view('style', compact('results', 'semua_all', 'notip'));
    }

    public function detail($id)
    {
        $dtstyle = DB::table('status')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->select(
                'status.description',
                'stage.stagenm',
                'status.progrespk',
                'progres.progresnm',
                'status.statuspk',
                'jadwal.jadwalpk',
                DB::raw('DATE_FORMAT(status.date, "%d %M %Y") as date'),
                DB::raw('DATE_FORMAT(status.cekdate, "%d %M %Y") as cekdate'),
                DB::raw('DATE_FORMAT(jadwal.receiptdate, "%d %M %Y") as receiptdate'),
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %M %Y") as finishsample'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %M %Y") as startsample'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %M %Y") as cutdate'),
                DB::raw('DATE_FORMAT(jadwal.matdate,"%d %M %Y") as matdate'),
                DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %M %Y") as shipdate'),
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.foto',
                'status.samplenm',
                'status.style',
                'status.season',
                'status.statuspk',
                'buyer.buyerpk',
                'buyer.buyernm',
                'status.userpk'
            )
            ->where('status.statuspk', '=', $id)
            ->get();

        $memoie = DB::table('status')
            ->leftJoin('memo', 'memo.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->select(
                'status.description',
                'status.progrespk',
                'status.statuspk',
                'status.style',
                'status.samplenm',
                'request.srno',
                'memo.date1',
                'memo.jenis',
                'status.foto',
                'stage.stagenm',
                'memo.date2',
                'memo.stsie',
                'status.iefile',
                'memo.stsmd',
                'memo.stssample',
                'status.reallocate',
                'memo.stspattern'
            )
            //->where('memo.jenis', '=', 'update')
            ->where('memo.stsmd', '=', '0')
            ->orderBy('memo.date1', 'DESC')
            ->limit('5')
            ->get();

        $cmemoie = $memoie->count();

        $semua = DB::table('status')
            ->leftJoin('memo', 'memo.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->select(
                'status.description',
                'status.progrespk',
                'status.statuspk',
                'status.style',
                'status.samplenm',
                'request.srno',
                'memo.date1',
                'memo.jenis',
                'status.foto',
                'stage.stagenm',
                'memo.date2',
                'memo.stsie',
                'memo.stssample',
                'memo.stsmd',
                'status.reallocate',
                'memo.stspattern'
            )
            //->where('memo.jenis', '=', 'update')
            ->where('memo.stsmd', '=', '0')
            ->orderBy('memo.date1', 'DESC')
            ->get();

        return view('detail', compact('cmemoie', 'memoie', 'semua'), ['style' => $dtstyle]);
    }

    public function stage()
    {
        $stage = stage::orderBy('stagepk', 'asc')->get();
        return json_encode($stage);
    }

    public function type()
    {
        $type = type::orderBy('mtypepk', 'asc')->get();
        return json_encode($type);
    }

    public function msize()
    {
        $msize = msize::orderBy('sizenm', 'asc')->get();
        return json_encode($msize);
    }

    public function progres()
    {
        $progres = progres::orderBy('progrespk', 'asc')->get();
        return json_encode($progres);
    }

    public function emblessment()
    {
        $emblessments = [
            ['emblessnm' => 'Heat Transfer', 'emblessval' => 'Heat Transfer'],
            ['emblessnm' => 'Embrodery', 'emblessval' => 'Embrodery'],
            ['emblessnm' => 'Printing', 'emblessval' => 'Printing'],
            ['emblessnm' => 'Laser', 'emblessval' => 'Laser'],
        ];

        return json_encode($emblessments);
    }

    public function categories()
    {
        $categories = [
            ['categorynm' => 'Top', 'categoryval' => 'Top'],
            ['categorynm' => 'Bottom', 'categoryval' => 'Bottom'],
            ['categorynm' => 'Jacket', 'categoryval' => 'Jacket'],
            ['categorynm' => 'Others', 'categoryval' => 'Others']
        ];

        return json_encode($categories);
    }

    public function statusOnPola()
    {
        $statusCode = [
            ['statusname' => 'All', 'stsval' => ''],
            ['statusname' => 'Done', 'stsval' => '1'],
            ['statusname' => 'Pending', 'stsval' => '0']
        ];

        return json_encode($statusCode);
    }

    public function group()
    {
        $group = DB::table('group')
            ->orderBy('grouppk', 'asc')
            ->get();
        return json_encode($group);
    }

    public function brands()
    {
        $brands = DB::table('status')
            ->select('buyer.buyerpk', 'buyer.buyernm')
            ->leftjoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->where('status.buyerpk2', '!=', null)
            ->where('status.buyerpk2', '!=', 0)
            ->orderBy('buyer.buyerpk', 'asc')
            ->groupBy('status.buyerpk2')
            ->get();

        return json_encode($brands);
    }

    public function brand(Request $request)
    {
        $userpk = Session::get('userpk');

        $guserpk = DB::table('user')
            ->where('userpk', '=', $userpk)
            ->value('guserpk');

        $user = $request->user;
        $caribrand1 = $request->caribrand1;
        $brandb0 = $request->brandb0;
        $brandb1 = $request->brandb1;
        $brandb2 = $request->brandb2;
        $brandb3 = $request->brandb3;
        $brandb4 = $request->brandb4;
        $brandb5 = $request->brandb5;
        $brandbx = array($brandb0, $brandb1, $brandb2, $brandb3, $brandb4, $brandb5);
        $tahun = date("Y") - 3;

        $brand = DB::table('status')
            ->select('buyer.buyerpk', 'buyer.buyernm')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->where('buyer.buyernm', 'like', "%" . $caribrand1 . "%")
            // ->where('status.userpk', '=', $user)
            ->where('status.aktif', '=', 0)
            ->where('status.buyerpk2', '>', 0)
            ->whereYEAR('status.date', '>=', $tahun);
        // ->get();

        if ($guserpk == 1) {
            $brand = $brand;
        } else {
            $brand = $brand->where('status.userpk', '=', $user);
        }

        if ($brandb0 <> '' || $brandb1 <> '' || $brandb2 <> '' || $brandb3 <> '' || $brandb4 <> '' || $brandb5 <> '') {
            $dtbrand = $brand->whereIn('buyer.buyernm', $brandbx)->groupBy('status.buyerpk2')
                ->orderBy('status.statuspk', 'desc')
                ->limit('6')
                ->get();
        } else {
            $dtbrand = $brand->groupBy('status.buyerpk2')
                ->orderBy('status.statuspk', 'desc')
                ->limit('6')
                ->get();
        }

        $result = array();

        $dtbrand2 = $brand->count();

        $result['total'] = $dtbrand2;
        $row = array();

        $index = 0;
        foreach ($dtbrand as $d) {
            $row[] = array(
                'index' => $index,
                'buyerpk' => $d->buyerpk,
                'buyernm' => $d->buyernm
            );

            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function brand2(Request $request)
    {
        $userpk = Session::get('userpk');

        $guserpk = DB::table('user')
            ->where('userpk', '=', $userpk)
            ->value('guserpk');

        $caribrand2 = $request->caribrand2;
        $user = $request->user;
        $tahun = date("Y") - 3;

        $brand2 = DB::table('status')
            ->select('buyer.buyerpk', 'buyer.buyernm')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->where('buyer.buyernm', 'like', "%" . $caribrand2 . "%")
            // ->where('status.userpk', '=', $user)
            ->where('status.aktif', '=', 0)
            ->where('status.buyerpk2', '>', 0)
            ->whereYEAR('status.date', '>=', $tahun)
            ->groupBy('status.buyerpk2')
            ->orderBy('buyer.buyernm', 'asc');
        // ->get();

        DB::statement("SET @rownum = 0");

        if ($guserpk == 1) {
            $brandkita = $brand2->get();
        } else {
            $brandkita = $brand2->where('status.userpk', '=', $user)->get();
        }

        $brandkita = $brandkita->map(function ($item, $index) {
            $item->index = $index + 1;
            return $item;
        });

        return json_encode($brandkita);
    }

    public function brandie(Request $request)
    {
        $user = $request->user;
        $caribrand1 = $request->caribrand1;
        $brandb0 = $request->brandb0;
        $brandb1 = $request->brandb1;
        $brandb2 = $request->brandb2;
        $brandb3 = $request->brandb3;
        $brandb4 = $request->brandb4;
        $brandb5 = $request->brandb5;
        $brandbx = array($brandb0, $brandb1, $brandb2, $brandb3, $brandb4, $brandb5);
        $tahun = date("Y") - 3;

        $brandie = DB::table('buyer')
            ->limit('6')
            ->get();
        return json_encode($brandie);
    }

    public function brandie2()
    {
        $brandie2 = Buyer::orderBy('buyerpk', 'asc')
            ->get();
        return json_encode($brandie2);
    }

    public function dtstyle(Request $request)
    {
        $tahun2 = date("Y");
        $userpk = Session::get('userpk');
        $guserpk = DB::table('user')->where('userpk', '=', $userpk)->value('guserpk');
        $gusernm = Session::get('gusernm');
        $prog_detail = "md-det";
        $user = $request->user;
        $filterByYear = $request->filterByYear;
        $sortlistByDate = $request->sortlistByDate;
        $cari1 = $request->cari1;
        $stage0 = $request->stage0;
        $stage1 = $request->stage1;
        $stage2 = $request->stage2;
        $stage3 = $request->stage3;
        $stage4 = $request->stage4;
        $stage5 = $request->stage5;
        $stage6 = $request->stage6;
        $stage7 = $request->stage7;
        $stagex = array($stage0, $stage1, $stage2, $stage3, $stage4, $stage5, $stage6, $stage7);
        $progres0 = $request->progres0;
        $progres1 = $request->progres1;
        $progres2 = $request->progres2;
        $progres3 = $request->progres3;
        $progres4 = $request->progres4;
        $progres5 = $request->progres5;
        $progresx = array($progres0, $progres1, $progres2, $progres3, $progres4, $progres5);
        $brand0 = $request->brand0;
        $brand1 = $request->brand1;
        $brand2 = $request->brand2;
        $brand3 = $request->brand3;
        $brand4 = $request->brand4;
        $brand5 = $request->brand5;
        $brandx = array($brand0, $brand1, $brand2, $brand3, $brand4, $brand5);

        if (!empty($filterByYear)) $tahun2 = $filterByYear;

        $page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->leftJoin('material', 'material.statuspk', '=', 'status.statuspk')
            ->select(
                'jadwal.stspause',
                'jadwal.stsout',
                'status.description',
                'status.stagepk',
                'stage.stagenm',
                'status.progrespk',
                'progres.progresnm',
                'status.statuspk',
                'status.srpk',
                'status.stagepk',
                'jadwal.jadwalpk',
                'status.date',
                'status.cekdate',
                'jadwal.receiptdate',
                'jadwal.finishsample',
                'jadwal.startsample',
                'jadwal.cutdate',
                'jadwal.matdate',
                'jadwal.trimdate',
                'jadwal.shipdate',
                'jadwal.pausedate',
                'jadwal.realdate',
                DB::raw('DATE_FORMAT(jadwal.finishsample2, "%d %M %Y") as finishsample2'),
                DB::raw('DATE_FORMAT(jadwal.startsample2,"%d %M %Y") as startsample2'),
                DB::raw('DATE_FORMAT(jadwal.cutdate2,"%d %M %Y") as cutdate2'),
                DB::raw('DATE_FORMAT(jadwal.finishsample2, "%d %b") as finishsample2'),
                DB::raw('DATE_FORMAT(jadwal.startsample2,"%d %b") as startsample2'),
                DB::raw('DATE_FORMAT(jadwal.cutdate2,"%d %b") as cutdate2'),
                DB::raw('DATE_FORMAT(status.date, "%d %b %Y") as date'),
                DB::raw('DATE_FORMAT(status.cekdate, "%d %b %Y") as cekdate'),
                DB::raw('DATE_FORMAT(jadwal.receiptdate, "%d %b %Y") as receiptdate'),
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %b %Y") as finishsample'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %b %Y") as startsample'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %b %Y") as cutdate'),
                DB::raw('DATE_FORMAT(jadwal.matdate,"%d %b %Y") as matdate'),
                DB::raw('DATE_FORMAT(jadwal.trimdate,"%d %b %Y") as trimdate'),
                DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %b %Y") as shipdate'),
                DB::raw('DATE_FORMAT(jadwal.pausedate,"%d %b %Y") as pausedate'),
                DB::raw('DATE_FORMAT(jadwal.realdate,"%d %b %Y") as realdate'),
                DB::raw('DATE_FORMAT(jadwal.semblesdate1,"%d %b %Y") as semblesdate1'),
                DB::raw('DATE_FORMAT(jadwal.swashdate1,"%d %b %Y") as swashdate1'),
                DB::raw('SUM(size.qty) as qty'),
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.pattern2',
                'status.foto',
                'status.samplenm',
                'status.style',
                'status.season',
                'status.statuspk',
                'buyer.buyerpk',
                'buyer.buyernm',
                'status.reject',
                'status.cansts',
                'request.srno',
                'status.opsi',
                'status.stsupdt2',
                'status.paused',
                'status.userpk',
                'status.washing',
                'status.artwork',
            )
            ->whereYEAR('status.date', '=', $tahun2)
            ->where('status.buyerpk2', '>', 0)
            ->where(DB::raw("CONCAT(request.srno,'',status.style,'',buyer.buyernm,'',status.season)"), 'like', '%' . $cari1 . '%')
            ->groupBy('statuspk');

        if ($guserpk == 1) {
            $dtstyle = $dtstyle;
        } else {
            $dtstyle = $dtstyle->where('status.userpk', '=', $user);
        }

        if ($stage0 <> '' || $stage1 <> '' || $stage2 <> '' || $stage3 <> '' || $stage4 <> '' || $stage5 <> '' || $stage6 <> '' || $stage7 <> '') {
            $dtstyle = $dtstyle->whereIn('stage.stagenm', $stagex);
        }
        if ($progres0 <> '' || $progres1 <> '' || $progres2 <> '' || $progres3 <> '' || $progres4 <> '' || $progres5 <> '') {
            $dtstyle = $dtstyle->whereIn('progres.progresnm', $progresx);
        }
        if ($brand0 <> '' || $brand1 <> '' || $brand2 <> '' || $brand3 <> '' || $brand4 <> '' || $brand5 <> '') {
            $dtstyle = $dtstyle->whereIn('buyer.buyernm', $brandx);
        }

        // if ($cari4 == 1) {
        //     $dtstyle = $dtstyle->orderBy('status.date', 'DESC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC');
        // } else if ($cari5 == 1) {
        //     $dtstyle = $dtstyle->orderBy('status.date', 'ASC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC');
        // } elseif ($cari6 == 1) {
        //     $dtstyle = $dtstyle->orderBy('status.stsupdt1', 'DESC')->orderBy('status.dateupdt', 'DESC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC');
        // } elseif ($cari7 == 1) {
        //     $dtstyle = $dtstyle->orderBy('jadwal.shipdate', 'DESC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC');
        // } else {
        //     $dtstyle = $dtstyle->orderBy('status.date', 'DESC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC');
        // }

        if ($sortlistByDate == 'oldestEntry') {
            $dtstyle = $dtstyle->orderBy('status.date', 'ASC');
        } elseif ($sortlistByDate == 'recentlyUpdated') {
            $dtstyle = $dtstyle->orderBy('status.dateupdt', 'DESC');
        } elseif ($sortlistByDate == 'dueDate') {
            $dtstyle = $dtstyle->orderBy('jadwal.shipdate', 'DESC');
            // $dtstyle = $dtstyle->orderBy('jadwal.shipdate', 'DESC')->orderBy('request.srno', 'DESC')->orderBy('status.stagepk', 'DESC')->orderBy('status.opsi', 'ASC');

        } else {
            $dtstyle = $dtstyle->orderBy('status.date', 'DESC');
        }

        $dtstyle2b = $dtstyle->get()->count();

        $offset = ($page - 1) * $rows;
        $dtstyle = $dtstyle->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $dtstyle2b;
        $row = array();

        foreach ($dtstyle as $d) {
            $dprogrespk = $d->progrespk;
            $dprogresnm = $d->progresnm;
            $dstatuspk = $d->statuspk;
            $samplenm = $d->samplenm;
            $style1 = $d->style;
            $buyer1 = $d->buyernm;
            $season1 = $d->season;
            $desc = $d->description;
            $techpack = $d->techpack;
            $patternkedua = $d->pattern2;
            $opsi = $d->opsi;
            $stsupdt2 = $d->stsupdt2;
            $paused = $d->paused;
            $qty = $d->qty;

            if ($d->stagepk == 0) {
                $stagenm1 = $d->stagenm;
                $stagenm1 = $d->stagenm . "<br>";
            } else if ($d->stagepk == 8) {
                $stagenm1 = htmlspecialchars($samplenm, ENT_QUOTES, 'UTF-8') . "<br>";
            } else {
                $stagenm1 = $d->stagenm;
            }

            $qty = DB::table('size')
                ->leftJoin('status', 'status.statuspk', '=', 'size.statuspk')
                ->select(DB::raw('SUM(size.qty) as qty'))
                ->where('size.statuspk', $d->statuspk)
                ->first();

            $dtmat = DB::table('material')
                ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
                ->where('material.statuspk', '=', $dstatuspk)
                ->count();

            $dtmat2 = DB::table('material')
                ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
                ->where('material.statuspk', '=', $dstatuspk)
                ->where('material.cek', '=', 1)
                ->count();

            $sr = DB::table('status')
                ->where('srpk', '=', $d->srpk)
                ->where('stagepk', '=', $d->stagepk)
                ->where('opsi', '=', "C")
                ->first();

            if ($d->date == "" || $d->date == "0000-00-00") {
                $date = "--";
            } else {
                $date = $d->date;
            }

            $dateInput = DB::table('material')
                ->select(DB::raw('DATE_FORMAT(material.cekdate, "%d %b") as MatCekDate'))
                ->where('material.statuspk', '=', $d->statuspk)
                ->orderBy('material.cekdate', 'DESC')
                ->get();

            $MatCekDate = null;

            if (!$dateInput->isEmpty()) {
                foreach ($dateInput->take(1) as $tglInput) {
                    $MatCekDate = $tglInput->MatCekDate;
                }
            }

            if ($MatCekDate == null || $MatCekDate == "" || $MatCekDate == "0000-00-00") {
                $MatCekDate = "--";
            } else {
                $MatCekDate = date("d M Y", strtotime($MatCekDate));
            }

            if ($d->trimdate == "" || $d->trimdate == "0000-00-00") {
                $trimdate = "--";
            } else {
                $trimdate = $d->trimdate;
            }

            if ($d->shipdate == "" || $d->shipdate == "0000-00-00") {
                $duedate = "--";
            } else {
                $duedate = date("d/m", strtotime($d->shipdate));
            }

            if ($d->cutdate == "" || $d->cutdate == "0000-00-00") {
                $cutdate = "--";
            } else {
                $cutdate = $d->cutdate;
            }

            if ($d->startsample == "" || $d->startsample == "0000-00-00") {
                $sewdate = "--";
            } else {
                $sewdate = $d->startsample;
            }

            if ($d->finishsample == "" || $d->finishsample == "0000-00-00") {
                $findate = "--";
            } else {
                $findate = $d->finishsample;
            }

            if ($d->receiptdate == "" || $d->receiptdate == "0000-00-00") {
                $recdate = "--";
            } else {
                $recdate = $d->receiptdate;
            }

            if ($d->shipdate == "" || $d->shipdate == "0000-00-00") {
                $shipdate = "--";
            } else {
                $shipdate = $d->shipdate;
            }

            if ($d->pausedate == "" || $d->pausedate == "0000-00-00") {
                $pausedate = "--";
            } else {
                $pausedate = $d->pausedate;
            }

            if ($d->realdate == "" || $d->realdate == "0000-00-00") {
                $realdate = "--";
            } else {
                $realdate = $d->realdate;
            }

            $dateprog = "--";
            if ($d->progrespk == 1) {
                $dateprog = $date;
            } elseif ($d->progrespk == 2) {
                $dateprog = $trimdate;
            } elseif ($d->progrespk == 3) {
                $dateprog = $cutdate;
            } elseif ($d->progrespk == 4) {
                $dateprog = $sewdate;
            } elseif ($d->progrespk == 5) {
                $dateprog = $findate;
            } elseif ($d->progrespk == 6) {
                $dateprog = $recdate;
            } elseif ($d->progrespk == 7) {
                $dateprog = $pausedate;
            } elseif ($d->progrespk == 8) {
                $dateprog = $realdate;
            } elseif ($d->progrespk == 9) {
                $dateprog = $d->semblesdate1;
            } elseif ($d->progrespk == 10) {
                $dateprog = $d->swashdate1;
            }

            $NamaProgres = "";
            if ($d->progrespk == 1 || $d->progrespk == 2) {
                $NamaProgres = 'On Hold';
            } elseif ($d->progrespk == 3) {
                $NamaProgres = 'Cutting';
            } elseif ($d->progrespk == 4) {
                $NamaProgres = 'Sewing';
            } elseif ($d->progrespk == 5) {
                $NamaProgres = 'Finishing';
            } elseif ($d->progrespk == 6) {
                $NamaProgres = 'Handover';
            } elseif ($d->progrespk == 7 &&  $d->stspause == 1 || $d->progrespk == 7 &&  $d->stspause == 2) {
                $NamaProgres = 'On Hold';
            } elseif ($d->progrespk == 7 &&  $d->stspause == 3) {
                $NamaProgres = 'Cutting';
            } elseif ($d->progrespk == 7 &&  $d->stspause == 4) {
                $NamaProgres = 'Sewing';
            } elseif ($d->progrespk == 8 &&  $d->stsout == 1 || $d->progrespk == 8 &&  $d->stsout == 2) {
                $NamaProgres = 'On Hold';
            } elseif ($d->progrespk == 8 &&  $d->stsout == 3) {
                $NamaProgres = 'Cutting';
            } elseif ($d->progrespk == 8 &&  $d->stsout == 4) {
                $NamaProgres = 'Sewing';
            } elseif ($d->progrespk == 9 || $d->progrespk == 7 &&  $d->stspause == 9) {
                $NamaProgres = 'Embellishment';
            } elseif ($d->progrespk == 10 || $d->progrespk == 7 &&  $d->stspause == 10) {
                $NamaProgres = 'Washing';
            }

            if ($d->pattern2 <> "") {
                $pattern = "<img src='public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $pattern = "<img src='public/css/images/abu.png' width='15px' height='15px'>";
            }

            if ($d->yy <> "") {
                $yy = "<img src='public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $yy = "<img src='public/css/images/abu.png' width='15px' height='15px'>";
            }

            if ($d->sam <> "") {
                $sam = "<img src='public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $sam = "<img src='public/css/images/abu.png' width='15px' height='15px'>";
            }

            if ($d->trimdate <> "") {
                $trimdate = "<img src='public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $trimdate = "<img src='public/css/images/abu.png' width='15px' height='15px'>";
            }

            $gambar = $d->foto;
            $folderPath = public_path('foto');

            if ($gambar && file_exists($folderPath . '/' . $gambar)) {
                $hasil_gambar = "<img src='public/foto/$gambar' width='65px' height='65px' style='margin-top:15px;'>";
            } else {
                $hasil_gambar = "<img src='public/css/no-img.png' width='65px' height='65px' style='margin-top:15px;'>";
            }


            if ($gambar && file_exists($folderPath . '/' . $gambar)) {
                $hasil_gambarR1 = "<img src='public/foto/$gambar' width='65px' height='65px' style='margin-top:-40px;'>";
            } else {
                $hasil_gambarR1 = "<img src='public/css/no-img.png' width='65px' height='65px' style='margin-top:-45px;'>";
            }


            $dcancel = $d->cansts;
            if ($d->reject > 0) {
                $foto = "<p style='font-weight: 700; font-size: 14px; line-height:18px; color:#FF6B00; margin-top: -13%;'>R1</p>
                        <div style='margin-top:65%'>$hasil_gambarR1</div>";
            } else if ($d->cansts > 0) {
                $foto = "<p style='font-weight: 700;font-size: 14px;line-height: 18px;color:rgba(0, 0, 0, 0.38); margin-top:-10%;'>Cancel</p>
                        <div style='margin-top:75%'>$hasil_gambarR1</div>";
            } else {
                $foto = "$hasil_gambar";
            }

            if ($d->opsi != '') {
                if ($d->cansts > 0) {
                    $styledt = "<div style='width:60px;height:60px;'>$foto</div>
                        <div style='margin-left:28%; margin-top:-13%;font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;color:#00000061'> 
                        SR $d->srno [$opsi]<br><span style='color:#000000;cursor:default;font-weight: 400;font-size: 14px;line-height: 18px;color:#00000061'>$style1 - Qty: $qty->qty Due: $duedate</span>
                        </div><div style='margin-left:25%;font-family: Arial;font-style: normal;font-weight: 500;font-size: 11px;line-height: 14px;color:#00000061'> 
                        <a href='javascript:void(0)' style='text-decoration:none; color:#00000061';cursor:default;' title='$buyer1, $season1 - $desc' class='easyui-tooltip'>$buyer1, $season1 - $desc</a> 
                        </div><br>";
                } else {
                    $styledt = "<div style='width:60px;height:60px;'>$foto</div>
                        <div style='margin-left:28%; margin-top:-20%;font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
                        SR $d->srno [$opsi]<br><span style='color:#000000;cursor:default;font-weight: 400;font-size: 14px;line-height: 18px;'>$style1 - Qty: $qty->qty Due: $duedate</span>
                        <div style='margin-left:0%; font-family: Arial;font-style: normal;font-weight: 500;font-size: 11px;line-height: 14px;color:#00000061'> 
                        <a href='javascript:void(0)' style='text-decoration:none; color:#00000061';cursor:default;' title='$buyer1, $season1 - $desc' class='easyui-tooltip'>$buyer1, $season1 - $desc</a> 
                        </div><br>";
                }
            } else {
                if ($d->cansts > 0) {
                    $styledt = "<div style='width:60px;height:60px;'>$foto</div>
                        <div style='margin-left:28%; margin-top:-13%;font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;color:#00000061'> 
                        SR $d->srno<br><span style='color:#000000;cursor:default;font-weight: 400;font-size: 14px;line-height: 18px;color:#00000061'>$style1 - Qty: $qty->qty Due: $duedate</span>
                        </div><div style='margin-left:25%;font-family: Arial;font-style: normal;font-weight: 500;font-size: 11px;line-height: 14px;color:#00000061'> 
                        <a href='javascript:void(0)' style='text-decoration:none; color:#00000061';cursor:default;' title='$buyer1, $season1 - $desc' class='easyui-tooltip'>$buyer1, $season1 - $desc</a> 
                        </div><br>";
                } else {
                    $styledt = "<div style='width:60px;height:60px;'>$foto</div>
                        <div style='margin-left:28%; margin-top:-17%;font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
                        SR $d->srno<br><span style='color:#000000;cursor:default;font-weight: 400;font-size: 14px;line-height: 18px;'>$style1 - Qty: $qty->qty Due: $duedate</span>
                        </div>
                        <div style='margin-left:28%; font-family: Arial;font-style: normal;font-weight: 500;font-size: 11px;line-height: 14px;color:#00000061'> 
                        <a href='javascript:void(0)' style='text-decoration:none; color:#00000061';cursor:default;' title='$buyer1, $season1 - $desc' class='easyui-tooltip'>$buyer1, $season1 - $desc</a> 
                        </div><br>";
                }
            }

            if ($d->cansts > 0) {
                $stage = "<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
                        <a href='javascript:void(0)' style='color:rgba(0, 0, 0, 0.38);text-decoration:none;cursor:default;' title='$stagenm1' class='easyui-tooltip'>$stagenm1</a>
                        <span style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $date </span></div>
                        <div style='color:rgba(0, 0, 0, 0.38);font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 12px; line-height: 16px;'>
                        Patern $pattern YY $yy SMV $sam TC $trimdate</div>";
                $datemat = "<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dtmat2/$dtmat </div>
                        <div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $MatCekDate </div>";
                $progres = "<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $NamaProgres </div>
                        <div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $dateprog </div>";
                $plandate = "<div style='color:rgba(0, 0, 0, 0.38);'>$shipdate</div>";
                // $act = "<div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 700;font-size: 20px;line-height: 26px;'>&#10247;</div>";
                $act = "";
                $act2 = "<a href='sample/detail/$d->statuspk' style='color:rgba(0, 0, 0, 0.38);text-decoration:none; font-family: Arial;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;'> &#5171; </a>";
            } else {
                $stage = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
                        <a href='javascript:void(0)' style='text-decoration:none;color:#000000;cursor:default;' title='$stagenm1' class='easyui-tooltip'>$stagenm1</a>
                        <span style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color:rgba(0, 0, 0, 0.6);'> $date </span></div>
                        <div style='font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 12px; line-height: 16px;'>
                        Patern $pattern YY $yy SMV $sam TC $trimdate</div>";
                $datemat = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dtmat2/$dtmat </div>
                        <div style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $MatCekDate </div>";
                $progres = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $NamaProgres </div>
                        <div style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $dateprog </div>";
                $plandate = "<div style=''>$shipdate</div>";
                // $act = "<div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 20px;line-height: 26px;color:#000000'>&#10247;</div>";
                $act = "";
                $act2 = "<a href='sample/detail/$d->statuspk' style='text-decoration:none; font-family: Arial;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;color:#000000''> &#5171; </a>";
            }

            $btnProgress = '';
            if ($dcancel == 1) {
                $btnProgress = '<button style="padding: 2px 4px;text-align:center; border-radius:5px; border:0px solid #000000; font-weight:300; font-size:14px; color:#FFFFFF; background-color:#EF3B42;">Cancelled</button>';
            } else {
                if ($d->progrespk == 1) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:left; border-radius:4px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#BDBDBD;">On hold</button>';
                } elseif ($d->progrespk == 2) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:left; border-radius:4px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#BDBDBD;">On hold</button>';
                } elseif ($d->progrespk == 3) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                } elseif ($d->progrespk == 4) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                } elseif ($d->progrespk == 5) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                } elseif ($d->progrespk == 6) {
                    $btnProgress = '<button style="width:72px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#19B715;">Complete</button>';
                } elseif ($d->progrespk == 7) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#FF6B00;">Pause</button>';
                } elseif ($d->progrespk == 8) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#FF6B00;">Transfer Out</button>';
                } elseif ($d->progrespk == 9) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                } elseif ($d->progrespk == 10) {
                    $btnProgress = '<button style="width:66px; height:22px; padding: 2px 4px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</button>';
                }
            }

            $text_emb = '';
            $text_embs = array();
            if ($d->washing == 1) array_push($text_embs, 'W');
            if (!empty($d->artwork)) {
                $artworks = explode(",", $d->artwork);
                foreach ($artworks as $artwork) {
                    if (trim($artwork, ' ') === "HT" || trim($artwork, ' ') === "Heat Transfer") array_push($text_embs, 'H');
                    if (trim($artwork, ' ') === "Embrodery") array_push($text_embs, 'E');
                    if (trim($artwork, ' ') === "Printing") array_push($text_embs, 'P');
                }
            }
            if (count($text_embs) > 0) $text_emb = implode(",", $text_embs);
            $emb = "<span>$text_emb</span>";

            $estCut = '--';
            if (!empty($d->cutdate2) && $d->cutdate2 != '0000-00-00' && $d->cutdate2 != '') $estCut = $d->cutdate2;
            $estSew = '--';
            if (!empty($d->startsample2) && $d->startsample2 != '0000-00-00' && $d->startsample2 != '') $estSew = $d->startsample2;
            $estFin = '--';
            if (!empty($d->finishsample2) && $d->finishsample2 != '0000-00-00' && $d->finishsample2 != '') $estFin = $d->finishsample2;
            $estimasi = "<span>CUT :$estCut</span><br><span>SEW: $estSew</span><br><span>FIN: $estFin</span>";

            $row[] = array(
                'statuspk' => $d->statuspk,
                'srpk' => $d->srpk,
                'stagepk' => $d->stagepk,
                'jadwalpk' => $d->jadwalpk,
                'reject' => $d->reject,
                'cansts' => $d->cansts,
                'foto' => $foto,
                'style' => $styledt,
                'dstyle' => $d->style,
                'stage' => $stage,
                'datemat' => $datemat,
                'progrespk' => $d->progrespk,
                'progres' => $progres,
                'plandate' => $plandate,
                'techpack' => $techpack,
                'pattern2' => $patternkedua,
                'trimdate' => $d->trimdate,
                'cutdate' => $d->cutdate,
                'paused' => $d->paused,
                'status' => $btnProgress,
                'stsupdt' => $stsupdt2,
                'gusernm' => $gusernm,
                'prog_detail' => $prog_detail,
                'sr' => $sr,
                'dprogres' => $dprogrespk,
                'userpk' => $d->userpk,
                'estimasi' => $estimasi,
                'emb' => $emb,
                'act' => $act,
                'act2' => $act2,
            );
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function dtstylee2(Request $request)
    {
        $tahun = date("Y");

        $user = $request->user;
        $guser = $request->guser;
        $cari1 = $request->cari1;
        $cari3 = $request->cari3;
        //$caristage = $request->caristage;
        //$cariprogres = $request->cariprogres;
        $cari4 = $request->cari4;
        $cari5 = $request->cari5;
        $cari6 = $request->cari6;
        $cari7 = $request->cari7;
        $stage0 = $request->stage0;
        $stage1 = $request->stage1;
        $stage2 = $request->stage2;
        $stage3 = $request->stage3;
        $stage4 = $request->stage4;
        $stage5 = $request->stage5;
        $stage6 = $request->stage6;
        $stage7 = $request->stage7;
        $stagex = array($stage0, $stage1, $stage2, $stage3, $stage4, $stage5, $stage6, $stage7);
        $progres0 = $request->progres0;
        $progres1 = $request->progres1;
        $progres2 = $request->progres2;
        $progres3 = $request->progres3;
        $progres4 = $request->progres4;
        $progres5 = $request->progres5;
        $progresx = array($progres0, $progres1, $progres2, $progres3, $progres4, $progres5);
        $brand0 = $request->brand0;
        $brand1 = $request->brand1;
        $brand2 = $request->brand2;
        $brand3 = $request->brand3;
        $brand4 = $request->brand4;
        $brand5 = $request->brand5;
        $brandx = array($brand0, $brand1, $brand2, $brand3, $brand4, $brand5);

        if ($cari3 > 0) {
            $tahun2 = $cari3;
        } else {
            $tahun2 = $tahun;
        }

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->select(
                'status.userpk',
                'request.srno',
                'status.description',
                'stage.stagenm',
                'buyer.buyernm',
                'status.stagepk',
                'status.progrespk',
                'progres.progresnm',
                'status.statuspk',
                'jadwal.jadwalpk',
                DB::raw('DATE_FORMAT(status.date, "%d %M %Y") as date'),
                DB::raw('DATE_FORMAT(status.cekdate, "%d %M %Y") as cekdate'),
                DB::raw('DATE_FORMAT(jadwal.receiptdate, "%d %M %Y") as receiptdate'),
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %M %Y") as finishsample'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %M %Y") as startsample'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %M %Y") as cutdate'),
                DB::raw('DATE_FORMAT(jadwal.matdate,"%d %M %Y") as matdate'),
                DB::raw('DATE_FORMAT(jadwal.trimdate,"%d %M %Y") as trimdate'),
                DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %M %Y") as shipdate'),
                DB::raw('DATE_FORMAT(jadwal.pausedate,"%d %M %Y") as pausedate'),
                DB::raw('DATE_FORMAT(jadwal.realdate,"%d %M %Y") as realdate'),
                'status.yy',
                'status.sam',
                'status.techpack',
                'status.pattern',
                'status.pattern2',
                'status.foto',
                'status.samplenm',
                'status.style',
                'status.season',
                'status.statuspk',
                'buyer.buyerpk',
                'buyer.buyernm',
                'status.reject',
                'status.cansts',
                'status.opsi'
            )
            ->whereYEAR('status.date', '=', $tahun2)
            ->where('status.buyerpk2', '>', 0)
            // ->where('status.foto', '<>', '')
            // ->where('status.userpk', '=', $user)
            //// ->where('jadwal.shipdate', '>', 0)
            ->where(DB::raw("CONCAT(request.srno,'',status.style,'',buyer.buyernm,'',status.season)"), 'like', '%' . $cari1 . '%')
            ->where('status.aktif', '=', 0);
        //if($caristage>0){ $dtstylee=$dtstyle->where('status.stagepk','=',$caristage)->get(); }
        //if($cariprogres>0){ $dtstylee=$dtstyle->where('status.progrespk','=',$cariprogres)->get(); }
        if ($guser == "Admin") {
            $dtstyle2 = $dtstyle;
        } else {
            $dtstyle2 = $dtstyle->where('status.userpk', '=', $user);
        }

        if ($stage0 <> '' || $stage1 <> '' || $stage2 <> '' || $stage3 <> '' || $stage4 <> '' || $stage5 <> '' || $stage6 <> '' || $stage7 <> '') {
            $dtstylee = $dtstyle2->whereIn('stage.stagenm', $stagex)->get();
        }
        if ($progres0 <> '' || $progres1 <> '' || $progres2 <> '' || $progres3 <> '' || $progres4 <> '' || $progres5 <> '') {
            $dtstylee = $dtstyle2->whereIn('progres.progresnm', $progresx)->get();
        }
        if ($brand0 <> '' || $brand1 <> '' || $brand2 <> '' || $brand3 <> '' || $brand4 <> '' || $brand5 <> '') {
            $dtstylee = $dtstyle2->whereIn('buyer.buyernm', $brandx)->get();
        }

        if ($cari4 == 1) {
            $dtstylee = $dtstyle2->orderBy('status.date', 'DESC')->orderBy('request.srno', 'DESC')->get();
        } else if ($cari5 == 1) {
            $dtstylee = $dtstyle2->orderBy('status.date', 'ASC')->orderBy('request.srno', 'DESC')->get();
        } elseif ($cari6 == 1) {
            $dtstylee = $dtstyle2->orderBy('status.stsupdt1', 'DESC')->orderBy('status.dateupdt', 'DESC')->orderBy('request.srno', 'DESC')->get();
        } elseif ($cari7 == 1) {
            $dtstylee = $dtstyle2->orderBy('jadwal.shipdate', 'DESC')->orderBy('request.srno', 'DESC')->get();
        } else {
            $dtstylee = $dtstyle2->orderBy('status.date', 'DESC')->orderBy('request.srno', 'DESC')->get();
        }

        $result = array();

        $dtstyle2b = $dtstylee->count();

        $result['total'] = $dtstyle2b;
        $row = array();

        foreach ($dtstylee as $d) {
            $dprogrespk = $d->progrespk;
            $dprogresnm = $d->progresnm;
            $dstatuspk = $d->statuspk;
            $style1 = $d->style;
            $style = Str::limit($style1, 20);
            $buyer1 = $d->buyernm;
            $buyer = Str::limit($buyer1, 10);
            $season1 = $d->season;
            $season = Str::limit($season1, 11);
            $opsi = $d->opsi;

            if ($d->stagepk == 0) {
                $stagenm1 = $d->stagenm;
                $stagenm = Str::limit($d->stagenm, 14) . "<br>";
            } else if ($d->stagepk == 8) {
                $stagenm1 = $d->samplenm;
                $stagenm = Str::limit($d->samplenm, 14) . "<br>";
            } else {
                $stagenm1 = $d->stagenm;
                $stagenm = $d->stagenm;
            }

            $dtmat = DB::table('material')
                ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
                ->where('material.statuspk', '=', $dstatuspk)
                ->count();

            $dtmat2 = DB::table('material')
                ->leftJoin('status', 'status.statuspk', '=', 'material.statuspk')
                ->where('material.statuspk', '=', $dstatuspk)
                ->where('material.cek', '=', 1)
                ->count();

            $sr = DB::table('status')
                ->where('srpk', '=', $srpk)
                ->where('opsi', '=', "C")
                ->first();

            // $d=DB::table('jadwal')
            // ->leftJoin('status', 'status.statuspk', '=', 'jadwal.statuspk')
            // ->select('jadwal.jadwalpk', DB::raw('DATE_FORMAT(jadwal.receiptdate, "%d %M %Y") as receiptdate'),
            // DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %M %Y") as finishsample'),
            // DB::raw('DATE_FORMAT(jadwal.startsample,"%d %M %Y") as startsample'),
            // DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %M %Y") as cutdate'),
            // DB::raw('DATE_FORMAT(jadwal.matdate,"%d %M %Y") as matdate'),
            // DB::raw('DATE_FORMAT(jadwal.trimdate,"%d %M %Y") as trimdate'),
            // DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %M %Y") as shipdate'))
            // ->where('jadwal.statuspk','=',$dstatuspk)
            // ->get(); 

            if ($d->date == "" || $d->date == "0000-00-00") {
                $date = "--";
            } else {
                $date = $d->date;
            } //$date= $date1->created_at->isoFormat('D MMM Y'); }

            if ($d->cekdate == "" || $d->cekdate == "0000-00-00") {
                $cekdate = "--";
            } else {
                $cekdate = $d->cekdate;
            }

            if ($d->trimdate == "" || $d->trimdate == "0000-00-00") {
                $trimdate = "--";
            } else {
                $trimdate = $d->trimdate;
            }

            if ($d->matdate == "" || $d->matdate == "0000-00-00") {
                $matdate = "--";
            } else {
                $matdate = $d->matdate;
            }

            if ($d->cutdate == "" || $d->cutdate == "0000-00-00") {
                $cutdate = "--";
            } else {
                $cutdate = $d->cutdate;
            }

            if ($d->startsample == "" || $d->startsample == "0000-00-00") {
                $sewdate = "--";
            } else {
                $sewdate = $d->startsample;
            }

            if ($d->finishsample == "" || $d->finishsample == "0000-00-00") {
                $findate = "--";
            } else {
                $findate = $d->finishsample;
            }

            if ($d->receiptdate == "" || $d->receiptdate == "0000-00-00") {
                $recdate = "--";
            } else {
                $recdate = $d->receiptdate;
            }

            if ($d->shipdate == "" || $d->shipdate == "0000-00-00") {
                $shipdate = "--";
            } else {
                $shipdate = $d->shipdate;
            }

            if ($d->pausedate == "" || $d->pausedate == "0000-00-00") {
                $pausedate = "--";
            } else {
                $pausedate = $d->pausedate;
            }

            if ($d->realdate == "" || $d->realdate == "0000-00-00") {
                $realdate = "--";
            } else {
                $realdate = $d->realdate;
            }

            if ($dprogrespk == 1) {
                $dateprog = $date;
            } elseif ($dprogrespk == 2) {
                $dateprog = $trimdate;
            } elseif ($dprogrespk == 3) {
                $dateprog = $cutdate;
            } elseif ($dprogrespk == 4) {
                $dateprog = $sewdate;
            } elseif ($dprogrespk == 5) {
                $dateprog = $findate;
            } elseif ($dprogrespk == 6) {
                $dateprog = $recdate;
            } elseif ($dprogrespk == 7) {
                $dateprog = $pausedate;
            } elseif ($dprogrespk == 8) {
                $dateprog = $realdate;
            } else {
                $dateprog = "--";
            }

            if ($d->pattern2 <> "") {
                $pattern = "<img src='../public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $pattern = "<img src='../public/css/images/abu.png' width='15px' height='15px'>";
            }

            if ($d->yy <> "") {
                $yy = "<img src='../public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $yy = "<img src='../public/css/images/abu.png' width='15px' height='15px'>";
            }

            if ($d->sam <> "") {
                $sam = "<img src='../public/css/images/hijau.png' width='15px' height='15px'>";
            } else {
                $sam = "<img src='../public/css/images/abu.png' width='15px' height='15px'>";
            }

            $gambar = $dtstyle->foto;
            $folderPath = public_path('foto');

            if ($gambar && file_exists($folderPath . '/' . $gambar)) {
                $hasil_gambar = "<img src='public/foto/$gambar' width='90px' height='90px'>";
            } else {
                $hasil_gambar = "<img src='public/css/no-img.png' width='90px' height='90px'>";
            }

            if ($d->reject > 0) {
                $foto = "<div style='border-top:1px solid #FF6B00; border-left: 1px solid #FF6B00; border-bottom:1px solid #FF6B00; border-top-left-radius:8px; border-bottom-left-radius:8px; width:120%; height:125px;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;color: #FF6B00;'>
                <span style='float:left; margin-left:3%;'>R1</span><br>
                $hasil_gambar
                </div>";
                if ($d->opsi != '') {
                    $style = "<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; width:130%; height:125px; margin-left:-8%;'>
                                <br><br>
                                <div style='font-family: Arial;font-style: normal;font-size: 16px;line-height: 20px; margin-left:5%;'> 
                                <span>SR $d->srno [$opsi]</span><br>
                                <a href='javascript:void(0)' style='font-weight: 700;text-decoration:none; color:#000000;cursor:default;' title='$style1' class='easyui-tooltip'>$style</a>
                                <a class='easyui-tooltip' href='javascript:void(0)' style='text-decoration:none; color:#359DD9;cursor:default;' title='$d->description'>(i)</a>
                                </div>
                                <div style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; margin-left:5%;'> 
                                <a href='javascript:void(0)' style='text-decoration:none; color:#000000;cursor:default;' title='$buyer1, $season1' class='easyui-tooltip'>$buyer, $season</a> 
                                </div>
                                </div>";
                } else {
                    $style = "<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; width:130%; height:125px; margin-left:-8%;'>
                                <br><br>
                                <div style='font-family: Arial;font-style: normal;font-size: 16px;line-height: 20px; margin-left:5%;'> 
                                <span>SR $d->srno</span><br>
                                <a href='javascript:void(0)' style='font-weight: 700;text-decoration:none; color:#000000;cursor:default;' title='$style1' class='easyui-tooltip'>$style</a>
                                <a class='easyui-tooltip' href='javascript:void(0)' style='text-decoration:none; color:#359DD9;cursor:default;' title='$d->description'>(i)</a>
                                </div>
                                <div style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; margin-left:5%;'> 
                                <a href='javascript:void(0)' style='text-decoration:none; color:#000000;cursor:default;' title='$buyer1, $season1' class='easyui-tooltip'>$buyer, $season</a> 
                                </div>
                                </div>";
                }

                $stage = "<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; width:130%; height:125px; margin-left:-7%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
                <a href='javascript:void(0)' style='text-decoration:none;color:#000000;cursor:default;' title='$stagenm1' class='easyui-tooltip'>$stagenm</a>
                <span style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color:rgba(0, 0, 0, 0.6);'> $date </span></div>
                <div style='font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 12px; line-height: 16px; margin-left:10%;'>
                Patern $pattern
                YY $yy
                SMV $sam
                </div>
                </div>";
                $datemat = "<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; width:130%; height:125px; margin-left:-12%;'>
                <br><br>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color: rgba(0, 0, 0, 0.38);'>Material cheklist</div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dtmat2/$dtmat </div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $cekdate </div>
                </div>";
                $progres = "<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; width:130%; height:125px; margin-left:-15%''>
                <br><br>
                <div style='margin-left:3%;font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color: rgba(0, 0, 0, 0.38);'>Sample stage</div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dprogresnm </div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $dateprog </div>
                </div>";
                $plandate = "<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; width:200%; height:125px; margin-left:-10%;font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px;'>
                <br><br>
                <div style='margin-left:3%;''>exp. delivery :<br>$shipdate</div>
                </div>";
                // $act="<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; width:850%; height:125px; margin-left:-285%;'>
                // <br><br>
                // <img id='dd' src='public/css/images/titik.png' height='17px'>
                // </div>";
                // $act2="<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; border-right:1px solid #FF6B00; border-top-right-radius:8px; border-bottom-right-radius:8px; width:180%; height:125px; margin-left:-50%;'>
                // <br><br>
                // <a href='detail/$d->statuspk' style='text-decoration:none; color:#000000;'><img id='dd' src='public/css/images/icon5.png' height='17px'></a>
                // </div>";
                $act = "<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; width:305%; height:125px; margin-left:-100%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 20px;line-height: 26px;color:#000000'>&#10247;</div>
                </div>";
                $act2 = "<div style='border-top:1px solid #FF6B00; border-bottom:1px solid #FF6B00; border-right:1px solid #FF6B00; border-top-right-radius:8px; border-bottom-right-radius:8px; width:180%; height:125px; margin-left:-50%;'>
                <br><br>
                <a href='detail/$d->statuspk' style='text-decoration:none; font-family: Arial;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;color:#000000''> &#5171; </a>
                </div>";
            } else {
                if ($d->cansts > 0) {
                    $foto = "<div style='border-top:3px solid #F1F1F1; border-left: 3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; border-top-left-radius:8px; border-bottom-left-radius:8px; width:120%; height:125px;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;color:rgba(0, 0, 0, 0.38);'>
                <span style='float:left; margin-left:3%;color:#EF3B42;'>Cancel</span><br>
                $hasil_gambar
                </div>";
                    if ($d->opsi != '') {
                        $style = "<div style='color:rgba(0, 0, 0, 0.38);border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-8%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-size: 16px;line-height: 20px; margin-left:5%;'> 
                <span>SR $d->srno [$opsi]</span><br>
                <a href='javascript:void(0)' style='font-weight: 700;text-decoration:none; color:rgba(0, 0, 0, 0.38);cursor:default;' title='$style1' class='easyui-tooltip'>$style</a>
                <a class='easyui-tooltip' href='javascript:void(0)' style='text-decoration:none; color:#359DD9;cursor:default;' title='$d->description'>(i)</a>
                </div>
                <div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; margin-left:5%;'> 
                <a href='javascript:void(0)' style='text-decoration:none; color:rgba(0, 0, 0, 0.38);cursor:default;' title='$buyer1, $season1' class='easyui-tooltip'>$buyer, $season</a> 
                </div>
                </div>";
                    } else {
                        $style = "<div style='color:rgba(0, 0, 0, 0.38);border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-8%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-size: 16px;line-height: 20px; margin-left:5%;'> 
                <span>SR $d->srno</span><br>
                <a href='javascript:void(0)' style='font-weight: 700;text-decoration:none; color:rgba(0, 0, 0, 0.38);cursor:default;' title='$style1' class='easyui-tooltip'>$style</a>
                <a class='easyui-tooltip' href='javascript:void(0)' style='text-decoration:none; color:#359DD9;cursor:default;' title='$d->description'>(i)</a>
                </div>
                <div style='color:rgba(0, 0, 0, 0.38);font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; margin-left:5%;'> 
                <a href='javascript:void(0)' style='text-decoration:none; color:rgba(0, 0, 0, 0.38);cursor:default;' title='$buyer1, $season1' class='easyui-tooltip'>$buyer, $season</a> 
                </div>
                </div>";
                    }
                    $stage = "<div style='color:rgba(0, 0, 0, 0.38);border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-7%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
                <a href='javascript:void(0)' style='text-decoration:none;color:rgba(0, 0, 0, 0.38);cursor:default;' title='$stagenm1' class='easyui-tooltip'>$stagenm</a>
                <span style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color:rgba(0, 0, 0, 0.6);'> $date </span></div>
                <div style='font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 12px; line-height: 16px; margin-left:10%;'>
                Patern $pattern
                YY $yy
                SMV $sam
                </div>
                </div>";
                    $datemat = "<div style='color:rgba(0, 0, 0, 0.38);border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-12%;'>
                <br><br>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color: rgba(0, 0, 0, 0.38);'>Material cheklist</div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dtmat2/$dtmat </div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $cekdate </div>
                </div>";
                    $progres = "<div style='color:rgba(0, 0, 0, 0.38);border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-15%''>
                <br><br>
                <div style='margin-left:3%;font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color: rgba(0, 0, 0, 0.38);'>Sample stage</div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dprogresnm </div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $dateprog </div>
                </div>";
                    $plandate = "<div style='color:rgba(0, 0, 0, 0.38);border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:200%; height:125px; margin-left:-10%;font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px;'>
                <br><br>
                <div style='margin-left:3%;''>exp. delivery :<br>$shipdate</div>
                </div>";
                    // $act="<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:850%; height:125px; margin-left:-285%;'>
                    // <br><br>
                    // <img id='dd' src='public/css/images/titik.png' height='17px'>
                    // </div>";
                    // $act2="<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; border-right:3px solid #F1F1F1; border-top-right-radius:8px; border-bottom-right-radius:8px; width:180%; height:125px; margin-left:-50%;'>
                    // <br><br>
                    // <a href='detail/$d->statuspk' style='text-decoration:none; color:#000000;'><img id='dd' src='public/css/images/icon5.png' height='17px'></a>
                    // </div>";
                    $act = "<div style='color:rgba(0, 0, 0, 0.38);border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:305%; height:125px; margin-left:-100%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 20px;line-height: 26px;color:rgba(0, 0, 0, 0.38);'>&#10247;</div>
                </div>";
                    $act2 = "<div style='color:rgba(0, 0, 0, 0.38);border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; border-right:3px solid #F1F1F1; border-top-right-radius:8px; border-bottom-right-radius:8px; width:180%; height:125px; margin-left:-50%;'>
                <br><br>
                <span style='text-decoration:none; font-family: Arial;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;color:rgba(0, 0, 0, 0.38);'> &#5171; </span>
                </div>";
                } else {
                    $foto = "<div style='border-top:3px solid #F1F1F1; border-left: 3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; border-top-left-radius:8px; border-bottom-left-radius:8px; width:120%; height:125px;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;color: #FF6B00;'>
                <br>
                $hasil_gambar
                </div>";
                    if ($d->opsi != '') {
                        $style = "<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-8%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-size: 16px;line-height: 20px; margin-left:5%;'> 
                <span>SR $d->srno [$d->opsi]</span><br>
                <a href='javascript:void(0)' style='font-weight: 700;text-decoration:none; color:#000000;cursor:default;' title='$style1' class='easyui-tooltip'>$style</a>
                <a class='easyui-tooltip' href='javascript:void(0)' style='text-decoration:none; color:#359DD9;cursor:default;' title='$d->description'>(i)</a>
                </div>
                <div style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; margin-left:5%;'> 
                <a href='javascript:void(0)' style='text-decoration:none; color:#000000;cursor:default;' title='$buyer1, $season1' class='easyui-tooltip'>$buyer, $season</a> 
                </div>
                </div>";
                    } else {
                        $style = "<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-8%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-size: 16px;line-height: 20px; margin-left:5%;'> 
                <span>SR $d->srno</span><br>
                <a href='javascript:void(0)' style='font-weight: 700;text-decoration:none; color:#000000;cursor:default;' title='$style1' class='easyui-tooltip'>$style</a>
                <a class='easyui-tooltip' href='javascript:void(0)' style='text-decoration:none; color:#359DD9;cursor:default;' title='$d->description'>(i)</a>
                </div>
                <div style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; margin-left:5%;'> 
                <a href='javascript:void(0)' style='text-decoration:none; color:#000000;cursor:default;' title='$buyer1, $season1' class='easyui-tooltip'>$buyer, $season</a> 
                </div>
                </div>";
                    }
                    $stage = "<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-7%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 16px;line-height: 20px;'> 
                <a href='javascript:void(0)' style='text-decoration:none;color:#000000;cursor:default;' title='$stagenm1' class='easyui-tooltip'>$stagenm</a>
                <span style='font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color:rgba(0, 0, 0, 0.6);'> $date </span></div>
                <div style='font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 12px; line-height: 16px; margin-left:10%;'>
                Patern $pattern
                YY $yy
                SMV $sam
                </div>
                </div>";
                    $datemat = "<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-12%;'>
                <br><br>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color: rgba(0, 0, 0, 0.38);'>Material cheklist</div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dtmat2/$dtmat </div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $cekdate </div>
                </div>";
                    $progres = "<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:130%; height:125px; margin-left:-15%''>
                <br><br>
                <div style='margin-left:3%;font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;color: rgba(0, 0, 0, 0.38);'>Sample stage</div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;'> $dprogresnm </div>
                <div style='margin-left:3%; font-family: Arial;font-style: normal;font-weight: 400;font-size: 12px;line-height: 16px;'> $dateprog </div>
                </div>";
                    $plandate = "<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:200%; height:125px; margin-left:-10%;font-family: Arial;font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px;'>
                <br><br>
                <div style='margin-left:3%;''>exp. delivery :<br>$shipdate</div>
                </div>";
                    // $act="<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:850%; height:125px; margin-left:-285%;'>
                    // <br><br>
                    // <img id='dd' src='public/css/images/titik.png' height='17px'>
                    // </div>";
                    // $act2="<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; border-right:3px solid #F1F1F1; border-top-right-radius:8px; border-bottom-right-radius:8px; width:180%; height:125px; margin-left:-50%;'>
                    // <br><br>
                    // <a href='detail/$d->statuspk' style='text-decoration:none; color:#000000;'><img id='dd' src='public/css/images/icon5.png' height='17px'></a>
                    // </div>";
                    $act = "<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; width:305%; height:125px; margin-left:-100%;'>
                <br><br>
                <div style='font-family: Arial;font-style: normal;font-weight: 700;font-size: 20px;line-height: 26px;color:#000000'>&#10247;</div>
                </div>";
                    $act2 = "<div style='border-top:3px solid #F1F1F1; border-bottom:3px solid #F1F1F1; border-right:3px solid #F1F1F1; border-top-right-radius:8px; border-bottom-right-radius:8px; width:180%; height:125px; margin-left:-50%;'>
                <br><br>
                <a href='detail/$d->statuspk' style='text-decoration:none; font-family: Arial;font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;color:#000000''> &#5171; </a>
                </div>";
                }
            }
            $row[] = array(
                // 'no'=>$k,
                'statuspk' => $d->statuspk,
                'jadwalpk' => $d->jadwalpk,
                'reject' => $d->reject,
                'cansts' => $d->cansts,
                'foto' => $foto,
                'style' => $style,
                'dstyle' => $d->style,
                'stage' => $stage,
                'datemat' => $datemat,
                'progres' => $progres,
                'plandate' => $plandate,
                'act' => $act,
                'act2' => $act2,
                'opsi' => $opsi,
                'sr' => $sr,
                'userpk' => $d->userpk,
                'trimdate' => $d->trimdate,
                'cutdate' => $d->cutdate
            );
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function notifpop2(Request $request)
    {

        $semua = DB::table('memo')
            ->orderBy('date1', 'DESC')
            ->get();

        return json_encode($semua);
    }

    public function opsi($statuspk)
    {
        $srpk_ambil = DB::table('status')
            ->select('*')
            ->where('statuspk', $statuspk)
            ->first();

        $opsi_terakhir = DB::table('status')
            ->select('opsi')
            ->where('srpk', '=', $srpk_ambil->srpk)
            ->orderBy('opsi', 'desc')
            ->first();

        if ($opsi_terakhir->opsi == "" || $opsi_terakhir->opsi == null) {
            $Jadikan = "A";

            DB::table('status')
                ->where('statuspk', $statuspk)
                ->update([
                    'opsi' => $Jadikan,
                    'dateupdt' => NOW()
                ]);
        } else if ($opsi_terakhir->opsi == "B") {
            $Jadikan = "C";
        }

        $data = DB::table('status')
            ->orderBy('statuspk', 'desc')
            ->limit('1')
            ->get();

        foreach ($data as $da) {
            $da1 = $da->statuspk;
            $da2 = $da1 + 1;
        }

        $da3 = $da2;

        $dtstatus = DB::table('status')
            ->select(
                'statuspk',
                'yy',
                'sam',
                'techpack',
                'pattern',
                'foto',
                'samplenm',
                'style',
                'season',
                'buyerpk2',
                'userpk',
                'opsi',
                'ketwash',
                'mobtape',
                'topstitch',
                'overlock',
                'elastic',
                'foto2',
                'foto3',
                'construction',
                'cw',
                'art',
                'artwork',
                'bobin',
                'simpan',
                'reject',
                'category',
                'aktif',
                'srpk',
                'progrespk',
                'stagepk',
                'description',
                'blockpatern',
                'date',
                'washing',
                'shipdate2'
            )
            ->where('statuspk', '=', $statuspk)
            ->get();

        //tambahan baru estimate
        $TrimCard = 1;
        $Pattern = 1;
        $Cutting = 1;
        $Embro = 0;
        $Sewing = 1;
        $Stitching = 0;
        $Washing = 0;
        $Finishing = 1;
        $HandOver = 1;

        $ForTrimCard = $TrimCard;
        $DateTrimCard = Carbon::now()->addDays($ForTrimCard);
        if ($DateTrimCard->isSunday()) $DateTrimCard->modify('+1 day');

        $ForPattern = $Pattern;
        $DatePattern = Carbon::parse($DateTrimCard)->addDays($ForPattern);
        if ($DatePattern->isSunday()) $DatePattern->modify('+1 day');

        $ForCutting = $Cutting;
        $DateCutting = Carbon::parse($DatePattern)->addDays($ForCutting);
        if ($DateCutting->isSunday()) $DateCutting->modify('+1 day');

        $ForEmbPrint = $Embro;
        $DateEmbPrint = Carbon::parse($DateCutting)->addDays($ForEmbPrint);
        if ($DateEmbPrint->isSunday()) $DateEmbPrint->modify('+1 day');

        $forSewing = $Sewing;
        $DateSewing = Carbon::parse($DateEmbPrint)->addDays($forSewing);
        if ($DateSewing->isSunday()) $DateSewing->modify('+1 day');

        $forStitching = $Stitching;
        $DateStitching = Carbon::parse($DateSewing)->addDays($forStitching);
        if ($DateStitching->isSunday()) $DateStitching->modify('+1 day');

        $forWash = $Washing;
        $DateWash = Carbon::parse($DateStitching)->addDays($forWash);
        if ($DateWash->isSunday()) $DateWash->modify('+1 day');

        $ForFinishing = $Finishing;
        $DateFinishing = Carbon::parse($DateWash)->addDays($ForFinishing);
        if ($DateFinishing->isSunday()) $DateFinishing->modify('+1 day');

        $ForHandOver = $HandOver;
        $DateHandOver = Carbon::parse($DateFinishing)->addDays($ForHandOver);
        if ($DateHandOver->isSunday()) $DateHandOver->modify('+1 day');

        foreach ($dtstatus as $ds) {

            if ($opsi_terakhir->opsi == "" || $opsi_terakhir->opsi == null) {
                DB::table('status')->insert([
                    'statuspk' => $da3,
                    'srpk' => $ds->srpk,
                    'userpk' => $ds->userpk,
                    'buyerpk2' => $ds->buyerpk2,
                    'progrespk' => $ds->progrespk,
                    'stagepk' => $ds->stagepk,
                    'samplenm' => $ds->samplenm,
                    'opsi' => "B",
                    'season' => $ds->season,
                    'style' => $ds->style,
                    'description' => $ds->description,
                    'blockpatern' => $ds->blockpatern,
                    'date' => $ds->date,
                    'washing' => $ds->washing,
                    'ketwash' => $ds->ketwash,
                    'mobtape' => $ds->mobtape,
                    'topstitch' => $ds->topstitch,
                    'overlock' => $ds->overlock,
                    'elastic' => $ds->elastic,
                    'foto' => $ds->foto,
                    'foto2' => $ds->foto2,
                    'foto3' => $ds->foto3,
                    'construction' => $ds->construction,
                    'cw' => $ds->cw,
                    'art' => $ds->art,
                    'artwork' => $ds->artwork,
                    'bobin' => $ds->bobin,
                    'shipdate2' => $ds->shipdate2,
                    'techpack' => $ds->techpack,
                    'pattern' => $ds->pattern,
                    'simpan' => $ds->simpan,
                    'category' => $ds->category,
                    'aktif' => $ds->aktif,
                    'dateupdt' => NOW()
                ]);

                $dtsize = DB::table('size')
                    ->select('statuspk', 'sizepk', 'size', 'qty', 'cw', 'stsyy')
                    ->where('statuspk', '=', $statuspk)
                    ->get();

                foreach ($dtsize as $dtz) {
                    DB::table('size')->insert([
                        'statuspk' => $da3,
                        'size' => $dtz->size,
                        'qty' => $dtz->qty,
                        'cw' => $dtz->cw,
                        'stsyy' => $dtz->stsyy
                    ]);
                }

                $dtmaterial = DB::table('material')
                    ->select(
                        'matpk',
                        'srpk',
                        'statuspk',
                        'suppk',
                        'matnm',
                        'color',
                        'used',
                        'remark',
                        'status',
                        'spec',
                        'colcode',
                        'qty',
                        'price',
                        'curpk',
                        'size',
                        'unit',
                        'teeth',
                        'slider',
                        'stopper',
                        'puler',
                        'stopper',
                        'puler',
                        'kelnm',
                        'grup',
                        'cek',
                        'meff',
                        'myy',
                        'garmentcw',
                        'placement',
                        'cekdate',
                        'cttmarker',
                        'dateinput'
                    )
                    ->where('statuspk', '=', $statuspk)
                    ->get();

                foreach ($dtmaterial as $dtm) {
                    DB::table('material')->insert([
                        'statuspk' => $da3,
                        'srpk' => $dtm->srpk,
                        'suppk' => $dtm->suppk,
                        'matnm' => $dtm->matnm,
                        'color' => $dtm->color,
                        'used' => $dtm->used,
                        'status' => $dtm->status,
                        'spec' => $dtm->spec,
                        'colcode' => $dtm->colcode,
                        'qty' => $dtm->qty,
                        'price' => $dtm->price,
                        'curpk' => $dtm->curpk,
                        'size' => $dtm->size,
                        'unit' => $dtm->unit,
                        'teeth' => $dtm->teeth,
                        'slider' => $dtm->slider,
                        'stopper' => $dtm->stopper,
                        'puler' => $dtm->puler,
                        'kelnm' => $dtm->kelnm,
                        'grup' => $dtm->grup,
                        'cek' => $dtm->cek,
                        'meff' => $dtm->meff,
                        'myy' => $dtm->myy,
                        'garmentcw' => $dtm->garmentcw,
                        'placement' => $dtm->placement,
                        'cekdate' => $dtm->cekdate,
                        'cttmarker' => $dtm->cttmarker,
                        'dateinput' => $dtm->dateinput,
                    ]);
                }

                $dtjadwal = DB::table('jadwal')
                    ->select(
                        'jadwalpk',
                        'progcut',
                        'progsew',
                        'progqa',
                        'preject',
                        'shipdate',
                        'receiptdate',
                        'finishsample',
                        'startsample',
                        'cutdate',
                        'cutdate2',
                        'trimdate',
                        'emblesdate2',
                        'stichdate',
                        'washdate2',
                        'patterndate2',
                        'trimdate2',
                        'startsample2',
                        'finishsample2',
                        'receiptdate2'
                    )
                    ->where('statuspk', '=', $statuspk)
                    ->get();

                // TANDA UBAH NABILA 
                foreach ($dtjadwal as $dtj) {
                    DB::table('jadwal')->insert([
                        //ini estimate baru yaw
                        'statuspk' => $da3,
                        'cutdate2' => $DateCutting,
                        'emblesdate2' => $DateEmbPrint,
                        'stichdate' => $DateStitching,
                        'washdate2' => $DateWash,
                        'patterndate2' => $DatePattern,
                        'trimdate2' => $DateTrimCard,
                        'startsample2' => $DateSewing,
                        'finishsample2' => $DateFinishing,
                        'receiptdate2' => $DateHandOver,
                    ]);
                }

                $memo = DB::table('memo')
                    ->where('statuspk', $da3)
                    ->get();

                DB::table('memo')->insert([
                    'statuspk' => $da3,
                    'jenis' => "ADD",
                    'date1' => NOW()
                ]);
            } else if ($opsi_terakhir->opsi == "B") {
                DB::table('status')->insert([
                    'statuspk' => $da3,
                    'srpk' => $ds->srpk,
                    'userpk' => $ds->userpk,
                    'buyerpk2' => $ds->buyerpk2,
                    'progrespk' => $ds->progrespk,
                    'stagepk' => $ds->stagepk,
                    'samplenm' => $ds->samplenm,
                    'opsi' => $Jadikan,
                    'season' => $ds->season,
                    'style' => $ds->style,
                    'description' => $ds->description,
                    'blockpatern' => $ds->blockpatern,
                    'date' => $ds->date,
                    'washing' => $ds->washing,
                    'ketwash' => $ds->ketwash,
                    'mobtape' => $ds->mobtape,
                    'topstitch' => $ds->topstitch,
                    'overlock' => $ds->overlock,
                    'elastic' => $ds->elastic,
                    'foto' => $ds->foto,
                    'foto2' => $ds->foto2,
                    'foto3' => $ds->foto3,
                    'construction' => $ds->construction,
                    'cw' => $ds->cw,
                    'art' => $ds->art,
                    'artwork' => $ds->artwork,
                    'bobin' => $ds->bobin,
                    'shipdate2' => $ds->shipdate2,
                    'techpack' => $ds->techpack,
                    'pattern' => $ds->pattern,
                    'simpan' => $ds->simpan,
                    'category' => $ds->category,
                    'aktif' => $ds->aktif,
                    'dateupdt' => NOW()
                ]);

                $dtsize = DB::table('size')
                    ->select('statuspk', 'sizepk', 'size', 'qty', 'cw', 'stsyy')
                    ->where('statuspk', '=', $statuspk)
                    ->get();

                foreach ($dtsize as $dtz) {
                    DB::table('size')->insert([
                        'statuspk' => $da3,
                        'size' => $dtz->size,
                        'qty' => $dtz->qty,
                        'cw' => $dtz->cw,
                        'stsyy' => $dtz->stsyy
                    ]);
                }

                $dtmaterial = DB::table('material')
                    ->select(
                        'matpk',
                        'srpk',
                        'statuspk',
                        'suppk',
                        'matnm',
                        'color',
                        'used',
                        'remark',
                        'status',
                        'spec',
                        'colcode',
                        'qty',
                        'price',
                        'curpk',
                        'size',
                        'unit',
                        'teeth',
                        'slider',
                        'stopper',
                        'puler',
                        'stopper',
                        'puler',
                        'kelnm',
                        'grup',
                        'cek',
                        'meff',
                        'myy',
                        'garmentcw',
                        'placement',
                        'cekdate',
                        'cttmarker',
                        'dateinput'
                    )
                    ->where('statuspk', '=', $statuspk)
                    ->get();

                foreach ($dtmaterial as $dtm) {
                    DB::table('material')->insert([
                        'statuspk' => $da3,
                        'srpk' => $dtm->srpk,
                        'suppk' => $dtm->suppk,
                        'matnm' => $dtm->matnm,
                        'color' => $dtm->color,
                        'used' => $dtm->used,
                        'status' => $dtm->status,
                        'spec' => $dtm->spec,
                        'colcode' => $dtm->colcode,
                        'qty' => $dtm->qty,
                        'price' => $dtm->price,
                        'curpk' => $dtm->curpk,
                        'size' => $dtm->size,
                        'unit' => $dtm->unit,
                        'teeth' => $dtm->teeth,
                        'slider' => $dtm->slider,
                        'stopper' => $dtm->stopper,
                        'puler' => $dtm->puler,
                        'kelnm' => $dtm->kelnm,
                        'grup' => $dtm->grup,
                        'cek' => $dtm->cek,
                        'meff' => $dtm->meff,
                        'myy' => $dtm->myy,
                        'garmentcw' => $dtm->garmentcw,
                        'placement' => $dtm->placement,
                        'cekdate' => $dtm->cekdate,
                        'cttmarker' => $dtm->cttmarker,
                        'dateinput' => $dtm->dateinput,
                    ]);
                }

                $dtjadwal = DB::table('jadwal')
                    ->select(
                        'jadwalpk',
                        'progcut',
                        'progsew',
                        'progqa',
                        'preject',
                        'shipdate',
                        'receiptdate',
                        'finishsample',
                        'startsample',
                        'cutdate',
                        'cutdate2',
                        'trimdate',
                        'emblesdate2',
                        'stichdate',
                        'washdate2',
                        'patterndate2',
                        'trimdate2',
                        'startsample2',
                        'finishsample2',
                        'receiptdate2'
                    )
                    ->where('statuspk', '=', $statuspk)
                    ->get();

                // TANDA UBAH NABILA 
                foreach ($dtjadwal as $dtj) {
                    DB::table('jadwal')->insert([
                        //ini estimate baru yaw
                        'statuspk' => $da3,
                        'cutdate2' => $DateCutting,
                        'emblesdate2' => $DateEmbPrint,
                        'stichdate' => $DateStitching,
                        'washdate2' => $DateWash,
                        'patterndate2' => $DatePattern,
                        'trimdate2' => $DateTrimCard,
                        'startsample2' => $DateSewing,
                        'finishsample2' => $DateFinishing,
                        'receiptdate2' => $DateHandOver,
                    ]);
                }

                $memo = DB::table('memo')
                    ->where('statuspk', $da3)
                    ->get();

                DB::table('memo')->insert([
                    'statuspk' => $da3,
                    'jenis' => "ADD",
                    'date1' => NOW()
                ]);
            }
        }

        return redirect()->back();
    }

    public function dtsamplefloor(Request $request)
    {
        $tahun = date("Y");
        $reqAll = $request->all();
        $searchByInput = $request->searchByInput;
        $filterByYear = $request->filterByYear;

        if (!empty($filterByYear)) $tahun = $filterByYear;

        $page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->leftJoin('material', 'material.statuspk', '=', 'status.statuspk')
            ->select(
                'status.artwork',
                'status.cansts',
                'status.category',
                'status.cekdate',
                'status.date',
                'status.dateupdt',
                'status.description',
                'status.foto',
                'status.grouppk',
                'status.opsi',
                'status.pattern',
                'status.pattern2',
                'status.paused',
                'status.progrespk',
                'status.reject',
                'status.sam',
                'status.samplenm',
                'status.season',
                'status.srpk',
                'status.stagepk',
                'stage.stagenm',
                'status.statuspk',
                'status.stsupdt2',
                'status.style',
                'status.techpack',
                'status.washing',
                'status.yy',
                'progres.progresnm',
                'group.groupnm',
                'jadwal.jadwalpk',
                'jadwal.receiptdate',
                'jadwal.finishsample',
                'jadwal.startsample',
                'jadwal.cutdate',
                'jadwal.matdate',
                'jadwal.trimdate',
                'jadwal.shipdate',
                'jadwal.pausedate',
                'jadwal.realdate',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srno',
                'material.matpk',
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d %b") as finishsampleFormatted'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %b") as startsampleFormatted'),
                DB::raw('SUM(size.qty) as qty')
            )
            ->where('status.buyerpk2', '>', 0)
            ->where('status.userpk', '>', 0)
            ->where('status.cansts', '=', 0)
            ->where('status.aktif', '=', 0)
            ->whereYEAR('status.date', $tahun)
            ->whereNotNull('status.grouppk')
            ->where(function ($query) {
                $query->whereNotNull('jadwal.startsample')
                    ->orWhere('jadwal.startsample', '!=', '0000-00-00');
            })
            ->orderBy('status.grouppk', 'ASC');

        if (!empty($searchByInput)) {
            $dtstyle = $dtstyle->where(DB::raw("CONCAT(request.srno,'',status.style,'',buyer.buyernm,'',status.season)"), 'like', '%' . $searchByInput . '%');
        }

        $dtstyleByGroup = $dtstyle->groupBy('group.grouppk')->get();
        $dtstyleAll = $dtstyle->groupBy('status.statuspk')->get();

        $arrgroup = array();
        foreach ($dtstyleByGroup as $itemDtstyleByGroup) {
            array_push($arrgroup, $itemDtstyleByGroup->grouppk);
        }

        $dtgroupAll = group::all();

        if (count($arrgroup) < count($dtgroupAll)) {
            $dtgroup = group::whereNotIn('grouppk', $arrgroup)->get();

            $dtstyleAll = $dtstyleAll->merge($dtgroup)->sortBy('groupnm');
        }

        $offset = ($page - 1) * $rows;
        $dtstyle = $dtstyle->skip($offset)->take($rows);

        $result = array();

        $dtstyleAllCount = $dtstyleAll->count();

        $result['total'] = $dtstyleAllCount;
        $result['reqAll'] = $reqAll;

        $row = array();

        foreach ($dtstyleAll as $d) {
            $sizes = DB::table('size')
                ->where('statuspk', $d->statuspk)
                ->get();

            $qty = DB::table('size')
                ->leftJoin('status', 'status.statuspk', '=', 'size.statuspk')
                ->select(DB::raw('SUM(size.qty) as qty'))
                ->where('size.statuspk', $d->statuspk)
                ->first();

            if (empty($grouppk)) $grouppk = 0;
            $team = "";
            $upcoming = "";
            if (!empty($d->grouppk) && $d->grouppk != $grouppk) {
                $team = "<span style='font-weight:700;'>$d->groupnm</span>";
                $grouppk = $d->grouppk;

                $dtupcoming = DB::table('status')
                    ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
                    ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
                    ->select(
                        'status.opsi',
                        'request.srno',
                        DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %b") as shipdate')
                    )
                    ->where('status.buyerpk2', '>', 0)
                    ->where('status.userpk', '>', 0)
                    ->where('status.cansts', '=', 0)
                    ->where('status.aktif', '=', 0)
                    ->where('status.grouppk', $d->grouppk)
                    ->whereYEAR('status.date', $tahun)
                    ->whereNotNull('status.grouppk')
                    ->where(function ($query) {
                        $query->whereNull('jadwal.startsample')
                            ->orWhere('jadwal.startsample', '=', '0000-00-00');
                    })
                    ->orderBy('status.grouppk', 'ASC')
                    ->groupBy('status.statuspk')
                    ->get();

                $upcoming_text = "";
                if ($dtupcoming->count() > 0) {
                    if ($dtupcoming->count() > 1) {
                        $upcoming_text = "";
                        $sr_text_arrays = [];
                        foreach ($dtupcoming as $dtup) {
                            $upcoming_text =  "SR " . $dtup->srno;
                            if (!empty($dtup->opsi)) $upcoming_text = $upcoming_text . "[" . $dtup->opsi . "]";
                            $upcoming_text = $upcoming_text  . " (S:" . $dtup->shipdate . ")";
                            array_push($sr_text_arrays, $upcoming_text);
                        }
                        for ($i = 0; $i < count($sr_text_arrays); $i++) {
                            if ($i != 0) {
                                $upcoming .= ", ";
                            }
                            if ($i != 0 && $i % 2 == 0) {
                                $upcoming .= "<br>";
                            }
                            $upcoming .= $sr_text_arrays[$i];
                        }
                    } else {
                        $upcoming_text = "SR " . $dtupcoming[0]->srno;
                        if (!empty($dtupcoming[0]->opsi)) $upcoming_text .= "[" . $dtupcoming[0]->opsi . "]";
                        $upcoming_text .= " (S:" . $dtupcoming[0]->shipdate . ")";
                        $upcoming = $upcoming_text;
                    }
                }
            }

            $colors = "";
            if ($sizes->count() > 0) $colors = $sizes->count() . " col";

            $sewdate = "";
            if (!empty($d->startsample) && $d->startsample != "0000-00-00") $sewdate = $d->startsampleFormatted;

            $findate = "";
            if (!empty($d->finishsample) && $d->finishsample != "0000-00-00") $findate = $d->finishsampleFormatted;

            $sr_text = $d->srno ? "SR $d->srno" : "";
            if (!empty($d->opsi)) $sr_text .= " [$d->opsi]";

            $text_emb = '';
            $text_embs = array();
            if ($d->washing == 1) array_push($text_embs, 'W');
            if (!empty($d->artwork)) {
                $artworks = explode(",", $d->artwork);
                foreach ($artworks as $artwork) {
                    if (trim($artwork, ' ') === "HT" || trim($artwork, ' ') === "Heat Transfer") array_push($text_embs, 'H');
                    if (trim($artwork, ' ') === "Embrodery") array_push($text_embs, 'E');
                    if (trim($artwork, ' ') === "Printing") array_push($text_embs, 'P');
                }
            }
            if (count($text_embs) > 0) $text_emb = implode(",", $text_embs);
            $emb = "<span>$text_emb</span>";

            $row[] = array(
                'statuspk' => $d->statuspk,
                'grouppk' => $d->grouppk,
                'buyerpk' => $d->buyerpk,
                'stagepk' => $d->stagepk,
                'jadwalpk' => $d->jadwalpk,
                'progrespk' => $d->progrespk,
                'buyernm' => $d->buyernm,
                'groupnm' => $d->groupnm,
                'srpk' => $d->srpk,
                'reject' => $d->reject,
                'cansts' => $d->cansts,
                'stsupdt' => $d->stsupdt2,
                'trimdate' => $d->trimdate,
                'cutdate' => $d->cutdate,
                'paused' => $d->paused,
                'material' => $d->matpk,
                'emb' => $emb,
                'team' => $team,
                'sr_text' => $sr_text,
                'qty' => $qty->qty,
                'category' => $d->category,
                'colors' => $colors,
                'sewdate' => $sewdate,
                'findate' => $findate,
                'upcoming' => $upcoming,
                'upcoming_text' => $upcoming_text,
            );
        }

        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }


    public function displaymdleft()
    {
        $nowd = Carbon::now();
        $ambilbulan = $nowd->format('m');
        // $ambiltahun = $nowd->format('Y');

        $results = MemoHelper::getMemo($ambilbulan);
        $semua_all = MemoHelper::getAllMemo($ambilbulan);
        $notip = MemoHelper::getAllNotip();

        $tahun = date("Y");
        $GroupLeft = [1, 2, 3, 4, 5, 6];
        // $GroupLeft = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
        $ProgresID = [4, 5];


        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            // ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->select(
                'status.cansts',
                'status.category',
                'status.date',
                'status.grouppk',
                'status.opsi',
                'status.progrespk',
                'status.samplenm',
                'status.season',
                'status.srpk',
                'status.stagepk',
                'stage.stagenm',
                'status.statuspk',
                'status.style',
                'progres.progresnm',
                'group.groupnm',
                'jadwal.jadwalpk',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srno',
                // DB::raw('DATE_FORMAT(jadwal.finishsample, "%d/%m/%y") as finishsampleFormatted'),
                DB::raw('DATE_FORMAT(jadwal.finishsample2, "%d/%m/%y") as finishsampleFormatted2'),
                DB::raw('DATE_FORMAT(jadwal.startsample, "%d/%m/%y") as startsampleFormatted'),
            )
            ->whereYEAR('status.date', $tahun)
            ->whereNotNull('status.grouppk')
            // ->whereNotNull('jadwal.finishsample', '<', Carbon::now())
            ->whereIn('status.progrespk', $ProgresID)
            ->whereIn('status.grouppk', $GroupLeft)
            ->orderBy('status.grouppk', 'ASC')->get()
            ->groupBy('grouppk');
        // dd($dtstyle);

        return view('development.displaymdleft', compact('results', 'semua_all', 'notip', 'dtstyle', 'GroupLeft'));
    }

    public function SampleFloor3()
    {
        $nowd = Carbon::now();
        $ambilbulanIni = $nowd->format('m');
        $ambiltahunIni = $nowd->format('Y');

        // Untuk bulan sebelumnya
        $previousMonth = $nowd->copy()->subMonth();
        $ambilbulanLalu = $previousMonth->format('m');

        // Mendapatkan memo untuk bulan ini    
        $results = MemoHelper::getMemo($ambilbulanIni, $ambilbulanLalu, $ambiltahunIni);
        $semua_all = MemoHelper::getAllMemo($ambilbulanIni, $ambilbulanLalu, $ambiltahunIni);
        $notip = MemoHelper::getAllNotip();

        return view('development.sample-floor3', compact('results', 'semua_all', 'notip'));
    }

    public function dtsamplefloorleft(Request $request)
    {
        $tahun = date("Y");
        $reqAll = $request->all();
        $searchByInput = $request->searchByInput;
        $filterByYear = $request->filterByYear;

        if (!empty($filterByYear)) $tahun = $filterByYear;

        $page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

        $GroupLeft = [1, 2, 3, 4, 5, 6];

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->leftJoin('material', 'material.statuspk', '=', 'status.statuspk')
            ->select(
                'status.artwork',
                'status.cansts',
                'status.category',
                'status.cekdate',
                'status.date',
                'status.dateupdt',
                'status.description',
                'status.foto',
                'status.grouppk',
                'status.opsi',
                'status.pattern',
                'status.pattern2',
                'status.paused',
                'status.progrespk',
                'status.reject',
                'status.sam',
                'status.samplenm',
                'status.season',
                'status.srpk',
                'status.stagepk',
                'stage.stagenm',
                'status.statuspk',
                'status.stsupdt2',
                'status.style',
                'status.techpack',
                'status.washing',
                'status.yy',
                'progres.progresnm',
                'group.groupnm',
                'jadwal.jadwalpk',
                'jadwal.receiptdate',
                'jadwal.finishsample',
                'jadwal.startsample',
                'jadwal.cutdate',
                'jadwal.matdate',
                'jadwal.trimdate',
                'jadwal.shipdate',
                'jadwal.pausedate',
                'jadwal.realdate',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srno',
                'material.matpk',
                DB::raw('DATE_FORMAT(jadwal.finishsample, "%d/%m/%y") as finishsampleFormatted'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d/%m/%y") as startsampleFormatted'),
                DB::raw('SUM(size.qty) as qty')
            )
            ->where('status.buyerpk2', '>', 0)
            ->where('status.userpk', '>', 0)
            ->where('status.cansts', '=', 0)
            ->where('status.aktif', '=', 0)
            ->whereYEAR('status.date', $tahun)
            ->whereNotNull('status.grouppk')
            ->where(function ($query) {
                $query->whereNotNull('jadwal.startsample')
                    ->orWhere('jadwal.startsample', '!=', '0000-00-00');
            })
            ->whereIn('status.grouppk', $GroupLeft)
            // ->take(6)
            ->orderBy('status.grouppk', 'ASC');

        if (!empty($searchByInput)) {
            $dtstyle = $dtstyle->where(DB::raw("CONCAT(request.srno,'',status.style,'',buyer.buyernm,'',status.season)"), 'like', '%' . $searchByInput . '%');
        }

        $dtstyleByGroup = $dtstyle->groupBy('group.grouppk')->get();
        $dtstyleAll = $dtstyle->groupBy('status.statuspk')->get();

        $arrgroup = array();
        foreach ($dtstyleByGroup as $itemDtstyleByGroup) {
            array_push($arrgroup, $itemDtstyleByGroup->grouppk);
        }



        $dtgroupAll = group::all();
        // whereIn('status.grouppk', $GroupLeft)
        if (count($arrgroup) < count($dtgroupAll)) {
            // $dtgroup = group::whereNotIn('grouppk',$arrgroup)->get();
            $dtgroup = group::whereIn('grouppk', $GroupLeft)->get();
            $dtstyleAll = $dtstyleAll->merge($dtgroup)->sortBy('groupnm');
        }

        $offset = ($page - 1) * $rows;
        $dtstyle = $dtstyle->skip($offset)->take($rows);

        $result = array();

        $dtstyleAllCount = $dtstyleAll->count();

        $result['total'] = $dtstyleAllCount;
        $result['reqAll'] = $reqAll;

        $row = array();

        foreach ($dtstyleAll as $d) {
            $sizes = DB::table('size')
                ->where('statuspk', $d->statuspk)
                ->get();

            $qty = DB::table('size')
                ->leftJoin('status', 'status.statuspk', '=', 'size.statuspk')
                ->select(DB::raw('SUM(size.qty) as qty'))
                ->where('size.statuspk', $d->statuspk)
                ->first();

            if (empty($grouppk)) $grouppk = 0;
            $team = "";
            $upcoming = "";
            if (!empty($d->grouppk) && $d->grouppk != $grouppk) {
                $team = "<span style='font-weight:700;'>$d->groupnm</span>";
                $grouppk = $d->grouppk;

                $dtupcoming = DB::table('status')
                    ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
                    ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
                    ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
                    ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
                    ->select(
                        'status.opsi',
                        'status.statuspk',
                        'status.buyerpk2',
                        'buyer.buyerpk',
                        'buyer.buyernm',
                        'request.srno',
                        DB::raw('DATE_FORMAT(jadwal.shipdate,"%d %b") as shipdate')
                    )
                    ->where('status.buyerpk2', '>', 0)
                    ->where('status.userpk', '>', 0)
                    ->where('status.cansts', '=', 0)
                    ->where('status.aktif', '=', 0)
                    ->where('status.grouppk', $d->grouppk)
                    ->whereYEAR('status.date', $tahun)
                    ->whereNotNull('status.grouppk')
                    ->where(function ($query) {
                        $query->whereNull('jadwal.startsample')->orWhere('jadwal.startsample', '=', '0000-00-00');
                    })
                    ->orderBy('status.grouppk', 'ASC')
                    ->whereIn('status.grouppk', $GroupLeft)
                    ->groupBy('status.statuspk')
                    ->get();



                $upcoming_text = "";
                if ($dtupcoming->count() > 0) {
                    if ($dtupcoming->count() > 1) {
                        $upcoming_text = "";
                        $sr_text_arrays = [];
                        foreach ($dtupcoming as $dtup) {
                            $upcoming_text =  "SR " . $dtup->srno;
                            if (!empty($dtup->opsi)) $upcoming_text = $upcoming_text . "[" . $dtup->opsi . "]";
                            $upcoming_text = $upcoming_text  . " (S:" . $dtup->shipdate . ")";
                            array_push($sr_text_arrays, $upcoming_text);
                        }
                        for ($i = 0; $i < count($sr_text_arrays); $i++) {
                            if ($i != 0) {
                                $upcoming .= ", ";
                            }
                            if ($i != 0 && $i % 2 == 0) {
                                $upcoming .= "<br>";
                            }
                            $upcoming .= $sr_text_arrays[$i];
                        }
                    } else {

                        $upcoming_text = $dtupcoming[0]->srno;
                        if (!empty($dtupcoming[0]->opsi)) $upcoming_text .= "[" . $dtupcoming[0]->opsi . "]";
                        $upcoming_text .= " (S:" . $dtupcoming[0]->shipdate . ")";
                        $upcoming = $upcoming_text . "&nbsp;" . $dtupcoming[0]->buyernm . "&nbsp; = &nbsp;" . $dtupcoming[0]->statuspk . "&nbsp;" . $d->qty;
                    }
                }
            }

            $colors = "";
            if ($sizes->count() > 0) $colors = $sizes->count() . " col";

            $sewdate = "";
            if (!empty($d->startsample) && $d->startsample != "0000-00-00") $sewdate = $d->startsampleFormatted;

            $findate = "";
            if (!empty($d->finishsample) && $d->finishsample != "0000-00-00") $findate = $d->finishsampleFormatted;

            // $sr_text = "<span style='font-weight:700'>".$d->srno ? "SR $d->srno" : "".'</span>';
            // $sr_text = "<div style='background-color:red;'>" . ($d->srno ? "SR $d->srno" : "") . "</div>";
            $sr_text = $d->srno ? "$d->srno" : "";
            if (!empty($d->opsi)) $sr_text .= " [$d->opsi]";

            // $text_emb = '';
            // $text_embs = array();
            // if ($d->washing == 1) array_push($text_embs, 'W');
            // if (!empty($d->artwork)) {
            //     $artworks = explode(",", $d->artwork);
            //     foreach ($artworks as $artwork) {
            //         if (trim($artwork, ' ') === "HT" || trim($artwork, ' ') === "Heat Transfer") array_push($text_embs, 'H');
            //         if (trim($artwork, ' ') === "Embrodery") array_push($text_embs, 'E');
            //         if (trim($artwork, ' ') === "Printing") array_push($text_embs, 'P');
            //     }
            // }
            // if (count($text_embs) > 0) $text_emb = implode(",", $text_embs);
            // $emb = "<span>$text_emb</span>";


            $row[] = array(
                'statuspk' => $d->statuspk,
                'grouppk' => $d->grouppk,
                'buyerpk' => $d->buyerpk,
                'stagepk' => $d->stagepk,
                'jadwalpk' => $d->jadwalpk,
                'progrespk' => $d->progrespk,
                'buyernm' => $d->buyernm,
                'groupnm' => $d->groupnm,
                'srpk' => $d->srpk,
                'reject' => $d->reject,
                'cansts' => $d->cansts,
                'stsupdt' => $d->stsupdt2,
                'trimdate' => $d->trimdate,
                'cutdate' => $d->cutdate,
                'paused' => $d->paused,
                'material' => $d->matpk,
                // 'emb' => $emb,
                'team' => $team,
                'sr_text' => $sr_text . '&nbsp;' . $d->statuspk,
                'qty' => $qty->qty,
                'category' => $d->category,
                'colors' => $colors,
                'sewdate' => $sewdate,
                'findate' => $findate,
                'upcoming' => $upcoming,
                // 'upcoming_text' => $upcoming_text,
            );
        }

        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function getSFLeft(Request $request)
    {
        $tahun = date("Y");
        $reqAll = $request->all();
        $GroupLeft = [1, 2, 3, 4, 5, 6];

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->select(
                'status.category',
                'status.grouppk',
                'status.opsi',
                'status.season',
                'status.paused',
                'status.progrespk',
                'status.samplenm',
                'status.srpk',
                'status.stagepk',
                'stage.stagenm',
                'status.statuspk',
                'status.style',
                'progres.progresnm',
                'group.groupnm',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srno',
                'jadwal.progsew',
                'jadwal.finishsample',
                DB::raw('DATE_FORMAT(jadwal.finishsample2, "%d %b") as finishsample2'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %b") as startsample'),
                DB::raw('SUM(size.qty) as qty')
            )
            ->where('status.buyerpk2', '>', 0)
            ->where('status.userpk', '>', 0)
            ->where('status.cansts', '=', 0)
            ->where('status.aktif', '=', 0)
            ->whereIn('status.grouppk', $GroupLeft)
            ->whereNotIn('status.progrespk', ['5', '6'])
            ->whereYEAR('status.date', $tahun)
            ->whereNotNull('status.grouppk')
            ->whereNull('jadwal.finishsample')
            ->where('jadwal.progsew', '=', 1)
            ->whereNotNull('status.category')

            // ->where(function ($query) {
            //     $query->whereNotNull('jadwal.startsample')
            //         ->orWhere('jadwal.startsample', '!=', '0000-00-00');
            // })
            ->orderByRaw('FIELD(status.grouppk, 1, 2, 3, 4, 5, 6)')
            ->orderBy('jadwal.startsample', 'DESC');

        $dtstyleAll = $dtstyle->groupBy('status.statuspk')->get();
        $dtstyleByGroup = $dtstyle->groupBy('group.grouppk')->get();

        $arrgroup = array();
        foreach ($dtstyleByGroup as $itemDtstyleByGroup) {
            array_push($arrgroup, $itemDtstyleByGroup->grouppk);
        }

        $dtgroupAll = group::all();
        if (count($arrgroup) < count($dtgroupAll)) {
            $dtgroup = group::whereNotIn('grouppk', $arrgroup)->whereIn('grouppk', $GroupLeft)->get();
            $dtstyleAll = $dtstyleAll->merge($dtgroup)->sortBy('groupnm');
        }

        $result = array();
        $dtstyleAllCount = $dtstyleAll->count();
        $result['total'] = $dtstyleAllCount;
        $result['reqAll'] = $reqAll;
        $row = array();
        $grouppk = 0;

        foreach ($dtstyleAll->groupBy('grouppk') as $groupedData) {
            $limitedData = $groupedData->take(5); // Ambil hanya 5 data per group

            foreach ($limitedData as $d) {

                $sizes = DB::table('size')->select('cw', 'qty')->where('statuspk', $d->statuspk)->get();

                $qty = DB::table('size')
                    ->leftJoin('status', 'status.statuspk', '=', 'size.statuspk')
                    ->select(DB::raw('SUM(size.qty) as qty'))
                    ->where('size.statuspk', $d->statuspk)
                    ->first();

                if (empty($grouppk)) $grouppk = 0;
                $team = "";

                if (!empty($d->grouppk) && $d->grouppk != $grouppk) {
                    $team = "<span style='font-weight:700;'>$d->groupnm</span>";
                    $grouppk = $d->grouppk;
                }

                $sewdate = "";
                if (!empty($d->startsample) && $d->startsample != "0000-00-00") $sewdate = $d->startsample;

                $findate = "";
                if (!empty($d->finishsample2) && $d->finishsample2 != "0000-00-00") $findate = $d->finishsample2;

                $SRtitle = "SR $d->srno";
                if (!empty($d->opsi) && $d->opsi != '') $SRtitle .= " [$d->opsi]";

                $warna = "";
                if ($sizes->count()) {
                    foreach ($sizes as $key => $data) {
                        if ($key + 1 == $sizes->count()) {
                            $warna .= "$data->cw ($data->qty pcs)";
                        } else {
                            $warna .= "$data->cw ($data->qty pcs), <br>";
                        }
                    }
                }

                $warna = Str::limit($warna, 50);

                $colom2 = $d->buyernm . ', ' . $d->season . ' - ' . $d->category . ' - Qty: ' . $qty->qty;
                $colom2 = Str::limit($colom2, 30);

                $styledt = "<div class='d-flex gap-2 align-items-center w-100'>
                            <div class='p-0 d-flex flex-column'>
                                <div class='p-0'><span class='txt-000000-700-16-20-87'>$SRtitle</span></div>
                                <div class='p-0 d-flex align-items-center text-00000061-400-12-16'><span class='text-wrap'>$colom2</span></div>
                            </div>
                        </div>";

                $warnaDiv = "<div class='d-flex gap-2 align-items-center w-100'>
                           <div class='p-0 d-flex flex-column'>
                                <div class='p-0'>
                                    <span class='txt-000000-700-16-20-87'>Color</span>
                                </div>
                            <div>

                            <div class='p-0 d-flex align-items-center text-00000061-400-12-16'>
                                <span class='text-wrap'>
                               $warna 
                                </span>
                            </div>
                        </div>";


                $estimasi = "<div class='d-flex flex-column'>
                <span class='text-nowrap'>SEW: $sewdate</span>
                <span class='text-nowrap'>FIN: $findate</span>
                </div>";

                $status = '';
                if ($d->progrespk == 4 && $d->progsew == 1) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                } elseif ($d->progrespk == 4 && $d->progsew == 2) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#19B715;">Complete</span>';
                } elseif ($d->progrespk == 5) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                } elseif ($d->progrespk == 6) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#19B715;">Complete</span>';
                } elseif ($d->progrespk == 7) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#FF6B00;">Pause</span>';
                } elseif ($d->progrespk == 8) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#FF6B00;">Transfer Out</span>';
                } elseif ($d->progrespk == 9) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                } elseif ($d->progrespk == 10) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                }


                $row[] = array(
                    'statuspk' => $d->statuspk,
                    'grouppk' => $d->grouppk,
                    'buyerpk' => $d->buyerpk,
                    'stagepk' => $d->stagepk,
                    'buyernm' => $d->buyernm,
                    'groupnm' => $d->groupnm,
                    'srpk' => $d->srpk,
                    'team' => $team,
                    'styledt' => $styledt,
                    'warna' => $warnaDiv,
                    'estimasi' => $estimasi,
                    'status' => $status,
                    'qty' => $qty->qty,
                    'category' => $d->category,
                );
            }
        }


        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function getSFLeftUpcoming(Request $request)
    {
        $tahun = date("Y");
        $reqAll = $request->all();
        $GroupLeft = ['1', '2', '3', '4', '5', '6'];

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->select(
                'status.category',
                'status.grouppk',
                'status.opsi',
                'status.paused',
                'status.progrespk',
                'status.samplenm',
                'status.season',
                'status.srpk',
                'status.stagepk',
                'stage.stagenm',
                'status.statuspk',
                'progres.progresnm',
                'group.groupnm',
                'jadwal.jadwalpk',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srno',
                DB::raw('DATE_FORMAT(jadwal.finishsample2, "%d %b") as finishsample2'),
                DB::raw('DATE_FORMAT(jadwal.startsample2,"%d %b") as startsample2'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %b") as cutdate'),
                DB::raw('SUM(size.qty) as qty')
            )
            ->where('status.buyerpk2', '>', 0)
            ->where('status.userpk', '>', 0)
            ->where('status.cansts', '=', 0)
            ->where('status.aktif', '=', 0)
            ->whereIn('status.grouppk', ['1', '2', '3', '4', '5', '6'])
            ->where('status.progrespk', '=', 3)
            ->whereNotIn('status.progrespk', ['5', '6'])
            ->whereYEAR('status.date', $tahun)
            ->whereNotNull('status.grouppk')
            ->whereNotNull('status.category')

            // ->where(function ($query) {
            //     $query->whereNotNull('jadwal.cutdate')
            //         ->orWhere('jadwal.cutdate', '!=', '0000-00-00');
            // })
            ->orderByRaw('FIELD(status.grouppk, 1, 2, 3, 4, 5, 6)')
            ->orderBy('jadwal.cutdate', 'DESC');

        $dtstyleAll = $dtstyle->groupBy('status.statuspk')->get();
        $dtstyleByGroup = $dtstyle->groupBy('group.grouppk')->get();

        $arrgroup = array();
        foreach ($dtstyleByGroup as $itemDtstyleByGroup) {
            array_push($arrgroup, $itemDtstyleByGroup->grouppk);
        }

        $dtgroupAll = group::all();

        if (count($arrgroup) < count($dtgroupAll)) {
            $dtgroup = group::whereNotIn('grouppk', $arrgroup)->whereIn('grouppk', $GroupLeft)->get();

            $dtstyleAll = $dtstyleAll->merge($dtgroup)->sortBy('groupnm');
        }

        $result = array();

        $dtstyleAllCount = $dtstyleAll->count();

        $result['total'] = $dtstyleAllCount;
        $result['reqAll'] = $reqAll;

        $row = array();

        foreach ($dtstyleAll->groupBy('grouppk') as $groupedData) {
            $limitedData = $groupedData->take(5); // Ambil hanya 5 data per group

            foreach ($limitedData as $d) {

                $sizes = DB::table('size')
                    ->where('statuspk', $d->statuspk)
                    ->get();

                $qty = DB::table('size')
                    ->leftJoin('status', 'status.statuspk', '=', 'size.statuspk')
                    ->select(DB::raw('SUM(size.qty) as qty'))
                    ->where('size.statuspk', $d->statuspk)
                    ->first();

                if (empty($grouppk)) $grouppk = 0;
                $team = "";

                if (!empty($d->grouppk) && $d->grouppk != $grouppk) {
                    $team = "<span style='font-weight:700;'>$d->groupnm</span>";
                    $grouppk = $d->grouppk;
                }

                $colors = "";
                if ($sizes->count() > 0) $colors = $sizes->count() . " col";


                $SRtitle = "SR $d->srno";
                if (!empty($d->opsi) && $d->opsi != '') $SRtitle .= " [$d->opsi]";

                $styledt = "<div class='d-flex gap-2 align-items-center w-100'>
                                <div class='p-0 d-flex flex-column'>
                                    <div class='p-0'><span class='txt-000000-700-16-20-87'>$SRtitle</span></div>
                                    <div class='p-0 d-flex align-items-center text-00000061-400-12-16'>
                                    <span class='text-wrap'>$d->buyernm, $d->season - $d->category - <br> Qty: $qty->qty</span></div>
                                </div>
                            </div>";

                $estCut = '--';
                if (!empty($d->cutdate) && $d->cutdate != '0000-00-00' && $d->cutdate != '') $estCut = $d->cutdate;
                $estSew = '--';
                if (!empty($d->startsample2) && $d->startsample2 != '0000-00-00' && $d->startsample2 != '') $estSew = $d->startsample2;
                $estFin = '--';
                if (!empty($d->finishsample2) && $d->finishsample2 != '0000-00-00' && $d->finishsample2 != '') $estFin = $d->finishsample2;

                $estimasi = "<div class='d-flex flex-column'>
                <span class='text-nowrap'>CUT: $estCut</span>
                <span class='text-nowrap'>SEW: $estSew</span>
                <span class='text-nowrap'>FIN: $estFin</span>
            </div>";

                $row[] = array(
                    'statuspk' => $d->statuspk,
                    'grouppk' => $d->grouppk,
                    'buyerpk' => $d->buyerpk,
                    'stagepk' => $d->stagepk,
                    'jadwalpk' => $d->jadwalpk,
                    'progrespk' => $d->progrespk,
                    'buyernm' => $d->buyernm,
                    'groupnm' => $d->groupnm,
                    'srpk' => $d->srpk,
                    'paused' => $d->paused,
                    'team' => $team,
                    'styledt' => $styledt,
                    'estimasi' => $estimasi,
                    'qty' => $qty->qty,
                    'category' => $d->category,
                    'colors' => $colors,
                );
            }
        }


        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function getSFRight(Request $request)
    {
        $tahun = date("Y");
        $reqAll = $request->all();
        $GroupRight = [7, 8, 9, 10, 11, 12, 13];

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->select(
                'status.category',
                'status.date',
                'status.dateupdt',
                'status.description',
                'status.grouppk',
                'status.opsi',
                'status.season',
                'status.paused',
                'status.progrespk',
                'status.samplenm',
                'status.srpk',
                'status.stagepk',
                'stage.stagenm',
                'status.statuspk',
                'status.style',
                'progres.progresnm',
                'group.groupnm',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srno',
                DB::raw('DATE_FORMAT(jadwal.finishsample2, "%d %b") as finishsample2'),
                DB::raw('DATE_FORMAT(jadwal.startsample,"%d %b") as startsample'),
                DB::raw('SUM(size.qty) as qty')
            )
            ->where('status.buyerpk2', '>', 0)
            ->where('status.userpk', '>', 0)
            ->where('status.cansts', '=', 0)
            ->where('status.aktif', '=', 0)
            ->whereIn('status.grouppk', $GroupRight)
            ->whereNotIn('status.progrespk', ['5', '6'])
            ->whereYEAR('status.date', $tahun)
            ->whereNotNull('status.grouppk')
            ->whereNull('jadwal.finishsample')
            ->where('jadwal.progsew', '=', 1)
            ->whereNotNull('status.category')

            // ->where(function ($query) {
            //     $query->whereNotNull('jadwal.startsample')
            //         ->orWhere('jadwal.startsample', '!=', '0000-00-00');
            // })
            ->orderByRaw('FIELD(status.grouppk, 7, 8, 9, 10, 11, 12, 13)')
            ->orderBy('jadwal.startsample', 'DESC');

        $dtstyleAll = $dtstyle->groupBy('status.statuspk')->get();
        $dtstyleByGroup = $dtstyle->groupBy('group.grouppk')->get();

        $arrgroup = array();
        foreach ($dtstyleByGroup as $itemDtstyleByGroup) {
            array_push($arrgroup, $itemDtstyleByGroup->grouppk);
        }

        $dtgroupAll = group::all();

        if (count($arrgroup) < count($dtgroupAll)) {
            $dtgroup = group::whereNotIn('grouppk', $arrgroup)->whereIn('grouppk', $GroupRight)->get();

            $dtstyleAll = $dtstyleAll->merge($dtgroup)->sortBy('groupnm');
        }

        $result = array();
        $dtstyleAllCount = $dtstyleAll->count();
        $result['total'] = $dtstyleAllCount;
        $result['reqAll'] = $reqAll;
        $row = array();
        $grouppk = 0;

        foreach ($dtstyleAll->groupBy('grouppk') as $groupedData) {
            $limitedData = $groupedData->take(5); // Ambil hanya 5 data per group

            foreach ($limitedData as $d) {

                $sizes = DB::table('size')->select('cw', 'qty')->where('statuspk', $d->statuspk)->get();

                $qty = DB::table('size')
                    ->leftJoin('status', 'status.statuspk', '=', 'size.statuspk')
                    ->select(DB::raw('SUM(size.qty) as qty'))
                    ->where('size.statuspk', $d->statuspk)
                    ->first();

                if (empty($grouppk)) $grouppk = 0;
                $team = "";

                if (!empty($d->grouppk) && $d->grouppk != $grouppk) {
                    $team = "<span style='font-weight:700;'>$d->groupnm</span>";
                    $grouppk = $d->grouppk;
                }

                $sewdate = "";
                if (!empty($d->startsample) && $d->startsample != "0000-00-00") $sewdate = $d->startsample;

                $findate = "";
                if (!empty($d->finishsample2) && $d->finishsample2 != "0000-00-00") $findate = $d->finishsample2;

                $SRtitle = "SR $d->srno";
                if (!empty($d->opsi) && $d->opsi != '') $SRtitle .= " [$d->opsi]";

                $warna = "";
                if ($sizes->count()) {
                    foreach ($sizes as $key => $data) {
                        if ($key + 1 == $sizes->count()) {
                            $warna .= "$data->cw ($data->qty pcs)";
                        } else {
                            $warna .= "$data->cw ($data->qty pcs), <br>";
                        }
                    }
                }

                $warna = Str::limit($warna, 50);

                $colom2 = $d->buyernm . ', ' . $d->season . ' - ' . $d->category . ' - Qty: ' . $qty->qty;
                $colom2 = Str::limit($colom2, 30);

                $styledt = "<div class='d-flex gap-2 align-items-center w-100'>
                            <div class='p-0 d-flex flex-column'>
                                <div class='p-0'><span class='txt-000000-700-16-20-87'>$SRtitle</span></div>
                                <div class='p-0 d-flex align-items-center text-00000061-400-12-16'><span class='text-wrap'>$colom2</span></div>
                            </div>
                        </div>";

                $warnaDiv = "<div class='d-flex gap-2 align-items-center w-100'>
                           <div class='p-0 d-flex flex-column'>
                                <div class='p-0'>
                                    <span class='txt-000000-700-16-20-87'>Color</span>
                                </div>
                            <div>

                            <div class='p-0 d-flex align-items-center text-00000061-400-12-16'>
                                <span class='text-wrap'>
                               $warna
                                </span>
                            </div>
                        </div>";


                $estimasi = "<div class='d-flex flex-column'>
                <span class='text-nowrap'>SEW: $sewdate</span>
                <span class='text-nowrap'>FIN: $findate</span>
                </div>";

                $status = '';
                if ($d->progrespk == 1) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#BDBDBD;">On hold</span>';
                } elseif ($d->progrespk == 3) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                } elseif ($d->progrespk == 4) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                } elseif ($d->progrespk == 5) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                } elseif ($d->progrespk == 6) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#19B715;">Complete</span>';
                } elseif ($d->progrespk == 7) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#FF6B00;">Pause</span>';
                } elseif ($d->progrespk == 8) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#FF6B00;">Transfer Out</span>';
                } elseif ($d->progrespk == 9) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                } elseif ($d->progrespk == 10) {
                    $status = '<span class="badge" style="min-width:66px; text-align:center; border-radius:5px; border:0px solid #000000; font-weight:700; font-size:14px; color:#FFFFFF; background-color:#0B89D2;">Active</span>';
                }


                $row[] = array(
                    'statuspk' => $d->statuspk,
                    'grouppk' => $d->grouppk,
                    'buyerpk' => $d->buyerpk,
                    'stagepk' => $d->stagepk,
                    'buyernm' => $d->buyernm,
                    'groupnm' => $d->groupnm,
                    'srpk' => $d->srpk,
                    'team' => $team,
                    'styledt' => $styledt,
                    'warna' => $warnaDiv,
                    'estimasi' => $estimasi,
                    'status' => $status,
                    'qty' => $qty->qty,
                    'category' => $d->category,
                );
            }
        }


        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function getSFRightUpcoming(Request $request)
    {
        $tahun = date("Y");
        $reqAll = $request->all();
        $GroupRight = [7, 8, 9, 10, 11, 12];

        $dtstyle = DB::table('status')
            ->leftJoin('request', 'request.srpk', '=', 'status.srpk')
            ->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
            ->leftJoin('user', 'user.userpk', '=', 'status.userpk')
            ->leftJoin('jadwal', 'jadwal.statuspk', '=', 'status.statuspk')
            ->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
            ->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
            ->leftJoin('group', 'group.grouppk', '=', 'status.grouppk')
            ->select(
                'status.grouppk',
                'status.opsi',
                'status.progrespk',
                'status.samplenm',
                'status.season',
                'status.category',
                'status.srpk',
                'status.statuspk',
                'jadwal.jadwalpk',
                'status.paused',
                'progres.progresnm',
                'group.groupnm',
                'buyer.buyerpk',
                'buyer.buyernm',
                'request.srno',
                DB::raw('DATE_FORMAT(jadwal.finishsample2, "%d %b") as finishsample2'),
                DB::raw('DATE_FORMAT(jadwal.startsample2,"%d %b") as startsample2'),
                DB::raw('DATE_FORMAT(jadwal.cutdate,"%d %b") as cutdate'),
                DB::raw('SUM(size.qty) as qty')
            )
            ->where('status.buyerpk2', '>', 0)
            ->where('status.userpk', '>', 0)
            ->where('status.cansts', '=', 0)
            ->where('status.aktif', '=', 0)
            ->where('status.progrespk', '=', 3)
            ->whereIn('status.grouppk', $GroupRight)
            ->whereNotIn('status.progrespk', ['5', '6'])
            ->whereYEAR('status.date', $tahun)
            ->whereNotNull('status.grouppk')
            ->whereNotNull('status.category')

            // ->where(function ($query) {
            //     $query->whereNotNull('jadwal.cutdate')
            //         ->orWhere('jadwal.cutdate', '!=', '0000-00-00');
            // })
            ->orderByRaw('FIELD(status.grouppk, 7, 8, 9, 10, 11, 12, 13)')
            ->orderBy('jadwal.cutdate', 'DESC');

        $dtstyleAll = $dtstyle->groupBy('status.statuspk')->get();
        $dtstyleByGroup = $dtstyle->groupBy('group.grouppk')->get();

        $arrgroup = array();
        foreach ($dtstyleByGroup as $itemDtstyleByGroup) {
            array_push($arrgroup, $itemDtstyleByGroup->grouppk);
        }

        $dtgroupAll = group::all();

        if (count($arrgroup) < count($dtgroupAll)) {
            $dtgroup = group::whereNotIn('grouppk', $arrgroup)->whereIn('grouppk', $GroupRight)->get();
            $dtstyleAll = $dtstyleAll->merge($dtgroup)->sortBy('groupnm');
        }

        $result = array();
        $dtstyleAllCount = $dtstyleAll->count();
        $result['total'] = $dtstyleAllCount;
        $result['reqAll'] = $reqAll;
        $row = array();

        foreach ($dtstyleAll->groupBy('grouppk') as $groupedData) {
            $limitedData = $groupedData->take(5); // Ambil hanya 5 data per group

            foreach ($limitedData as $d) {
                $sizes = DB::table('size')
                    ->where('statuspk', $d->statuspk)
                    ->get();

                $qty = DB::table('size')
                    ->leftJoin('status', 'status.statuspk', '=', 'size.statuspk')
                    ->select(DB::raw('SUM(size.qty) as qty'))
                    ->where('size.statuspk', $d->statuspk)
                    ->first();

                if (empty($grouppk)) $grouppk = 0;
                $team = "";

                if (!empty($d->grouppk) && $d->grouppk != $grouppk) {
                    $team = "<span style='font-weight:700;'>$d->groupnm</span>";
                    $grouppk = $d->grouppk;
                }

                $colors = "";
                if ($sizes->count() > 0) $colors = $sizes->count() . " col";

                $SRtitle = "SR $d->srno";
                if (!empty($d->opsi) && $d->opsi != '') $SRtitle .= " [$d->opsi]";

                $styledt = "<div class='d-flex gap-2 align-items-center w-100'>
                            <div class='p-0 d-flex flex-column'>
                                <div class='p-0'><span class='txt-000000-700-16-20-87'>$SRtitle</span></div>
                                <div class='p-0 d-flex align-items-center text-00000061-400-12-16'>
                                <span class='text-wrap'>$d->buyernm, $d->season - $d->category - Qty: $qty->qty</span></div>
                            </div>
                        </div>";

                $estCut = '--';
                if (!empty($d->cutdate) && $d->cutdate != '0000-00-00' && $d->cutdate != '') $estCut = $d->cutdate;
                $estSew = '--';
                if (!empty($d->startsample2) && $d->startsample2 != '0000-00-00' && $d->startsample2 != '') $estSew = $d->startsample2;
                $estFin = '--';
                if (!empty($d->finishsample2) && $d->finishsample2 != '0000-00-00' && $d->finishsample2 != '') $estFin = $d->finishsample2;

                $estimasi = "<div class='d-flex flex-column'>
                            <span class='text-nowrap'>CUT: $estCut</span>
                            <span class='text-nowrap'>SEW: $estSew</span>
                            <span class='text-nowrap'>FIN: $estFin</span>
                        </div>";

                $row[] = array(
                    'statuspk' => $d->statuspk,
                    'grouppk' => $d->grouppk,
                    'buyerpk' => $d->buyerpk,
                    'jadwalpk' => $d->jadwalpk,
                    'progrespk' => $d->progrespk,
                    'buyernm' => $d->buyernm,
                    'groupnm' => $d->groupnm,
                    'srpk' => $d->srpk,
                    'paused' => $d->paused,
                    'team' => $team,
                    'styledt' => $styledt,
                    'estimasi' => $estimasi,
                    'qty' => $qty->qty,
                    'category' => $d->category,
                    'colors' => $colors,
                );
            }
        }

        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }
}
