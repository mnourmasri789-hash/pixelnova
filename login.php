<?php
/**
 * PixelNova Admin — Login
 */
session_start();
require_once __DIR__ . '/../db_config.php';

// Already logged in? Go to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (empty($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid session. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1');
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    // Clear old CSRF token so dashboard generates a fresh one
                    unset($_SESSION['csrf_token']);
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error = 'A server error occurred. Please try again later.';
            }
        }
    }
}

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelNova — Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background orbs */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            z-index: 0;
            animation: float 8s ease-in-out infinite alternate;
        }
        body::before {
            width: 500px; height: 500px;
            background: #00f0ff;
            top: -150px; left: -100px;
        }
        body::after {
            width: 400px; height: 400px;
            background: #7b2ff7;
            bottom: -100px; right: -80px;
            animation-delay: 4s;
        }

        @keyframes float {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.1); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(15, 15, 30, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 20px;
            padding: 48px 40px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255,255,255,0.05);
        }

        .login-card .logo {
            text-align: center;
            margin-bottom: 36px;
        }
        .login-card .logo h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #00f0ff, #7b2ff7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        .login-card .logo p {
            color: rgba(255,255,255,0.35);
            font-size: 13px;
            margin-top: 6px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 22px;
        }
        .form-group label {
            display: block;
            color: rgba(255,255,255,0.55);
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }
        .form-group input:focus {
            border-color: rgba(0, 240, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
        }
        .form-group input::placeholder {
            color: rgba(255,255,255,0.2);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #00f0ff, #7b2ff7);
            color: #0a0a0f;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            letter-spacing: 0.3px;
            margin-top: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 240, 255, 0.3);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        .error-msg {
            background: rgba(255, 60, 80, 0.1);
            border: 1px solid rgba(255, 60, 80, 0.25);
            color: #ff6b7a;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 22px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 36px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>PixelNova</h1>
                <p>Admin Panel</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?= esc($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= esc($csrf) ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required
                           value="<?= esc($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
