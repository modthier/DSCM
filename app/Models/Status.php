<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'status_english_title',
        'status_arabic_title',
        'status_description'
    ];
    protected $table='status';
}
