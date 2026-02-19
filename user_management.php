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
            <div class="sidebar-section">
                <span class="sidebar-section-title">Settings</span>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="user_management.php" class="sidebar-link active">
                    <span class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></span>
                    <span class="sidebar-label">Add New User/Staff</span>
                </a>
                <?php endif; ?>
            </div>
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
                    <div class="admin-logo-placeholder" aria-hidden="true">
                        <img src="assets/images/pho-logo.png" alt="" class="admin-logo-img" onerror="this.style.display='none';this.parentElement.classList.add('no-img')">
                        <span class="admin-logo-fallback">PHO</span>
                    </div>
                    <div class="admin-header-title">
                        <h1>User Management</h1>
                        <span class="admin-header-subtitle">Settings · Provincial Health Office</span>
                    </div>
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
                    <div class="form-group" id="facilityContainer" style="display: none;">
                        <label for="assigned_facility">Assign Facility <span class="required">*</span></label>
                        <select id="assigned_facility" name="assigned_facility" class="form-control">
                            <option value="" disabled selected>Select Facility</option>
                            <option value="ABORLAN MEDICARE HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'ABORLAN MEDICARE HOSPITAL' ? 'selected' : ''; ?>>ABORLAN MEDICARE HOSPITAL</option>
                            <option value="ABORLAN MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'ABORLAN MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>ABORLAN MUNICIPAL HEALTH OFFICE</option>
                            <option value="ARACELI-DUMARAN DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'ARACELI-DUMARAN DISTRICT HOSPITAL' ? 'selected' : ''; ?>>ARACELI-DUMARAN DISTRICT HOSPITAL</option>
                            <option value="BALABAC DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'BALABAC DISTRICT HOSPITAL' ? 'selected' : ''; ?>>BALABAC DISTRICT HOSPITAL</option>
                            <option value="BALABAC MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'BALABAC MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>BALABAC MUNICIPAL HEALTH OFFICE</option>
                            <option value="BATARAZA DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'BATARAZA DISTRICT HOSPITAL' ? 'selected' : ''; ?>>BATARAZA DISTRICT HOSPITAL</option>
                            <option value="BATARAZA MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'BATARAZA MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>BATARAZA MUNICIPAL HEALTH OFFICE</option>
                            <option value="BATARAZA MUNICIPAL HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'BATARAZA MUNICIPAL HOSPITAL' ? 'selected' : ''; ?>>BATARAZA MUNICIPAL HOSPITAL</option>
                            <option value="BROOKES POINT MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'BROOKES POINT MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>BROOKES POINT MUNICIPAL HEALTH OFFICE</option>
                            <option value="BROOKES POINT MUNICIPAL HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'BROOKES POINT MUNICIPAL HOSPITAL' ? 'selected' : ''; ?>>BROOKES POINT MUNICIPAL HOSPITAL</option>
                            <option value="BUSUANGA HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'BUSUANGA HEALTH OFFICE' ? 'selected' : ''; ?>>BUSUANGA HEALTH OFFICE</option>
                            <option value="CORON DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'CORON DISTRICT HOSPITAL' ? 'selected' : ''; ?>>CORON DISTRICT HOSPITAL</option>
                            <option value="CORON MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'CORON MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>CORON MUNICIPAL HEALTH OFFICE</option>
                            <option value="CULION MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'CULION MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>CULION MUNICIPAL HEALTH OFFICE</option>
                            <option value="CUYO DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'CUYO DISTRICT HOSPITAL' ? 'selected' : ''; ?>>CUYO DISTRICT HOSPITAL</option>
                            <option value="DR JOSE RIZAL DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'DR JOSE RIZAL DISTRICT HOSPITAL' ? 'selected' : ''; ?>>DR JOSE RIZAL DISTRICT HOSPITAL</option>
                            <option value="EL NIDO COMMUNITY HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'EL NIDO COMMUNITY HOSPITAL' ? 'selected' : ''; ?>>EL NIDO COMMUNITY HOSPITAL</option>
                            <option value="KALAYAAN MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'KALAYAAN MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>KALAYAAN MUNICIPAL HEALTH OFFICE</option>
                            <option value="LINAPACAN MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'LINAPACAN MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>LINAPACAN MUNICIPAL HEALTH OFFICE</option>
                            <option value="MUNICIPALITY OF AGUTAYA" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF AGUTAYA' ? 'selected' : ''; ?>>MUNICIPALITY OF AGUTAYA</option>
                            <option value="MUNICIPALITY OF ARACELI" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF ARACELI' ? 'selected' : ''; ?>>MUNICIPALITY OF ARACELI</option>
                            <option value="MUNICIPALITY OF CAGAYANCILLO" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF CAGAYANCILLO' ? 'selected' : ''; ?>>MUNICIPALITY OF CAGAYANCILLO</option>
                            <option value="MUNICIPALITY OF DUMARAN" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF DUMARAN' ? 'selected' : ''; ?>>MUNICIPALITY OF DUMARAN</option>
                            <option value="MUNICIPALITY OF EL NIDO" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF EL NIDO' ? 'selected' : ''; ?>>MUNICIPALITY OF EL NIDO</option>
                            <option value="MUNICIPALITY OF MAGSAYSAY" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF MAGSAYSAY' ? 'selected' : ''; ?>>MUNICIPALITY OF MAGSAYSAY</option>
                            <option value="MUNICIPALITY OF ROXAS" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF ROXAS' ? 'selected' : ''; ?>>MUNICIPALITY OF ROXAS</option>
                            <option value="MUNICIPALITY OF SAN VICENTE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF SAN VICENTE' ? 'selected' : ''; ?>>MUNICIPALITY OF SAN VICENTE</option>
                            <option value="MUNICIPALITY OF TAYTAY" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'MUNICIPALITY OF TAYTAY' ? 'selected' : ''; ?>>MUNICIPALITY OF TAYTAY</option>
                            <option value="NARRA MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'NARRA MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>NARRA MUNICIPAL HEALTH OFFICE</option>
                            <option value="NARRA MUNICIPAL HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'NARRA MUNICIPAL HOSPITAL' ? 'selected' : ''; ?>>NARRA MUNICIPAL HOSPITAL</option>
                            <option value="NORTHERN PALAWAN PROVINCIAL HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'NORTHERN PALAWAN PROVINCIAL HOSPITAL' ? 'selected' : ''; ?>>NORTHERN PALAWAN PROVINCIAL HOSPITAL</option>
                            <option value="QUEZON MEDICARE HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'QUEZON MEDICARE HOSPITAL' ? 'selected' : ''; ?>>QUEZON MEDICARE HOSPITAL</option>
                            <option value="QUEZON MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'QUEZON MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>QUEZON MUNICIPAL HEALTH OFFICE</option>
                            <option value="RIZAL DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'RIZAL DISTRICT HOSPITAL' ? 'selected' : ''; ?>>RIZAL DISTRICT HOSPITAL</option>
                            <option value="RIZAL MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'RIZAL MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>RIZAL MUNICIPAL HEALTH OFFICE</option>
                            <option value="ROXAS MEDICARE HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'ROXAS MEDICARE HOSPITAL' ? 'selected' : ''; ?>>ROXAS MEDICARE HOSPITAL</option>
                            <option value="SAN VICENTE DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'SAN VICENTE DISTRICT HOSPITAL' ? 'selected' : ''; ?>>SAN VICENTE DISTRICT HOSPITAL</option>
                            <option value="SOFRONIO ESPAÑOLA DISTRICT HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'SOFRONIO ESPAÑOLA DISTRICT HOSPITAL' ? 'selected' : ''; ?>>SOFRONIO ESPAÑOLA DISTRICT HOSPITAL</option>
                            <option value="SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE' ? 'selected' : ''; ?>>SOFRONIO ESPAÑOLA MUNICIPAL HEALTH OFFICE</option>
                            <option value="SOUTHERN PALAWAN PROVINCIAL HOSPITAL" <?php echo isset($_POST['assigned_facility']) && $_POST['assigned_facility'] === 'SOUTHERN PALAWAN PROVINCIAL HOSPITAL' ? 'selected' : ''; ?>>SOUTHERN PALAWAN PROVINCIAL HOSPITAL</option>
                        </select>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5" class="no-data">No users yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo (int)$u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($u['role'])); ?></td>
                                    <td><?php echo htmlspecialchars($u['assigned_facility'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($u['created_at'] ?? '-'); ?></td>
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
    <script>
        function toggleFacility() {
            var role = document.getElementById("role").value;
            var container = document.getElementById("facilityContainer");
            var field = document.getElementById("assigned_facility");

            if (role === "staff") {
                container.style.display = "block"; // Show
                field.setAttribute("required", "required"); // Required pag staff
            } else {
                container.style.display = "none"; // Hide
                field.removeAttribute("required"); // Not required pag admin
                field.value = ""; // Clear value
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', toggleFacility);
    </script>
</body>
</html>
