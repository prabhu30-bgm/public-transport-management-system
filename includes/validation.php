<?php
class Validation {
    // Input sanitization
    public static function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Password validation
    public static function validatePassword($password) {
        if (strlen($password) < 6) {
            return "Password must be at least 6 characters long";
        }
        return true;
    }

    // Email validation
    public static function validateEmail($email) {
        // Check if email ends with .com
        if (!preg_match('/\.com$/', $email)) {
            return "Only .com email addresses are allowed";
        }
        
        // Validate the rest of the email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format";
        }
        
        return true;
    }

    // Phone validation
    public static function validatePhone($phone) {
        // Remove any non-digit characters
        $phone = preg_replace("/[^0-9]/", "", $phone);
        
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return "Phone number must be between 10 and 15 digits";
        }
        return true;
    }

    // Username validation
    public static function validateUsername($username) {
        if (!preg_match("/^[a-zA-Z0-9_]{4,20}$/", $username)) {
            return "Username must be 4-20 characters long and can only contain letters, numbers, and underscores";
        }
        return true;
    }

    // CSRF Token generation and validation
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    /**
     * Sanitize output for HTML display
     * @param string $data The data to sanitize
     * @return string Sanitized data
     */
    public static function sanitizeOutput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeOutput'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate and sanitize integer input
     * @param mixed $input The input to validate
     * @param int $min Minimum allowed value
     * @param int $max Maximum allowed value
     * @return int|false Returns sanitized integer or false if invalid
     */
    public static function validateInt($input, $min = null, $max = null) {
        $input = filter_var($input, FILTER_VALIDATE_INT);
        if ($input === false) {
            return false;
        }
        if ($min !== null && $input < $min) {
            return false;
        }
        if ($max !== null && $input > $max) {
            return false;
        }
        return $input;
    }

    /**
     * Validate date format
     * @param string $date Date string to validate
     * @param string $format Expected date format
     * @return bool True if valid, false otherwise
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validate and sanitize string input
     * @param string $input The input to validate
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @return string|false Returns sanitized string or false if invalid
     */
    public static function validateString($input, $minLength = 1, $maxLength = 255) {
        $input = trim($input);
        if (strlen($input) < $minLength || strlen($input) > $maxLength) {
            return false;
        }
        return filter_var($input, FILTER_SANITIZE_STRING);
    }

    /**
     * Validate status value
     * @param string $status Status to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateStatus($status) {
        $validStatuses = ['scheduled', 'departed', 'completed', 'cancelled'];
        return in_array(strtolower($status), $validStatuses);
    }

    /**
     * Validate issue type
     * @param string $type Issue type to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateIssueType($type) {
        $validTypes = ['mechanical', 'puncture', 'fuel', 'accident', 'traffic', 'weather', 'passenger'];
        return in_array(strtolower($type), $validTypes);
    }

    /**
     * Validate time format
     * @param string $time Time string to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateTime($time) {
        return preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $time);
    }

    /**
     * Log validation error
     * @param string $message Error message
     * @param array $context Additional context
     */
    public static function logError($message, $context = []) {
        $logMessage = date('Y-m-d H:i:s') . " - Validation Error: " . $message;
        if (!empty($context)) {
            $logMessage .= " - Context: " . json_encode($context);
        }
        error_log($logMessage);
    }

    /**
     * Check rate limit for an IP address
     * @param string $ip IP address to check
     * @param string $action Action being rate limited (e.g., 'login')
     * @param int $maxAttempts Maximum number of attempts allowed (default: 5)
     * @param int $timeWindow Time window in seconds (default: 300 - 5 minutes)
     * @return bool True if within rate limit, false if limit exceeded
     */
    public static function checkRateLimit($ip, $action, $maxAttempts = 5, $timeWindow = 300) {
        $rateLimit = self::getRateLimitData();
        $currentTime = time();
        
        // Clean up old entries
        foreach ($rateLimit as $key => $data) {
            if ($currentTime - $data['timestamp'] > $timeWindow) {
                unset($rateLimit[$key]);
            }
        }
        
        // Generate unique key for this IP and action
        $key = $ip . '_' . $action;
        
        // If no attempts yet, initialize
        if (!isset($rateLimit[$key])) {
            $rateLimit[$key] = [
                'attempts' => 1,
                'timestamp' => $currentTime
            ];
            self::saveRateLimitData($rateLimit);
            return true;
        }
        
        // Check if within time window
        if ($currentTime - $rateLimit[$key]['timestamp'] > $timeWindow) {
            // Reset if time window expired
            $rateLimit[$key] = [
                'attempts' => 1,
                'timestamp' => $currentTime
            ];
            self::saveRateLimitData($rateLimit);
            return true;
        }
        
        // Increment attempts
        $rateLimit[$key]['attempts']++;
        self::saveRateLimitData($rateLimit);
        
        // Check if exceeded max attempts
        return $rateLimit[$key]['attempts'] <= $maxAttempts;
    }
    
    /**
     * Get rate limit data from storage
     * @return array Rate limit data
     */
    private static function getRateLimitData() {
        $file = __DIR__ . '/../data/rate_limit.json';
        if (!file_exists($file)) {
            if (!is_dir(__DIR__ . '/../data')) {
                mkdir(__DIR__ . '/../data', 0755, true);
            }
            file_put_contents($file, '{}');
            return [];
        }
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Save rate limit data to storage
     * @param array $data Rate limit data to save
     */
    private static function saveRateLimitData($data) {
        $file = __DIR__ . '/../data/rate_limit.json';
        file_put_contents($file, json_encode($data));
    }

    /**
     * Validates a driver's license number format
     * Format: 2 letters (state), 2 digits (RTO), space, 11 digits (unique number)
     * Example: MH12 20110012345
     * 
     * @param string $license The license number to validate
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateDriverLicense($license) {
        // Remove any extra spaces
        $license = trim($license);
        
        // Check if the license matches the required format
        if (!preg_match('/^[A-Z]{2}[0-9]{2}\s[0-9]{11}$/', $license)) {
            return [
                'valid' => false,
                'message' => 'Invalid license format. Required format: 2 letters (state), 2 digits (RTO), space, 11 digits (e.g., MH12 20110012345)'
            ];
        }
        
        // Extract state code
        $stateCode = substr($license, 0, 2);
        
        // List of valid state codes (you can expand this list)
        $validStateCodes = [
            'AN', 'AP', 'AR', 'AS', 'BR', 'CH', 'CT', 'DL', 'GA', 'GJ', 'HP', 'HR', 'JH', 'KA', 'KL', 
            'MP', 'MH', 'MN', 'ML', 'MZ', 'NL', 'OR', 'PB', 'RJ', 'SK', 'TN', 'TS', 'TR', 'UP', 'UT', 'WB'
        ];
        
        if (!in_array($stateCode, $validStateCodes)) {
            return [
                'valid' => false,
                'message' => 'Invalid state code in license number'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Valid license format'
        ];
    }

    /**
     * Validates a driver's license class
     * 
     * @param string $licenseClass The license class to validate
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateLicenseClass($licenseClass) {
        // List of valid license classes for bus drivers
        $validClasses = ['Heavy', 'Medium', 'Light'];
        
        if (!in_array($licenseClass, $validClasses)) {
            return [
                'valid' => false,
                'message' => 'Invalid license class. Must be one of: ' . implode(', ', $validClasses)
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Valid license class'
        ];
    }

    /**
     * Validates a driver's license expiry date
     * 
     * @param string $expiryDate The expiry date to validate (format: YYYY-MM-DD)
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateLicenseExpiry($expiryDate) {
        // Check if the date is in the correct format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiryDate)) {
            return [
                'valid' => false,
                'message' => 'Invalid date format. Required format: YYYY-MM-DD'
            ];
        }
        
        // Convert to timestamp
        $expiryTimestamp = strtotime($expiryDate);
        $currentTimestamp = time();
        
        // Check if the date is valid
        if ($expiryTimestamp === false) {
            return [
                'valid' => false,
                'message' => 'Invalid date'
            ];
        }
        
        // Check if the license has expired
        if ($expiryTimestamp < $currentTimestamp) {
            return [
                'valid' => false,
                'message' => 'License has expired'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Valid expiry date'
        ];
    }
} 