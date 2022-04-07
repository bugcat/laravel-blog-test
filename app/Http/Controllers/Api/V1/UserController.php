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
use Illuminate\Http\Request;
use App\Models\{User, PersonalAccessToken};
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{

    protected $user = null;
    protected $req_data = [];

    const ALLOWED_ROLES = ['admin'];

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
            if ( in_array($this->user->role, self::ALLOWED_ROLES) ) {
                return $next($request);
            } else {
                return $this->error('Permission denied.', 403);
            }
        });
    }

    /**
     * Get an user info.
     * @return json
     */
    public function info()
    {
        // validator
        $_validation = $this->validator($this->req_data, ['user_id' => 'required|integer']);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // get user
        $user = User::find($this->req_data['user_id']);
        if ( empty($user) ) {
            return $this->error('No user for this user_id.');
        }
        // response
        $info = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];
        return $this->success($info);
    }

    /**
     * Create an user.
     * @return json
     */
    public function create()
    {
        // validator
        $_validation = $this->validator($this->req_data, [
            'name'     => 'required|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required',
            'role'     => ['required', Rule::in(User::ROLE_LIST)],
        ]);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // create
        $user = User::create([
            'name'     => $this->req_data['name'],
            'email'    => $this->req_data['email'],
            'password' => Hash::make($this->req_data['password']),
            'role'     => $this->req_data['role'],
        ]);
        $user->save();
        // response
        return $this->success('User create succesfully');
    }

    /**
     * Update an user.
     * @return json
     */
    public function update()
    {
        // validator
        $_validation = $this->validator($this->req_data, [
            'user_id'  => 'required|integer',
            'name'     => 'required|max:255',
            'password' => 'required',
            'role'     => ['required', Rule::in(User::ROLE_LIST)],
        ]);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // get user
        $user = User::find($this->req_data['user_id']);
        if ( empty($user) ) {
            return $this->error('No user for this user_id.');
        }
        // update
        $user->name     = $this->req_data['name'];
        $user->password = Hash::make($this->req_data['password']);
        $user->role     = $this->req_data['role'];
        $user->save();
        // response
        return $this->success('User update succesfully');
    }

    /**
     * Delete an user.
     * @return json
     */
    public function delete()
    {
        // validator
        $_validation = $this->validator($this->req_data, ['user_id' => 'required|integer']);
        if ( true !== $_validation ) {
            return $_validation;
        }
        if ( $this->req_data['user_id'] == $this->user->id ) {
            return $this->error('Cannot delete yourself.');
        }
        // get user
        $user = User::find($this->req_data['user_id']);
        if ( empty($user) ) {
            return $this->error('No user for this user_id.');
        }
        // delete
        $user->delete();
        // delete all tokens for this user.
        $deleted = PersonalAccessToken::where([
            ['tokenable_type', PersonalAccessToken::USER['type']],
            ['tokenable_id',   $this->req_data['user_id']],
        ])->delete();
        // response
        return $this->success('User delete succesfully.');
    }

}
