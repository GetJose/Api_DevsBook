<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\UserRelation;
use DateTime;
use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;
use SebastianBergmann\Diff\Diff;

class UserController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->loggedUser = auth()->user();
    }

    public function update(Request $request)
    {
        $array['error'] = '';

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');
        $birthdate = $request->input('birthdate');

        $city = $request->input('city');
        $work = $request->input('work');

        $user = User::find($this->loggedUser['id']);
        if ($name) {
            $user->name = $name;
        }
        if ($email) {
            if ($email != $user->email) {
                $emailverification = User::where('email', $email)->count();
                if ($emailverification === 0) {
                    $user->email = $email;
                } else {
                    $array['error'] = 'the email is alredy begin used';
                    return $array;
                }
            }
        }
        if ($birthdate) {
            if (strtotime($birthdate) === false) {
                $array['error'] = 'the date of birthdate is invalid';
                return $array;
            }
            $user->birth_date = $birthdate;
        }
        if ($city) {
            $user->city = $city;
        }
        if ($work) {
            $user->work = $work;
        }

        if ($password === $password_confirmation) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $user->password = $hash;
        } else {
            $array['error'] = 'the passwords not match';
            return $array;
        }


        $user->save();
        return $array;
    }

    public function updateAvatar(Request $request)
    {
        $array = ['error' => ''];

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $image = $request->file('avatar');

        if ($image) {
            if (in_array($image->getMimeType(), $allowedTypes)) {
                $fileName = md5(time() . rand(0, 9999) . '.jpg');
                $destPath = public_path('media/avatars');
                $img = Image::make($image->path())->fit(200, 200)->save($destPath . '/' . $fileName);
                $user = User::find($this->loggedUser['id']);
                $user->avatar = $fileName;
                $user->save();
                $array['url'] = url('media/avatars/' . $fileName);
            } else {
                $array['error'] = 'file format is not supported, only jpg e png!';
                return $array;
            }
        } else {
            $array['error'] = 'the file was not sent';
            return $array;
        }

        return $array;
    }
    public function updateCover(Request $request)
    {
        $array = ['error' => ''];

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $image = $request->file('cover');

        if ($image) {
            if (in_array($image->getMimeType(), $allowedTypes)) {
                $fileName = md5(time() . rand(0, 9999) . '.jpg');
                $destPath = public_path('media/covers');
                $img = Image::make($image->path())->fit(850, 310)->save($destPath . '/' . $fileName);
                $user = User::find($this->loggedUser['id']);
                $user->cover = $fileName;
                $user->save();
                $array['url'] = url('media/covers/' . $fileName);
            } else {
                $array['error'] = 'file format is not supported, only jpg e png!';
                return $array;
            }
        } else {
            $array['error'] = 'the file was not sent';
            return $array;
        }

        return $array;
    }

    public function read($id = false)
    {
        $array = ['error' => ''];
        if ($id) {
            $info = User::find($id);
            if (!$info) {
                $array['error'] = 'user not find';
            }
        } else {
            $info = User::find($this->loggedUser['id']);
        }

        $info['avatar'] = url('media/avatars/' . $info['avatar']);
        $info['cover'] = url('media/covers/' . $info['cover']);

        $info['me'] = ($info['id'] == $this->loggedUser['id']) ? true : false;

        $dateFrom = new DateTime($info['birth_date']);
        $dateTo = new DateTime('now');

        $info['age'] = $dateFrom->diff($dateTo)->y;
        $info['followers'] = UserRelation::where('user_to', $info['id'])->count();
        $info['following'] = UserRelation::where('user_from', $info['id'])->count();

        $hasRelation = UserRelation::where('user_from', $this->loggedUser['id'])->where('user_to', $info['id'])->count();

        $info['isFollowing'] = ($hasRelation > 0) ? true : false;

        $info['photoCount'] = Post::where('user_id', $info['id'])->where('type', 'photo')->count();

        $array['data'] = $info;
        return $array;
    }

    public function follow($id){
        $array = ['error' => ''];
        if($id == $this->loggedUser['id']){
            $array['error'] = 'You do not follow yourself';
            return $array;
        }

        if(User::find($id)){
            $uR = UserRelation::where('user_from', $id)->where('user_to', $this->loggedUser['id'])->first();
            if($uR){
                $uR->delete();
            }else{
                $newUR = new UserRelation();
                $newUR->user_from = $id;
                $newUR->user_to = $this->loggedUser['id'];
                $newUR->save();
            }
        }else{
            $array['error'] = 'User not find';
            return $array;
        }
        return $array;
    }
    public function followers($id){
        $array = ['error' => ''];

        if(User::find($id)){
            $followers = UserRelation::where('user_to', $id)->get();
            $following = UserRelation::where('user_from', $id)->get();

            foreach($followers as $follower){
                $user = User::find($follower['user_from']);
                $array['followers'][]= [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => url('media/avatars/'.$user->avatar)
                ] ;
            }

            foreach($following as $follower){
                $user = User::find($follower['user_from']);
                $array['following'][]= [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => url('media/avatars/'.$user->avatar)
                ] ;
            }
        }else{
            $array['error'] = 'User not find';
            return $array;
        }
        return $array;
    }
    public function photo($id){
        $array = ['error' => ''];

        return $array;
    }
}
