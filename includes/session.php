<?php
class SessionManager {
    private static $sessionTimeout = 1800; // 30 minutes
    private static $regenerateTime = 300; // 5 minutes

    public static function initSession() {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 1);
        
        session_start();
    }

    public static function validateSession() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            self::destroySession();
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > self::$sessionTimeout)) {
            self::destroySession();
            return false;
        }

        // Check if we need to regenerate session ID
        if (!isset($_SESSION['regenerated']) || (time() - $_SESSION['regenerated'] > self::$regenerateTime)) {
            self::regenerateSession();
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function regenerateSession() {
        // Save old session data
        $old_session_data = $_SESSION;
        
        // Create new session
        session_regenerate_id(true);
        
        // Restore session data
        $_SESSION = $old_session_data;
        $_SESSION['regenerated'] = time();
    }

    public static function destroySession() {
        // Unset all session variables
        $_SESSION = array();

        // Delete the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();
    }

    public static function setUserSession($user, $role) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['regenerated'] = time();

        // Regenerate session ID to prevent session fixation
        self::regenerateSession();
    }

    public static function requireLogin() {
        if (!self::validateSession()) {
            header("Location: ../login.php");
            exit();
        }
    }

    public static function requireRole($role) {
        self::requireLogin();
        if ($_SESSION['role'] !== $role) {
            header("Location: ../index.php");
            exit();
        }
    }
} 