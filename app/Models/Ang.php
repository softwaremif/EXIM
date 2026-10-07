<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ang extends Model
{
    use HasFactory;

    protected $table = 'ang';
    protected $primaryKey = 'angpk';
    public $timestamps = false;
    protected $guarded = [''];
}
