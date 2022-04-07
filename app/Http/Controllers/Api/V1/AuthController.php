<?php
/**
 * Copyright © 2022 . All rights reserved.
 * @Author: Sage Feng <i@bug.cat>
 * @Created: 2021-04-07
 *
 * @Description: Api Controller for user auth.
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\{User, PersonalAccessToken};
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{

    /**
     * User register.
     * @param \Illuminate\Http\Request $request
     * @return json
     */
    public function register(Request $request)
    {
        // validator
        $data = $request->all();
        $_validation = $this->validator($data, [
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
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);
        $user->save();
        // response
        return $this->success('User register succesfully');
    }

    /**
     * User login.
     * @param \Illuminate\Http\Request $request
     * @return json
     */
    public function login(Request $request)
    {
        // validator
        $data = $request->all();
        $_validation = $this->validator($data, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // take login
        $user = User::where('email', $data['email'])->first();
        if ( empty($user) || empty($user->id) || empty($user->password) || !Hash::check($data['password'], $user->password) ) {
            return $this->error('Email or password error.');
        }
        // get token
        $token = PersonalAccessToken::userLogin($user->id);
        if ( empty($token) ) {
            return $this->error('Login failed.');
        }
        // response
        $info = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
            'token' => $token,
        ];
        return $this->success($info);
    }

}
