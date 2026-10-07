<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    use HasFactory;

    protected $table = 'gud';
    protected $primaryKey = 'gudpk';
    public $timestamps = false;
    protected $guarded = [''];
}
