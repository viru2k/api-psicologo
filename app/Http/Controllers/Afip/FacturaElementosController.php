<?php

namespace App\Http\Controllers\Afip;
use Afip;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

class FacturaElementosController extends ApiController
{
  var $produccion = false;

  public function Alicuota(Request $request)
  {
    $res = DB::table("factura_alicuota")
      ->select("descripcion", "porcentaje", "porcentaje_simple", "iva_id")
      ->get();
    return $this->showAll($res);
  }

  public function AlicuotaAsociada(Request $request)
  {
    $res = DB::table("factura_alicuota_asociada")
      ->select("id", "importe_gravado", "Importe", "factura_encabezado_id")
      ->get();
    return $this->showAll($res);
  }

  public function Comprobante(Request $request)
  {
    $res = DB::table("factura_comprobante")
      ->select("descripcion", "id", "comprobante_codigo", "letra", "es_afip")
      ->get();
    return $this->showAll($res);
  }

  public function Concepto(Request $request)
  {
    $res = DB::table("factura_concepto")
      ->select("descripcion", "id")
      ->get();
    return $this->showAll($res);
  }

  public function Documento(Request $request)
  {
    $res = DB::table("factura_documento_comprador")
      ->select("descripcion", "id")
      ->get();
    return $this->showAll($res);
  }

  public function PtoVta(Request $request)
  {
    $res = DB::table("factura_punto_vta")
      ->select("punto_vta", "id")
      ->get();
    return $this->showAll($res);
  }

  public function CategoriaIva(Request $request)
  {
    $res = DB::table("categoria_iva")
      ->select("categoria_iva", "id")
      ->get();
    return $this->showAll($res);
  }

  public function crearFactura(Request $request)
  {
    $numero_recibo = 0;
    try {
      $medico = DB::select(
        DB::raw(
          "SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = " .
            $request->medico_id .
            ""
        )
      );
      $afip = new Afip([
        "CUIT" => (float) $medico[0]->cuit,
        "production" => $this->produccion,
        "cert" => $medico[0]->factura_crt,
        "key" => $medico[0]->factura_key,
      ]);
    } catch (\Throwable $th) {
      //throw $th;
    }

    $Iva = [];
    $i = 0;
    //if($request->factura_comprobante_id == 15){
    if ($request->es_afip === "NO") {
      $recibo = DB::select(
        DB::raw(
          "SELECT * FROM factura_encabezado WHERE  medico_id = " .
            $request->medico_id .
            " AND factura_comprobante_id = " .
            $request->factura_comprobante_id .
            " ORDER BY id DESC limit 1 "
        )
      );
      //  echo $request->factura_comprobante_id;
      // echo $request->medico_id;
      //  var_dump($recibo);
      if (!$recibo) {
        // SI NO EXISTE DEVUELVE EL PRIMER NUMERO
        //  echo 0;
        //   echo $request->factura_pto_vta_id;
        $numero_recibo = $numero_recibo + 1;
      } else {
        //  var_dump($recibo[0]);
        $numero_recibo = $recibo[0]->factura_numero + 1;
      }

      $factura_encabezado_id = DB::table("factura_encabezado")->insertGetId([
        "factura_pto_vta_id" => $request->factura_pto_vta_id,
        "medico_id" => $request->medico_id,
        "factura_comprobante_id" => $request->factura_comprobante_id,
        "factura_concepto_id" => $request->factura_concepto_id,
        "factura_documento_comprador_id" =>
          $request->factura_documento_comprador_id,
        "categoria_iva" => $request->elementoCondicionIva,
        "factura_documento" => $request->factura_documento,
        "factura_obra_social" => $request->factura_obra_social,
        "factura_cliente" => $request->factura_cliente,
        "factura_numero" => $numero_recibo,
        "fecha" => $request->fecha,
        "fecha_desde" => $request->fecha_desde,
        "fecha_hasta" => $request->fecha_hasta,
        "importe_gravado" => $request->importe_gravado,
        "importe_exento_iva" => $request->importe_exento_iva,
        "importe_iva" => $request->importe_iva,
        "importe_total" => $request->importe_total,
      ]);

      foreach ($request->facturaElectronicaRenglon as $res) {
        DB::table("factura_renglon")->insertGetId([
          "factura_id" => $factura_encabezado_id,
          "descripcion" => $res["descripcion"],
          "cantidad" => $res["cantidad"],
          "precio_unitario" => $res["precio_unitario"],
          "alicuota_id" => $res["alicuota_id"],
          "alicuota" => $res["alicuota"],
          "iva" => $res["iva"],
          "total_sin_iva" => $res["total_sin_iva"],
          "total_renglon" => $res["total_renglon"],
        ]);

        // REALIZAR COBRO

        $tmp_fecha = str_replace("/", "-", $res["mat_fecha_pago"]);
        $mat_fecha_pago = date("Y-m-d", strtotime($tmp_fecha));
        $tmp_fecha = str_replace("/", "-", $res["mat_fecha_vencimiento"]);
        $mat_fecha_vencimiento = date("Y-m-d", strtotime($tmp_fecha));

        $res = DB::table("mat_pago_historico")
          ->where("id_pago_historico", $res["id_pago_historico"])
          ->update([
            "mat_fecha_pago" => $mat_fecha_pago,
            "mat_monto" => $res["total_renglon"],
            //'mat_monto_cobrado' => $request->input('mat_monto_cobrado'),
            //'mat_num_cuota' => $res["mat_fecha_pago"],
            "mat_descripcion" => $res["descripcion"],
            //'id_concepto' => $res["mat_fecha_pago"] $request->input('id_concepto'),
            // 'mat_tipo_pago' => $res["mat_fecha_pago"] $request->input('mat_tipo_pago'),
            "mat_estado" => "P",
            "id_usuario" => $res["usuario_id"],
          ]);
      }

      return response()->json($numero_recibo, 201);
    }

    if ($request->factura_comprobante_id != 11) {
      /********************** */
      // DEBE AGREGARSE UN ARREGLO PARA EL IVA

      $j = 0;
      /*  $item =  ['id' => $s['id'] ,'BaseImp' => $s['Importe'], 'Importe' => $s['importe_gravado']];              
         $Iva[$i] = $item;
         $i++;*/
      foreach ($request->facturaAlicuotaAsociada as $s) {
        if ($s["id"] == 5) {
          $array[$j] = [
            "Id" => $s["id"], // Id del tipo de IVA (5 = 21%)
            "BaseImp" => $s["Importe"],
            "Importe" => $s["importe_gravado"],
          ];
          $j++;
        }

        if ($s["id"] == 3) {
          $array[$j] = [
            "Id" => $s["id"], // Id del tipo de IVA (5 = 21%)
            "BaseImp" => $s["Importe"],
            "Importe" => $s["importe_gravado"],
          ];
          $j++;
        }

        if ($s["id"] == 4) {
          $array[$j] = [
            "Id" => $s["id"], // Id del tipo de IVA (5 = 21%)
            "BaseImp" => $s["Importe"],
            "Importe" => $s["importe_gravado"],
          ];
          $j++;
        }

        if ($s["id"] == 6) {
          $array[$j] = [
            "Id" => $s["id"], // Id del tipo de IVA (5 = 21%)
            "BaseImp" => $s["Importe"],
            "Importe" => $s["importe_gravado"],
          ];
          $j++;
        }

        if ($s["id"] == 8) {
          $array[$j] = [
            "Id" => $s["id"], // Id del tipo de IVA (5 = 21%)
            "BaseImp" => $s["Importe"],
            "Importe" => $s["importe_gravado"],
          ];
          $j++;
        }

        if ($s["id"] == 9) {
          $array[$j] = [
            "Id" => $s["id"], // Id del tipo de IVA (5 = 21%)
            "BaseImp" => $s["Importe"],
            "Importe" => $s["importe_gravado"],
          ];
          $j++;
        }
      }

      //OBTENGO EL ULTIMO NUMERO DE COMPROBANTE

      $last_voucher = $afip->ElectronicBilling->GetLastVoucher(
        $request->factura_pto_vta_id,
        $request->factura_comprobante_id
      ); //Devuelve el número del último comprobante creado para el punto de venta 1 y el tipo de comprobante 6 (Factura B)
      /**
       * Numero de factura
       **/
      $numero_de_factura = $last_voucher + 1;

      $data = [
        // agregado por gaston
        "FchServDesde" => intval(str_replace("-", "", $request->fecha_desde)), //Fecha de inicio de servicio (formato aaaammdd)
        "FchServHasta" => intval(str_replace("-", "", $request->fecha_hasta)), //Fecha de fin de servicio (formato aaaammdd)
        "FchVtoPago" => intval(str_replace("-", "", $request->fecha)), //Fecha de vencimiento de pago (formato aaaammdd)

        "CantReg" => 1, // Cantidad de facturas a registrar
        "PtoVta" => $request->factura_pto_vta_id,
        "CbteTipo" => $request->factura_comprobante_id,
        "Concepto" => $request->factura_concepto_id,
        "DocTipo" => $request->factura_documento_comprador_id,
        "DocNro" => (float) $request->factura_documento,
        "CbteDesde" => $numero_de_factura,
        "CbteHasta" => $numero_de_factura,
        "CbteFch" => intval(str_replace("-", "", $request->fecha)),
        "ImpTotal" => $request->importe_total,
        "ImpTotConc" => 0, // Importe neto no gravado
        "ImpNeto" => $request->importe_gravado,
        "ImpOpEx" => $request->importe_exento_iva,
        "ImpIVA" => $request->importe_iva,
        "ImpTrib" => 0, //Importe total de tributos
        "MonId" => "PES", //Tipo de moneda usada en la factura ('PES' = pesos argentinos)
        "MonCotiz" => 1, // Cotización de la moneda usada (1 para pesos argentinos)
        //=>$Iva
        "Iva" => $array,
      ];
    } else {
      $last_voucher = $afip->ElectronicBilling->GetLastVoucher(
        $request->factura_pto_vta_id,
        $request->factura_comprobante_id
      ); //Devuelve el número del último comprobante creado para el punto de venta 1 y el tipo de comprobante 6 (Factura B)
      /**
       * Numero de factura
       **/
      $numero_de_factura = $last_voucher + 1;

      $data = [
        // agregado por gaston
        "FchServDesde" => intval(str_replace("-", "", $request->fecha_desde)), //Fecha de inicio de servicio (formato aaaammdd)
        "FchServHasta" => intval(str_replace("-", "", $request->fecha_hasta)), //Fecha de fin de servicio (formato aaaammdd)
        "FchVtoPago" => intval(str_replace("-", "", $request->fecha)), //Fecha de vencimiento de pago (formato aaaammdd)
        "CantReg" => 1, // Cantidad de facturas a registrar
        "PtoVta" => $request->factura_pto_vta_id,
        "CbteTipo" => $request->factura_comprobante_id,
        "Concepto" => $request->factura_concepto_id,
        "DocTipo" => $request->factura_documento_comprador_id,
        "DocNro" => (float) $request->factura_documento,
        "CbteDesde" => $numero_de_factura,
        "CbteHasta" => $numero_de_factura,
        "CbteFch" => intval(str_replace("-", "", $request->fecha)),
        "ImpTotal" => $request->importe_total,
        "ImpTotConc" => 0, // Importe neto no gravado
        "ImpNeto" => $request->importe_gravado,
        "ImpOpEx" => 0,
        "ImpIVA" => 0,
        "ImpTrib" => 0, //Importe total de tributos
        "MonId" => "PES", //Tipo de moneda usada en la factura ('PES' = pesos argentinos)
        "MonCotiz" => 1, // Cotización de la moneda usada (1 para pesos argentinos)
      ];
    }
    //var_dump($data);

    $resAfip = $afip->ElectronicBilling->CreateVoucher($data);

    //  VALIDO QUE EL RESULTADO SEA CORRECTO
    if ($resAfip) {
      $factura_encabezado_id = DB::table("factura_encabezado")->insertGetId([
        "factura_pto_vta_id" => $request->factura_pto_vta_id,
        "medico_id" => $request->medico_id,
        "factura_comprobante_id" => $request->factura_comprobante_id,
        "factura_concepto_id" => $request->factura_concepto_id,
        "factura_documento_comprador_id" =>
          $request->factura_documento_comprador_id,
        "factura_documento" => $request->factura_documento,
        "factura_obra_social" => $request->factura_obra_social,
        "factura_cliente" => $request->factura_cliente,
        "factura_numero" => $request->factura_numero,
        "fecha" => $request->fecha,
        "fecha_desde" => $request->fecha_desde,
        "fecha_hasta" => $request->fecha_hasta,
        "importe_gravado" => $request->importe_gravado,
        "importe_exento_iva" => $request->importe_exento_iva,
        "importe_iva" => $request->importe_iva,
        "importe_total" => $request->importe_total,
      ]);

      foreach ($request->facturaElectronicaRenglon as $res) {
        DB::table("factura_renglon")->insertGetId([
          "factura_id" => $factura_encabezado_id,
          "descripcion" => $res["descripcion"],
          "cantidad" => $res["cantidad"],
          "precio_unitario" => $res["precio_unitario"],
          "alicuota_id" => $res["alicuota_id"],
          "alicuota" => $res["alicuota"],
          "iva" => $res["iva"],
          "total_sin_iva" => $res["total_sin_iva"],
          "total_renglon" => $res["total_renglon"],
        ]);
      }
      /*
   
   SI ES FACTURA C EL IVA NO SE INFORMA 
   SE DIVIDE EN DOS EL SI ES 11 -- FACTURA C Y SINO, EL PRIMERO OMITE EL ARRAY DE ALICUOTA Y EL SEGUNDO CONTEMPLA TODOS LOS DATOS 
   */

      if ($request->factura_comprobante_id != 11) {
        foreach ($request->facturaAlicuotaAsociada as $res1) {
          DB::table("factura_alicuota_asociada")->insertGetId([
            "factura_encabezado_id" => $factura_encabezado_id,
            "id" => $res1["id"],
            "importe_gravado" => $res1["importe_gravado"],
            "importe" => $res1["Importe"],
          ]);
        }
      }

      DB::table("factura_encabezado")
        ->where("id", $factura_encabezado_id)
        ->update([
          "cae" => $resAfip["CAE"],
          "cae_vto" => $resAfip["CAEFchVto"],
          "factura_numero" => $numero_de_factura,
        ]);
      //   echo $numero_de_factura;
      $factura = DB::select(
        DB::raw(
          "        SELECT  cae, cae_vto, factura_numero FROM factura_encabezado WHERE factura_numero = '" .
            $numero_de_factura .
            "'"
        )
      );
    }

    return response()->json($factura, 201);
  }

  public function crearFacturaNotaCredito(Request $request)
  {
    $timezone = -3;
    $fecha = date("Y-m-d");
    $Iva = [];
    $i = 0;
    echo $request->id;

    $encabezado = DB::select(
      DB::raw(
        "SELECT factura_encabezado.id, factura_pto_vta_id, medico_id, factura_concepto_id, factura_documento_comprador_id, factura_documento, factura_cliente, factura_numero, fecha, fecha_desde, fecha_hasta, importe_gravado, importe_exento_iva, importe_iva, importe_total, cae, cae_vto , factura_punto_vta.punto_vta, factura_comprobante.id as factura_comprobante_id
        FROM factura_encabezado , factura_punto_vta, factura_comprobante, factura_concepto, factura_documento_comprador
        WHERE factura_punto_vta.id = factura_encabezado.factura_pto_vta_id AND factura_encabezado.factura_comprobante_id = factura_comprobante.id AND factura_encabezado.factura_concepto_id = factura_concepto.id AND factura_encabezado.factura_documento_comprador_id = factura_documento_comprador.id  AND factura_encabezado.id = " .
          $request->id .
          ""
      )
    );
    //  var_dump($encabezado[0]);
    //   echo $encabezado[0]->id;
    $medico = DB::select(
      DB::raw(
        "SELECT cuit, factura_key, factura_crt FROM medicos WHERE id = " .
          $encabezado[0]->medico_id .
          ""
      )
    );
    $afip = new Afip([
      "CUIT" => (float) $medico[0]->cuit,
      "production" => $this->produccion,
      "cert" => $medico[0]->factura_crt,
      "key" => $medico[0]->factura_key,
    ]);

    $alicuota = DB::select(
      DB::raw(
        " SELECT id, importe_gravado, importe, factura_encabezado_id 
                                         FROM factura_alicuota_asociada WHERE factura_encabezado_id = " .
          $encabezado[0]->id .
          ""
      )
    );
    //   var_dump($alicuota);

    $j = 0;
    foreach ($alicuota as $s) {
      if ($s->id == 5) {
        $array[$j] = [
          "Id" => 88, //$s->id, // Id del tipo de IVA (5 = 21%)
          "BaseImp" => $s->importe,
          "Importe" => $s->importe_gravado,
        ];
        $j++;
      }

      if ($s->id == 3) {
        $array[$j] = [
          "Id" => 88, //$s->id, // Id del tipo de IVA (3 = 0%)
          "BaseImp" => $s->importe,
          "Importe" => $s->importe_gravado,
        ];
        $j++;
      }

      if ($s->id == 4) {
        $array[$j] = [
          "Id" => $s->id, // Id del tipo de IVA (4 = 10.5%)
          "BaseImp" => $s->importe,
          "Importe" => $s->importe_gravado,
        ];
        $j++;
      }

      if ($s->id == 6) {
        $array[$j] = [
          "Id" => $s->id, // Id del tipo de IVA (6 = 27%)
          "BaseImp" => $s->importe,
          "Importe" => $s->importe_gravado,
        ];
        $j++;
      }

      if ($s->id == 8) {
        $array[$j] = [
          "Id" => $s->id, // Id del tipo de IVA (8 = 5%)
          "BaseImp" => $s->importe,
          "Importe" => $s->importe_gravado,
        ];
        $j++;
      }

      if ($s->id == 9) {
        $array[$j] = [
          "Id" => $s->id, // Id del tipo de IVA (2.5 = 9%)
          "BaseImp" => $s->importe,
          "Importe" => $s->importe_gravado,
        ];
        $j++;
      }
    }

    //punto de venta al que se debe anular, y comprobante -- nota de credito a b o c
    $last_voucher = $afip->ElectronicBilling->GetLastVoucher(
      $encabezado[0]->factura_pto_vta_id,
      $encabezado[0]->factura_comprobante_id
    ); //Devuelve el número del último comprobante creado para el punto de venta 1 y el tipo de comprobante 6 (Factura B)
    /**
     * Numero de factura
     **/
    $numero_de_nota = $last_voucher + 1;
    // echo $numero_de_nota;

    $data = [
      "FchServDesde" => intval(str_replace("-", "", $fecha)), //Fecha de inicio de servicio (formato aaaammdd)
      "FchServHasta" => intval(str_replace("-", "", $fecha)), //Fecha de fin de servicio (formato aaaammdd)
      "FchVtoPago" => intval(str_replace("-", "", $fecha)), //Fecha de vencimiento de pago (formato aaaammdd)
      "CantReg" => 1, // Cantidad de Notas de Crédito a registrar
      "PtoVta" => $encabezado[0]->factura_pto_vta_id,
      "CbteTipo" => $encabezado[0]->factura_comprobante_id,
      "Concepto" => $encabezado[0]->factura_concepto_id,
      "DocTipo" => $encabezado[0]->factura_documento_comprador_id,
      "DocNro" => (float) $encabezado[0]->factura_documento,
      "CbteDesde" => $numero_de_nota,
      "CbteHasta" => $numero_de_nota,
      "CbteFch" => intval(str_replace("-", "", $fecha)),
      "ImpTotal" =>
        $encabezado[0]->importe_gravado +
        $encabezado[0]->importe_iva +
        $encabezado[0]->importe_exento_iva,
      "ImpTotConc" => 0, // Importe neto no gravado
      "ImpNeto" => $encabezado[0]->importe_gravado,
      "ImpOpEx" => $encabezado[0]->importe_exento_iva,
      "ImpIVA" => $encabezado[0]->importe_iva,
      "ImpTrib" => 0, //Importe total de tributos
      "MonId" => "PES", //Tipo de moneda usada en la Nota de Crédito ('PES' = pesos argentinos)
      "MonCotiz" => 1, // Cotización de la moneda usada (1 para pesos argentinos)
      "CbtesAsoc" => [
        //Factura asociada
        [
          "Tipo" => $encabezado[0]->factura_comprobante_id,
          "PtoVta" => $encabezado[0]->factura_pto_vta_id,
          "Nro" => $encabezado[0]->factura_numero,
        ],
      ],
      "Iva" => $array,
    ];

    var_dump($data);
    $resAfip = $afip->ElectronicBilling->CreateVoucher($data);
    return response()->json($resAfip, 201);
  }

  public function GetFacturaBetweenDates(Request $request)
  {
    $tmp_fecha = str_replace("/", "-", $request->input("fecha_desde"));
    $fecha_desde = date("Y-m-d", strtotime($tmp_fecha));
    $tmp_fecha = str_replace("/", "-", $request->input("fecha_hasta"));
    $fecha_hasta = date("Y-m-d", strtotime($tmp_fecha));
    $filtro = $request->input("filtro");

    if ($filtro === "MATRICULA") {
      $res = DB::select(
        DB::raw(
          "SELECT factura_encabezado.id, factura_pto_vta_id, medico_id, factura_concepto_id, factura_encabezado.factura_documento_comprador_id, factura_documento, factura_cliente, factura_numero, fecha, fecha_desde, fecha_hasta, importe_gravado, importe_exento_iva, importe_iva, importe_total, cae, cae_vto , factura_punto_vta.punto_vta, factura_comprobante.descripcion, factura_comprobante.letra, factura_comprobante.comprobante_codigo , factura_comprobante.id as factura_comprobante_id, factura_documento_comprador.descripcion as factura_documento_comprador_descripcion , CONCAT (medicos.apellido, ' ', medicos.nombre)  as nombreyapellido , users.nombreyapellido AS creado, modulo_gravado, metodo_pago, mat_pago_historico.mat_tipo_pago 
          FROM factura_encabezado , factura_punto_vta, factura_comprobante, factura_concepto, factura_documento_comprador,  medicos, users , mat_pago_historico
          WHERE factura_punto_vta.id = factura_encabezado.factura_pto_vta_id AND factura_encabezado.factura_comprobante_id = factura_comprobante.id AND factura_encabezado.usuario_id = users.id AND factura_encabezado.factura_concepto_id = factura_concepto.id AND factura_encabezado.factura_documento_comprador_id =  factura_documento_comprador.id AND mat_pago_historico.mat_numero_recibo_id = factura_encabezado.id AND modulo_gravado = '" .
            $filtro .
            "' AND  factura_encabezado.medico_id = medicos.id AND fecha BETWEEN '" .
            $fecha_desde .
            "' AND '" .
            $fecha_hasta .
            "' GROUP BY factura_encabezado.id ORDER BY fecha DESC"
        )
      );
    } else {
      $res = DB::select(
        DB::raw(
          "SELECT factura_encabezado.id, factura_pto_vta_id, medico_id, factura_concepto_id, factura_encabezado.factura_documento_comprador_id, factura_documento, factura_cliente, factura_numero, fecha, fecha_desde, fecha_hasta, importe_gravado, importe_exento_iva, importe_iva, importe_total, cae, cae_vto , factura_punto_vta.punto_vta, factura_comprobante.descripcion, factura_comprobante.letra, factura_comprobante.comprobante_codigo , factura_comprobante.id as factura_comprobante_id, factura_documento_comprador.descripcion as factura_documento_comprador_descripcion , CONCAT (medicos.apellido, ' ', medicos.nombre)  as nombreyapellido , users.nombreyapellido AS creado, modulo_gravado, metodo_pago
            FROM factura_encabezado , factura_punto_vta, factura_comprobante, factura_concepto, factura_documento_comprador,  medicos, users 
            WHERE factura_punto_vta.id = factura_encabezado.factura_pto_vta_id AND factura_encabezado.factura_comprobante_id = factura_comprobante.id AND factura_encabezado.usuario_id = users.id AND factura_encabezado.factura_concepto_id = factura_concepto.id AND factura_encabezado.factura_documento_comprador_id =  factura_documento_comprador.id AND factura_encabezado.medico_id = medicos.id AND fecha BETWEEN '" .
            $fecha_desde .
            "' AND '" .
            $fecha_hasta .
            "' ORDER BY fecha DESC"
        )
      );
    }

    return response()->json($res, 201);
  }

  public function GetFacturaByNameOrDocumento(Request $request)
  {
    $factura_documento = $request->input("factura_documento");
    $factura_cliente = $request->input("factura_cliente");

    $res = DB::select(
      DB::raw(
        "SELECT factura_encabezado.id, factura_pto_vta_id, medico_id, factura_concepto_id, factura_encabezado.factura_documento_comprador_id, factura_documento, factura_cliente, factura_numero, fecha, fecha_desde, fecha_hasta, importe_gravado, importe_exento_iva, importe_iva, importe_total, cae, cae_vto , factura_punto_vta.punto_vta, factura_comprobante.descripcion, factura_comprobante.letra, factura_comprobante.comprobante_codigo , factura_comprobante.id as factura_comprobante_id, factura_documento_comprador.descripcion as factura_documento_comprador_descripcion , CONCAT (medicos.apellido, ' ', medicos.nombre)  as nombreyapellido
        FROM factura_encabezado , factura_punto_vta, factura_comprobante, factura_concepto, factura_documento_comprador,  medicos 
        WHERE factura_punto_vta.id = factura_encabezado.factura_pto_vta_id AND factura_encabezado.factura_comprobante_id = factura_comprobante.id AND factura_encabezado.factura_concepto_id = factura_concepto.id AND factura_encabezado.factura_documento_comprador_id =  factura_documento_comprador.id AND factura_encabezado.medico_id = medicos.id  AND (factura_encabezado.factura_cliente LIKE '%" .
          $factura_cliente .
          "%' OR factura_encabezado.factura_documento LIKE '%" .
          $factura_documento .
          "%') ORDER BY fecha DESC"
      )
    );
    return response()->json($res, 201);
  }

  public function FacturaArticulo(Request $request)
  {
    $factura = DB::select(
      DB::raw("SELECT factura_articulo.id, factura_articulo.descripcion, factura_alicuota_id, importe, cantidad, unidad, tipo_articulo, factura_alicuota.descripcion as  factura_alicuota_descripcion, factura_alicuota.porcentaje,  porcentaje_simple,factura_alicuota.iva_id, factura_tipo_articulo.dscripcion AS  factura_tipo_articulo_dscripcion, factura_tipo_articulo.tipo_movimiento 
        FROM factura_articulo, factura_alicuota, factura_tipo_articulo 
        WHERE factura_articulo.factura_alicuota_id = factura_alicuota.iva_id AND factura_articulo.tipo_articulo = factura_tipo_articulo.id  
        ORDER BY factura_articulo.id  DESC
         ")
    );
    return $factura;
  }

  public function CrearFacturaArticulo(Request $request)
  {
    $paciente_id = DB::table("factura_articulo")->insertGetId([
      "descripcion" => $request->descripcion,
      "factura_alicuota_id" => $request->factura_alicuota_id,
      "importe" => $request->importe,
      "cantidad" => $request->cantidad,
      "unidad" => $request->unidad,
      "tipo_articulo" => 5, //$request->tipo_articulo
    ]);

    return $paciente_id;
  }

  public function ActualizarFacturaArticulo(Request $request, $id)
  {
    $res = DB::table("factura_articulo")
      ->where("id", $id)
      ->update([
        "descripcion" => $request["descripcion"],
        "factura_alicuota_id" => $request["factura_alicuota_id"],
        "importe" => $request["importe"],
        "cantidad" => $request["cantidad"],
        "unidad" => $request["unidad"],
        "tipo_articulo" => 5, //$request['tipo_articulo']
      ]);
    return $res;
  }

  public function GetFacturaByArticuloTipo(Request $request)
  {
    $res = DB::table("factura_tipo_articulo")
      ->select("descripcion", "id", "tipo_movimiento")
      ->get();
    return $this->showAll($res);
  }

  public function ActualizarFacturaArticuloTipo(Request $request, $id)
  {
    $res = DB::table("factura_tipo_articulo")
      ->where("id", $id)
      ->update([
        "dscripcion" => $dscripcion,
        "tipo_movimiento" => $tipo_movimiento,
      ]);
    return $res;
  }

  public function CrearFacturaArticuloTipo(Request $request)
  {
    $paciente_id = DB::table("factura_tipo_articulo")->insertGetId([
      "descripcion" => $request->descripcion,
      "tipo_movimiento" => $request->factura_alicuota_id,
    ]);

    return $paciente_id;
  }

  public function ReimprimirFactura(Request $request)
  {
    $factura = DB::select(
      DB::raw(
        "SELECT factura_encabezado.id , factura_pto_vta_id, medico_id, factura_encabezado.factura_comprobante_id, factura_concepto_id, 
        factura_encabezado.factura_documento_comprador_id, factura_documento, factura_cliente, factura_numero, fecha, fecha_desde, fecha_hasta, importe_gravado, importe_exento_iva, 
        importe_iva, importe_total, cae, cae_vto, factura_punto_vta.punto_vta, factura_renglon.descripcion,factura_renglon.cantidad, factura_renglon.precio_unitario, 
        factura_renglon.alicuota, factura_renglon.alicuota_id, factura_renglon.iva, factura_renglon.total_sin_iva, factura_renglon.total_renglon, factura_comprobante.descripcion as factura_comprobante_descripcion 
        , factura_comprobante.letra, factura_comprobante.comprobante_codigo, factura_comprobante.id_interno , factura_concepto.descripcion as   factura_concepto_descripcion, factura_documento_comprador.descripcion as  factura_documento_comprador_descripcion
        , CONCAT (medicos.apellido, ' ', medicos.nombre)  as nombreyapellido
        FROM factura_encabezado,factura_punto_vta, factura_renglon, factura_comprobante, factura_concepto, factura_documento_comprador, medicos 
        WHERE  factura_encabezado.id = factura_renglon.factura_id AND factura_encabezado.factura_pto_vta_id = factura_punto_vta.id AND factura_encabezado.medico_id = medicos.id  
        AND  factura_encabezado.factura_comprobante_id = factura_comprobante.id
         AND factura_encabezado.factura_concepto_id = factura_concepto.id AND  factura_encabezado.factura_documento_comprador_id = factura_documento_comprador.id AND factura_numero = " .
          $request->input("factura_numero") .
          "
         "
      )
    );
    return $factura;
  }

  public function FacturaById(Request $request)
  {
    $factura = DB::select(
      DB::raw(
        "SELECT factura_encabezado.id , factura_pto_vta_id, medico_id, factura_encabezado.factura_comprobante_id, factura_concepto_id, 
        factura_encabezado.factura_documento_comprador_id, factura_documento, factura_cliente, factura_numero, fecha, fecha_desde, fecha_hasta, importe_gravado, importe_exento_iva, 
        importe_iva, importe_total, cae, cae_vto, factura_punto_vta.punto_vta, factura_renglon.descripcion,factura_renglon.cantidad, factura_renglon.precio_unitario, 
        factura_renglon.alicuota, factura_renglon.alicuota_id, factura_renglon.iva, factura_renglon.total_sin_iva, factura_renglon.total_renglon, factura_comprobante.descripcion as factura_comprobante_descripcion 
        , factura_comprobante.letra, factura_comprobante.comprobante_codigo, factura_comprobante.id_interno , factura_concepto.descripcion as   factura_concepto_descripcion, factura_documento_comprador.descripcion as  factura_documento_comprador_descripcion
        , CONCAT (medicos.apellido, ' ', medicos.nombre)  as nombreyapellido, factura_renglon.descripcion, cantidad, precio_unitario, alicuota_id, alicuota, iva, total_sin_iva, total_renglon , medicos.ing_brutos, medicos.cuit, medicos.fecha_alta_afip, medicos.domicilio, categoria_iva.categoria_iva, modulo_gravado, metodo_pago
        FROM factura_encabezado,factura_punto_vta, factura_renglon, factura_comprobante, factura_concepto, factura_documento_comprador, medicos , categoria_iva
        WHERE  factura_encabezado.id = factura_renglon.factura_id AND factura_encabezado.factura_pto_vta_id = factura_punto_vta.id AND factura_encabezado.medico_id = medicos.id  
        AND  factura_encabezado.factura_comprobante_id = factura_comprobante.id
         AND factura_encabezado.factura_concepto_id = factura_concepto.id AND  factura_encabezado.factura_documento_comprador_id = factura_documento_comprador.id AND categoria_iva.id = medicos.categoria_iva_id  AND factura_encabezado.id = " .
          $request->input("factura_numero") .
          "
         "
      )
    );

    $matricula = DB::select(
      DB::raw(
        "SELECT id_pago_historico, mat_matricula, CONCAT(mat_matricula.mat_apellido, ' ' , mat_matricula.mat_nombre) AS mat_nombreyapellido, mat_fecha_pago, mat_fecha_vencimiento, mat_pago_historico.mat_monto, mat_monto_cobrado, mat_interes, mat_pago_historico.mat_descripcion,
    mat_num_cuota, mat_id_plan, mat_numero_comprobante, mat_tipo_pago, mat_estado, id_usuario ,
     mat_pago_historico.id_concepto, mat_concepto , nombreyapellido, id_liquidacion_detalle, mat_numero_recibo, mat_numero_recibo_id
    FROM `mat_pago_historico`, mat_concepto, mat_matricula  , users
    WHERE  mat_pago_historico.mat_matricula = mat_matricula.mat_matricula_psicologo
    AND mat_concepto.id_concepto = mat_pago_historico.id_concepto
    AND users.id = mat_pago_historico.id_usuario
    AND mat_numero_recibo_id  =   " .
          $request->input("factura_numero") .
          "
         "
      )
    );

    $cobro = DB::select(
      DB::raw(
        "SELECT mov_registro.id, mov_concepto_cuenta_id, descripcion, mov_cuenta_id , fecha_carga, mov_tipo_comprobante_id,
         comprobante_numero, tiene_enlace_factura, mov_tipo_moneda_id,  mov_registro.importe,  mov_registro.cotizacion,
         mov_registro.total, factura_encabezado_id, paciente_id, proveedor_id, proveedor_nombre, 
        proveedor_cuit, proveedor_direccion , 
       factura_encabezado.factura_pto_vta_id, factura_encabezado.medico_id, factura_encabezado.factura_comprobante_id, 
        factura_encabezado.factura_concepto_id, concepto_cuenta, 
        cuenta_nombre, movimiento_tipo, tipo_comprobante ,tipo_moneda , cierre_caja_id, mov_registro.usuario_id, mov_registro.factura_numero
        FROM mov_registro 
        LEFT JOIN paciente_proveedor ON mov_registro.proveedor_id = paciente_proveedor.id        
        LEFT JOIN factura_encabezado ON mov_registro.factura_encabezado_id = factura_encabezado.id ,
        mov_concepto_cuenta, mov_cuenta, mov_tipo_comprobante, mov_tipo_moneda 
        WHERE mov_registro.mov_concepto_cuenta_id = mov_concepto_cuenta.id 
        AND mov_registro.mov_cuenta_id = mov_cuenta.id 
        AND mov_registro.mov_tipo_comprobante_id = mov_tipo_comprobante.id 
        AND mov_registro.mov_tipo_moneda_id = mov_tipo_moneda.id
        AND factura_encabezado.id  =   " .
          $request->input("factura_numero") .
          "
    "
      )
    );

    return [
      "factura" => $factura,
      "matricula" => $matricula,
      "cobro" => $cobro,
    ];
    //  return $matricula;
  }

  public function getLibroIva(Request $request)
  {
    $medico_id = $request->input("medico_id");
    $tmp_fecha = str_replace("/", "-", $request->input("fecha_desde"));
    $fecha_desde = date("Y-m-d H:i:s", strtotime($tmp_fecha));
    $tmp_fecha = str_replace("/", "-", $request->input("fecha_hasta"));
    $fecha_hasta = date("Y-m-d H:i:s", strtotime($tmp_fecha));

    $factura = DB::select(
      DB::raw(
        "SELECT factura_encabezado.fecha, factura_comprobante.descripcion as comprobante_tipo, CONCAT( LPAD(factura_punto_vta.punto_vta,4,0),'-',LPAD(factura_encabezado.factura_numero,8,0)) as numero , factura_renglon.descripcion  , factura_encabezado.categoria_iva, factura_encabezado.factura_cliente,factura_documento_comprador.descripcion as  DNI_CUIT, factura_encabezado.factura_documento , ((factura_renglon.alicuota-1)*100) AS alicuota , factura_renglon.iva, 
        factura_renglon.total_sin_iva, factura_renglon.total_renglon importe_gravado, importe_exento_iva,importe_iva, importe_total
        FROM factura_encabezado, factura_punto_vta, medicos, factura_comprobante, factura_concepto, factura_documento_comprador, categoria_iva, factura_renglon, factura_alicuota
        WHERE factura_encabezado.factura_pto_vta_id = factura_punto_vta.id AND factura_encabezado.medico_id = medicos.id AND factura_encabezado.factura_comprobante_id = factura_comprobante.id AND factura_encabezado.factura_concepto_id = factura_concepto.id AND factura_encabezado.factura_documento_comprador_id = factura_documento_comprador.id AND medicos.categoria_iva_id = categoria_iva.id AND factura_encabezado.id = factura_renglon.factura_id AND  factura_encabezado.factura_comprobante_id != 15 AND factura_renglon.alicuota_id = factura_alicuota.id AND medico_id = '" .
          $medico_id .
          "' AND  factura_encabezado.fecha    BETWEEN '" .
          $fecha_desde .
          "' AND '" .
          $fecha_hasta .
          "'  
        ORDER BY factura_encabezado.fecha, numero  ASC
         "
      )
    );
    return $factura;
  }

  public function cobrarRecibo(Request $request)
  {
    $sector = $request->input("sector_cobro");
    $factura_comprobante_id = 15;

    if ($sector === "MATRICULA") {
      $factura_comprobante_id = 18;
    }
    if ($sector === "INGRESO") {
      $factura_comprobante_id = 16;
    }
    if ($sector === "EGRESO") {
      $factura_comprobante_id = 17;
    }
    if ($sector === "INTERNO") {
      $factura_comprobante_id = 15;
    }
    $factura_numero = $this->getLastFacturaByPtoVta($request->punto_vta);
    $_factura_numero = $factura_numero[0]->factura_numero + 1;

    $factura_encabezado_id = DB::table("factura_encabezado")->insertGetId([
      "factura_pto_vta_id" => $request->punto_vta,
      "medico_id" => 1,
      "factura_comprobante_id" => $factura_comprobante_id,
      "factura_concepto_id" => 3,
      "factura_documento_comprador_id" => 96,
      "categoria_iva" => "Consumidor Final",
      "factura_documento" => $request->matricula,
      "factura_obra_social" => "",
      "factura_cliente" => $request->nombreyapellido,
      "factura_numero" => $_factura_numero,
      "fecha" => $request->mat_fecha_pago,
      "fecha_desde" => $request->mat_fecha_pago,
      "fecha_hasta" => $request->mat_fecha_pago,
      "importe_gravado" => 0,
      "importe_exento_iva" => 0,
      "importe_iva" => 0,
      "importe_total" => $request->total,
      "usuario_id" => $request->usuario_id,
      "modulo_gravado" => "MATRICULA",
    ]);

    foreach ($request->reciboElectronico as $res) {
      $res1 = DB::table("factura_renglon")->insertGetId([
        "factura_id" => $factura_encabezado_id,
        "descripcion" => $res["mat_descripcion"],
        "cantidad" => 1,
        "precio_unitario" => $res["mat_monto_cobrado"],
        "alicuota_id" => 3,
        "alicuota" => 0,
        "iva" => 0,
        "total_sin_iva" => $res["mat_monto_cobrado"],
        "total_renglon" => $res["mat_monto_cobrado"],
      ]);
      // REALIZAR COBRO

      $res = DB::table("mat_pago_historico")
        ->where("id_pago_historico", $res["id_pago_historico"])
        ->update([
          "mat_numero_recibo" => $_factura_numero,
          "mat_numero_recibo_id" => $factura_encabezado_id,
          "mat_fecha_pago" => $request->mat_fecha_pago,
          "mat_monto" => $res["mat_monto_cobrado"],
          "mat_descripcion" => $res["mat_descripcion"],
          "mat_estado" => "P",
          "id_usuario" => $request->usuario_id,
        ]);
    }

    return response()->json($_factura_numero, 201);
  }

  public function cobrarMovimientoCaja(Request $request)
  {
    $sector = $request->input("sector");
    $factura_comprobante_id = 15;

    if ($sector === "MATRICULA") {
      $factura_comprobante_id = 18;
    }
    if ($sector === "INGRESO") {
      $factura_comprobante_id = 16;
    }
    if ($sector === "EGRESO") {
      $factura_comprobante_id = 17;
    }
    if ($sector === "INTERNO") {
      $factura_comprobante_id = 15;
    }
    $factura_numero = $this->getLastFacturaByPtoVta($request->punto_vta);
    $_factura_numero = $factura_numero[0]->factura_numero + 1;

    $factura_encabezado_id = DB::table("factura_encabezado")->insertGetId([
      "factura_pto_vta_id" => $request->punto_vta,
      "medico_id" => 1,
      "factura_comprobante_id" => $factura_comprobante_id,
      "factura_concepto_id" => 3,
      "factura_documento_comprador_id" => 96,
      "categoria_iva" => "Consumidor Final",
      "factura_documento" => $request->proveedor_cuit,
      "factura_obra_social" => "",
      "factura_cliente" => $request->proveedor_nombre,
      "factura_numero" => $_factura_numero,
      "fecha" => $request->fecha_carga,
      "fecha_desde" => $request->fecha_carga,
      "fecha_hasta" => $request->fecha_carga,
      "importe_gravado" => 0,
      "importe_exento_iva" => 0,
      "importe_iva" => 0,
      "importe_total" => $request->total,
      "usuario_id" => $request->usuario_id,
      "modulo_gravado" => $sector,
    ]);

    $res1 = DB::table("factura_renglon")->insertGetId([
      "factura_id" => $factura_encabezado_id,
      "descripcion" => $request->reciboElectronicoCaja["descripcion"],
      "cantidad" => 1,
      "precio_unitario" => $request->reciboElectronicoCaja["total"],
      "alicuota_id" => 3,
      "alicuota" => 0,
      "iva" => 0,
      "total_sin_iva" => $request->reciboElectronicoCaja["total"],
      "total_renglon" => $request->reciboElectronicoCaja["total"],
    ]);
    // REALIZAR COBRO

    $res = DB::table("mov_registro")
      ->where("id", $request->mov_registro_id)
      ->update([
        "comprobante_numero" => $_factura_numero,
        "factura_numero" => $_factura_numero,
        "factura_encabezado_id" => $factura_encabezado_id,
        "usuario_id" => $request->usuario_id,
      ]);

    return response()->json($_factura_numero, 201);
  }

  public function getLastFacturaByPtoVta($pto_vta_id)
  {
    $factura = DB::select(
      DB::raw(
        "  SELECT MAX(factura_numero) as  factura_numero FROM `factura_encabezado` WHERE `factura_pto_vta_id` = " .
          $pto_vta_id .
          "
         "
      )
    );

    return $factura;
  }
}
