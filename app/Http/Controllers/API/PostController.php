<?php

namespace App\Http\Controllers\API;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest('created_at')->paginate(10);
        foreach ($posts as $post){
            $post->images = json_decode($post->images);
        }
//        $posts['status'] = '200';
        return response()->json($posts);
    }

    public function show($id)
    {
        $post = Post::find($id);
        if ($post != null){
            $post->images = json_decode($post->images);
            return $post;
        } else
            return response()->json([
                'status' => '404',
                'message' => 'Post not found.'
            ]);


    }
}
