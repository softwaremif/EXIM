<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Spbdt;
use App\Models\Byr;
use Maatwebsite\Excel\Concerns\WithStartRow;
use DB;

class SpbdtImport implements ToModel, WithStartRow
{
    protected $spbpk;

    public function __construct($spbpk)
    {
        $this->spbpk = $spbpk;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            $NamaBuyer = Byr::where('byrnm', trim($row[4]))->value('byrpk') ?? 0;

            $cleanNoop = str_replace(' ', '', $row[0]);
            $getBdown = DB::connection('mysql_sig')
                ->table('bdown')
                ->select('bdown.bdownpk', 'bdown.ordpk', 'bdown.poref')
                ->where('bdown.opno', 'like', "%" . $cleanNoop . "%")
                ->first();

            $poref = $getBdown ? $getBdown->poref : null;
            $ordpk = $getBdown ? $getBdown->ordpk : null;
            $bdownpk = $getBdown ? $getBdown->bdownpk : null;

            $getOrd = $ordpk ? DB::connection('mysql_sig')
                ->table('ord')
                ->where('ordpk', $ordpk)
                ->first() : null;

            $artno = $getOrd ? $getOrd->artno : null;
            $prodes = $getOrd ? $getOrd->prodes : null;

            $TakeId = Spbdt::create([
                'spbpk' => $this->spbpk,
                'ordpk' => $ordpk,
                'bdownpk' => $bdownpk,
                'noop' => $row[0],
                'orderno' => $row[1],
                'prodes' => $row[2],
                'art' => $row[3],
                'byrpk' => $NamaBuyer,
                'negara' => $row[5],
                'jmlsatuan' => $row[6],
                'jmlkarton' => $row[7],
                'berat' => $row[8],
                'kubikasi' => $row[9],
                'smarks' => isset($row[10]) ? $row[10] : null,

            ]);

            $getInv = DB::table('invoice')->where('spbpk', $this->spbpk)->first();

            if ($getInv) {
                $ambilinvpk = $getInv->invpk;

                DB::table('invoicedt')->insert([
                    'spbdtpk' => $TakeId->spbdtpk,
                    'invpk' => $ambilinvpk,
                    'mpo' => $poref,
                    'mstyle' => $artno,
                    'mprodes' => $prodes,
                    'qty' => $row[6],
                ]);
            }
            return $TakeId;
        });
    }
}
