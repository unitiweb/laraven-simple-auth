<?php

namespace TokenAuth\Services;

use Firebase\JWT\JWT;
use TokenAuth\Credentials;
use Carbon\Carbon;
use TokenAuth\Exceptions\AuthorizationFailedException;
use TokenAuth\Models\Token;
use TokenAuth\Models\Token as TokenModel;

/**
 * Class TokenService
 *
 * @package App\Services
 */
class TokenService
{
    /**
     * Get a TokenModel from the database
     * Throw exception if it doesn't exist
     *
     * @return mixed
     * @throws AuthorizationFailedException
     */
    public static function getToken($user, $type, $jwt)
    {
        // Get the stored refresh token and throw an exception if it's not found
        $idField = ConfigService::idField();

        $token = TokenModel::where([
            'user_id' => $user{$idField},
            'token_type' => $type,
            'token' => $jwt,
        ]);

        if ($token) {
            return $token;
        }

        throw new AuthorizationFailedException('Token not found');
    }

    /**
     * Sign a token.
     * This will be used to sign both an access or refresh token
     *
     * @param $user
     * @param int $expiration The token expiration in seconds
     *
     * @return string
     */
    public static function sign($user, int $expiration): string
    {
        // Get the configured user's model id field to be used for signing
        $userIdField = ConfigService::uuidField();

        $payload = [
            'iss' => config('token_auth.jwt.issuer'), // Issuer of the token
            'sub' => $user{$userIdField}, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + $expiration // Expiration time
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode(
            $payload,
            config('token_auth.jwt.secret'),
            config('token_auth.jwt.algorithm')
        );
    }

    /**
     * Remove the older tokens that are over the max allowed and have not expired
     *
     * @param $user
     * @param string $tokenType The token type
     * @param int $maxAllowed The max allowed tokens
     *
     * @return void
     */
    public static function removeOverMaxAllowedTokens($user, string $tokenType, int $maxAllowed): void
    {
        AuthService::assertUserModel($user);

        // Remove all expired tokens
        self::removeExpiredTokens($user, $tokenType);

        // Get all tokens for the given user and order by created_at desc
        $userIdField = ConfigService::idField();
        $tokens = Token::where('user_id', $user{$userIdField})
            ->where('token_type', $tokenType)
            ->orderBy('created_at', 'desc')
            ->get();

        $c = 1;
        foreach ($tokens as $token) {
            if ($c >= $maxAllowed) {
                $token->delete();
            }
            $c++;
        }
    }

    /**
     * Remove all expired tokens for the given user and given token type
     *
     * @param $user
     * @param string|null $tokenType The token type
     *
     * @return void
     */
    public static function removeExpiredTokens($user, string $tokenType = null): void
    {
        AuthService::assertUserModel($user);

        $userIdField = ConfigService::idField();

        Token::where('user_id', $user{$userIdField})
            ->where('token_type', $tokenType)
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }
}
