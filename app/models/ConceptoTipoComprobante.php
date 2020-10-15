<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class ConceptoTipoComprobante extends Model
{
    protected $table = 'mov_tipo_comprobante';

    protected $fillable = [
        'id',
        'tipo_comprobante'
    ];

}