<?php
/**
 * PixelNova Admin — Dashboard
 */
session_start();
require_once __DIR__ . '/../db_config.php';

// ── Auth guard ──────────────────────────────────────────────────────────────
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db   = getDB();
$csrf = generateCSRFToken();

// ── Fetch stats ─────────────────────────────────────────────────────────────
$totalProjects  = (int) $db->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$activeProjects = (int) $db->query('SELECT COUNT(*) FROM projects WHERE is_active = 1')->fetchColumn();
$totalMessages  = (int) $db->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$unreadMessages = (int) $db->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();

// ── Fetch projects ──────────────────────────────────────────────────────────
$projects = $db->query('SELECT * FROM projects ORDER BY sort_order ASC, id DESC')->fetchAll();

// ── Fetch messages ──────────────────────────────────────────────────────────
$messages = $db->query('SELECT * FROM messages ORDER BY date_created DESC')->fetchAll();

// ── Flash status from actions.php ───────────────────────────────────────────
$flashStatus = $_GET['status'] ?? '';
$flashMsg    = $_GET['msg'] ?? '';

// ── Edit mode? ──────────────────────────────────────────────────────────────
$editProject = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt   = $db->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editId]);
    $editProject = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelNova — Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── Reset & Base ────────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            color: #e0e0e0;
            min-height: 100vh;
            line-height: 1.6;
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0a0a0f; }
        ::-webkit-scrollbar-thumb { background: rgba(0,240,255,0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,240,255,0.4); }

        /* ── Ambient background ──────────────────────────────────────── */
        body::before {
            content: '';
            position: fixed;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(0,240,255,0.06), transparent 70%);
            top: -200px; left: -100px;
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(123,47,247,0.05), transparent 70%);
            bottom: -150px; right: -80px;
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        /* ── Navbar ──────────────────────────────────────────────────── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 32px;
            background: rgba(10, 10, 15, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .navbar .brand {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #00f0ff, #7b2ff7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .navbar .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .navbar .nav-right span {
            color: rgba(255,255,255,0.4);
            font-size: 13px;
        }
        .navbar .nav-right a {
            color: #ff6b7a;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 18px;
            border: 1px solid rgba(255,107,122,0.25);
            border-radius: 8px;
            transition: all 0.3s;
        }
        .navbar .nav-right a:hover {
            background: rgba(255,107,122,0.1);
            border-color: rgba(255,107,122,0.5);
        }

        /* ── Layout ──────────────────────────────────────────────────── */
        .container {
            position: relative;
            z-index: 1;
            max-width: 1300px;
            margin: 0 auto;
            padding: 32px 24px 60px;
        }

        /* ── Flash Messages ──────────────────────────────────────────── */
        .flash {
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .flash.success {
            background: rgba(0, 230, 130, 0.08);
            border: 1px solid rgba(0, 230, 130, 0.2);
            color: #00e682;
        }
        .flash.error {
            background: rgba(255, 60, 80, 0.08);
            border: 1px solid rgba(255, 60, 80, 0.2);
            color: #ff6b7a;
        }
        .flash .flash-close {
            margin-left: auto;
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 18px;
            opacity: 0.6;
        }
        .flash .flash-close:hover { opacity: 1; }

        /* ── Stats Grid ──────────────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: rgba(15, 15, 30, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 28px 24px;
            transition: transform 0.3s, border-color 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(0,240,255,0.15);
        }
        .stat-card .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.35);
            margin-bottom: 10px;
            font-weight: 500;
        }
        .stat-card .stat-value {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #00f0ff, #7b2ff7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-card.cyan .stat-value   { background: linear-gradient(135deg, #00f0ff, #00b8d4); -webkit-background-clip: text; background-clip: text; }
        .stat-card.green .stat-value  { background: linear-gradient(135deg, #00e682, #00b368); -webkit-background-clip: text; background-clip: text; }
        .stat-card.purple .stat-value { background: linear-gradient(135deg, #7b2ff7, #a855f7); -webkit-background-clip: text; background-clip: text; }
        .stat-card.orange .stat-value { background: linear-gradient(135deg, #ff9f43, #ff6b6b); -webkit-background-clip: text; background-clip: text; }

        /* ── Section ─────────────────────────────────────────────────── */
        .section {
            margin-bottom: 48px;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .section-header h2 {
            font-size: 22px;
            font-weight: 600;
            color: #fff;
        }

        /* ── Glass Card ──────────────────────────────────────────────── */
        .glass-card {
            background: rgba(15, 15, 30, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            overflow: hidden;
        }

        /* ── Table ───────────────────────────────────────────────────── */
        .table-wrap {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        table th {
            text-align: left;
            padding: 14px 18px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.35);
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            white-space: nowrap;
        }
        table td {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle;
        }
        table tbody tr {
            transition: background 0.2s;
        }
        table tbody tr:hover {
            background: rgba(0, 240, 255, 0.02);
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Badges ──────────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-active   { background: rgba(0,230,130,0.12); color: #00e682; }
        .badge-inactive { background: rgba(255,107,122,0.12); color: #ff6b7a; }
        .badge-unread   { background: rgba(0,240,255,0.12); color: #00f0ff; }
        .badge-read     { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.35); }

        /* ── Row unread highlight ────────────────────────────────────── */
        tr.unread-row {
            background: rgba(0, 240, 255, 0.03);
        }
        tr.unread-row:hover {
            background: rgba(0, 240, 255, 0.05);
        }

        /* ── Buttons ─────────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-primary {
            background: linear-gradient(135deg, #00f0ff, #7b2ff7);
            color: #0a0a0f;
        }
        .btn-primary:hover {
            box-shadow: 0 4px 20px rgba(0,240,255,0.3);
            transform: translateY(-1px);
        }
        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 8px;
        }
        .btn-edit {
            background: rgba(0, 240, 255, 0.1);
            color: #00f0ff;
            border: 1px solid rgba(0,240,255,0.2);
        }
        .btn-edit:hover {
            background: rgba(0, 240, 255, 0.2);
            border-color: rgba(0,240,255,0.4);
        }
        .btn-danger {
            background: rgba(255, 60, 80, 0.1);
            color: #ff6b7a;
            border: 1px solid rgba(255,60,80,0.2);
        }
        .btn-danger:hover {
            background: rgba(255, 60, 80, 0.2);
            border-color: rgba(255,60,80,0.4);
        }
        .btn-success {
            background: rgba(0, 230, 130, 0.1);
            color: #00e682;
            border: 1px solid rgba(0,230,130,0.2);
        }
        .btn-success:hover {
            background: rgba(0, 230, 130, 0.2);
            border-color: rgba(0,230,130,0.4);
        }
        .btn-ghost {
            background: rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.5);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .btn-ghost:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }
        .actions-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ── Form ────────────────────────────────────────────────────── */
        .project-form {
            padding: 32px;
        }
        .project-form h3 {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-grid .full-width {
            grid-column: 1 / -1;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.45);
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: rgba(0,240,255,0.4);
            box-shadow: 0 0 0 3px rgba(0,240,255,0.08);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 90px;
        }
        .form-group select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M6 8L1 3h10z' fill='%23555'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }
        .form-group select option {
            background: #15152a;
            color: #fff;
        }
        .form-group input[type="file"] {
            padding: 10px;
        }
        .form-group input[type="file"]::file-selector-button {
            background: rgba(0,240,255,0.1);
            color: #00f0ff;
            border: 1px solid rgba(0,240,255,0.2);
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            margin-right: 12px;
            transition: background 0.3s;
        }
        .form-group input[type="file"]::file-selector-button:hover {
            background: rgba(0,240,255,0.2);
        }
        .checkbox-group {
            flex-direction: row;
            align-items: center;
            gap: 10px;
            padding-top: 28px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #00f0ff;
            cursor: pointer;
        }
        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        /* ── Message preview ─────────────────────────────────────────── */
        .msg-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: rgba(255,255,255,0.6);
        }

        /* ── Empty state ─────────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: rgba(255,255,255,0.25);
            font-size: 14px;
        }

        /* ── Inline confirm ──────────────────────────────────────────── */
        .confirm-delete {
            display: none;
        }

        /* ── Responsive ──────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .navbar { padding: 14px 16px; }
            .container { padding: 20px 16px 40px; }
            .form-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
            .stat-card { padding: 20px 18px; }
            .stat-card .stat-value { font-size: 28px; }
            .section-header { flex-direction: column; align-items: flex-start; }
            .project-form { padding: 24px 16px; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ── Navbar ─────────────────────────────────────────────────────────── -->
<nav class="navbar">
    <div class="brand">PixelNova Admin</div>
    <div class="nav-right">
        <span>👤 <?= esc($_SESSION['admin_username'] ?? 'Admin') ?></span>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

    <!-- ── Flash Message ─────────────────────────────────────────────── -->
    <?php if ($flashMsg): ?>
        <div class="flash <?= $flashStatus === 'success' ? 'success' : 'error' ?>">
            <span><?= esc($flashMsg) ?></span>
            <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>

    <!-- ── Stats ─────────────────────────────────────────────────────── -->
    <div class="stats-grid">
        <div class="stat-card cyan">
            <div class="stat-label">Total Projects</div>
            <div class="stat-value"><?= $totalProjects ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Active Projects</div>
            <div class="stat-value"><?= $activeProjects ?></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-label">Total Messages</div>
            <div class="stat-value"><?= $totalMessages ?></div>
        </div>
        <div class="stat-card orange">
            <div class="stat-label">Unread Messages</div>
            <div class="stat-value"><?= $unreadMessages ?></div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════ -->
    <!--  PROJECTS SECTION                                               -->
    <!-- ════════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-header">
            <h2>📂 Projects</h2>
            <?php if (!$editProject): ?>
                <button class="btn btn-primary" onclick="document.getElementById('projectFormCard').style.display = document.getElementById('projectFormCard').style.display === 'none' ? 'block' : 'none'">
                    + Add Project
                </button>
            <?php endif; ?>
        </div>

        <!-- ── Add / Edit Form ───────────────────────────────────────── -->
        <div class="glass-card" id="projectFormCard" style="<?= $editProject ? '' : 'display:none;' ?> margin-bottom: 24px;">
            <form class="project-form" method="POST" action="actions.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= esc($csrf) ?>">

                <?php if ($editProject): ?>
                    <input type="hidden" name="action" value="edit_project">
                    <input type="hidden" name="project_id" value="<?= (int) $editProject['id'] ?>">
                    <h3>Edit Project — <?= esc($editProject['title']) ?></h3>
                <?php else: ?>
                    <input type="hidden" name="action" value="add_project">
                    <h3>New Project</h3>
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required
                               value="<?= esc($editProject['title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="project_url">Project URL</label>
                        <input type="text" id="project_url" name="project_url" placeholder="https://..."
                               value="<?= esc($editProject['project_url'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="description_tr">Description (TR)</label>
                        <textarea id="description_tr" name="description_tr"><?= esc($editProject['description_tr'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="description_en">Description (EN)</label>
                        <textarea id="description_en" name="description_en"><?= esc($editProject['description_en'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image_file">Upload Image</label>
                        <input type="file" id="image_file" name="image_file" accept=".jpg,.jpeg,.png,.gif,.webp">
                        <?php if (!empty($editProject['image'])): ?>
                            <span style="font-size:12px;color:rgba(255,255,255,0.35);margin-top:6px;">Current: <?= esc($editProject['image']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="image_path">Or Image Path (manual)</label>
                        <input type="text" id="image_path" name="image_path" placeholder="images/my-project.webp"
                               value="<?= esc($editProject['image'] ?? '') ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="tech_stack">Tech Stack (comma separated)</label>
                        <input type="text" id="tech_stack" name="tech_stack" placeholder="HTML, CSS, JavaScript, PHP"
                               value="<?= esc($editProject['tech_stack'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="badge_text_tr">Badge Text (TR)</label>
                        <input type="text" id="badge_text_tr" name="badge_text_tr"
                               value="<?= esc($editProject['badge_text_tr'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="badge_text_en">Badge Text (EN)</label>
                        <input type="text" id="badge_text_en" name="badge_text_en"
                               value="<?= esc($editProject['badge_text_en'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="badge_class">Badge Class</label>
                        <select id="badge_class" name="badge_class">
                            <option value="">— Select —</option>
                            <?php
                            $classes = ['ecommerce', 'menu', 'automation', 'rsvp'];
                            foreach ($classes as $cls):
                                $sel = (($editProject['badge_class'] ?? '') === $cls) ? 'selected' : '';
                            ?>
                                <option value="<?= $cls ?>" <?= $sel ?>><?= ucfirst($cls) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" min="0"
                               value="<?= (int)($editProject['sort_order'] ?? 0) ?>">
                    </div>

                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               <?= (!$editProject || !empty($editProject['is_active'])) ? 'checked' : '' ?>>
                        <label for="is_active">Active</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $editProject ? 'Update Project' : 'Add Project' ?>
                    </button>
                    <?php if ($editProject): ?>
                        <a href="dashboard.php" class="btn btn-ghost">Cancel</a>
                    <?php else: ?>
                        <button type="button" class="btn btn-ghost"
                                onclick="document.getElementById('projectFormCard').style.display='none'">Cancel</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ── Projects Table ────────────────────────────────────────── -->
        <div class="glass-card">
            <?php if (empty($projects)): ?>
                <div class="empty-state">No projects yet. Click "Add Project" to create one.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Badge</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $p): ?>
                            <tr>
                                <td style="color:rgba(255,255,255,0.3);"><?= (int)$p['id'] ?></td>
                                <td style="font-weight:500;color:#fff;"><?= esc($p['title']) ?></td>
                                <td>
                                    <?php if ($p['badge_text_en']): ?>
                                        <span class="badge badge-active"><?= esc($p['badge_text_en']) ?></span>
                                    <?php else: ?>
                                        <span style="color:rgba(255,255,255,0.2);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color:rgba(255,255,255,0.4);"><?= (int)$p['sort_order'] ?></td>
                                <td>
                                    <span class="badge <?= $p['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td style="color:rgba(255,255,255,0.35);font-size:12px;">
                                    <?= esc(date('d M Y', strtotime($p['updated_at']))) ?>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="dashboard.php?edit=<?= (int)$p['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <form method="POST" action="actions.php" style="display:inline;"
                                              onsubmit="return confirm('Delete this project permanently?')">
                                            <input type="hidden" name="csrf_token" value="<?= esc($csrf) ?>">
                                            <input type="hidden" name="action" value="delete_project">
                                            <input type="hidden" name="project_id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════ -->
    <!--  MESSAGES SECTION                                               -->
    <!-- ════════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-header">
            <h2>💬 Messages</h2>
        </div>

        <div class="glass-card">
            <?php if (empty($messages)): ?>
                <div class="empty-state">No messages received yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $m): ?>
                            <tr class="<?= $m['is_read'] ? '' : 'unread-row' ?>">
                                <td style="font-weight:500;color:#fff;"><?= esc($m['name']) ?></td>
                                <td>
                                    <a href="mailto:<?= esc($m['email']) ?>" style="color:#00f0ff;text-decoration:none;">
                                        <?= esc($m['email']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="msg-preview" title="<?= esc($m['message']) ?>">
                                        <?= esc($m['message']) ?>
                                    </div>
                                </td>
                                <td style="color:rgba(255,255,255,0.35);font-size:12px;white-space:nowrap;">
                                    <?= esc(date('d M Y H:i', strtotime($m['date_created']))) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $m['is_read'] ? 'badge-read' : 'badge-unread' ?>">
                                        <?= $m['is_read'] ? 'Read' : 'Unread' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <?php if (!$m['is_read']): ?>
                                            <form method="POST" action="actions.php" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= esc($csrf) ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="message_id" value="<?= (int)$m['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success">Mark Read</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="actions.php" style="display:inline;"
                                              onsubmit="return confirm('Delete this message?')">
                                            <input type="hidden" name="csrf_token" value="<?= esc($csrf) ?>">
                                            <input type="hidden" name="action" value="delete_message">
                                            <input type="hidden" name="message_id" value="<?= (int)$m['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /.container -->

<script>
// Auto-dismiss flash messages after 5 seconds
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.4s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 400);
        }, 5000);
    }
});
</script>
</body>
</html>
