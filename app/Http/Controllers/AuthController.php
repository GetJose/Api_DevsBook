<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function unauthorized(){
        return response()->json(['error' => 'access denied'], 401);
    }

    public function login(Request $request){
        $array = ['error'=> ''];

        $email = $request->input('email');
        $password = $request->input('password');
        if($email && $password){
            $token = auth()->attempt([
                'email' => $email,
                'password' => $password
            ]);
    
            if(!$token){
                $array['error'] = 'E-mail or Password is wrong';
                return $array;
            }
            $array['token'] = $token;
            return $array;
        }else{
            $array['error'] = 'incomplet data';
            return $array;
        }

        return $array;
    }

    public function logout(){
        auth()->logout();
        $array = ['error'=> ''];
        return $array;
    }

    public function reload(){
        $token = auth()->refresh();
        $array = [ 
            'error' => '',
            'token' => $token,
        ];
        return $array;
    }

    public function create(Request $r){
        $array = ['error'=> ''];

       $name = $r->input('name');
       $email = $r->input('email');
       $password = $r->input('password');
       $birthdate = $r->input('birthdate');

       if($name && $email && $password && $birthdate){
            if(strtotime($birthdate) === false){
                $array = ['error' => 'the date of birthdate is invalid'];
                return $array;
            }
            $emailverification = User::where('email', $email)->count();
            if($emailverification === 0){
               $hash = password_hash($password,PASSWORD_DEFAULT);
               $newUser = new User();
               $newUser->name = $name;
               $newUser->email = $email;
               $newUser->password = $hash;
               $newUser->birth_date = $birthdate;
               $newUser->save();

               $token = auth()->attempt([
                'email' => $email,
                'password' => $password
               ]);

               if(!$token){
                $array = ['error' => 'an error has occurred'];
                return $array;
               }
               $array['token'] = $token;
            }else{
                $array = ['error' => 'the email is alredy begin used'];
                return $array;
            }

       }else{
        $array = ['error'=> 'all fildes are requerid'];
        return $array;
       }


        return $array;
    }
}
