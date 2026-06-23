<?php
/**
 * PixelNova Portfolio — Contact Form Handler (AJAX)
 * 
 * Receives POST data from the contact form and saves to the messages table.
 * Returns JSON response.
 */

header('Content-Type: application/json; charset=utf-8');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/db_config.php';

// Get and validate input
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

if (empty($name) || mb_strlen($name) > 255) {
    $errors[] = 'Name is required (max 255 characters).';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}

if (empty($message) || mb_strlen($message) > 5000) {
    $errors[] = 'Message is required (max 5000 characters).';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Rate limiting: simple check based on IP (max 5 messages per hour)
session_start();
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateKey = 'msg_rate_' . md5($ip);

if (!isset($_SESSION[$rateKey])) {
    $_SESSION[$rateKey] = ['count' => 0, 'reset' => time() + 3600];
}

if (time() > $_SESSION[$rateKey]['reset']) {
    $_SESSION[$rateKey] = ['count' => 0, 'reset' => time() + 3600];
}

$_SESSION[$rateKey]['count']++;

if ($_SESSION[$rateKey]['count'] > 5) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many messages. Please try again later.']);
    exit;
}

// Save to database
try {
    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO messages (name, email, message, date_created) VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$name, $email, $message]);

    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save message. Please try again.']);
}
