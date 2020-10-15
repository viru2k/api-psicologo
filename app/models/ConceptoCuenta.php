<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class ConceptoCuenta extends Model
{
    protected $table = 'mov_concepto_cuenta';

    protected $fillable = [
        'id',
        'concepto_cuenta', 
    ];

}