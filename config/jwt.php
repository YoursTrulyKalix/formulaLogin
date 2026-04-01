<?php
// -------------------------------------------------------
//  config/jwt.php
//  JWT helper – requires firebase/php-jwt (see README)
//  Install: composer require firebase/php-jwt
// -------------------------------------------------------

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// CHANGE THIS to a long random string in production!
define('JWT_SECRET', 'your_super_secret_key_change_this_in_production_2024');
define('JWT_ALGO',   'HS256');
define('JWT_TTL',    3600); // token lifetime in seconds (1 hour)

/**
 * Generate a signed JWT for the given user.
 *
 * @param  int    $userId
 * @param  string $username
 * @param  string $role     'admin' | 'user'
 * @return string           Signed JWT string
 */
function generateJWT(int $userId, string $username, string $role): string
{
    $issuedAt  = time();
    $expiresAt = $issuedAt + JWT_TTL;

    $payload = [
        'iss'      => 'loginform_app',   // issuer
        'iat'      => $issuedAt,         // issued at
        'exp'      => $expiresAt,        // expiry
        'user_id'  => $userId,
        'username' => $username,
        'role'     => $role,
    ];

    return JWT::encode($payload, JWT_SECRET, JWT_ALGO);
}

/**
 * Verify and decode a JWT.
 * Returns the decoded payload object, or null if invalid/expired.
 *
 * @param  string $token
 * @return object|null
 */
function verifyJWT(string $token): ?object
{
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGO));
        return $decoded;
    } catch (Exception $e) {
        // Token expired, tampered, or malformed
        return null;
    }
}

/**
 * Get the JWT from the current request cookie.
 * Returns the decoded payload or null.
 *
 * @return object|null
 */
function getAuthenticatedUser(): ?object
{
    $token = $_COOKIE['jwt_token'] ?? null;

    if (!$token) {
        return null;
    }

    return verifyJWT($token);
}