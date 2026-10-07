<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use HasFactory;

    protected $table = 'cont';
    protected $primaryKey = 'contpk';
    public $timestamps = false;
    protected $guarded = [''];
}
