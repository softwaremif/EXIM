<?php

namespace App\Helpers;

use App\Models\Kk;
use Carbon\Carbon;
use App\Models\Listdb;
use Illuminate\Support\Facades\DB;

class GenerateNumber
{
  public static function byDbnote()
  {
    $lastnomor = Listdb::lockForUpdate()->orderBy('nomor', 'desc')->value('nomor');
    $nomor = !empty($lastnomor) ? $lastnomor + 1 : 1;

    return $nomor;
  }

  public static function byCobaDbnote()
  {
    $year = date('Y');
    $query = Listdb::lockForUpdate()
      ->orderBy('nomor', 'desc')
      ->select('nomor', 'datecreate')
      ->first();
    $lastnomor = $query->nomor;
    $lastYear = $query->datecreate->format('Y');
    $nomor = !empty($lastnomor) && $year == $lastYear ? $lastnomor + 1 : 1;

    return $nomor;
  }

  public static function byCobaKk(Carbon $tglkk)
  {
      $year = $tglkk->year;

      $lastNumber = Kk::whereYear('tglkk', $year)
          ->lockForUpdate()
          ->max('kkno');

      return $lastNumber ? $lastNumber + 1 : 1;
  }
}
