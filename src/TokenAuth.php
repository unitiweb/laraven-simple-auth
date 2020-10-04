<?php

namespace TokenAuth;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use TokenAuth\Exceptions\AuthorizationFailedException;
use TokenAuth\Exceptions\AuthSignatureInvalidException;
use TokenAuth\Exceptions\RefreshTokenExpiredException;
use TokenAuth\Services\AuthService;
use TokenAuth\Services\ConfigService;
use TokenAuth\Services\TokenService;
use TokenAuth\Models\Token as TokenModel;

/**
 * Class TokenAuth
 * @package TokenAuth
 */
class TokenAuth
{
    /**
     * The user model configured in the config file
     */
    protected $user;

    /**
     * Get the user model if one was added
     * This won't be set until either authenticate, validate, or refresh is called
     */
    public function getUser()
    {
        return $this->user ?? null;
    }

    /**
     * Authorize with the given Credentials
     *
     * @param Credentials $credentials
     *
     * @return Token
     * @throws AuthorizationFailedException
     * @throws Exception
     */
    public function authenticate(Credentials $credentials): Token
    {
        // Get the user model
        $this->user = AuthService::findUser($credentials);
        $credentials->setUser($this->user);

        if (!$credentials->validatePassword()) {
            throw new AuthorizationFailedException;
        }

        $token = new Token;
        $token->signAccessToken($this->user);
        $token->signRefreshToken($this->user);

        return $token;
    }

    /**
     * Take in the access token string.
     * The token will then be parsed and if successful return the user model
     *
     * @param string $jwt
     *
     * @return mixed
     *
     * @throws AuthSignatureInvalidException
     * @throws AuthorizationFailedException
     * @throws RefreshTokenExpiredException
     */
    public function validate(string $jwt)
    {
        $jwt = trim($jwt);

        if (substr(strtolower($jwt), 0, 7) === 'bearer ') {
            $jwt = trim(substr($jwt, 7));
        }

        try {
            $decoded = JWT::decode($jwt, config('token_auth.jwt.secret'), [config('token_auth.jwt.algorithm')]);
        } catch (SignatureInvalidException $ex) {
            throw new AuthSignatureInvalidException;
        } catch (ExpiredException $ex) {
            throw new RefreshTokenExpiredException;
        } catch (Exception $ex) {
            throw $ex;
        }

        try {
            $user = AuthService::getUserModel();
            $userUuidField = ConfigService::uuidField();
            $this->user = $user::where($userUuidField, $decoded->sub)->firstOrFail();

            return $this->user;
        } catch (Exception $ex) {
            throw new AuthorizationFailedException('The authenticated user does not exist');
        }
    }

    /**
     * Take in the refresh token, validate, resign and return a enw token
     *
     * @param string $jwt
     *
     * @return Token
     * @throws AuthorizationFailedException
     * @throws Exception
     */
    public function refresh(string $jwt): Token
    {
        $this->user = $this->validate($jwt);

        $max = config('token_auth.jwt.max_concurrent_logins', 5);
        TokenService::removeOverMaxAllowedTokens($this->user, TokenModel::TYPE_REFRESH, $max);

        // Ge the token. Exception is thrown if it doesn't exist.
        $tokenModel = TokenService::getToken($this->user, TokenModel::TYPE_REFRESH, $jwt);
        $tokenModel->delete();

        $token = new Token;
        $token->signAccessToken($this->user);
        $token->signRefreshToken($this->user);

        return $token;
    }
}
