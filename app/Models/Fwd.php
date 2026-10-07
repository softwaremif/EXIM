<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fwd extends Model
{
    use HasFactory;

    protected $table = 'fwd';
    protected $primaryKey = 'fwdpk';
    public $timestamps = false;
    protected $guarded = [''];
}
