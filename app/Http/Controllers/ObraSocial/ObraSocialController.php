<?php

namespace App\Http\Controllers\ObraSocial;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB; 

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ObraSocialController extends ApiController
{
    public function getObraSocial()
    {      
      $res = DB::select( DB::raw("SELECT `id`, `nombre`, `descripcion`, `es_habilitada`, `created_at`, `updated_at` FROM `obra_social` WHERE  es_habilitada = 'S'
      "));
          return response()->json($res, "200");
    }

    public function setObraSocial(Request $request)
    {
  

  
      $id =    DB::table('obra_social')->insertGetId([
        
        'nombre' => $request->nombre,         
        'descripcion' => $request->descripcion,            
        'es_habilitada' => $request->es_habilitada,
        'updated_at' => date("Y-m-d H:i:s")  ,
        'created_at' => date("Y-m-d H:i:s")  
        

    ]);    
      return response()->json($id, "200");  
    }
  
    
    public function putObraSocial(Request $request, $id)
    {
  
     
  
  
      $res =  DB::table('obra_social')
      ->where('id', $id)
      ->update([
        'nombre' => $request->input('nombre'),        
        'descripcion' => $request->input('descripcion'),
        'es_habilitada' => $request->input('es_habilitada'),
        'updated_at' => date("Y-m-d H:i:s")  
        ]);
        
        return response()->json($res, "200");
    }
  
}
