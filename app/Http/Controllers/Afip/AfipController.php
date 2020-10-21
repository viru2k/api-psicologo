<?php

namespace App\Http\Controllers\Afip;
use Afip;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 

class AfipController extends Controller
{
       var  $produccion = FALSE;
    
    public function testAfipGetLastVoucher(){
        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = 24"));
        $afip = new Afip(array(
        'CUIT' => 20300712143,
        'production' => $this->produccion,
        'cert'         => $medico[0]->factura_crt,
        'key'          => $medico[0]->factura_key));
        $last_voucher = $afip->ElectronicBilling->GetLastVoucher(1,4);
        var_dump( $last_voucher);
    }
    
    public function testAfip(){
        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = 24"));
        $afip = new Afip(array('CUIT' => (float)$medico[0]->cuit,
        'cert'          => $medico[0]->factura_crt,
        'key'          => $medico[0]->factura_key)); //Reemplazar el CUIT

        //Esta línea es solo para probar si funciona pero no es obligatorio
        $test = $afip->ElectronicBilling->GetDocumentTypes();
        var_dump( $test);
    }



    public function GetVoucherInfo(Request $request){

        // echo $request->input('comprobante_numero');
        // echo $request->input('punto_vta');
        // echo $request->input('comprobante_id');
        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = ".$request->input('medico_id').""));
        $afip = new Afip(array(
        'CUIT' => (float)$medico[0]->cuit,
        'production' => $this->produccion,
        'cert'         => $medico[0]->factura_crt,
        'key'          => $medico[0]->factura_key
        ));

        //Esta línea es solo para probar si funciona pero no es obligatorio
        $voucher_info = $afip->ElectronicBilling->GetVoucherInfo($request->input('comprobante_numero'),$request->input('punto_vta'),$request->input('comprobante_id')); //Devuelve la información del comprobante 1 para el punto de venta 1 y el tipo de comprobante 6 (Factura B)

        if($voucher_info === NULL){
          //  echo 'El comprobante no existe';
            return response()->json('El comprobante no existe', 401);
        }
        else{
            // echo 'Esta es la información del comprobante:';
            // echo '<pre>';
            // print_r($voucher_info);
            // echo '</pre>';
            return response()->json($voucher_info, 201);
        }
    }

    public function CrearFacturaA(Request $request){

        /**
         * 
         *  SI ES 2 = Servicios  3 = Productos y Servicios DEBE TENER FECHA DESDE Y HASTA 
         */

        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = ".$request->input('medico_id').""));
        $afip = new Afip(array(
        'CUIT' => (float)$medico[0]->cuit,
        'production' => $this->produccion,
        'cert'         => $medico[0]->factura_crt,
        'key'          => $medico[0]->factura_key
        ));

            /**
             * Número de la ultima Factura A
             **/
            $last_voucher = $afip->ElectronicBilling->GetLastVoucher(1,1);

            /**
             * Numero del punto de venta
             **/
            $punto_de_venta = 1;

            /**
             * Tipo de factura
             **/
            $tipo_de_factura = 1; // 1 = Factura A

            /**
             * Concepto de la factura
             *
             * Opciones:
             *
             * 1 = Productos
             * 2 = Servicios
             * 3 = Productos y Servicios
             **/
            $concepto = 1;

            /**
             * Tipo de documento del comprador
             *
             * Opciones:
             *
             * 80 = CUIT
             * 86 = CUIL
             * 96 = DNI
             * 99 = Consumidor Final
             **/
            $tipo_de_documento = 80;

            /**
             * Numero de documento del comprador (0 para consumidor final)
             **/
            $numero_de_documento = 27316427095;

                    /**
                     * Numero de factura
                     **/
                    $numero_de_factura = $last_voucher+1;

                    /**
                     * Fecha de la factura en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
                     **/
                    $fecha = date('Y-m-d');

                    /**
                     * Importe sujeto al IVA (sin icluir IVA)
                     **/
                    $importe_gravado = 100;

                    /**
                     * Importe exento al IVA
                     **/
                    $importe_exento_iva = 0;

                    /**
                     * Importe de IVA
                     **/
                    $importe_iva = 21;


                    $data = array(
                            'CantReg'       => 1, // Cantidad de facturas a registrar
                            'PtoVta'        => $punto_de_venta,
                            'CbteTipo'      => $tipo_de_factura,
                            'Concepto'      => $concepto,
                            'DocTipo'       => $tipo_de_documento,
                            'DocNro'        => $numero_de_documento,
                            'CbteDesde' => $numero_de_factura,
                            'CbteHasta' => $numero_de_factura,
                            'CbteFch'       => intval(str_replace('-', '', $fecha)),
                            'ImpTotal'      => $importe_gravado + $importe_iva + $importe_exento_iva,
                            'ImpTotConc'=> 0, // Importe neto no gravado
                            'ImpNeto'       => $importe_gravado,
                            'ImpOpEx'       => $importe_exento_iva,
                            'ImpIVA'        => $importe_iva,
                            'ImpTrib'       => 0, //Importe total de tributos
                            'MonId'         => 'PES', //Tipo de moneda usada en la factura ('PES' = pesos argentinos)
                            'MonCotiz'      => 1, // Cotización de la moneda usada (1 para pesos argentinos)
                            'Iva'           => array(// Alícuotas asociadas al factura
                                    array(
                                            'Id'            => 5, // Id del tipo de IVA (5 = 21%)
                                            'BaseImp'       => $importe_gravado,
                                            'Importe'       => $importe_iva
                                    )
                            ),
                    );

                    /**
                     * Creamos la Factura
                     **/
                    $res = $afip->ElectronicBilling->CreateVoucher($data);

                    /**
                     * Mostramos por pantalla los datos de la nueva Factura
                     **/
                    /*var_dump(array(
                            'cae' => $res['CAE'], //CAE asignado a la Factura
                            'vencimiento' => $res['CAEFchVto'] //Fecha de vencimiento del CAE
                    ));*/
                    return $res;
    }

    public function CrearFacturaB(Request $request){
        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = ".$request->input('id').""));
        $afip = new Afip(array( 
        'CUIT' => (float)$medico[0]->cuit,
        'production' => $this->produccion,
        'cert'         => $medico[0]->factura_crt,
        'key'          => $medico[0]->factura_key));


        /**
         * Número de la ultima Factura B
         **/
        $last_voucher = $afip->ElectronicBilling->GetLastVoucher(1,6);
        
        /**
         * Numero del punto de venta
         **/
        $punto_de_venta = 1;
        
        /**
         * Tipo de factura
         **/
        $tipo_de_factura = 6; // 6 = Factura B
        
        /**
         * Concepto de la factura
         *
         * Opciones:
         *
         * 1 = Productos
         * 2 = Servicios
         * 3 = Productos y Servicios
         **/
        $concepto = 1;
        
        /**
         * Tipo de documento del comprador
         *
         * Opciones:
         *
         * 80 = CUIT
         * 86 = CUIL
         * 96 = DNI
         * 99 = Consumidor Final
         **/
        $tipo_de_documento = 99;
        
        /**
         * Numero de documento del comprador (0 para consumidor final)
         **/
        $numero_de_documento = 0;
        
        /**
         * Numero de factura
         **/
        $numero_de_factura = $last_voucher+1;
        
        /**
         * Fecha de la factura en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
         **/
        $fecha = date('Y-m-d');
        
        /**
         * Importe sujeto al IVA (sin icluir IVA)
         **/
        $importe_gravado = 100;
        
        /**
         * Importe exento al IVA
         **/
        $importe_exento_iva = 0;
        
        /**
         * Importe de IVA
         **/
        $importe_iva = 21;
        
        
        $data = array(
                'CantReg'       => 1, // Cantidad de facturas a registrar
                'PtoVta'        => $punto_de_venta,
                'CbteTipo'      => $tipo_de_factura,
                'Concepto'      => $concepto,
                'DocTipo'       => $tipo_de_documento,
                'DocNro'        => $numero_de_documento,
                'CbteDesde' => $numero_de_factura,
                'CbteHasta' => $numero_de_factura,
                'CbteFch'       => intval(str_replace('-', '', $fecha)),
                'ImpTotal'      => $importe_gravado + $importe_iva + $importe_exento_iva,
                'ImpTotConc'=> 0, // Importe neto no gravado
                'ImpNeto'       => $importe_gravado,
                'ImpOpEx'       => $importe_exento_iva,
                'ImpIVA'        => $importe_iva,
                'ImpTrib'       => 0, //Importe total de tributos
                'MonId'         => 'PES', //Tipo de moneda usada en la factura ('PES' = pesos argentinos)
                'MonCotiz'      => 1, // Cotización de la moneda usada (1 para pesos argentinos)
                'Iva'           => array(// Alícuotas asociadas al factura
                        array(
                                'Id'            => 5, // Id del tipo de IVA (5 = 21%)
                                'BaseImp'       => $importe_gravado,
                                'Importe'       => $importe_iva
                        )
                ),
        );
        
        /**
         * Creamos la Factura
         **/
        $res = $afip->ElectronicBilling->CreateVoucher($data);
        
        /**
         * Mostramos por pantalla los datos de la nueva Factura
         **/
        var_dump(array(
                'cae' => $res['CAE'], //CAE asignado a la Factura
                'vencimiento' => $res['CAEFchVto'] //Fecha de vencimiento del CAE
        ));
    }

    public function CrearFacturaC(Request $request){
        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt, factura_punto_vta_medico.punto_vta FROM medicos, factura_punto_vta_medico WHERE medicos.id = factura_punto_vta_medico.medico_id AND medicos.id = ".$request->input('id').""));
        $afip = new Afip(array(
                'CUIT' => (float)$medico[0]->cuit,
                'production' => $this->produccion,
                'cert'         => $medico[0]->factura_crt,
                'key'          => $medico[0]->factura_key
        ));


        /**
         * Número de la ultima Factura C
         **/
        $last_voucher = $afip->ElectronicBilling->GetLastVoucher(1,11);
        echo $last_voucher;
        /**
         * Numero del punto de venta
         **/
        $punto_de_venta = $medico[0]->punto_vta;
        
        /**
         * Tipo de factura
         **/
        $tipo_de_comprobante = 11; // 11 = Factura C
        
        /**
         * Concepto de la factura
         *
         * Opciones:
         *
         * 1 = Productos
         * 2 = Servicios
         * 3 = Productos y Servicios
         **/
        $concepto = 3;
        
        /**
         * Tipo de documento del comprador
         *
         * Opciones:
         *
         * 80 = CUIT
         * 86 = CUIL
         * 96 = DNI
         * 99 = Consumidor Final
         **/
        $tipo_de_documento = 80;
        
        /**
         * Numero de documento del comprador (0 para consumidor final)
         **/
        $numero_de_documento = 33693450239;
        
        /**
         * Numero de comprobante
         **/
        $numero_de_factura = $last_voucher+1;
        
        /**
         * Fecha de la factura en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
         **/
        $fecha = date('Y-m-d');
        $FchServDesde = intval(str_replace('-', '', date('Y-m-d')));  //20190901
        $FchServHasta = intval(str_replace('-', '', date('Y-m-d'))); //20190930
        $FchVtoPago = intval(str_replace('-', '', date('Y-m-d'))); //20190901
        /**
         * Importe de la Factura
         **/
        $importe_total = 100;
        
        
        $data = array(
            'FchServDesde'  => $FchServDesde,//Fecha de inicio de servicio (formato aaaammdd)
            'FchServHasta'  => $FchServHasta,//Fecha de fin de servicio (formato aaaammdd)
            'FchVtoPago'    => $FchVtoPago,//Fecha de vencimiento de pago (formato aaaammdd)
                'CantReg'       => 1, // Cantidad de facturas a registrar
                'PtoVta'        => $punto_de_venta,
                'CbteTipo'      => $tipo_de_comprobante,
                'Concepto'      => $concepto,
                'DocTipo'       => $tipo_de_documento,
                'DocNro'        => $numero_de_documento,
                'CbteDesde' => $numero_de_factura,
                'CbteHasta' => $numero_de_factura,
                'CbteFch'       => intval(str_replace('-', '', $fecha)),
                'ImpTotal'      => $importe_total,
                'ImpTotConc'=> 0, // Importe neto no gravado
                'ImpNeto'       => $importe_total, // Importe neto
                'ImpOpEx'       => 0, // Importe exento al IVA
                'ImpIVA'        => 0, // Importe de IVA
                'ImpTrib'       => 0, //Importe total de tributos
                'MonId'         => 'PES', //Tipo de moneda usada en la factura ('PES' = pesos argentinos)
                'MonCotiz'      => 1, // Cotización de la moneda usada (1 para pesos argentinos)
        );
        
        /**
         * Creamos la Factura
         **/
        $res = $afip->ElectronicBilling->CreateVoucher($data);
        
        /**
         * Mostramos por pantalla los datos de la nueva Factura
         **/
        var_dump($res);
    }

    public function CrearNotaCreditoA(Request $request){

        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = ".$request->input('id').""));
        $afip = new Afip(array(
                'CUIT' => (float)$medico[0]->cuit,
                'production' => $this->produccion,
                'cert'         => $medico[0]->factura_crt,
                'key'          => $medico[0]->factura_key
        ));

            /**
             * Número de la ultima Nota de Crédito A
             **/
            $last_voucher = $afip->ElectronicBilling->GetLastVoucher(1,3);

            /**
             * Numero del punto de venta
             **/
            $punto_de_venta = 1;

            /**
             * Tipo de Nota de Crédito
             **/
            $tipo_de_nota = 3; // 3 = Nota de Crédito A

            /**
             * Numero del punto de venta de la Factura
             * asociada a la Nota de Crédito
             **/
            $punto_factura_asociada = 1;

            /**
             * Tipo de Factura asociada a la Nota de Crédito
             **/
            $tipo_factura_asociada = 1; // 1 = Factura A

            /**
             * Numero de Factura asociada a la Nota de Crédito
             **/
            $numero_factura_asociada = 1;

            /**
             * Concepto de la Nota de Crédito
             *
             * Opciones:
             *
             * 1 = Productos
             * 2 = Servicios
             * 3 = Productos y Servicios
             **/
            $concepto = 1;

            /**
             * Tipo de documento del comprador
             *
             * Opciones:
             *
             * 80 = CUIT
             * 86 = CUIL
             * 96 = DNI
             * 99 = Consumidor Final
             **/
            $tipo_de_documento = 80;

            /**
             * Numero de documento del comprador (0 para consumidor final)
             **/
            $numero_de_documento = 33693450239;

            /**
             * Numero de Nota de Crédito
             **/
            $numero_de_nota = $last_voucher+1;

            /**
             * Fecha de la Nota de Crédito en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
             **/
            $fecha = date('Y-m-d');

            /**
             * Importe sujeto al IVA (sin icluir IVA)
             **/
            $importe_gravado = 100;

            /**
             * Importe exento al IVA
             **/
            $importe_exento_iva = 0;

            /**
             * Importe de IVA
             **/
            $importe_iva = 21;

            $data = array(
                    'CantReg'       => 1, // Cantidad de Notas de Crédito a registrar
                    'PtoVta'        => $punto_de_venta,
                    'CbteTipo'      => $tipo_de_nota,
                    'Concepto'      => $concepto,
                    'DocTipo'       => $tipo_de_documento,
                    'DocNro'        => $numero_de_documento,
                    'CbteDesde' => $numero_de_nota,
                    'CbteHasta' => $numero_de_nota,
                    'CbteFch'       => intval(str_replace('-', '', $fecha)),
                    'ImpTotal'      => $importe_gravado + $importe_iva + $importe_exento_iva,
                    'ImpTotConc'=> 0, // Importe neto no gravado
                    'ImpNeto'       => $importe_gravado,
                    'ImpOpEx'       => $importe_exento_iva,
                    'ImpIVA'        => $importe_iva,
                    'ImpTrib'       => 0, //Importe total de tributos
                    'MonId'         => 'PES', //Tipo de moneda usada en la Nota de Crédito ('PES' = pesos argentinos)
                    'MonCotiz'      => 1, // Cotización de la moneda usada (1 para pesos argentinos)
                    'CbtesAsoc' => array( //Factura asociada
                            array(
                                    'Tipo'          => $tipo_factura_asociada,
                                    'PtoVta'        => $punto_factura_asociada,
                                    'Nro'           => $numero_factura_asociada,
                            )
                    ),
                    'Iva'           => array( // Alícuotas asociadas a la Nota de Crédito
                            array(
                                    'Id'            => 5, // Id del tipo de IVA (5 = 21%)
                                    'BaseImp'       => $importe_gravado,
                                    'Importe'       => $importe_iva
                            )
                    ),
            );

            /**
             * Creamos la Nota de Crédito
             **/
            $res = $afip->ElectronicBilling->CreateVoucher($data);

            /**
             * Mostramos por pantalla los datos de la nueva Nota de Crédito
             **/
            var_dump(array(
                    'cae' => $res['CAE'], //CAE asignado a la Nota de Crédito
                    'vencimiento' => $res['CAEFchVto'] //Fecha de vencimiento del CAE
            ));
    }
    
    public function CrearNotaCreditoB(Request $request){

        $medico = DB::select( DB::raw("SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = ".$request->input('id').""));
        $afip = new Afip(array(
                'CUIT' => (float)$medico[0]->cuit,
                'production' => $this->produccion,
                'cert'         => $medico[0]->factura_crt,
                'key'          => $medico[0]->factura_key        
        ));
        $last_voucher = $afip->ElectronicBilling->GetLastVoucher(1,8);

        /**
         * Numero del punto de venta
         **/
        $punto_de_venta = 1;

        /**
         * Tipo de Nota de Crédito
         **/
        $tipo_de_nota = 8; // 8 = Nota de Crédito B

        /**
         * Numero del punto de venta de la Factura
         * asociada a la Nota de Crédito
         **/
        $punto_factura_asociada = 1;

        /**
         * Tipo de Factura asociada a la Nota de Crédito
         **/
        $tipo_factura_asociada = 6; // 6 = Factura B

        /**
         * Numero de Factura asociada a la Nota de Crédito
         **/
        $numero_factura_asociada = 1;

        /**
         * Concepto de la Nota de Crédito
         *
         * Opciones:
         *
         * 1 = Productos
         * 2 = Servicios
         * 3 = Productos y Servicios
         **/
        $concepto = 1;

        /**
         * Tipo de documento del comprador
         *
         * Opciones:
         *
         * 80 = CUIT
         * 86 = CUIL
         * 96 = DNI
         * 99 = Consumidor Final
         **/
        $tipo_de_documento = 99;

        /**
         * Numero de documento del comprador (0 para consumidor final)
         **/
        $numero_de_documento = 0;

        /**
         * Numero de Nota de Crédito
         **/
        $numero_de_nota = $last_voucher+1;

        /**
         * Fecha de la Nota de Crédito en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
         **/
        $fecha = date('Y-m-d');

        /**
         * Importe sujeto al IVA (sin icluir IVA)
         **/
        $importe_gravado = 100;

        /**
         * Importe exento al IVA
         **/
        $importe_exento_iva = 0;

        /**
         * Importe de IVA
         **/
        $importe_iva = 21;


        $data = array(
                'CantReg'       => 1, // Cantidad de Notas de Crédito a registrar
                'PtoVta'        => $punto_de_venta,
                'CbteTipo'      => $tipo_de_nota,
                'Concepto'      => $concepto,
                'DocTipo'       => $tipo_de_documento,
                'DocNro'        => $numero_de_documento,
                'CbteDesde' => $numero_de_nota,
                'CbteHasta' => $numero_de_nota,
                'CbteFch'       => intval(str_replace('-', '', $fecha)),
                'ImpTotal'      => $importe_gravado + $importe_iva + $importe_exento_iva,
                'ImpTotConc'=> 0, // Importe neto no gravado
                'ImpNeto'       => $importe_gravado,
                'ImpOpEx'       => $importe_exento_iva,
                'ImpIVA'        => $importe_iva,
                'ImpTrib'       => 0, //Importe total de tributos
                'MonId'         => 'PES', //Tipo de moneda usada en la Nota de Crédito ('PES' = pesos argentinos)
                'MonCotiz'      => 1, // Cotización de la moneda usada (1 para pesos argentinos)
                'CbtesAsoc' => array( //Factura asociada
                        array(
                                'Tipo'          => $tipo_factura_asociada,
                                'PtoVta'        => $punto_factura_asociada,
                                'Nro'           => $numero_factura_asociada,
                        )
                ),
                'Iva'           => array(// Alícuotas asociadas a la Nota de Crédito
                        array(
                                'Id'            => 5, // Id del tipo de IVA (5 = 21%)
                                'BaseImp'       => $importe_gravado,
                                'Importe'       => $importe_iva
                        )
                ),
        );

        /**
         * Creamos la Nota de Crédito
         **/
        $res = $afip->ElectronicBilling->CreateVoucher($data);

        /**
         * Mostramos por pantalla los datos de la nueva Nota de Crédito
         **/
        var_dump(array(
                'cae' => $res['CAE'], //CAE asignado a la Nota de Crédito
                'vencimiento' => $res['CAEFchVto'] //Fecha de vencimiento del CAE
        ));
    }


        public function getMedicosFacturan(){
                $medico = DB::select( DB::raw("SELECT medicos.id,CONCAT(apellido,' ',nombre) AS nombreyapellido , domicilio, fecha_matricula, cuit, ing_brutos, usuario_id, factura_key, factura_crt, 
                categoria_iva.categoria_iva, factura_documento_comprador.id  AS factura_documento_comprador_id, factura_documento_comprador.descripcion, factura_punto_vta.id AS  factura_punto_vta_id, 
                factura_punto_vta.punto_vta , factura_comprobante.id AS factura_comprobante_id, factura_comprobante.es_afip,  factura_comprobante.descripcion AS factura_comprobante_descripcion 
                FROM medicos, categoria_iva, factura_documento_comprador, factura_punto_vta, factura_comprobante WHERE medicos.punto_vta_id = factura_punto_vta.id AND factura_documento_comprador.id = medicos.factura_documento_comprador_id AND  medicos.factura_comprobante_id = factura_comprobante.id AND   cuit != '' AND factura_key != '' AND factura_crt !='' AND medicos.categoria_iva_id = categoria_iva.id ORDER BY nombreyapellido ASC"));
                return $medico;
        }


        public function getDatoMedico(Request $request){
                $medico_id = $request->input('medico_id');
                
                $medico = DB::select( DB::raw("SELECT medicos.id,CONCAT(apellido,' ',nombre) AS nombreyapellido , domicilio, fecha_matricula, cuit, ing_brutos, usuario_id, factura_key, factura_crt, 
                categoria_iva.categoria_iva, factura_documento_comprador.id  AS factura_documento_comprador_id, factura_documento_comprador.descripcion, factura_punto_vta.id AS  factura_punto_vta_id, 
                factura_punto_vta.punto_vta , factura_punto_vta.punto_vta, factura_comprobante.id AS factura_comprobante_id,  factura_comprobante.es_afip, factura_comprobante.letra, 
                factura_comprobante.comprobante_codigo, factura_comprobante.descripcion AS factura_comprobante_descripcion , es_afip
                FROM medicos, categoria_iva, factura_documento_comprador, factura_punto_vta, factura_comprobante WHERE medicos.punto_vta_id = factura_punto_vta.id AND factura_documento_comprador.id = medicos.factura_documento_comprador_id AND  medicos.factura_comprobante_id = factura_comprobante.id AND   cuit != '' AND factura_key != '' AND factura_crt !='' AND medicos.categoria_iva_id = categoria_iva.id AND medicos.id = ".$medico_id." ORDER BY nombreyapellido ASC"));
                return $medico;
        }

}
