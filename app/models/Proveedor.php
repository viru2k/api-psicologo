<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'paciente_proveedor';

    protected $fillable = [
        'id',
        'proveedor_nombre', 
        'proveedor_cuit', 
        'proveedor_direccion',
    ];

}