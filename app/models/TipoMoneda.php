<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class TipoMoneda extends Model
{
    protected $table = 'mov_tipo_moneda';

    protected $fillable = [
        'id',
        'tipo_moneda', 
    ];

}