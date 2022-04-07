<?php
/**
 * Copyright © 2022 . All rights reserved.
 * @Author: Sage Feng <i@bug.cat>
 * @Created: 2021-04-07
 *
 * @Description: Api Base Controller.
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\{PersonalAccessToken};

class ApiController extends Controller
{

    /**
     * Response succesfully.
     * @param null $data
     * @param int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, int $code = 200)
    {
        return $this->jsonResponse($data, $code);
    }

    /**
     * Response error.
     * @param str $msg
     * @param int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($msg = 'Error.', int $code = 400)
    {
        return $this->jsonResponse($msg, $code);
    }

    /**
     * Response a json for API.
     * @param null $data
     * @param int  $code
     * @param int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($data = null, int $code = 200, $status = 200)
    {
        $message = 'Successful.';
        if ( empty($data) ) {
            $data = null;
        }
        if ( is_string($data) ) {
            $message = $data;
            $data = null;
        }

        return response()->json(
            ['code' => $code, 'message' => $message, 'data' => $data],
            $status,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Request validator.
     * @param array $data
     * @param array $rule
     * @return \Illuminate\Http\JsonResponse || true
     */
    protected function validator(array $data, array $rule)
    {
        $validator = Validator::make($data, $rule);
        if ( $validator->fails() ) {
            $err_arr = [];
            foreach ( $validator->messages()->toArray() as $msgs ) {
                foreach ( $msgs as $_msg ) {
                    $err_arr[] = $_msg;
                }
            }
            $err_str = implode(' ', $err_arr);
            return $this->error($err_str);
        }
        return true;
    }

    /**
     * Get user model by token.
     * @param array $data
     * @return \App\Models\User || null
     */
    protected function getUserByToken(array $data)
    {
        // validator
        $_validation = $this->validator($data, ['token' => 'required']);
        if ( true !== $_validation ) {
            return $_validation;
        }
        // get user id
        $user = PersonalAccessToken::getUser($data['token']);
        if ( empty($user) ) {
            return $this->error('token is error or expired.');
        }
        return $user;
    }

}
