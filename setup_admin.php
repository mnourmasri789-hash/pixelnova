<?php
/**
 * PixelNova Portfolio — Setup Script
 * 
 * Run this once to generate a proper admin password hash.
 * Usage: php setup_admin.php
 * 
 * After running, delete this file for security.
 */

require_once __DIR__ . '/db_config.php';

$username = 'admin';
$password = 'PixelNova2026!';

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $db = getDB();
    
    // Check if admin already exists
    $stmt = $db->prepare('SELECT id FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        // Update existing
        $stmt = $db->prepare('UPDATE admin_users SET password_hash = ? WHERE username = ?');
        $stmt->execute([$hash, $username]);
        echo "✅ Admin password updated successfully!\n";
    } else {
        // Insert new
        $stmt = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, $hash]);
        echo "✅ Admin user created successfully!\n";
    }
    
    echo "\n   Username: {$username}\n";
    echo "   Password: {$password}\n";
    echo "\n⚠️  Delete this file after running it!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nMake sure you've imported database.sql first:\n";
    echo "   mysql -u root -p < database.sql\n";
}
