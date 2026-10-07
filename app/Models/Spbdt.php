<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spbdt extends Model
{
    use HasFactory;

    protected $table = 'spbdt';
    protected $guarded = [''];
    protected $primaryKey = 'spbdtpk';
    public $timestamps = false;

}
