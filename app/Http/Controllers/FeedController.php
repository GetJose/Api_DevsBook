<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;



class FeedController extends Controller
{
    private $loggedUser;

    private function __PostListToObject($postList, $loggedId)
    {

        foreach ($postList as $postKey => $postItem)
            if ($postItem['user_id'] === $loggedId) {
                $postList[$postKey]['mine'] = true;
            } else {
                $postList[$postKey]['mine'] = false;
            }

        $userInfo = User::find($postItem['user_id']);
        $userInfo['avatar'] = url('media/avatars/' . $userInfo['avatar']);
        $userInfo['cover'] = url('media/covers/' . $userInfo['cover']);
        $postList[$postKey]['user'] = $userInfo;

        $likes = PostLike::where('post_id', $postItem['id'])->count();
        $postList[$postKey]['likeCount'] = $likes;

        $isLiked =  PostLike::where('post_id', $postItem['id'])->where('user_id', $loggedId)->count();
        $postList[$postKey]['liked'] = ($isLiked > 0) ? true : false;

        $comments = PostComment::where('post_id', $postItem['id'])->get();

        foreach ($comments as $commentKey => $comment) {
            $userInfo = User::find($postItem['user_id']);
            $userInfo['avatar'] = url('media/avatars/' . $userInfo['avatar']);
            $userInfo['cover'] = url('media/covers/' . $userInfo['cover']);
            $comments[$commentKey]['user'] = $userInfo;
        }

        $postList[$postKey]['comments'] = $comments;
        return $postList;
    }

    public function __construct()
    {
        $this->loggedUser = auth()->user();
    }

    public function create(Request $request)
    {
        $array = ['error' => ''];

        $type = $request->input('type');
        $body = $request->input('body');
        $photo = $request->file('photo');

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        if ($type) {
            switch ($type) {
                case 'text':
                    if (!$body) {
                        $array['error'] = 'the post text is null';
                        return $array;
                    }
                    break;
                case 'photo':
                    if ($photo) {
                        if (in_array($photo->getMimeType(), $allowedTypes)) {
                            $fileName = md5(time() . rand(0, 9999) . '.jpg');
                            $destPath = public_path('media/uploads');
                            $img = Image::make($photo->path())->resize(800, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($destPath . '/' . $fileName);
                            $body = $fileName;
                        } else {
                            $array['error'] = 'file format is not supported, only jpg e png!';
                            return $array;
                        }
                    } else {
                        $array['error'] = 'the file was not sent';
                        return $array;
                    }
                    break;
                default:
                    $array['error'] = 'Post type invalid!';
                    return $array;
                    break;
            }
            if ($body) {
                $newPost = new Post();
                $newPost->user_id = $this->loggedUser['id'];
                $newPost->type = $type;
                $newPost->created_at = date('Y-m-d H:i:s');
                $newPost->body = $body;
                $newPost->save();
            }
        } else {
            $array['error'] = 'the data was not sent';
            return $array;
        }

        return $array;
    }

    public function read(Request $request)
    {
        $array = ['error' => ''];
        $page = intval($request->input('page'));
        $perPage = 2;

        $users = [];
        $userList = UserRelation::where('user_from', $this->loggedUser['id'])->get();

        foreach ($userList as $userItem) {
            $users[] = $userItem['user_to'];
        }

        $users[] = $this->loggedUser['id'];

        $postList = Post::whereIn('user_id', $users)
            ->orderBy('created_at', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $total =  Post::whereIn('user_id', $users)->count();
        $pageCount = ceil($total / $perPage);

        $posts = $this->__PostListToObject($postList, $this->loggedUser['id']);

        $array['posts'] = $posts;
        $array['currentPage'] = $page;
        $array['pageCount'] = $pageCount;

        return $array;
    }

    public function userFeed(Request $request, $id = false)
    {
        $array = ['error' => ''];

        if ($id == false) {
            $id = $this->loggedUser['id'];
        }

        $page = intval($request->input('page'));
        $perPage = 2;

        $postList = Post::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $total =  Post::where('user_id', $id)->count();
        $pageCount = ceil($total / $perPage);
        $posts = [];
        
        if (count($postList) > 0) {
            $posts = $this->__PostListToObject($postList, $this->loggedUser['id']);
        }

        $array['posts'] = $posts;
        $array['currentPage'] = $page;
        $array['pageCount'] = $pageCount;

        return $array;
    }
    public function userPhoto(Request $request , $id = false){
        $array = ['error' => ''];

        if ($id == false) {
            $id = $this->loggedUser['id'];
        }

        $page = intval($request->input('page'));
        $perPage = 2;

        $postList = Post::where('user_id', $id)
            ->where('type', 'photo')
            ->orderBy('created_at', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $total =  Post::where('user_id', $id)->where('type', 'photo')->count();
        $pageCount = ceil($total / $perPage);
        $posts = [];
        
        if (count($postList) > 0) {
            $posts = $this->__PostListToObject($postList, $this->loggedUser['id']);
        }

        foreach($posts as $postKey => $item){
            $posts[$postKey]['body'] = url('media/uploads/'.$posts[$postKey]['body'] );
        }

        $array['posts'] = $posts;
        $array['currentPage'] = $page;
        $array['pageCount'] = $pageCount;

        return $array;
    }
}
