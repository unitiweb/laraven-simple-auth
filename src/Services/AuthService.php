<?php

namespace TokenAuth\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use TokenAuth\Credentials;
use TokenAuth\Exceptions\AuthorizationFailedException;
use TokenAuth\Exceptions\UserModelMisconfiguredException;

/**
 * Class AuthService
 *
 * @package TokenAuth\Services
 */
class AuthService
{
    /**
     * Get the configured user model class qualified name
     *
     * @return string
     */
    public static function getUserModelClass(): string
    {
        return config('token_auth.model.user', '\\App\\Models\\User');
    }

    /**
     * To be used to check that the supplied $user is of the correct model
     * The model is configured in the token_auth config file
     *
     * @param $user
     */
    public static function assertUserModel($user)
    {
        $model = self::getUserModelClass();
        assert($user instanceof $model);
    }

    /**
     * Get and instantiate the configured user model
     *
     * @return mixed The user model configured in token-auth.model config file
     * @throws Exception
     */
    public static function getUserModel()
    {
        $userClass = self::getUserModelClass();

        if (!class_exists($userClass)) {
            throw new UserModelMisconfiguredException;
        }

        return new $userClass;
    }

    /**
     * Get the user model by the configured username field
     *
     * @param Credentials $credentials
     *
     * @return Model The user model configured in the config file
     * @throws Exception
     * @throws AuthorizationFailedException
     */
    public static function findUser(Credentials $credentials)
    {
        $model = self::getUserModel();
        $usernameField = ConfigService::usernameField();

        if (!$user = $model::where($usernameField, $credentials->getUsername())->first()) {
            throw new AuthorizationFailedException;
        }

        return $user;
    }
}
