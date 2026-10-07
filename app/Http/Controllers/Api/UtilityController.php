<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function get_month()
    {
        $month = [
            ['monthnm' => 'January',   'monthval' => '01'],
            ['monthnm' => 'February',  'monthval' => '02'],
            ['monthnm' => 'March',     'monthval' => '03'],
            ['monthnm' => 'April',     'monthval' => '04'],
            ['monthnm' => 'May',       'monthval' => '05'],
            ['monthnm' => 'June',      'monthval' => '06'],
            ['monthnm' => 'July',      'monthval' => '07'],
            ['monthnm' => 'August',    'monthval' => '08'],
            ['monthnm' => 'September', 'monthval' => '09'],
            ['monthnm' => 'October',   'monthval' => '10'],
            ['monthnm' => 'November',  'monthval' => '11'],
            ['monthnm' => 'December',  'monthval' => '12'],
        ];

        return json_encode($month);
    }
}
