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

      $res = DB::select( DB::raw("SELECT `id`, `os_nombre`, `os_capitalizada`, `mat_obra_social`, `es_habilitada` FROM `os_obra_social`
      "));
          return response()->json($res, "200");
    }

    public function getObraSocialHabilitado()
    {

      $res = DB::select( DB::raw("SELECT `id`, `os_nombre`, `os_capitalizada`, `mat_obra_social`, `es_habilitada` FROM `os_obra_social` WHERE  es_habilitada = 'S'
      "));
          return response()->json($res, "200");
    }

    public function setObraSocial(Request $request)
    {
      $id =    DB::table('os_obra_social')->insertGetId([
        'os_nombre' => $request->os_nombre,
        'os_capitalizada' => $request->os_capitalizada,
        'mat_obra_social' => $request->mat_obra_social,
        'es_habilitada' => $request->es_habilitada
    ]);
      return response()->json($id, "200");
    }


    public function putObraSocial(Request $request, $id)
    {
      $res =  DB::table('os_obra_social')
      ->where('id', $id)
      ->update([
        'os_nombre' => $request->input('os_nombre'),
        'os_capitalizada' => $request->input('os_capitalizada'),
        'mat_obra_social' => $request->input('mat_obra_social'),
        'es_habilitada' => $request->input('es_habilitada')
        ]);

        return response()->json($res, "200");
    }

    public function getConvenioByObraSocial(Request $request)
    {
        $obra_social_id = $request->input('obra_social_id');
      $res = DB::select( DB::raw("SELECT os_obra_social.id , os_nombre, es_habilitada, os_sesion.id_sesion, os_sesion.id_precio, os_sesion.os_sesion_mes, os_sesion.os_sesion_anual, os_sesion_tipo.os_sesion, os_sesion_tipo.os_sesion_codigo
      FROM `os_obra_social`, os_sesion, os_sesion_tipo
      WHERE os_sesion.id_obra_social = os_obra_social.id AND os_sesion.id_sesion_tipo = os_sesion_tipo.id_sesion_tipo
      AND os_obra_social.id = :obra_social_id
      "),array('obra_social_id' => $obra_social_id));

          return response()->json($res, "200");
    }



    public function getConvenioByObraSocialHabilitado(Request $request)
    {

      $res = DB::select( DB::raw("SELECT os_obra_social.id , os_nombre, es_habilitada, os_sesion.id_sesion, os_sesion.id_precio, os_sesion.os_sesion_mes, os_sesion.os_sesion_anual, os_sesion_tipo.os_sesion, os_sesion_tipo.os_sesion_codigo
      FROM `os_obra_social`, os_sesion, os_sesion_tipo
      WHERE os_sesion.id_obra_social = os_obra_social.id AND os_sesion.id_sesion_tipo = os_sesion_tipo.id_sesion_tipo AND es_habilitada = 'S'
      "));

          return response()->json($res, "200");
    }



    public function getConvenioHabilitado(Request $request)
    {

        $res = DB::select( DB::raw("SELECT os_obra_social.id , os_nombre, es_habilitada, os_sesion.id_sesion, os_sesion.id_precio,
        os_sesion.os_sesion_mes, os_sesion.os_sesion_anual, os_sesion_tipo.os_sesion, os_sesion_tipo.os_sesion_codigo, os_sesion.id_sesion_tipo
        FROM `os_obra_social`, os_sesion, os_sesion_tipo
        WHERE os_sesion.id_obra_social = os_obra_social.id AND os_sesion.id_sesion_tipo = os_sesion_tipo.id_sesion_tipo
        AND os_obra_social.es_habilitada = 'S' ORDER BY os_sesion.id_sesion DESC
        "));

          return response()->json($res, "200");
    }



    public function setConvenio(Request $request)
    {
      $id =    DB::table('os_sesion')->insertGetId([
        'id_obra_social' => $request->id_obra_social,
        'id_sesion_tipo' => $request->id_sesion_tipo,
        'id_precio' => $request->id_precio,
        'os_sesion_mes' => $request->os_sesion_mes,
        'os_sesion_anual' => $request->os_sesion_anual
    ]);
      return response()->json($id, "200");
    }


    public function putConvenio(Request $request, $id)
    {
      $res =  DB::table('os_sesion')
      ->where('id_sesion', $id)
      ->update([
        'id_obra_social' => $request->input('id'),
        'id_sesion_tipo' => $request->input('id_sesion_tipo'),
        'id_precio' => $request->input('id_precio'),
        'os_sesion_mes' => $request->input('os_sesion_mes'),
        'os_sesion_anual' => $request->input('os_sesion_anual')
        ]);

        return response()->json($res, "200");
    }

}
