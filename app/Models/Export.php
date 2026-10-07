<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
    use HasFactory;

    protected $table = 'export';
    protected $primaryKey = 'exportpk';
    public $timestamps = false;
    protected $guarded = [''];
}
