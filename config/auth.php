<?php
// -------------------------------------------------------
//  config/auth.php
//  Reusable authentication & role guard middleware.
//
//  USAGE at the top of any protected page:
//
//    require_once '../config/auth.php';
//    $authUser = requireAuth();           // any logged-in user
//    $authUser = requireRole('admin');    // admins only
//    $authUser = requireRole('user');     // regular users only
// -------------------------------------------------------

require_once __DIR__ . '/jwt.php';

/**
 * Ensure the request carries a valid JWT.
 * Redirects to login if missing or expired.
 *
 * @param  string $redirectTo  Where to send unauthenticated users
 * @return object              Decoded JWT payload
 */
function requireAuth(string $redirectTo = '../index.php'): object
{
    $authUser = getAuthenticatedUser();

    if (!$authUser) {
        header("Location: {$redirectTo}");
        exit();
    }

    return $authUser;
}

/**
 * Ensure the request carries a valid JWT AND the user has the required role.
 * Redirects to login if not authenticated.
 * Redirects to 403 page if authenticated but wrong role.
 *
 * @param  string $requiredRole  'admin' | 'user'
 * @param  string $redirectTo    Where to send unauthenticated users
 * @return object                Decoded JWT payload
 */
function requireRole(string $requiredRole, string $redirectTo = '../index.php'): object
{
    $authUser = requireAuth($redirectTo);

    if ($authUser->role !== $requiredRole) {
        // Authenticated but wrong role — show 403
        header("Location: ../screens/403.php");
        exit();
    }

    return $authUser;
}