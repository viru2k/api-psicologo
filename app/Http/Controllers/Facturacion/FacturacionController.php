<?php

namespace App\Http\Controllers\Facturacion;

use Illuminate\Http\Request;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB; 

class FacturacionController extends ApiController
{

   private $TOTALLIQUIDADO = 0;
   private $TOTALGASTOSADM= 0;
   private $TOTALIMPCHEQUE= 0;
   private $TOTALINGBRUTO = 0;
   private $TOTALLOTEHOGAR = 0;
   private $TOTALBRUTO = 0;
   private $PERCEPCION_ING_BRUTO = false;
   private $TIENESALDO = true;


   public function geRegistroMovimientoBydate(Request $request) {
        $this->getLiquidacionDetalle()
   }
   


     // OBTENGO EL DETALLE DE LA LIQUIDACION

   public function getLiquidacionDetalle($id_liquidacion_generada)
   {

       $res = DB::select( DB::raw("SELECT `id_liquidacion_detalle`, `mat_matricula`, `os_liq_bruto`, `os_ing_brutos`, `os_lote_hogar`, `os_gasto_admin`, `os_imp_cheque`, `os_descuentos`, `os_desc_matricula`, `os_desc_fondo_sol`, `os_otros_ing_eg`, `os_liq_neto`, `num_comprobante`, `os_num_ing_bruto`, `id_liquidacion_generada` FROM `os_liq_liquidacion_detalle` WHERE id_liquidacion_generada = :id_liquidacion_generada
   "), array(
        'id_liquidacion_generada' =>$id_liquidacion_generada
      ));
      
     return $res;
   }

    // valido si puedo seguir descontando
    private function tieneSaldo($valor, $aDescontar) {
            $saldo = $valor - $aDescontar;

            if($saldo >= 0) {
                return true;
            } else {
                return false;
            }
    }


    public function getConceptosPercepcion()
    {
        $porcentajes = DB::select( DB::raw("SELECT os_liq_monto_porcentaje	FROM os_liq_percepcion
    "));
       
      return $porcentajes;
    }

    // LOS INGRESOS BRUTOS MAYORES AL VALOR DE CONCEPTO SERAN GENERADOS LOS NUMEROS
    public function calcularIngresosBrutos()
    {
        $porcentajes = DB::select( DB::raw("SELECT os_liq_monto_porcentaje	FROM os_liq_percepcion
    "));
       
      return $porcentajes;
    }


  
}
