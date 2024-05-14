<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->loggedUser = auth()->user();
    }

    public function search(Request $request){
        $array = ['error' => '', 'users' => []];
        $txt = $request->input('txt');

        if($txt){
            $userList = User::where('name', 'like', '%'.$txt.'%')->get();
            foreach($userList as $userItem){
                $array['users'][] = [
                    'id' => $userItem['id'],
                    'name' => $userItem['name'],
                    'avatar' => url('media/avatars/'.$userItem['avatar'])
                ];
            }
        }else{
            $array['error'] = 'the search term must not be null';
        }
        return $array;
    }
}
