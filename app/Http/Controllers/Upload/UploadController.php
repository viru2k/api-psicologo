<?php

namespace App\Http\Controllers\Upload;
use Illuminate\Support\Facades\DB; 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    //


    public function uploadSubmit(Request $request)
    {

     /*   $this->validate($request, [
        'name' => 'required',
        'photos'=>'required',
        ]);
    if($request->hasFile('photos'))
    {
        $allowedfileExtension=['pdf','jpg','png','docx'];
        $files = $request->file('photos');
        foreach($files as $file){
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $check=in_array($extension,$allowedfileExtension);
        //dd($check);
    if($check)
    {
        $items= Item::create($request->all());
        foreach ($request->photos as $photo) {
            $filename = $photo->store('photos');
            ItemDetail::create([
            'item_id' => $items->id,
            'filename' => $filename
            ]);
         }
         return response()->json('subido con éxito', 201);
    }
    else
    {
        return response()->json($request, 201);
    
    }
    }
    }*/
    return response()->json($request, 201);
    }

/* -------------------------------------------------------------------------- */
/*              subida de archivos  y asociacion a base de datos              */
/* -------------------------------------------------------------------------- */

    public function showUploadFile(Request $request) {

                    
         
        $parts = explode('/', $request->url());
       
         $mat_matricula =  $parts[8];
         $liq_liquidacion_detalle_id =  $parts[9];
         $id_liquidacion =  $parts[10];       
         $files = $request->file('images');
         $fecha = date("Y-m-d-H-i-s");
         $allowedfileExtension=['pdf','jpg','png','docx','pdf'];
         $files = $request->file('images');
         foreach($files as $file){
         $filename = $file->getClientOriginalName();
     //    echo $filename;
         $extension = $file->getClientOriginalExtension();
     //    echo $extension;
         $check=in_array($extension,$allowedfileExtension);
         $parts = explode('/', $request->url());    
          $last = end($parts);
      //   echo $last;
         $destinationPath = 'uploads/'.$id_liquidacion.'/'.$mat_matricula.$last.'-'.$fecha;
         $without_extension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
         




            $file->move($destinationPath,$filename);

            $id =    DB::table('factura_liquidacion')->insertGetId([              
                'mat_matricula' => $mat_matricula, 
                'liq_liquidacion_detalle_id' => $liq_liquidacion_detalle_id, 
                'id_liquidacion' => $id_liquidacion, 
                 'url' => $destinationPath.'/'.$filename,
                 'fecha_subida' => date("Y-m-d H:i:s")                 
            ]);        

            } 

        return response()->json("Upload Successfully ", 201); 
     }







     public function showUploadFileDatos(Request $request){
        $fecha = date("Y-m-d-Hi");
        $t =$request;
        $subcarpeta = 
        $tmp_fecha = str_replace('/', '-', $t[0]["fecha_estudio"]);
         $fecha_estudio =  date('Y-m-d H:i', strtotime($tmp_fecha));
     $id =    DB::table('estudio')->insertGetId(
         ['estudio' => $t[0]["estudio"],
        'paciente_id' => $t[0]["paciente_id"],
         'medico_id' => $t[0]["medico_id"],
         'fecha_estudio' => $fecha_estudio,
         'usuario_realiza_id' => $t[0]["usuario_realiza_id"],
         'updated_at' => date("Y-m-d H:i:s"),
         'created_at' => date("Y-m-d H:i:s")
         ]           
        );
        $i = 0;
        while(isset($t[$i])){
            
      //  $usuario_id=$t[$i]["usuario_id"];
    //    $tmp_fecha = str_replace('/', '-', $t[$i]["fecha"]);
       // $fecha_desde =  date('Y-m-d H:i', strtotime($tmp_fecha));   
         $estudio_id =    DB::table('estudios_imagen')->insertGetId(             
            [
            'estudio_id' => $id,
            'nombre' => $t[$i]["file_name"],
            'file' => $t[$i]["file"],
            'url' => 'uploads/'.$fecha,
            'updated_at' => date("Y-m-d H:i:s"),
            'created_at' => date("Y-m-d H:i:s")
             ]           
            );   
            $i++;
        }    

        $FICHA_id =    DB::table('ficha')->insertGetId(
            [
           'paciente_id' => $t[0]["paciente_id"],
            'PACIENTE' => $t[0]["paciente_dni"],
            'MEDICONOM' => 'ESTUDIOS',
            'MEDICO' => 'SIS-NU',
            'FECHA' => $fecha_estudio
            
            ]           
           );

           $FICHAOFTALMO_id =    DB::table('fichaoftal01')->insertGetId(
            ['estudio_id' => $id,          
            'estudio_nombre' => $t[0]["estudio"],
            'NUMERO' => $FICHA_id,
            'updated_at' => date("Y-m-d H:i:s"),
            'created_at' => date("Y-m-d H:i:s")
            ]           
           );
        return response()->json($t, "201");
    }


    public function getEstudioImagenes(Request $request){
        $id =$request->input('id');

        $horario = DB::select( DB::raw("SELECT estudio.id as estudio_id, estudio.paciente_id , fecha_estudio, nombre, file, url, estudio FROM `estudio`, estudios_imagen WHERE  estudio.id = estudios_imagen.estudio_id AND estudio.id = ".$id."
    "));
       
      return response()->json($horario, 201);

    }
    public function getLocalStoragePath(){
   // echo Storage::disk('local')->url($fileName);
}




/******************************************************************************************** */
// ARCHIVOS DE NOTIFICACIONES


public function showUploadFileNoticias(Request $request) {

                    var_dump($request);
         
    $parts = explode('/', $request->url());
   //  var_dump($parts);
     $tipo_documento =  $parts[8];
     $tipo_privacidad =  $parts[9];     
   //  echo $mat_matricula;
   $files = $request->file('images');
 
   
     $fecha = date("Y-m-d-H-i-s");
     $allowedfileExtension=['docx','pdf','PDF', 'doc','MP4','mp4','jpg','JPG','PNG','png','jpeg','JPEG','avi', 'AVI'];
     $files = $request->file('images');
     foreach($files as $file){
     $filename = $file->getClientOriginalName();
 
     $extension = $file->getClientOriginalExtension();
 
     $check=in_array($extension,$allowedfileExtension);
     $parts = explode('/', $request->url());    
      $last = end($parts);
  
     $destinationPath = 'uploads/'.$tipo_documento.$last.'-'.$fecha;
     $without_extension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
     

/* 


        $file->move($destinationPath,$filename);

        $id =    DB::table('factura_liquidacion')->insertGetId([              
            'mat_matricula' => $mat_matricula, 
            'liq_liquidacion_detalle_id' => $liq_liquidacion_detalle_id, 
            'id_liquidacion' => $id_liquidacion, 
             'url' => $destinationPath.'/'.$filename,
             'fecha_subida' => date("Y-m-d H:i:s")                 
        ]);     */    

        } 

    return response()->json("Upload Successfully ", 201); 
 }

}
