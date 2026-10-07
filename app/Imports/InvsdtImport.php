<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Invsdt;
use Maatwebsite\Excel\Concerns\WithStartRow;
use DB;

class InvsdtImport implements ToModel, WithStartRow
{
    protected $invspk;

    public function __construct($invspk)
    {
        $this->invspk = $invspk;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {

            $TakeId = Invsdt::create([
                'invspk' => $this->invspk,
                'nos' => $row[0],
                'style' => $row[1],
                'pono' => $row[2],
                'itemnm' => $row[3],
                'samplenm' => $row[4],
                'hscode' => $row[5],
                'colnm' => $row[6],
                'fabnm' => $row[7],
                'sizenm' => $row[8],
                'qty' => $row[9],
                'price' => $row[10]
            ]);

            return $TakeId;
        });
    }
}
