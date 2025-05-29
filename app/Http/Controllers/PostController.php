<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'caption' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        Post::create([
            'user_id' => Auth::id(),
            'caption' => $request->caption,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('dashboard')->with('status', 'Success Create Post!');
    }

    public function index()
    {
        $posts = Post::with(['user', 'likes', 'comments'])->latest()->get();
        return view('home', compact('posts'));
    }
}
