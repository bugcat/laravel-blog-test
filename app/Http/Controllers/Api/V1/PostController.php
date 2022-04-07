<?php
/**
 * Copyright © 2022 . All rights reserved.
 * @Author: Sage Feng <i@bug.cat>
 * @Created: 2021-04-07
 *
 * @Description: Api Controller for users.
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\{Post};

class PostController extends ApiController
{

    protected $user = null;
    protected $req_data = [];

    const ALLOWED_ROLES = ['manager', 'admin'];

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->req_data = $request->all();
            $this->user = $this->getUserByToken($this->req_data);
            return $next($request);
        });
    }

    /**
     * Post detail.
     * @return json
     */
    public function detail()
    {
        // validator
        $_validation = $this->validator($this->req_data, ['post_id' => 'required|integer']);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // get post
        $post = Post::find($this->req_data['post_id']);
        if ( empty($post) ) {
            return $this->error('No post for this post_id.');
        }
        // permission validator
        if ( $post->user_id != $this->user->id && !in_array($this->user->role, self::ALLOWED_ROLES) ) {
            return $this->error('Permission denied.', 403);
        }
        // response
        $info = [
            'id'       => $post->id,
            'user_id'  => $post->user_id,
            'title'    => $post->title,
            'content'  => $post->content,
        ];
        return $this->success($info);
    }

    /**
     * Create a new post.
     * @return json
     */
    public function create()
    {
        // validator
        $_validation = $this->validator($this->req_data, [
            'title'   => 'required|max:255',
            'content' => 'required',
        ]);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // create
        $post = Post::create([
            'title'   => $this->req_data['title'],
            'content' => $this->req_data['content'],
            'user_id' => $this->user->id,
        ]);
        $post->save();
        // response
        return $this->success('Post create succesfully');
    }

    /**
     * Update a post.
     * @return json
     */
    public function update()
    {
        // validator
        $_validation = $this->validator($this->req_data, [
            'post_id' => 'required|integer',
            'title'   => 'required|max:255',
            'content' => 'required',
        ]);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // get post
        $post = Post::find($this->req_data['post_id']);
        if ( empty($post) ) {
            return $this->error('No post for this post_id.');
        }
        // permission validator
        if ( $post->user_id != $this->user->id && !in_array($this->user->role, self::ALLOWED_ROLES) ) {
            return $this->error('Permission denied.', 403);
        }
        // update
        $post->title   = $this->req_data['title'];
        $post->content = $this->req_data['content'];
        $post->save();
        // response
        return $this->success('Post update succesfully');
    }

    /**
     * Delete a post.
     * @return json
     */
    public function delete()
    {
        // validator
        $_validation = $this->validator($this->req_data, ['post_id' => 'required|integer']);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // get user
        $post = Post::find($this->req_data['post_id']);
        if ( empty($post) ) {
            return $this->error('No post for this post_id.');
        }
        // permission validator
        if ( $post->user_id != $this->user->id && !in_array($this->user->role, self::ALLOWED_ROLES) ) {
            return $this->error('Permission denied.', 403);
        }
        // delete
        $post->delete();
        // response
        return $this->success('Post delete succesfully.');
    }

}
