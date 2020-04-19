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
     $horario = DB::table('users')
            
     ->select(
        'users.id',
        'users.name',
        'users.nombreyapellido',
        'users.email',  
        'users.admin',
        'users.user_modulo_id'    
        )
            ->where('users.email','=',$email)                                   
            ->get();
           
        return $this->showAll($horario);
    
    }
    

    public function getMenu(Request $request )
    {
              
       
     //echo $fecha_turno;
     $horario = DB::table( 'modulo')       
     ->select(
        'id as modulo_id',
        'nombre as modulo_nombre'
        )            
       ->get();
           
        return $this->showAll($horario);
    
    }

    public function agregarMenuUsuario(Request $request,$id){       
      
        $i = 0;
       $modulo_id =   $request['modulo_id'] ;
         
         $id= DB::table('user_modulo')->insertGetId(
           [ 'user_id' => $id,
            'modulo_id' =>  $modulo_id    ]    
        );
    

        return response()->json($modulo_id, "201"); 
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
}
