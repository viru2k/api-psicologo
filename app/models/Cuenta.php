<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $table = 'mov_cuenta';

    protected $fillable = [
        'id',
        'cuenta_nombre', 
        'movimiento_tipo', 
    ];

}