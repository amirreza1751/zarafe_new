<?php

namespace App\Http\Controllers\API;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        foreach ($posts as $post){
            $post->images = json_decode($post->images);
            foreach ($post->images as $image){

            }
        }
        return $posts;
    }
}
