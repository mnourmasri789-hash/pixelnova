<?php
/**
 * PixelNova Portfolio — Database Configuration
 * 
 * Update the credentials below to match your environment.
 */
define('DB_HOST', getEnvVar('MYSQLHOST', 'MYSQL_HOST'));
define('DB_NAME', getEnvVar('MYSQLDATABASE', 'MYSQL_DATABASE'));
define('DB_USER', getEnvVar('MYSQLUSER', 'MYSQL_USER'));
define('DB_PASS', getEnvVar('MYSQLPASSWORD', 'MYSQL_PASSWORD'));
define('DB_PORT', getEnvVar('MYSQLPORT', 'MYSQL_PORT'));
define('DB_CHARSET', 'utf8mb4');
/**
 * Get a PDO database connection (singleton pattern).
 *
 * @return PDO
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // تم تحديث الـ DSN ليشمل المنفذ (Port) بشكل صحيح
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // تظهر الخطأ الحقيقي للمساعدة في التشخيص
            http_response_code(500);
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Generate a CSRF token and store it in the session.
 *
 * @return string
 */
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session token.
 *
 * @param string $token
 * @return bool
 */
function verifyCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize a string for safe HTML output.
 *
 * @param string|null $str
 * @return string
 */
function esc(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
