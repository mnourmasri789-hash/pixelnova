
<?php
/**
 * PixelNova Portfolio — Database Configuration
 */

function getEnvVar($name, $alternative) {
    return getenv($name) !== false ? getenv($name) : getenv($alternative);
}

define('DB_HOST', getenv('MYSQLHOST') ?: 'mysql.railway.internal');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: 'YOUR_PASSWORD');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

function getDB()
{
    static $pdo = null;

    if ($pdo === null) {

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

            if (function_exists('mysqli_connect')) {
                $conn = mysqli_connect(
                    DB_HOST,
                    DB_USER,
                    DB_PASS,
                    DB_NAME,
                    (int)DB_PORT
                );

                if ($conn) {
                    return $conn;
                }
            }

            http_response_code(500);
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    return $pdo;
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
