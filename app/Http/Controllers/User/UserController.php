<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\User;
use Illuminate\Support\Facades\DB; 

class UserController extends ApiController
{

    public function __construct(){
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::all();
        return $this->showAll($user);
     
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ];

        $this->validate($request, $rules);
    
        $params = $request->all();
        $params ['password'] = bcrypt($request->password);
        $params['verified'] = User::USUARIO_NO_VERIFICADO;
        $params['verification_token'] = User::generarVerificationToken();
        $params['admin'] = User::USUARIO_REGULAR;

        $usuario = User::create($params);
        
        return response()-> json(['data'=>$usuario],201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = User::all();
        return response()-> json(array(
          'usuario' => $user,
          'status' => 'success'
        ), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }


    
    public function getPassword(Request $request)
    {
           
        
        $password =$request->input('password');
        $ret_password=bcrypt($password);
       
      return response()->json($ret_password, 201);
    }

    public function getUserDataAndMenu(Request $request )
    {
              
        $email =  $request->input('email');
     //echo $fecha_turno;
     $horario = DB::table('user_modulo', 'modulo','users')
     ->join('users', 'users.id', '=', 'user_modulo.user_id')        
     ->join('modulo', 'modulo.id', '=', 'user_modulo.modulo_id')        
     ->select(
        'users.id',
        'users.name',
        'users.admin',
        'users.nombreyapellido',
        'users.email',
        'modulo.id as modulo_id',
        'modulo.nombre as modulo_nombre',   
        'titulo',
        'user_modulo.id as user_modulo_id',
        'users.puede_notificar'
        )
            ->where('users.email','=',$email)      
            ->orderBy('titulo', 'ASC')                             
            ->get();
           
        return $this->showAll($horario);
    
    }
    

    public function getMenu(Request $request )
    {
              
       
     //echo $fecha_turno;
     $horario = DB::table( 'modulo')       
     ->select(
        'id as modulo_id',
        'nombre as modulo_nombre',
        'titulo'
        )            
        ->orderBy('titulo', 'ASC')
       ->get();
           
        return $this->showAll($horario);
    
    }

    public function agregarMenuUsuario(Request $request,$id){       
      
        $i = 0;

     //  echo $request[0]['modulo_id'];
       foreach($request as $req) {
        $res = DB::select( DB::raw(" INSERT INTO user_modulo (user_id, modulo_id) SELECT '".$id."','".$request[$i]['modulo_id']."' FROM DUAL
        WHERE NOT EXISTS 
          (SELECT user_id,modulo_id FROM user_modulo WHERE user_id = '".$id."' AND modulo_id= '".$request[$i]['modulo_id']."' )"));
        $i++; 
       }
      
  /*     $modulo_id =   $request['modulo_id'] ;
         
         $id= DB::table('user_modulo')->insertGetId(
           [ 'user_id' => $id,
            'modulo_id' =>  $modulo_id    ]    
        );
    
*/
      //  var_dump($request);

        return response()->json($request, "201"); 
//echo $id;
    }

    
    public function borrarMenuUsuario($id){            
        DB::table('user_modulo')->where('id', $id)->delete();
        
        return response()->json("ok", "201"); 
//echo $id;
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
        {
            $user->fill($request->only([
                'name',
        ]));

    if ($user->isClean()) {
            return $this->errorRepsonse('Se debe especificar al menos un valor', 422);
        }
    $user->save();
        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    
    public function getUsuarios(Request $request )
    {
               $horario = DB::select( DB::raw(" SELECT `id`, `name`, `nombreyapellido`, `email` FROM `users`             "));

        return response()->json($horario, 201);
    
    }

    public function CrearUsuario(Request $request )
    {
        $id =    DB::table('users')->insertGetId(
            ['name' => $request["name"],
            'nombreyapellido' => $request["nombreyapellido"],
            'email' => $request["email"],
            'password' => '$2y$10$qC.hjJ7O6Mm9qd5rWDqgmef7GgF7tIWPQuMFK5EG06hHJGET8Y8wa',
            'verification_token'=> '1',
            'admin' => $request["admin"],
            'puede_notificar'=> 'SI',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")    
             ]           
            );  

        return response()->json($id, 201);
    
    }

    public function EditarUsuario(Request $request, $id)
    {
        $update = DB::table('users')         
        ->where('id', $id)->limit(1) 
        ->update( [            
         'name' =>$request->input('name'),
         'nombreyapellido' =>$request->input('nombreyapellido'),
         'email' =>$request->input('email'),
         'admin' =>$request->input('admin'),
         'updated_at' => date("Y-m-d H:i:s")  
          ]);  
          return response()->json($update, 201);
        } 

       
    

    public function EditarUsuarioPassword(Request $request,$id )
    {

        bcrypt($request->password);
        //ech
        $result = DB::select( DB::raw(" 
        SELECT * FROM  users WHERE id = :id")
       , array(
           'id' => $id
       ));

   $password = $request->input('password');
   $ret_password=bcrypt($password);

       $update = DB::table('users')->limit(1) 
       ->where('id',  $id)
       ->update( [ 
        'password' => bcrypt($request->password),       
        'updated_at' => date("Y-m-d H:i:s")     ]); 

   

        return response()->json($update, 201);
    
    }
}



//$2y$10$1oz148ZpmshOaJvRyILBJ.74kNouYf0cnzU2V2ucLHbViZjXnNlqi
 