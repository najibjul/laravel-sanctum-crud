<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\Post;
use DB;
use Illuminate\Http\Request;
use Storage;
use Validator;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return new ApiResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            $post = Post::create([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);

            DB::commit();
            return new ApiResource(true, 'Data Post Berhasil Ditambahkan!', $post);

        } catch (\Throwable $error) {
            DB::rollBack();
            return response()->json($error->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $post = Post::find($id);
        return new ApiResource(true, 'Detail Data Post!', $post);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json('Data not found', 404);   
            }

            if ($request->hasFile('image')) {

                $image = $request->file('image');
                $image->storeAs('public/posts', $image->hashName());

                Storage::delete('public/posts/' . basename($post->image));

                $post->update([
                    'image' => $image->hashName(),
                    'title' => $request->title,
                    'content' => $request->content,
                ]);

            } else {
                $post->update([
                    'title' => $request->title,
                    'content' => $request->content,
                ]);
            }

            DB::commit();
            return new ApiResource(true, 'Data Post Berhasil Diubah!', $post);

        } catch (\Throwable $error) {
            DB::rollBack();
            return response()->json($error->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json('Data not found', 404);   
            }

            Storage::delete('public/posts/' . basename($post->image));

            $post->delete();

            DB::commit();
            return response()->noContent();

        } catch (\Throwable $error) {
            DB::rollBack();
            return response()->json($error->getMessage(), 500);
        }
    }
}
