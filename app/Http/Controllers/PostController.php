<?php

namespace App\Http\Controllers;

//use model post
use App\Models\Post;

//return type view
use Illuminate\view\view;

//return type redirectResponse
use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

//import facades "storage"
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * index
     * 
     * @return view
     */

    public function index(): View
    {
        //get posts
        $posts = Post::latest()->paginate(5);

        //render view with posts
        return view('posts.index', compact('posts'));
    }

    /**
     * create
     * 
     * @return view
     */

    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * store
     * 
     * @param mixed $request
     * @return RedirectResponse
     */

    public function store(Request $request): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);

        //upload image
        $image = $request->file('image');
        $image->storeAS('public/posts', $image->hashName());

        //create  post
        Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content
        ]);

        //redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Disimpan']);
    }

    /**
     * show
     * 
     * @param mixed $id
     * @return view
     * 
     */

    public function show(string $id): View
    {
        //get post by ID
        $post = Post::findOrFail($id);

        //render view with post
        return view('posts.show', compact('post'));
    }

    /**
     * 
     * edit
     * 
     * @param mixed $id
     * @return View
     * 
     */

    public function edit(string $id): View
    {
        $post = Post::findOrFail($id);

        return view('posts.edit', compact('post'));
    }


    /**
     * update
     * 
     * 
     * @param mixed $request
     * @param mixed $id
     * @return RedirectResponse
     * 
     */

    public function update(Request $request, $id): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'image' => 'image|mimes:jpeg,jpg,png|max:2048',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);

        //get post by ID
        $post = Post::findOrFail($id);

        //check if image uploaded
        if($request->hasFile('image')) {

            //upload new image
            $image = $request->file('image');
            $image->storeAS('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/'.$post->image);

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);

        } else {

            //update post without image
            $post->update([
                'title' => $request->title,
                'content' => $request->content
            ]);
        }

        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil di Ubah']);
    }

    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $post = Post::findOrFail($id);

        //delete image
        Storage::delete('public/posts/'. $post->image);

        //delete post
        $post->delete();

        //redirect to index
        return redirect()->route('posts.index')->with(['success'=> 'Data Berhasil di Hapus']);
    }
}
