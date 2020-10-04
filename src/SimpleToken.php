<?php

namespace TokenAuth;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use phpDocumentor\Reflection\Types\Array_;
use Ramsey\Uuid\Uuid;
use TokenAuth\Exceptions\TokenAuthConfigurationException;
use \TokenAuth\Models\Token;
use TokenAuth\Services\ConfigService;
use TokenAuth\Services\TokenService;

/**
 * Class SimpleToken
 * @package TokenAuth
 */
class SimpleToken
{
    /**
     * The user model configured in the config file
     */
    private $user;

    /**
     * An array with the token type configuration
     *
     * @var array
     */
    protected $config;

    /**
     * The type of the token which is the configuration key
     *
     * @var string
     */
    private $type;

    /**
     * The token expiration time
     *
     * @var int
     */
    private $expires;

    /**
     * The token algorithm type
     *
     * @var string
     */
    private $algorithm;

    /**
     * The max number of concurrent tokens for a single user
     *
     * @var mixed
     */
    private $max;

    /**
     * SimpleToken constructor.
     *
     * @param $user
     * @param string $type
     *
     * @throws TokenAuthConfigurationException
     * @throws Exception
     */
    public function __construct($user, string $type)
    {
        $config = ConfigService::simpleToken($type);

        $this->user = $user;
        $this->type = $type;
        $this->algorithm = $config['algorithm'];
        $this->max = $config['max'];

        // Get expires at in seconds and convert to future time
        $this->expires = new Carbon(time() + (int) $config['expires']);
    }

    /**
     * Create a simple token of configured type
     * It must be one of the configure simple_tokens configured in the token_auth file
     *
     * @return Token
     * @throws Exception
     */
    public function generate(): Token
    {
        // Remove all the expired tokens of given type
        TokenService::removeExpiredTokens($this->user, $this->type);
        TokenService::removeOverMaxAllowedTokens($this->user, $this->type, $this->max);

        return Token::create([
            'user_id' => $this->user{ConfigService::idField()},
            'token_type' => $this->type,
            'token' => $this->generateToken(),
            'expires_at' => $this->expires,
        ]);
    }

    /**
     * Validate a given token
     *
     * @param string $tokenString
     *
     * @return bool
     */
    public function validate(string $tokenString): bool
    {
        $userIdField = ConfigService::idField();

        $token = Token::where('user_id', $this->user{$userIdField})
            ->where('token_type', $this->type)
            ->where('token', $tokenString)
            ->first();

        if (!$token) {
            return false;
        }

        if ((new Carbon())->lessThanOrEqualTo($token->expires_at)) {
            $token->delete();
            return true;
        }

        TokenService::removeExpiredTokens($this->user, $this->type);

        return false;
    }

    /**
     * Generate a token with the configured algorithm
     *
     * @return string
     */
    protected function generateToken(): string
    {

        $token = null;

        if ($this->algorithm === 'uuid') {
            $token = Uuid::uuid4()->toString();
        } else if (substr($this->algorithm, 0, 4) === 'code') {
            $parts = explode(':', $this->algorithm);
            $length = count($parts) === 2 ? (int) $parts[1] : 8;
            $token = $this->generateCode($length);
        } else {
            $token = hash($this->algorithm, $this->generateCode(32));
        }

        return $token;
    }

    /**
     * Generate a code string using upper and lower case letters and numbers
     *
     * @param int $length The length of the string
     *
     * @return string
     */
    protected function generateCode(int $length = 8): string
    {
        $characters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }
}
