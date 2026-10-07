<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jang extends Model
{
    use HasFactory;

    protected $table = 'jang';
    protected $primaryKey = 'jangpk';
    public $timestamps = false;
    protected $guarded = [''];
}
