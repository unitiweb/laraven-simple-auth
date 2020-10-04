<?php

namespace TokenAuth;

use Carbon\Carbon;
use Exception;
use TokenAuth\Services\AuthService;
use TokenAuth\Models\Token as TokenModel;
use TokenAuth\Services\ConfigService;
use TokenAuth\Services\TokenService;

/**
 * Class Token
 *
 * @package TokenAuth
 */
class Token
{
    /**
     * @var AccessToken
     */
    protected $access;

    /**
     * @var RefreshToken
     */
    protected $refresh;

    /**
     * Sign and set the access token
     *
     * @param $user
     *
     * @return void
     */
    public function signAccessToken($user): void
    {
        AuthService::assertUserModel($user);

        $ttl = (int) config('jwt.access_expires', 60 * 60);
        $token = TokenService::sign($user, $ttl);

        $this->access = new AccessToken;
        $this->access->setTtl($ttl);
        $this->access->setToken($token);
    }

    /**
     * Sign and set the refresh token
     *
     * @param $user
     *
     * @return void
     * @throws Exception
     */
    public function signRefreshToken($user): void
    {
        AuthService::assertUserModel($user);

        $ttl = (int) config('jwt.refresh_expires', 60 * 60 * 24);
        $tokenString = TokenService::sign($user, $ttl);

        $this->refresh = new RefreshToken();
        $this->refresh->setTtl($ttl);
        $this->refresh->setToken($tokenString);

        $expiresDateTime = new Carbon(time() + $ttl);

        //ToDo: Remove any tokens more than the max
        // Delete all expired refresh tokens
        TokenService::removeOverMaxAllowedTokens(
            $user,
            TokenModel::TYPE_REFRESH,
            config('token_auth.jwt.max_concurrent_logins')
        );

        $id = ConfigService::idField();
        TokenModel::create([
            'user_id' => $user{$id},
            'token_type' => TokenModel::TYPE_REFRESH,
            'token' => $tokenString,
            'expires_at' => $expiresDateTime,
        ]);
    }

    /**
     * Return the access token
     *
     * @return AccessToken
     */
    public function getAccessToken(): AccessToken
    {
        return $this->access;
    }

    /**
     * Return the refresh token
     *
     * @return RefreshToken
     */
    public function getRefreshToken(): RefreshToken
    {
        return $this->refresh;
    }
}
