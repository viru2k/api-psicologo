<?php

namespace App\Http\Controllers\Padron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 
use Illuminate\Auth\AuthenticationException; 
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Hash;

class LandingController extends ApiController
{
    public function __construct()
    {
        //$this->middleware('auth:api')->except('index','getPadron');
       // $this->middleware('auth:api')->except(['index', 'show','getPadron']);
       // $this->middleware('auth:api', ['except' => ['index', 'getPadron']]); 
       $this->middleware('auth:api', ['only' => ['getinformacionPrivado']]); 
    
    }
    public function getinformacionPrivado()
{
    //echo "padron";

    $res = DB::select( DB::raw("
    SELECT id, titulo, descripcion, tiene_enlace, enlace, tiene_imagen, imagen, es_youtube, enlace_video_youtube, es_video, enlace_video, estado, pagina, created_at, updated_at , es_curso 
    FROM informa_privado WHERE estado = 'ACTIVO' ORDER BY fecha_creacion DESC
"));
         
return response()->json($res, 201);

}

public function getinformacionPublico()
{
    //echo "padron";

    $res = DB::select( DB::raw("
    SELECT id, titulo, descripcion, tiene_enlace, enlace, tiene_imagen, imagen, es_youtube, enlace_video_youtube, es_video, enlace_video, estado, pagina,created_at, updated_at 
    FROM informa_publico WHERE estado = 'ACTIVO' ORDER BY fecha_creacion DESC
"));
         
return response()->json($res, 201);

}
}