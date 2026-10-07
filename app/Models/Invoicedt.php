<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoicedt extends Model
{
    use HasFactory;

    protected $table = 'invoicedt';
    protected $guarded = [''];
    protected $primaryKey = 'invdtpk';
    public $timestamps = false;
}
