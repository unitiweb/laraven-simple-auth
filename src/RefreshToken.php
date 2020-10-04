<?php

namespace TokenAuth;

/**
 * Class RefreshToken
 *
 * @package TokenAuth
 */
class RefreshToken
{
    /**
     * The time to live
     *
     * @var string
     */
    protected $ttl;

    /**
     * The token string
     *
     * @var string
     */
    protected $token;

    /**
     * Get the time to live
     *
     * @return string|null
     */
    public function getTtl(): ?string
    {
        return $this->ttl ?? null;
    }

    /**
     * Get the token
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token ?? null;
    }

    /**
     * Set the time to live
     *
     * @param string $ttl
     *
     * @return void
     */
    public function setTtl(string $ttl): void
    {
        $this->ttl = $ttl;
    }

    /**
     * Set the token
     *
     * @param string $token
     *
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
