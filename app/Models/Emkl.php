<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emkl extends Model
{
    use HasFactory;

    protected $table = 'emkl';
    protected $primaryKey = 'emklpk';
    public $timestamps = false;
    protected $guarded = [''];
}
