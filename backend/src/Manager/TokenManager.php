<?php

namespace App\Manager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

/**
 * TokenManager
 *
 * Manages operations related to JWT tokens, including validation, blacklisting, and decoding.
 */
class TokenManager
{
    /**
     * @var CacheManager
     *
     * Manages caching operations, such as storing and retrieving blacklisted tokens.
     */
    private CacheManager $cacheManager;

    /**
     * @var JWTEncoderInterface
     *
     * Encodes and decodes JWT tokens.
     */
    private JWTEncoderInterface $jwtEncoderInterface;

    /**
     * TokenManager constructor.
     *
     * @param CacheManager $cacheManager Manages caching operations.
     * @param JWTEncoderInterface $jwtEncoderInterface Encodes and decodes JWT tokens.
     */
    public function __construct(CacheManager $cacheManager, JWTEncoderInterface $jwtEncoderInterface)
    {
        $this->cacheManager = $cacheManager;
        $this->jwtEncoderInterface = $jwtEncoderInterface;
    }

    /**
     * Checks if a token is blacklisted.
     *
     * @param string $token The JWT token to check.
     *
     * @return bool True if the token is blacklisted, false otherwise.
     */
    public function isTokenBlacklisted(string $token): bool
    {
        try {
            return $this->cacheManager->isCached('auth_token_' . $token);
        } catch (\Exception $e) {
            // TODO: Handle error
            return false;
        }
    }

    /**
     * Blacklists a token.
     *
     * @param string $token The JWT token to blacklist.
     *
     * @return void
     */
    public function blacklistToken(string $token): void
    {
        try {
            if (!$this->isTokenBlacklisted($token)) {
                $this->cacheManager->setValue('auth_token_' . $token, 'auth_token', 604800);
            }
        } catch (\Exception $e) {
            // TODO: Handle error
        }
    }

    /**
     * Removes a token from the blacklist.
     *
     * @param string $token The JWT token to unblacklist.
     *
     * @return void
     */
    public function unBlacklistToken(string $token): void
    {
        try {
            $this->cacheManager->deleteValue('auth_token_' . $token);
        } catch (\Exception $e) {
            // TODO: Handle error
        }
    }

    /**
     * Extracts the token from the request headers.
     *
     * @param Request $request The HTTP request.
     *
     * @return string|null The extracted token or null if not found.
     */
    public function getTokenFromRequest(Request $request): ?string
    {
        try {
            return $request->headers->get('Authorization');
        } catch (\Exception $e) {
            // TODO: Handle error
            return null;
        }
    }

    /**
     * Decodes a JWT token.
     *
     * @param string $token The JWT token to decode.
     *
     * @return array The decoded token data.
     */
    public function decodeToken(string $token): array
    {
        try {
            return $this->jwtEncoderInterface->decode($token);
        } catch (\Exception $e) {
            // TODO: Handle error
            return [];
        }
    }

    /**
     * Validates a JWT token.
     *
     * Checks if the token is expired and if it is blacklisted.
     *
     * @param string $token The JWT token to validate.
     *
     * @return bool True if the token is valid, false otherwise.
     */
    public function isTokenValid(string $token): bool
    {
        try {
            // Decode the token
            $decodedToken = $this->decodeToken($token);

            // Check if the token is expired
            if ($decodedToken['exp'] < time()) {
                return false;
            }

            // Check if token is blacklisted
            if ($this->isTokenBlacklisted($token)) {
                return false;
            }

            // The token is valid
            return true;
        } catch (\Exception $e) {
            // TODO: Handle error
            return false;
        }
    }
}
