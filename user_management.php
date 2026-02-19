<?php
/**
 * PHO CONSO HFDP - User Management (Settings)
 * Add new Staff accounts
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

require_once __DIR__ . '/config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || strlen($username) < 3) {
        $message = 'Username must be at least 3 characters.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } elseif ($password !== $password_confirm) {
        $message = 'Passwords do not match.';
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
                $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
                $stmt->bind_param('ss', $username, $hash);
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
    $res = $conn->query("SELECT id, username, created_at FROM users ORDER BY username");
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
                    <span class="sidebar-admin-role">Admin</span>
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
                <a href="user_management.php" class="sidebar-link active">
                    <span class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></span>
                    <span class="sidebar-label">Add New User/Staff</span>
                </a>
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
                                <span class="admin-trigger-label">ADMIN</span>
                            </button>
                            <div class="admin-dropdown" id="admin-dropdown" role="menu" aria-hidden="true">
                                <div class="admin-dropdown-header">
                                    <span class="admin-dropdown-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg></span>
                                    <div class="admin-dropdown-info">
                                        <span class="admin-dropdown-role">ADMIN</span>
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
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="3" class="no-data">No users yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo (int)$u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
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
</body>
</html>
