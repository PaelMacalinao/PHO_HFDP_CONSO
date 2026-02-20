<?php
/**
 * PHO CONSO HFDP - User Management (Settings)
 * Add new Staff accounts
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

// Restrict access to admin users only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access Denied: Only administrators can access this page.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$message = '';
$messageType = '';

// Check for session flash messages (e.g. from delete action)
if (!empty($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']);
} elseif (!empty($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $messageType = 'error';
    unset($_SESSION['error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $assigned_facility = $_POST['assigned_facility'] ?? '';

    if ($username === '' || strlen($username) < 3) {
        $message = 'Username must be at least 3 characters.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } elseif ($password !== $password_confirm) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif ($role === 'staff' && trim($assigned_facility) === '') {
        $message = 'Facility assignment is required for staff users.';
        $messageType = 'error';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->bind_result($existingId);
            $exists = $stmt->fetch();
            $stmt->close();
            if ($exists) {
                $message = 'Username already exists.';
                $messageType = 'error';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $facilityValue = ($role === 'staff') ? $assigned_facility : null;
                $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role, assigned_facility) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssss', $username, $hash, $role, $facilityValue);
                if ($stmt->execute()) {
                    $message = 'Staff account created successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Could not create account. Please try again.';
                    $messageType = 'error';
                }
                $stmt->close();
            }
            $db->close();
        } catch (Throwable $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Load existing users
$users = [];
try {
    $db = new Database();
    $conn = $db->getConnection();
    $res = $conn->query("SELECT id, username, role, assigned_facility, created_at FROM users ORDER BY username");
    if ($res) {
        while ($row = $res->fetch_assoc()) $users[] = $row;
        $res->free();
    }
    $db->close();
} catch (Throwable $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHO CONSO HFDP - User Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .facility-auto-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-top: 10px; }
        .facility-auto-grid .form-group { margin-bottom: 0; }
        .facility-auto-grid input[readonly] {
            background: var(--bg-light, #f4f6f8); color: var(--text, #1a2b1d);
            border: 1px solid var(--border, #c8d6c8); cursor: default; opacity: 0.85;
        }
        .select2-container--default .select2-selection--single {
            height: 40px; padding: 5px 8px; border: 1px solid var(--border, #c8d6c8);
            border-radius: 8px; font-size: 0.95rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 28px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
        @media (max-width: 768px) { .facility-auto-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div id="page-preloader" class="page-preloader" aria-hidden="false">
        <div class="page-preloader-inner">
            <img src="assets/images/pho-logo.png" alt="" class="page-preloader-logo" onerror="this.style.display='none'">
            <div class="page-preloader-spinner"></div>
            <p class="page-preloader-text">Loading...</p>
        </div>
    </div>
    <aside class="sidebar" id="sidebar" aria-label="Main navigation">
        <div class="sidebar-brand">
            <img src="assets/images/pho-logo.png" alt="" class="sidebar-logo-img" onerror="this.style.display='none'">
            <span class="sidebar-brand-text">PHO HFDP</span>
        </div>
        <div class="sidebar-admin">
            <div class="sidebar-admin-inner">
                <div class="sidebar-admin-avatar" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
                </div>
                <div class="sidebar-admin-info">
                    <span class="sidebar-admin-role"><?php echo htmlspecialchars(strtoupper($_SESSION['role'] ?? 'staff')); ?></span>
                    <span class="sidebar-admin-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'phoadmin'); ?></span>
                </div>
            </div>
            <a href="logout.php" class="sidebar-signout">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                Sign Out
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="sidebar-link">
                <span class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
                <span class="sidebar-label">Dashboard</span>
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="sidebar-section">
                <span class="sidebar-section-title">Settings</span>
                <a href="user_management.php" class="sidebar-link active">
                    <span class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></span>
                    <span class="sidebar-label">Add New User/Staff</span>
                </a>
            </div>
            <?php endif; ?>
        </nav>
    </aside>
    <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>
    <div class="main-wrap" id="main-wrap">
    <div class="container">
        <header class="admin-header">
            <div class="admin-header-inner">
                <div class="admin-header-left">
                    <button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle menu" aria-expanded="false">
                        <span class="hamburger"><span></span><span></span><span></span></span>
                    </button>
                    <a href="index.php" class="admin-header-home">
                        <div class="admin-logo-placeholder" aria-hidden="true">
                            <img src="assets/images/pho-logo.png" alt="" class="admin-logo-img" onerror="this.style.display='none';this.parentElement.classList.add('no-img')">
                            <span class="admin-logo-fallback">PHO</span>
                        </div>
                        <div class="admin-header-title">
                            <h1>User Management</h1>
                            <span class="admin-header-subtitle">Settings · Provincial Health Office</span>
                        </div>
                    </a>
                </div>
                <div class="admin-header-right">
                    <div class="admin-header-meta">
                        <div class="admin-clock">
                            <div class="clock-date" id="clock-date">-- -- ----</div>
                            <div class="clock-time" id="clock-time">--:--:--</div>
                        </div>
                        <div class="admin-user-wrap">
                            <button type="button" class="admin-trigger" id="admin-trigger" aria-expanded="false" aria-haspopup="true">
                                <span class="admin-trigger-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg></span>
                                <span class="admin-trigger-label"><?php echo htmlspecialchars(strtoupper($_SESSION['role'] ?? 'staff')); ?></span>
                            </button>
                            <div class="admin-dropdown" id="admin-dropdown" role="menu" aria-hidden="true">
                                <div class="admin-dropdown-header">
                                    <span class="admin-dropdown-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg></span>
                                    <div class="admin-dropdown-info">
                                        <span class="admin-dropdown-role"><?php echo htmlspecialchars(strtoupper($_SESSION['role'] ?? 'staff')); ?></span>
                                        <span class="admin-dropdown-username"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <div class="admin-dropdown-sep"></div>
                                <a href="logout.php" class="admin-dropdown-signout" role="menuitem">
                                    <span class="admin-dropdown-signout-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg></span>
                                    Sign out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="data-section">
            <h2 class="section-header" style="margin-bottom: 16px;">Add New Staff Account</h2>
            <form method="post" action="user_management.php" class="data-form" style="max-width: 480px;">
                <input type="hidden" name="add_user" value="1">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" required minlength="3" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="role">Role <span class="required">*</span></label>
                        <select id="role" name="role" required onchange="toggleFacility()">
                            <option value="" disabled selected>Select Role</option>
                            <option value="admin" <?php echo isset($_POST['role']) && $_POST['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="staff" <?php echo isset($_POST['role']) && $_POST['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                        </select>
                    </div>
                    <div id="facilityContainer" style="display: none;">
                        <div class="form-group">
                            <label for="assigned_facility">Assign Facility <span class="required">*</span></label>
                            <select id="assigned_facility" name="assigned_facility" class="form-control" style="width:100%">
                                <option value="">Select Facility</option>
                                <option value="ABORLAN MEDICARE HOSPITAL">ABORLAN MEDICARE HOSPITAL</option>
                                <option value="ABORLAN MUNICIPAL HEALTH OFFICE">ABORLAN MUNICIPAL HEALTH OFFICE</option>
                                <option value="AGUTAYA MUNICIPAL HEALTH OFFICE">AGUTAYA MUNICIPAL HEALTH OFFICE</option>
                                <option value="ARACELI MUNICIPAL HEALTH OFFICE">ARACELI MUNICIPAL HEALTH OFFICE</option>
                                <option value="ARACELI-DUMARAN DISTRICT HOSPITAL">ARACELI-DUMARAN DISTRICT HOSPITAL</option>
                                <option value="BALABAC DISTRICT HOSPITAL">BALABAC DISTRICT HOSPITAL</option>
                                <option value="BALABAC MUNICIPAL HEALTH OFFICE">BALABAC MUNICIPAL HEALTH OFFICE</option>
                                <option value="BATARAZA DISTRICT HOSPITAL">BATARAZA DISTRICT HOSPITAL</option>
                                <option value="BATARAZA MUNICIPAL HEALTH OFFICE">BATARAZA MUNICIPAL HEALTH OFFICE</option>
                                <option value="BROOKE'S POINT MUNICIPAL HEALTH OFFICE">BROOKE'S POINT MUNICIPAL HEALTH OFFICE</option>
                                <option value="BUSUANGA HEALTH OFFICE">BUSUANGA HEALTH OFFICE</option>
                                <option value="CAGAYANCILLO MUNICIPAL HEALTH OFFICE">CAGAYANCILLO MUNICIPAL HEALTH OFFICE</option>
                                <option value="CORON DISTRICT HOSPITAL">CORON DISTRICT HOSPITAL</option>
                                <option value="CORON MUNICIPAL HEALTH OFFICE">CORON MUNICIPAL HEALTH OFFICE</option>
                                <option value="CULION MUNICIPAL HEALTH OFFICE">CULION MUNICIPAL HEALTH OFFICE</option>
                                <option value="CUYO DISTRICT HOSPITAL">CUYO DISTRICT HOSPITAL</option>
                                <option value="CUYO MUNICIPAL HEALTH OFFICE">CUYO MUNICIPAL HEALTH OFFICE</option>
                                <option value="DR. JOSE RIZAL DISTRICT HOSPITAL">DR. JOSE RIZAL DISTRICT HOSPITAL</option>
                                <option value="DUMARAN MUNICIPAL HEALTH OFFICE">DUMARAN MUNICIPAL HEALTH OFFICE</option>
                                <option value="EL NIDO COMMUNITY HOSPITAL">EL NIDO COMMUNITY HOSPITAL</option>
                                <option value="EL NIDO MUNICIPAL HEALTH OFFICE">EL NIDO MUNICIPAL HEALTH OFFICE</option>
                                <option value="FRANCISCO F. PONCE DE LEON HOSPITAL">FRANCISCO F. PONCE DE LEON HOSPITAL</option>
                                <option value="KALAYAAN MUNICIPAL HEALTH OFFICE">KALAYAAN MUNICIPAL HEALTH OFFICE</option>
                                <option value="LINAPACAN MUNICIPAL HEALTH OFFICE">LINAPACAN MUNICIPAL HEALTH OFFICE</option>
                                <option value="MAGSAYSAY MUNICIPAL HEALTH OFFICE">MAGSAYSAY MUNICIPAL HEALTH OFFICE</option>
                                <option value="NARRA MUNICIPAL HEALTH OFFICE">NARRA MUNICIPAL HEALTH OFFICE</option>
                                <option value="NARRA MUNICIPAL HOSPITAL">NARRA MUNICIPAL HOSPITAL</option>
                                <option value="NORTHERN PALAWAN PROVINCIAL HOSPITAL">NORTHERN PALAWAN PROVINCIAL HOSPITAL</option>
                                <option value="QUEZON MEDICARE HOSPITAL">QUEZON MEDICARE HOSPITAL</option>
                                <option value="QUEZON MUNICIPAL HEALTH OFFICE">QUEZON MUNICIPAL HEALTH OFFICE</option>
                                <option value="RIZAL MUNICIPAL HEALTH OFFICE">RIZAL MUNICIPAL HEALTH OFFICE</option>
                                <option value="ROXAS MEDICARE HOSPITAL">ROXAS MEDICARE HOSPITAL</option>
                                <option value="ROXAS MUNICIPAL HEALTH OFFICE">ROXAS MUNICIPAL HEALTH OFFICE</option>
                                <option value="SAN VICENTE DISTRICT HOSPITAL">SAN VICENTE DISTRICT HOSPITAL</option>
                                <option value="SAN VICENTE MUNICIPAL HEALTH OFFICE">SAN VICENTE MUNICIPAL HEALTH OFFICE</option>
                                <option value="SOFRONIO ESPAÑOLA DISTRICT HOSPITAL">SOFRONIO ESPAÑOLA DISTRICT HOSPITAL</option>
                                <option value="SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE">SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE</option>
                                <option value="SOUTHERN PALAWAN PROVINCIAL HOSPITAL">SOUTHERN PALAWAN PROVINCIAL HOSPITAL</option>
                                <option value="TAYTAY MUNICIPAL HEALTH OFFICE">TAYTAY MUNICIPAL HEALTH OFFICE</option>
                            </select>
                        </div>
                        <div class="facility-auto-grid">
                            <div class="form-group">
                                <label for="autoMunicipality">Municipality</label>
                                <input type="text" id="autoMunicipality" readonly placeholder="—">
                            </div>
                            <div class="form-group">
                                <label for="autoFacilityType">Type</label>
                                <input type="text" id="autoFacilityType" readonly placeholder="—">
                            </div>
                            <div class="form-group">
                                <label for="autoCluster">Health Cluster</label>
                                <input type="text" id="autoCluster" readonly placeholder="—">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Staff Account</button>
                    <a href="user_management.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="data-section" style="margin-top: 20px;">
            <h2 class="section-header">Existing Staff Accounts</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Assigned Facility</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="6" class="no-data">No users yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo (int)$u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($u['role'])); ?></td>
                                    <td><?php echo htmlspecialchars($u['assigned_facility'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($u['created_at'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (strtolower($u['role']) === 'staff'): ?>
                                        <form method="POST" action="api/delete_user.php" class="inline-form" onsubmit="return confirmDeleteUser(this);">
                                            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete user">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                                Delete
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <script src="assets/js/clock.js"></script>
    <script src="assets/js/admin-menu.js"></script>
    <script src="assets/js/sidebar.js"></script>
    <script src="assets/js/preloader.js"></script>
    <!-- jQuery + Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        /* ═══════════════════════════════════════════════════════════════
         *  Facility Database – extracted from HFDP Fields FACILITY.csv
         * ═══════════════════════════════════════════════════════════════ */
        var facilityDatabase = {
            // ── BCCL ──
            'LINAPACAN MUNICIPAL HEALTH OFFICE':        { municipality: 'LINAPACAN',        type: 'MHO',      cluster: 'BCCL' },
            'CULION MUNICIPAL HEALTH OFFICE':           { municipality: 'CULION',            type: 'MHO',      cluster: 'BCCL' },
            'BUSUANGA HEALTH OFFICE':                   { municipality: 'BUSUANGA',          type: 'MHO',      cluster: 'BCCL' },
            'CORON DISTRICT HOSPITAL':                  { municipality: 'CORON',             type: 'HOSPITAL', cluster: 'BCCL' },
            'CORON MUNICIPAL HEALTH OFFICE':            { municipality: 'CORON',             type: 'MHO',      cluster: 'BCCL' },
            // ── CAM ──
            'AGUTAYA MUNICIPAL HEALTH OFFICE':          { municipality: 'AGUTAYA',           type: 'MHO',      cluster: 'CAM' },
            'CUYO DISTRICT HOSPITAL':                   { municipality: 'CUYO',              type: 'HOSPITAL', cluster: 'CAM' },
            'CUYO MUNICIPAL HEALTH OFFICE':             { municipality: 'CUYO',              type: 'MHO',      cluster: 'CAM' },
            'MAGSAYSAY MUNICIPAL HEALTH OFFICE':        { municipality: 'MAGSAYSAY',         type: 'MHO',      cluster: 'CAM' },
            // ── NABBrRBEQ-K ──
            'ABORLAN MUNICIPAL HEALTH OFFICE':          { municipality: 'ABORLAN',           type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            'ABORLAN MEDICARE HOSPITAL':                { municipality: 'ABORLAN',           type: 'HOSPITAL', cluster: 'NABBrRBEQ-K' },
            'BALABAC DISTRICT HOSPITAL':                { municipality: 'BALABAC',           type: 'HOSPITAL', cluster: 'NABBrRBEQ-K' },
            'BALABAC MUNICIPAL HEALTH OFFICE':          { municipality: 'BALABAC',           type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            'BATARAZA DISTRICT HOSPITAL':               { municipality: 'BATARAZA',          type: 'HOSPITAL', cluster: 'NABBrRBEQ-K' },
            'BATARAZA MUNICIPAL HEALTH OFFICE':         { municipality: 'BATARAZA',          type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            'BROOKE\'S POINT MUNICIPAL HEALTH OFFICE':  { municipality: 'BROOKE\'S POINT',   type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            'SOUTHERN PALAWAN PROVINCIAL HOSPITAL':     { municipality: 'BROOKE\'S POINT',   type: 'HOSPITAL', cluster: 'NABBrRBEQ-K' },
            'DR. JOSE RIZAL DISTRICT HOSPITAL':         { municipality: 'RIZAL',             type: 'HOSPITAL', cluster: 'NABBrRBEQ-K' },
            'RIZAL MUNICIPAL HEALTH OFFICE':            { municipality: 'RIZAL',             type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            'KALAYAAN MUNICIPAL HEALTH OFFICE':         { municipality: 'KALAYAAN',          type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            'NARRA MUNICIPAL HOSPITAL':                 { municipality: 'NARRA',             type: 'HOSPITAL', cluster: 'NABBrRBEQ-K' },
            'NARRA MUNICIPAL HEALTH OFFICE':            { municipality: 'NARRA',             type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            'QUEZON MEDICARE HOSPITAL':                 { municipality: 'QUEZON',            type: 'HOSPITAL', cluster: 'NABBrRBEQ-K' },
            'QUEZON MUNICIPAL HEALTH OFFICE':           { municipality: 'QUEZON',            type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            'SOFRONIO ESPAÑOLA DISTRICT HOSPITAL':      { municipality: 'SOFRONIO ESPAÑOLA', type: 'HOSPITAL', cluster: 'NABBrRBEQ-K' },
            'SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE':{ municipality: 'SOFRONIO ESPAÑOLA', type: 'MHO',      cluster: 'NABBrRBEQ-K' },
            // ── REDCATS ──
            'ARACELI MUNICIPAL HEALTH OFFICE':          { municipality: 'ARACELI',           type: 'MHO',      cluster: 'REDCATS' },
            'ARACELI-DUMARAN DISTRICT HOSPITAL':        { municipality: 'DUMARAN',           type: 'HOSPITAL', cluster: 'REDCATS' },
            'CAGAYANCILLO MUNICIPAL HEALTH OFFICE':     { municipality: 'CAGAYANCILLO',      type: 'MHO',      cluster: 'REDCATS' },
            'DUMARAN MUNICIPAL HEALTH OFFICE':          { municipality: 'DUMARAN',           type: 'MHO',      cluster: 'REDCATS' },
            'FRANCISCO F. PONCE DE LEON HOSPITAL':      { municipality: 'DUMARAN',           type: 'HOSPITAL', cluster: 'REDCATS' },
            'EL NIDO COMMUNITY HOSPITAL':               { municipality: 'EL NIDO',           type: 'HOSPITAL', cluster: 'REDCATS' },
            'EL NIDO MUNICIPAL HEALTH OFFICE':          { municipality: 'EL NIDO',           type: 'MHO',      cluster: 'REDCATS' },
            'NORTHERN PALAWAN PROVINCIAL HOSPITAL':     { municipality: 'TAYTAY',            type: 'HOSPITAL', cluster: 'REDCATS' },
            'ROXAS MEDICARE HOSPITAL':                  { municipality: 'ROXAS',             type: 'HOSPITAL', cluster: 'REDCATS' },
            'ROXAS MUNICIPAL HEALTH OFFICE':            { municipality: 'ROXAS',             type: 'MHO',      cluster: 'REDCATS' },
            'SAN VICENTE DISTRICT HOSPITAL':            { municipality: 'SAN VICENTE',       type: 'HOSPITAL', cluster: 'REDCATS' },
            'SAN VICENTE MUNICIPAL HEALTH OFFICE':      { municipality: 'SAN VICENTE',       type: 'MHO',      cluster: 'REDCATS' },
            'TAYTAY MUNICIPAL HEALTH OFFICE':           { municipality: 'TAYTAY',            type: 'MHO',      cluster: 'REDCATS' }
        };

        /* ═══ Toggle facility section based on role ═══ */
        function toggleFacility() {
            var role = document.getElementById('role').value;
            var container = document.getElementById('facilityContainer');
            var field = document.getElementById('assigned_facility');

            if (role === 'staff') {
                container.style.display = 'block';
                field.setAttribute('required', 'required');
            } else {
                container.style.display = 'none';
                field.removeAttribute('required');
                // Clear Select2 + auto fields
                if ($.fn.select2 && $('#assigned_facility').data('select2')) {
                    $('#assigned_facility').val('').trigger('change');
                }
                document.getElementById('autoMunicipality').value = '';
                document.getElementById('autoFacilityType').value = '';
                document.getElementById('autoCluster').value = '';
            }
        }

        /* ═══ Confirm delete ═══ */
        function confirmDeleteUser(form) {
            return confirm('Are you sure you want to delete this user?');
        }

        /* ═══ jQuery: Select2 init + auto-populate ═══ */
        $(document).ready(function() {
            // Initialize Select2 on the facility dropdown
            $('#assigned_facility').select2({
                placeholder: 'Search or select a facility...',
                allowClear: true,
                width: '100%'
            });

            // Auto-populate on change
            $('#assigned_facility').on('change', function() {
                var selected = $(this).val();
                var info = facilityDatabase[selected];
                if (info) {
                    $('#autoMunicipality').val(info.municipality);
                    $('#autoFacilityType').val(info.type);
                    $('#autoCluster').val(info.cluster);
                } else {
                    $('#autoMunicipality').val('');
                    $('#autoFacilityType').val('');
                    $('#autoCluster').val('');
                }
            });

            // If a value was already selected (e.g. POST reload), trigger change
            var preselected = '<?php echo addslashes($_POST['assigned_facility'] ?? ''); ?>';
            if (preselected) {
                $('#assigned_facility').val(preselected).trigger('change');
            }

            // Init role toggle on page load
            toggleFacility();
        });
    </script>
</body>
</html>
