<?php

namespace App\Http\Controllers\MovimientosCaja;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

use App\models\ConceptoMoneda; 
use App\models\ConceptoCuenta; 
use App\models\ConceptoTipoComprobante; 
use App\models\Cuenta;
use App\models\Proveedor;

class MovimientosCajaController extends ApiController
{
    public function getConceptoMonedas()
    {
        $conceptoMoneda = ConceptoMoneda::all();
        return $this->showAll($conceptoMoneda);
    }

    public function getConceptoCuentas()
    {
        $conceptoCuenta = ConceptoCuenta::all();
        return $this->showAll($conceptoCuenta);
    }

    public function getCuentas()
    {
        $cuenta = Cuenta::all();
        return $this->showAll($cuenta);
    }

    public function getConceptoTipoComprobantes()
    {
        $cuenta = ConceptoTipoComprobante::all();
        return $this->showAll($cuenta);
    }






    public function getConceptoMoneda(ConceptoMoneda $Sector)
    {
        return $this->showOne($Sector);
    }

    public function getConceptoCuenta(ConceptoCuenta $Sector)
    {
        return $this->showOne($Sector);
    }

    public function getConceptoTipoComprobante(ConceptoTipoComprobante $Sector)
    {
        return $this->showOne($Sector);
    }


    public function getCuenta(Cuenta $Sector)
    {
        return $this->showOne($Sector);
    }




    public function setConceptoMoneda(Request $request)
    {
        $id= DB::table('mov_tipo_moneda')->insertGetId([
            'tipo_moneda' => $request->tipo_moneda,           
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")    

        ]);
        $resp = ConceptoMoneda::find($id);
        return $this->showOne($resp);
    }

    public function setConceptoCuenta(Request $request)
    {
        $id= DB::table('mov_concepto_cuenta')->insertGetId([
            'concepto_cuenta' => $request->concepto_cuenta,                       
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")    

        ]);
        $resp = ConceptoCuenta::find($id);
        return $this->showOne($resp);        
    }

    
    public function setConceptoTipoComprobante(Request $request)
    {
        $id= DB::table('mov_tipo_comprobante')->insertGetId([
            'tipo_comprobante' => $request->tipo_comprobante,                       
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")    

        ]);
        $resp = ConceptoTipoComprobante::find($id);
        return $this->showOne($resp);        
    }


    public function setCuenta(Request $request)
    {
        $id= DB::table('mov_cuenta')->insertGetId([
            'cuenta_nombre' => $request->cuenta_nombre,                       
            'movimiento_tipo' => $request->movimiento_tipo,                       
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")    

        ]);
        $resp = Cuenta::find($id);
        return $this->showOne($resp);  
    }



    public function putConceptoMoneda(Request $request, $id)
    {
        $pmo = ConceptoMoneda::findOrFail($id);
        $pmo->fill($request->only([
            'tipo_moneda'

    ]));

   if ($pmo->isClean()) {
        return $this->errorRepsonse('Se debe especificar al menos un valor', 422);
    }
   $pmo->save();
    return $this->showOne($pmo);
    }

    public function putConceptoCuenta(Request $request, $id)
    {
        $pmo = ConceptoCuenta::findOrFail($id);
        $pmo->fill($request->only([
            'concepto_cuenta'

    ]));

   if ($pmo->isClean()) {
        return $this->errorRepsonse('Se debe especificar al menos un valor', 422);
    }
   $pmo->save();
    return $this->showOne($pmo);
    }

    public function putConceptoTipoComprobante(Request $request, $id)
    {
        $pmo = ConceptoTipoComprobante::findOrFail($id);
        $pmo->fill($request->only([
            'tipo_comprobante'

    ]));

   if ($pmo->isClean()) {
        return $this->errorRepsonse('Se debe especificar al menos un valor', 422);
    }
   $pmo->save();
    return $this->showOne($pmo);
    }


    public function putCuenta(Request $request, $id)
    {
        $pmo = Cuenta::findOrFail($id);
        $pmo->fill($request->only([
            'cuenta_nombre',
            'movimiento_tipo'

    ]));

   if ($pmo->isClean()) {
        return $this->errorRepsonse('Se debe especificar al menos un valor', 422);
    }
   $pmo->save();
    return $this->showOne($pmo);
    }
    
    public function geRegistroMovimientoBydate(Request $request)
    {
        $tmp_fecha = str_replace('/', '-', $request->input('fecha_desde'));
        $fecha_desde =  date('Y-m-d H:i:s', strtotime($tmp_fecha));         
        $tmp_fecha = str_replace('/', '-', $request->input('fecha_hasta'));
        $fecha_hasta =  date('Y-m-d H:i:s', strtotime($tmp_fecha));      

        $horario = DB::select( DB::raw("SELECT mov_registro.id, mov_concepto_cuenta_id, descripcion, mov_cuenta_id , fecha_carga, mov_tipo_comprobante_id, comprobante_numero, 
        tiene_enlace_factura, mov_tipo_moneda_id,  mov_registro.importe,  mov_registro.cotizacion, mov_registro.total,factura_encabezado_id, proveedor_id, proveedor_nombre, 
        proveedor_cuit, proveedor_direccion , 
         factura_encabezado.factura_pto_vta_id, factura_encabezado.medico_id, factura_encabezado.factura_comprobante_id, 
        factura_encabezado.factura_concepto_id, concepto_cuenta, 
        cuenta_nombre, movimiento_tipo, tipo_comprobante ,tipo_moneda    , cierre_caja_id
        FROM mov_registro 
        LEFT JOIN paciente_proveedor ON mov_registro.proveedor_id = paciente_proveedor.id         
        LEFT JOIN factura_encabezado ON mov_registro.factura_encabezado_id = factura_encabezado.id ,
        mov_concepto_cuenta, mov_cuenta, mov_tipo_comprobante, mov_tipo_moneda 
        WHERE mov_registro.mov_concepto_cuenta_id = mov_concepto_cuenta.id 
        AND mov_registro.mov_cuenta_id = mov_cuenta.id 
        AND mov_registro.mov_tipo_comprobante_id = mov_tipo_comprobante.id 
        AND mov_registro.mov_tipo_moneda_id = mov_tipo_moneda.id
        AND fecha_carga BETWEEN :fecha_desde AND :fecha_hasta
    "), array(
        'fecha_desde' =>$fecha_desde,
        'fecha_hasta' => $fecha_hasta 
      ));
       
      return response()->json($horario, 201);
    }


    public function setMovimientoCaja(Request $request)
    {
        $fecha =  date('Y-m-d H:i:s', strtotime($request->fecha_carga)); 
        $id= DB::table('mov_registro')->insertGetId([                              
            'mov_concepto_cuenta_id' => $request->mov_concepto_cuenta_id,
            'descripcion' => $request->descripcion,
            'mov_cuenta_id' => $request->mov_cuenta_id,
            'fecha_carga' => $fecha,
            'mov_tipo_comprobante_id' => $request->mov_tipo_comprobante_id,
            'comprobante_numero' => $request->comprobante_numero,
            'tiene_enlace_factura' => $request->tiene_enlace_factura,
            'mov_tipo_moneda_id' => $request->mov_tipo_moneda_id,
            'importe' => $request->importe,
            'cotizacion' => $request->cotizacion,
            'total' => $request->total,
            'liq_liquidacion_distribucion_id' => $request->liq_liquidacion_distribucion_id,
            'factura_encabezado_id' => $request->factura_encabezado_id,
            'paciente_id' => $request->paciente_id,
            'proveedor_id' => $request->proveedor_id,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")    

        ]);
       
        return response()->json($id, 201); 
    }


    public function putMovimientoCaja(Request $request, $id)
    {        
        $fecha =  date('Y-m-d H:i:s', strtotime($request['fecha_carga'])); 
        $update = DB::table('mov_registro') 
        ->where('id', $id) ->limit(1) 
        ->update( [ 
         
            'mov_concepto_cuenta_id' => $request['mov_concepto_cuenta_id'],     
            'descripcion' =>  $request['descripcion'],                 
            'mov_cuenta_id' => $request['mov_cuenta_id'],     
            'fecha_carga' => $fecha,                
            'mov_tipo_comprobante_id' => $request['mov_tipo_comprobante_id'],      
            'comprobante_numero' => $request['comprobante_numero'],                  
            'tiene_enlace_factura' => $request['tiene_enlace_factura'],
            'mov_tipo_moneda_id' => $request['mov_tipo_moneda_id'],
            'importe' => $request['importe'],
            'cotizacion' => $request['cotizacion'],   
            'total' =>  $request['total'] ,         
            'liq_liquidacion_distribucion_id' =>  $request['liq_liquidacion_distribucion_id'] ,
            'factura_encabezado_id' =>  $request['factura_encabezado_id'] ,
            'paciente_id' =>  $request['paciente_id'] ,
            'proveedor_id' =>  $request['proveedor_id'] ,            
            'updated_at' => date("Y-m-d H:i:s")     ]); 

                  
           return response()->json($request, 201);   
        
    }

    
/* -------------------------------------------------------------------------- */
/*                                  PROVEEDOR                                 */
/* -------------------------------------------------------------------------- */

public function getProveedores()
{
    $cuenta = Proveedor::all();
    return $this->showAll($cuenta);
}

public function getProveedor(Proveedor $Sector)
{
    return $this->showOne($Sector);
}



public function setProveedor(Request $request)
{
    $id= DB::table('paciente_proveedor')->insertGetId([
        'proveedor_nombre' => $request->proveedor_nombre,                       
        'proveedor_cuit' => $request->proveedor_cuit,                       
        'proveedor_direccion' => $request->proveedor_direccion,  
        'created_at' => date("Y-m-d H:i:s"),
        'updated_at' => date("Y-m-d H:i:s")    

    ]);
  
    return response()->json($id, 201);
}


public function putProveedor(Request $request, $id)
{
    $pmo = Proveedor::findOrFail($id);
    $pmo->fill($request->only([
        'proveedor_nombre',
        'proveedor_cuit',
        'proveedor_direccion'
]));

if ($pmo->isClean()) {
    return $this->errorRepsonse('Se debe especificar al menos un valor', 422);
}
$pmo->save();
return $this->showOne($pmo);
}


}
