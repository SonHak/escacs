<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\fichas;
use App\partida;

const peon_b = 1;
const peon_n = 2;



class ControladorPrincipal extends Controller
{
    //
    //logea usuario y devuelve token
    public function login(Request $request)
    {
        $email = $request->input('email');
        $pass = $request->input('password');


        if (Auth::attempt(['email'=>$email,'password'=>$pass])) {
            // Authentication passed...

            $rand_part = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789".uniqid());

            $user = User::where('email','=',$email)->first();
            $user->token = $rand_part;
            $user->save();

            header("Access-Control-Allow-Origin: *");

            return $user;
            
        }else{

            header("Access-Control-Allow-Origin: *");

            return "error al loggear";
        }
        
    }


    //deslogea al usuario
    public function logout(Request $request)
    {

        $email = $request->input('email');
        $token = $request->input('token');


        Auth::logout();


        header("Access-Control-Allow-Origin: *");
        return "Logout +1";

    }


    //devuelve una lista de usuarios que no están en partida
    public function espera(Request $request)
    {  
        $enPartida = $this->comprobarPartidas();

        if(count($enPartida) === 0){
            header("Access-Control-Allow-Origin: *");
            return User::select()->get();
        }else{    
            //hace una sentencia sql para devolver todos los usuarios que no están en partida
            header("Access-Control-Allow-Origin: *");
            return User::select()->whereNotIn('id',$enPartida)->get();
        }
        

 
    }





    public function jugar(Request $request)
    {
        
        try{

         $newPartida = new Partida;

         $user1 = $request->input('usuarioCrea');
         $user2 = $request->input('usuarioAcepta');
        
         $aleatorio = rand(0,1);


         //si aleatorio es 0 empieza las blancas
         if($aleatorio === 0){
            $newPartida->blancas = 1;
         }
         //si aleatorio es 1 empiezan las negras
         else{
            $newPartida->negras = 1;
         }

         $newPartida->user1 = $user1;
         $newPartida->user2 = $user2;

         $newPartida->save();

            header("Access-Control-Allow-Origin: *");
            return ($newPartida);
         //return $this->inicializaPartida($newPartida->id);
         }
         catch (\Exception $e){
            header("Access-Control-Allow-Origin: *");
            return $e->getMessage();

         }
        

    }






    public function setFichas(Request $request){
       
      

       $currentId = $request->input('currentId');

       fichas::where("idPartida", $currentId)->delete();

       $peon1 = new fichas;

       $peon1->posFinal = '25';
       $peon1->idPartida = $currentId;
       $peon1->color = 'blanca';
       $peon1->figura = 2;
       $peon1->save();

       $peon2 = new fichas;
       $peon2->posFinal = '73';
       $peon2->idPartida = $currentId;
       $peon2->color = 'negra';
       $peon2->figura = 1;
       $peon2->save();
        return response(["state"=>"1"])->header("Access-Control-Allow-Origin:", "*");
       return json_encode(["state"=>"1"]);
    }


      public function getFichas(Request $request){
        
        $id = $request->input('id');

        header("Access-Control-Allow-Origin: *");
        return fichas::where('idPartida','=',$id)->get();

    }




    //funcion que devuelve una array con los usuarios que están en partida
    private function comprobarPartidas(){
        $usuarios = User::all();
        $partidas = Partida::all();

        $enPartida = [];
         //recorre todos los usuarios que hay
        foreach($usuarios as $user){
            //Recorre todas las partidas que hay
            foreach($partidas as $partida){

                //comprueba si el id del usuario está en el campo user1 o user2 
                //de la partida y lo guarda en una array
                if($user->id == $partida->user1 || $user->id == $partida->user2){ 
                        array_push($enPartida,$user->id);
                    
                }
            }
        }
        header("Access-Control-Allow-Origin: *");
        return $enPartida;
    }



    public function getPartida(Request $request){
        
        $id = $request->input('id');

        header("Access-Control-Allow-Origin: *");
        return Partida::where('user1','=',$id)->orWhere('user2','=',$id)->first();

    }

    public function actualizar(Request $request){

        

        $posOrigen = $request->input('posOrigen');
        $posFinal = $request->input('posFinal');
        $idFicha = $request->input('idFigura');
        $idPartida = $request->input('idPartida');

        $fichaAct = fichas::where('idPartida','=',$idPartida)->where("id","=",$idFicha)
            ->update(['posInicial' => $posOrigen,'posFinal' => $posFinal]);

       // $fichaAct->posInicial = $posOrigen;
       // $fichaAct->posFinal = $posFinal;

        //$fichaAct->save();

        header("Access-Control-Allow-Origin: *");
        return json_encode(["state"=>"1"]);

    }


}




