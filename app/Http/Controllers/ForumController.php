<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Validators\PostApiValidator;
use App\Http\Controllers\BaseApiController;

class ForumController extends BaseApiController
{
    //

    public function storePost(Request $request)
    {

        try {

            $data = $request->except(array_keys($request->query()));

            $validateRequest = PostApiValidator::storePost($data);

            if (!$validateRequest->fails()) {

                $post = Post::create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'status' => (auth()->user()->inRole('admin')) ? 'approved' : 'pending',
                    'post_date' => now(),
                    'posted_by' => auth()->user()->id,
                ]);

                return response()->json([
                    'error' => true,
                    'data' => $post,
                ], 200);

            } else {
                return response()->json([
                    'error' => true,
                    'message' => $validateRequest->errors()->first(),
                ], 400);
            }

        } catch (\Throwable $th) {

            return $this->returnErrorMessage($th);

        }
    }

    public function editPost(Request $request)
    {

        try {

            $data = $request->except(array_keys($request->query()));

            $validateRequest = PostApiValidator::editPost($data);

            if (!$validateRequest->fails()) {

                if (auth()->user()->inRole('admin')) {

                    $post = Post::where('id', '=', $data['id'])->first();

                } else {

                    $post = Post::where('id', '=', $data['id'])->where('posted_by', '=', auth()->user()->id)->first();

                }

                if (!$post) {

                    return response()->json([
                        'error' => true,
                        'message' => 'Post not found',
                    ], 404);
                }

                $post->title = $data['title'];
                $post->description = $data['description'];
                $post->save();

                return response()->json([
                    'error' => true,
                    'data' => $post,
                ], 200);

            } else {
                return response()->json([
                    'error' => true,
                    'message' => $validateRequest->errors()->first(),
                ], 400);
            }

        } catch (\Throwable $th) {

            return $this->returnErrorMessage($th);

        }
    }

    public function deletePost(Request $request,$id)
    {

        try {

            if (auth()->user()->inRole('admin')) {

                $post = Post::where('id', '=', $id)->first();
                
                Comment::where('post_id', '=', $id)->delete();
                

            } else {
                $post = Post::where('id', '=', $id)->where('posted_by', '=', auth()->user()->id)->first();
            }

            if (!$post) {

                return response()->json([
                    'error' => true,
                    'message' => 'Post not found',
                ], 404);
            }

            $post->delete();

            return response()->json([
                'error' => true,
                'msg' => 'Post deleted successfully',
            ], 200);



        } catch (\Throwable $th) {
            return $this->returnErrorMessage($th);
        }
    }

    public function getMyPosts(Request $request)
    {

        try {

            $data = $request->all();

            $validateRequest = PostApiValidator::deletePost($data);

            if (!$validateRequest->fails()) {

                if (auth()->user()->inRole('admin')) {

                    $posts = Post::get();

                } else {
                    $posts = Post::where('posted_by', '=', auth()->user()->id)->get();
                }

                return response()->json([
                    'error' => true,
                    'data' => $posts,
                ], 200);

            } else {
                return response()->json([
                    'error' => true,
                    'message' => $validateRequest->errors()->first(),
                ], 400);
            }

        } catch (\Throwable $th) {
            return $this->returnErrorMessage($th);
        }
    }

    public function getPostById(Request $request)
    {

        try {

            $post = Post::with('comments')
                ->leftJoin('users', 'users.id', 'posts.posted_by')
                ->where('posts.id', '=', $request->id)
                ->select('posts.*', 'users.name')
                ->first();

            if (!$post) {
                return response()->json([
                    'error' => true,
                    'message' => 'Post Not Found',
                ], 404);
            }

            return response()->json([
                'error' => true,
                'data' => $post,
            ], 200);

        } catch (\Throwable $th) {
            return $this->returnErrorMessage($th);
        }
    }

    public function getAllPosts(Request $request)
    {

        try {

            $posts = Post::with('comments')
                ->leftJoin('users', 'users.id', 'posts.posted_by')
                ->where('status', '=' , 'approved');

            if (isset($request->search) && $request->search != "") {
                $posts->where(function ($query) use ($request) {
                    $query->orWhere('users.name', 'like', '%' . $request->search . '%')
                        ->orWhere('posts.description', 'like', '%' . $request->search . '%')
                        ->orWhere('posts.title', 'like', '%' . $request->search . '%');
                });
            }

            $posts = $posts->select('posts.*', 'users.name')->get();

            return response()->json([
                'error' => true,
                'data' => $posts,
            ], 200);

        } catch (\Throwable $th) {
            return $this->returnErrorMessage($th);
        }
    }

    public function postComment(Request $request)
    {

        try {

            $data = $request->except(array_keys($request->query()));

            $validateRequest = PostApiValidator::postComment($data);

            if (!$validateRequest->fails()) {

                $post = Post::where('id', '=', $data['id'])->first();

                if (!$post) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Post not found',
                    ], 404);
                }

                $comment = Comment::create([
                    'post_id' => $post->id,
                    'user_id' => auth()->user()->id,
                    'comment' => $data['comment'],
                    'comment_date' => now(),
                ]);

                return response()->json([
                    'error' => true,
                    'data' => $comment,
                ], 200);

            } else {
                return response()->json([
                    'error' => true,
                    'message' => $validateRequest->errors()->first(),
                ], 400);
            }

        } catch (\Throwable $th) {
          return $this->returnErrorMessage($th);
        }
    }

    public function getComments(Request $request)
    {

        try {

            $data = $request->all();

            $validateRequest = PostApiValidator::getComments($data);

            if (!$validateRequest->fails()) {

                $post = Post::where('id', '=', $data['id'])->first();

                if (!$post) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Post not found',
                    ], 404);
                }

                $comments = Comment::where('post_id', '=', $data['id'])->get();

                return response()->json([
                    'error' => true,
                    'data' => $comments,
                ], 200);

            } else {
                return response()->json([
                    'error' => true,
                    'message' => $validateRequest->errors()->first(),
                ], 400);
            }

        } catch (\Throwable $th) {

            return $this->returnErrorMessage($th);

        }
    }

    public function postChangeStatus(Request $request)
    {

        try {

            if (!auth()->user()->inRole('admin')) {
                
                return response()->json([
                    'error' => true,
                    'message' => 'Access Denied',
                ], 401);
            }

            $data = $request->except(array_keys($request->query()));

            $validateRequest = PostApiValidator::postChangeStatus($data);

            if (!$validateRequest->fails()) {

                $post = Post::find($request->id);

                if (!$post) {

                    return response()->json([
                        'error' => true,
                        'message' => 'Post not found',
                    ], 404);

                }

                $post->status = $request->status;
                $post->save();

                return response()->json([
                    'error' => true,
                    'data' => $post,
                ], 200);

            } else {
                return response()->json([
                    'error' => true,
                    'message' => $validateRequest->errors()->first(),
                ], 400);
            }

        } catch (\Throwable $th) {

            return $this->returnErrorMessage($th);

        }
    }

}
