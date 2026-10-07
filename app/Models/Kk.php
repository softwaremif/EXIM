<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kk extends Model
{
    use HasFactory;

    protected $table = 'kk';
    protected $primaryKey = 'kkpk';
    public $timestamps = false;
    protected $guarded = [''];

}
