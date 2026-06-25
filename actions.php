<?php
/**
 * PixelNova Admin — Action Handler
 *
 * All POST actions are routed through this file.
 * Every action validates the session and CSRF token.
 */
session_start();
require_once __DIR__ . '/../db_config.php';

// ── Auth guard ──────────────────────────────────────────────────────────────
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// ── Only accept POST ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// ── CSRF guard ──────────────────────────────────────────────────────────────
if (empty($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    header('Location: dashboard.php?status=error&msg=' . urlencode('Invalid security token. Please try again.'));
    exit;
}

$action = $_POST['action'] ?? '';
$db = getDB();

// ── Helper: redirect with status ────────────────────────────────────────────
function redirectBack(string $status, string $msg): void
{
    header('Location: dashboard.php?status=' . urlencode($status) . '&msg=' . urlencode($msg));
    exit;
}

// ── Helper: handle image upload ─────────────────────────────────────────────
function handleImageUpload(string $inputName, ?string $existingImage = null): ?string
{
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $uploadDir = __DIR__ . '/../images/';

    // No file uploaded — keep existing
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $existingImage;
    }

    $file = $_FILES[$inputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $existingImage;
    }

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        redirectBack('error', 'Invalid image type. Allowed: ' . implode(', ', $allowed));
    }

    // Validate MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowedMimes, true)) {
        redirectBack('error', 'Invalid image file content.');
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        redirectBack('error', 'Image too large. Maximum 5 MB.');
    }

    // Generate unique filename
    $newName = 'project_' . bin2hex(random_bytes(8)) . '.' . $ext;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
        redirectBack('error', 'Failed to save uploaded image.');
    }

    return 'images/' . $newName;
}

// ── Action routing ──────────────────────────────────────────────────────────
try {
    switch ($action) {

        // ── ADD PROJECT ─────────────────────────────────────────────────
        case 'add_project':
            $title        = trim($_POST['title'] ?? '');
            $project_url  = trim($_POST['project_url'] ?? '');
            $desc_tr      = trim($_POST['description_tr'] ?? '');
            $desc_en      = trim($_POST['description_en'] ?? '');
            $tech_stack   = trim($_POST['tech_stack'] ?? '');
            $badge_tr     = trim($_POST['badge_text_tr'] ?? '');
            $badge_en     = trim($_POST['badge_text_en'] ?? '');
            $badge_class  = trim($_POST['badge_class'] ?? '');
            $sort_order   = (int)($_POST['sort_order'] ?? 0);
            $is_active    = isset($_POST['is_active']) ? 1 : 0;

            if ($title === '') {
                redirectBack('error', 'Project title is required.');
            }

            $image = handleImageUpload('image_file');
            // Fallback: manual image path
            if (!$image) {
                $image = trim($_POST['image_path'] ?? '');
            }

            $stmt = $db->prepare('
                INSERT INTO projects (title, project_url, description_tr, description_en, image, tech_stack, badge_text_tr, badge_text_en, badge_class, sort_order, is_active, created_at, updated_at)
                VALUES (:title, :project_url, :desc_tr, :desc_en, :image, :tech_stack, :badge_tr, :badge_en, :badge_class, :sort_order, :is_active, NOW(), NOW())
            ');
            $stmt->execute([
                ':title'       => $title,
                ':project_url' => $project_url,
                ':desc_tr'     => $desc_tr,
                ':desc_en'     => $desc_en,
                ':image'       => $image,
                ':tech_stack'  => $tech_stack,
                ':badge_tr'    => $badge_tr,
                ':badge_en'    => $badge_en,
                ':badge_class' => $badge_class,
                ':sort_order'  => $sort_order,
                ':is_active'   => $is_active,
            ]);
            redirectBack('success', 'Project added successfully.');
            break;

        // ── EDIT PROJECT ────────────────────────────────────────────────
        case 'edit_project':
            $id           = (int)($_POST['project_id'] ?? 0);
            $title        = trim($_POST['title'] ?? '');
            $project_url  = trim($_POST['project_url'] ?? '');
            $desc_tr      = trim($_POST['description_tr'] ?? '');
            $desc_en      = trim($_POST['description_en'] ?? '');
            $tech_stack   = trim($_POST['tech_stack'] ?? '');
            $badge_tr     = trim($_POST['badge_text_tr'] ?? '');
            $badge_en     = trim($_POST['badge_text_en'] ?? '');
            $badge_class  = trim($_POST['badge_class'] ?? '');
            $sort_order   = (int)($_POST['sort_order'] ?? 0);
            $is_active    = isset($_POST['is_active']) ? 1 : 0;

            if ($id <= 0 || $title === '') {
                redirectBack('error', 'Invalid project data.');
            }

            // Get existing image
            $stmt = $db->prepare('SELECT image FROM projects WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $existing = $stmt->fetch();
            $existingImage = $existing ? $existing['image'] : null;

            $image = handleImageUpload('image_file', $existingImage);
            // Fallback: manual image path (only if no upload and user typed one)
            $manualPath = trim($_POST['image_path'] ?? '');
            if ($manualPath !== '' && (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] === UPLOAD_ERR_NO_FILE)) {
                $image = $manualPath;
            }

            $stmt = $db->prepare('
                UPDATE projects
                SET title = :title, project_url = :project_url, description_tr = :desc_tr, description_en = :desc_en, image = :image,
                    tech_stack = :tech_stack, badge_text_tr = :badge_tr, badge_text_en = :badge_en,
                    badge_class = :badge_class, sort_order = :sort_order, is_active = :is_active, updated_at = NOW()
                WHERE id = :id
            ');
            $stmt->execute([
                ':title'       => $title,
                ':project_url' => $project_url,
                ':desc_tr'     => $desc_tr,
                ':desc_en'     => $desc_en,
                ':image'       => $image,
                ':tech_stack'  => $tech_stack,
                ':badge_tr'    => $badge_tr,
                ':badge_en'    => $badge_en,
                ':badge_class' => $badge_class,
                ':sort_order'  => $sort_order,
                ':is_active'   => $is_active,
                ':id'          => $id,
            ]);
            redirectBack('success', 'Project updated successfully.');
            break;

        // ── DELETE PROJECT ──────────────────────────────────────────────
        case 'delete_project':
            $id = (int)($_POST['project_id'] ?? 0);
            if ($id <= 0) {
                redirectBack('error', 'Invalid project ID.');
            }
            $stmt = $db->prepare('DELETE FROM projects WHERE id = :id');
            $stmt->execute([':id' => $id]);
            redirectBack('success', 'Project deleted.');
            break;

        // ── DELETE MESSAGE ──────────────────────────────────────────────
        case 'delete_message':
            $id = (int)($_POST['message_id'] ?? 0);
            if ($id <= 0) {
                redirectBack('error', 'Invalid message ID.');
            }
            $stmt = $db->prepare('DELETE FROM messages WHERE id = :id');
            $stmt->execute([':id' => $id]);
            redirectBack('success', 'Message deleted.');
            break;

        // ── MARK MESSAGE AS READ ────────────────────────────────────────
        case 'mark_read':
            $id = (int)($_POST['message_id'] ?? 0);
            if ($id <= 0) {
                redirectBack('error', 'Invalid message ID.');
            }
            $stmt = $db->prepare('UPDATE messages SET is_read = 1 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            redirectBack('success', 'Message marked as read.');
            break;

        // ── UNKNOWN ACTION ──────────────────────────────────────────────
        default:
            redirectBack('error', 'Unknown action.');
            break;
    }
} catch (PDOException $e) {
    redirectBack('error', 'Database error. Please try again.');
}
