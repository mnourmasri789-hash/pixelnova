
<?php
/**
 * PixelNova Portfolio — Database Configuration
 */

define('DB_HOST', getenv('MYSQLHOST') ?: 'mysql.railway.internal');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD'));
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

function getDB()
{
    static $conn = null;

    if ($conn === null) {

        $conn = mysqli_connect(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            (int)DB_PORT
        );

        if (!$conn) {
            die('MySQLi Error: ' . mysqli_connect_error());
        }

        mysqli_set_charset($conn, "utf8mb4");
    }

    return $conn;
}

function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

function esc(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
