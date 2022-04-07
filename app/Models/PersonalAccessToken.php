<?php
/**
 * Copyright © 2022 . All rights reserved.
 * @Author: Sage Feng <i@bug.cat>
 * @Created: 2021-04-07
 *
 * @Description: Model for table `personal_access_tokens`.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personal_access_tokens';

    const USER = [
        'type'   => 'user', //tokenable type
        'expire' => 3600, //expired time for token
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
    ];

    /**
     * Get a token when user login.
     * @param int $user_id
     * @return \App\Models\User || null
     */
    public static function userLogin(int $user_id)
    {
        // get access record
        $_token = null;
        $access = self::where([
            ['tokenable_type', self::USER['type']],
            ['tokenable_id',   $user_id],
        ])->first();
        if ( empty($access) ) {
            $_token = null;
        } else {
            // Determine if the token has expired
            $time = time() - strtotime($access->last_used_at);
            if ( $time >= self::USER['expire'] ) {
                $_token = null;
                $access->delete();
            } else {
                $_token = $access->token;
            }
        }
        // Generate new token
        if ( empty($_token) ) {
            $_token = bin2hex(random_bytes(16));
        }
        $attributes = [
            'tokenable_type' => self::USER['type'],
            'tokenable_id'   => $user_id,
            'token'          => $_token,
        ];
        $values = [
            'name'         => 'user token',
            'abilities'    => 'user login api token.',
            'last_used_at' => now(),
        ];
        // update or create access record
        $_access = self::updateOrCreate($attributes, $values);
        if ( empty($_access) ) {
            return null;
        } else {
            return $_access->token;
        }
    }

    /**
     * Get user model by token.
     * @param string $token
     * @return \App\Models\User || null
     */
    public static function getUser($token)
    {
        $user = null;
        $access = self::where([
            ['tokenable_type', self::USER['type']],
            ['token',          $token],
        ])->first();
        if ( empty($access) ) {
            $user = null;
        } else {
            $time = time() - strtotime($access->last_used_at);
            if ( $time >= self::USER['expire'] ) {
                $user = null;
                $access->delete();
            } else {
                $user_id = $access->tokenable_id;
                $user = User::find($user_id);
                $access->last_used_at = now();
                $access->save();
            }
        }
        return $user;
    }

}
