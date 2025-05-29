<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function store(Post $post)
    {
        if (!$post->isLikedByUser(Auth::user())) {
            $post->likes()->create([
                'user_id' => Auth::id(),
            ]);
            return back()->with('status');
        }

        return back()->with('error');
    }

    public function destroy(Post $post)
    {
        $post->likes()->where('user_id', Auth::id())->delete();
        return back()->with('status');
    }
}
