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
    SELECT id, titulo, descripcion, tiene_enlace, enlace, tiene_imagen, imagen, es_youtube, enlace_video_youtube, es_video, enlace_video, estado, pagina, created_at, updated_at , es_curso ,es_importante
    FROM informa_privado WHERE estado = 'ACTIVO' ORDER BY fecha_creacion DESC
"));
         
return response()->json($res, 201);

}

public function getinformacionPublico()
{
    //echo "padron";

    $res = DB::select( DB::raw("
    SELECT id, titulo, descripcion, tiene_enlace, enlace, tiene_imagen, imagen, es_youtube, enlace_video_youtube, es_video, enlace_video, estado, pagina ,es_importante, created_at, updated_at 
    FROM informa_publico WHERE estado = 'ACTIVO' ORDER BY fecha_creacion DESC
"));
         
return response()->json($res, 201);

}


public function setNoticiaPublico(Request $request)
{
  $id =    DB::table('informa_publico')->insertGetId([
    
    'titulo' => $request->titulo, 
    'descripcion' => $request->descripcion,    
    'pagina' => $request->pagina,
    'fecha_creacion' => $request->fecha_creacion,
    'tiene_enlace' => $request->tiene_enlace,
    'enlace' => $request->enlace,
    'tiene_imagen' => $request->tiene_imagen,
    'imagen' => $request->imagen,
    'es_youtube' => $request->es_youtube,
    'enlace_video_youtube' => $request->enlace_video_youtube,
    'es_video' => $request->es_video,
    'enlace_video' => $request->enlace_video,
    'es_importante' => $request->es_importante,
    'estado' => $request->estado,
    'es_curso' => $request->es_curso,
    'created_at' => $request->fecha_creacion,
    'updated_at' => date("Y-m-d H:i:s")
]);    
  return response()->json($id, "200");  
}


public function putNoticiaPublico(Request $request, $id)
{
  $res =  DB::table('informa_publico')
  ->where('id', $id)
  ->update([
    'titulo' => $request->input('titulo'),
    'descripcion' => $request->input('descripcion'),
    'pagina' => $request->input('pagina'),
    'fecha_creacion' => $request->input('fecha_creacion'),
    'tiene_enlace' => $request->input('tiene_enlace'),
    'enlace' => $request->input('enlace'),
    'tiene_imagen' => $request->input('tiene_imagen'),
    'imagen' => $request->input('imagen'),
    'es_youtube' => $request->input('es_youtube'),
    'enlace_video_youtube' => $request->input('enlace_video_youtube'),
    'es_video' => $request->input('es_video'),
    'enlace_video' => $request->input('enlace_video'),
    'es_importante' => $request->input('es_importante'),
    'estado' => $request->input('estado'),
    'es_curso' => $request->input('es_curso'),    
    'updated_at' => date("Y-m-d H:i:s")
    ]);
    
    return response()->json($res, "200");
}


public function setNoticiaPrivado(Request $request)
{
    $id =    DB::table('informa_privado')->insertGetId([
    
        'titulo' => $request->titulo, 
        'descripcion' => $request->descripcion,    
        'pagina' => $request->pagina,
        'fecha_creacion' => $request->fecha_creacion,
        'tiene_enlace' => $request->tiene_enlace,
        'enlace' => $request->enlace,
        'tiene_imagen' => $request->tiene_imagen,
        'imagen' => $request->imagen,
        'es_youtube' => $request->es_youtube,
        'enlace_video_youtube' => $request->enlace_video_youtube,
        'es_video' => $request->es_video,
        'enlace_video' => $request->enlace_video,
        'es_importante' => $request->es_importante,
        'estado' => $request->estado,
        'es_curso' => $request->es_curso,
        'created_at' => $request->fecha_creacion,
        'updated_at' => date("Y-m-d H:i:s")
    ]);     
}


public function putNoticiaPrivado(Request $request, $id)
{/* 
    $res =  DB::table('informa_privado')
    ->where('id', $id)
    ->update([
      'titulo' => $request->input('titulo'),
      'descripcion' => $request->input('descripcion'),
      'pagina' => $request->input('pagina'),
      'fecha_creacion' => $request->input('fecha_creacion'),
      'tiene_enlace' => $request->input('tiene_enlace'),
      'enlace' => $request->input('enlace'),
      'tiene_imagen' => $request->input('tiene_imagen'),
      'imagen' => $request->input('imagen'),
      'es_youtube' => $request->input('es_youtube'),
      'enlace_video_youtube' => $request->input('enlace_video_youtube'),
      'es_video' => $request->input('es_video'),
      'enlace_video' => $request->input('enlace_video'),
      'es_importante' => $request->input('es_importante'),
      'estado' => $request->input('estado'),
      'es_curso' => $request->input('es_curso'),    
      'updated_at' => date("Y-m-d H:i:s")
      ]); 
      
    
    return response()->json($res, "200");*/
      
   //   echo $request->files('files');
 //     var_dump($request->files);
   // $files = $request->files;
    $file = $request->input('files');
    var_dump($file);
      var_dump($file['files'][0]);
     $filename =  $file['files'][0];
   // $filename = $file->getClientOriginalName();
     //    echo $filename;
     //    $extension = $file->getClientOriginalExtension();
       // echo $extension;
       //  $check=in_array($extension,$allowedfileExtension);
      //   $parts = explode('/', $request->url());    
       //   $last = end($parts);
      //   echo $last;
         $destinationPath = 'uploads/pruebassss';
      //   $without_extension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
         




            $file->move($destinationPath,$filename);
    return response()->json($files, "200");
}


}