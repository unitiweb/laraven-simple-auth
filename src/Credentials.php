<?php

namespace TokenAuth;

use Illuminate\Support\Facades\Crypt;
use TokenAuth\Services\AuthService;
use TokenAuth\Services\ConfigService;

/**
 * Class Credentials
 * @package TokenAuth
 */
class Credentials
{
    /**
     * The user model to validate against
     */
    protected $user;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    public function __construct(?string $username = null, ?string $password = null)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Set the user model to perform actions on
     *
     * @param $user
     */
    public function setUser($user)
    {
        AuthService::assertUserModel($user);
        $this->user = $user;
    }

    /**
     * Get the username
     *
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * The the username for authentication
     * This can be a username, or email, or whatever
     *
     * @param string $username
     *
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Get the password
     *
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * The the password for authentication
     *
     * @param string $password
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Validate the given password with the stored password
     *
     * @return bool
     */
    public function validatePassword(): bool
    {
        $password = $this->encode();

        return $password === $this->user{ConfigService::passwordField()};
    }

    /**
     * Take the set password and hash and/or encrypt depending on how it's configured
     * and return the finalized password to be stored in the database
     *
     * @return string
     */
    public function encode(): string
    {
        // Hash the password if an algorithm is configured
        $password = $this->hash($this->password);

        // Encrypt the test password if encrypt is enabled
        $password = $this->encrypt($password);

        return $password;
    }

    /**
     * Hash with the configured algorithm
     * If no algorithm is configured then just return the password
     *
     * @param string $password
     *
     * @return string
     */
    protected function hash(string $password): string
    {
        $algorithm = config('token_auth.model.password.algorithm');

        if (empty($algorithm)) {
            return $password;
        }

        return hash($algorithm, $password);
    }

    /**
     * Encrypt the password if encryption is enabled
     * If encrypt is not configured then just return the password
     *
     * @param string $password
     *
     * @return string
     */
    protected function encrypt(string $password): string
    {
        if (!config('token_auth.model.password.encrypt', false)) {
            return $password;
        }

        return Crypt::encrypt($password);
    }
}
