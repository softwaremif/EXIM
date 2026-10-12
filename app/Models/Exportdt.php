<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exportdt extends Model
{
    use HasFactory;

    protected $table = 'exportdt';
    protected $primaryKey = 'exportdtpk';
    public $timestamps = false;
    protected $guarded = [''];
}
