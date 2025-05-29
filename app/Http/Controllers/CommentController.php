<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Comment successfully added',
            'comment' => $comment
        ]);
    }

    public function index(Post $post)
    {
        $comments = $post->comments()->with('user')->latest()->get();
        return response()->json($comments);
    }
}
