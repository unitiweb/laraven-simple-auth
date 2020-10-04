<?php

namespace TokenAuth\Services;

use TokenAuth\Exceptions\TokenAuthConfigurationException;

/**
 * Class ConfigService
 * @package TokenAuth\Services
 */
class ConfigService
{
    /**
     * Get the configured user model's username field
     *
     * @return string
     */
    public static function usernameField(): string
    {
        return config('token_auth.model.username.name', 'username');
    }

    /**
     * Get the configured user model's int id field
     *
     * @return string
     */
    public static function idField(): string
    {
        return config('token_auth.model.id.int', 'id');
    }

    /**
     * Get the configured user model's uuid field
     *
     * @return string
     */
    public static function uuidField(): string
    {
        return config('token_auth.model.id.uuid', 'uid');
    }

    /**
     * Get the configured user model's password field
     *
     * @return string
     */
    public static function passwordField(): string
    {
        return config('token_auth.model.password.name', 'password');
    }

    /**
     * Get all the configured simple token types
     *
     * @param string $type The type of the simple token to return
     *
     * @return array
     * @throws TokenAuthConfigurationException
     */
    public static function simpleToken(string $type): array
    {
        $types = ConfigService::simpleTokenTypes();

        if (!isset($types[$type])) {
            throw new TokenAuthConfigurationException("The simple token $type is not configured");
        }

        $type = $types[$type];

        return [
            'expires' => (int) $type['expires'] ?? 60 * 60, // Defaults to 1 hour
            'algorithm' => (string) $type['algorithm'] ?? 'uuid',
            'max' => (int) $type['max'] ?? 1,
        ];
    }

    /**
     * Get all the configured simple token types
     *
     * @return array
     */
    public static function simpleTokenTypes(): array
    {
        return config('token_auth.simple_tokens', []);
    }
}
