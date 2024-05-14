<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->loggedUser = auth()->user();
    }

    public function like($id)
    {
        $array = ['error' => ''];
        if (Post::find($id)) {
            $pl = PostLike::where('post_id', $id)->where('user_id', $this->loggedUser['id'])->count();
            if ($pl > 0) {
                $postLike = PostLike::where('post_id', $id)->where('user_id', $this->loggedUser['id'])->first();
                $postLike->delete();
                $isLiked = false;
            } else {
                $newPL = new PostLike();
                $newPL->post_id = $id;
                $newPL->user_id = $this->loggedUser['id'];
                $newPL->created_at = date('Y-m-d H:i:s');
                $newPL->save();
                $isLiked = true;
            }
            $array['isLiked'] = $isLiked;
            $array['likeCount'] = PostLike::where('post_id', $id)->count();
        } else {
            $array['error'] = 'Post not find';
        }
        return $array;
    }

    public function comment(Request $request, $id)
    {
        $array = ['error' => ''];
        $comment = trim($request->input('txt'));
        if (Post::find($id)) {
            if ($comment) {
                $newComment = new PostComment();
                $newComment->post_id = $id;
                $newComment->user_id = $this->loggedUser['id'];
                $newComment->created_at = date('Y-m-d H:i:s');
                $newComment->body = $comment;

                $newComment->save();
            } else {
                $array['error'] = 'The comment is not valid!';
            }
        } else {
            $array['error'] = 'Post not find';
        }
        return $array;
    }
}
