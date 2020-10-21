<?php

namespace App\Http\Controllers\Afip;
use Afip;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 

class AfipDatosController extends Controller
{
    
   var  $produccion = FALSE;

    public function GetLastVoucher(Request $request){
        $punto_vta =  $request->input('punto_vta');
        $comprobante_tipo =  $request->input('comprobante_tipo');
        $afip = $this->getDatosUsuario($request);
       return  $last_voucher = $afip->ElectronicBilling->GetLastVoucher($punto_vta,$comprobante_tipo) ;
    } 

    public function getIformacionComprobante(Request $request){
        $afip = $this->getDatosUsuario($request);
       $comprobante_nro =  $request->input('comprobante_nro');
       $punto_vta =  $request->input('punto_vta');
       $comprobante_tipo =  $request->input('comprobante_tipo');
        $voucher_info = $afip->ElectronicBilling->GetVoucherInfo($comprobante_nro,$punto_vta,$comprobante_tipo); //Devuelve la información del comprobante 1 para el punto de venta 1 y el tipo de comprobante 6 (Factura B)

        if($voucher_info === NULL){
            return 'El comprobante no existe';
        }
        else{
           
            echo 'Esta es la información del comprobante:';
    echo '<pre>';
    print_r($voucher_info);
    echo '</pre>';
            
        }
    }

    public function TipoComprobantesDisponibles(Request $request){
        $afip = $this->getDatosUsuario($request);
        $voucher_types = $afip->ElectronicBilling->GetVoucherTypes();
        return $voucher_types;
    }

    public function GetConceptTypes(Request $request){
        $afip = $this->getDatosUsuario($request);
        return $concept_types = $afip->ElectronicBilling->GetConceptTypes();
    }

    public function TipoDocumentosDisponibles(Request $request){
        $afip = $this->getDatosUsuario($request);
        return $document_types = $afip->ElectronicBilling->GetDocumentTypes();
    }

    public function TipoAlicuotasDisponibles(Request $request){
        $afip = $this->getDatosUsuario($request);
        return $aloquot_types = $afip->ElectronicBilling->GetAliquotTypes();
    }

    public function GetOptionsTypes(Request $request){
        $afip = $this->getDatosUsuario($request);
        return $option_types = $afip->ElectronicBilling->GetOptionsTypes();;
    }

    
    public function GetTaxTypes(Request $request){
        $afip = $this->getDatosUsuario($request);
        return$tax_types = $afip->ElectronicBilling->GetTaxTypes();
    }


    
    public function ObetenerEstadoDelServidor(Request $request){
        $afip = $this->getDatosUsuario($request);
        $server_status = $afip->ElectronicBilling->GetServerStatus();
    //    var_dump( $server_status);
       $AppServer = $server_status->AppServer;
        $DbServer =  $server_status->DbServer;
        $AuthServer =  $server_status->AuthServer;
        return "Servidor: ".$AppServer." Base de datos ".$DbServer." Autenticado ".$AuthServer;
    }


    private function getDatosUsuario(Request $request){
        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = ".$request->input('medico_id')." ORDER BY apellido ASC"));
        $afip = new Afip(array(
            'CUIT' => (float)$medico[0]->cuit,
            'production' => $this->produccion,
            'cert'         => $medico[0]->factura_crt,
            'key'          => $medico[0]->factura_key
            ));
        return $afip;
    }
}
