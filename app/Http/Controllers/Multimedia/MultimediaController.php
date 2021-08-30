<?php

namespace App\Http\Controllers\Multimedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;

class MultimediaController extends ApiController
{



public function getMultimedia(Request $request){

    
    $tipo_noticias = $request->input('tipo_noticias');

    if($tipo_noticias === 'privado'){

    }else {

    }
   $res = DB::select( DB::raw(
   " SELECT `id`, `titulo`, `descripcion`, `pagina`, `fecha_creacion`, `tiene_enlace`, `enlace`, `tiene_imagen`, `imagen`, `es_youtube`, `enlace_video_youtube`, `es_video`, `enlace_video`, `es_importante`, `estado`, `es_curso`, `created_at`, `updated_at` FROM `informa_privado` ORDER BY  fecha_creacion  DESC
  "));

     return $res;
}


public function delMultimedia($id)
{

DB::table('multimedia')->where('id', '=', $id)->delete();
return response()->json($id, "200");
}


}
