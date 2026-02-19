<?php
/**
 * PHO CONSO HFDP Login
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Ensure users table exists (first-time: run schema or create table here)
            $conn->query("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin', 'staff') DEFAULT 'staff',
                assigned_facility VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Ensure RBAC columns exist (for existing installations)
            $columns = $conn->query("SHOW COLUMNS FROM users");
            $column_names = [];
            if ($columns) {
                while ($col = $columns->fetch_assoc()) {
                    $column_names[] = $col['Field'];
                }
                $columns->free();
            }
            
            if (!in_array('role', $column_names)) {
                $conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin', 'staff') DEFAULT 'staff'");
                $conn->query("ALTER TABLE users ADD INDEX idx_role (role)");
            }
            
            if (!in_array('assigned_facility', $column_names)) {
                $conn->query("ALTER TABLE users ADD COLUMN assigned_facility VARCHAR(255)");
            }

            // Ensure admin user has proper role
            $conn->query("UPDATE users SET role = 'admin' WHERE username = 'phoadmin'");

            // Ensure admin user exists (first-time setup)
            $check = $conn->query("SELECT id FROM users WHERE username = 'phoadmin' LIMIT 1");
            if ($check && $check->num_rows === 0) {
                $hash = password_hash('phoadmin', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $adminUser = 'phoadmin';
                $adminHash = $hash;
                $adminRole = 'admin';
                $stmt->bind_param('sss', $adminUser, $adminHash, $adminRole);
                $stmt->execute();
                $stmt->close();
            }
            if ($check) {
                $check->free();
            }

            // Verify credentials (use bind_result for compatibility without mysqlnd)
            $stmt = $conn->prepare("SELECT id, username, password_hash, role, assigned_facility FROM users WHERE username = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->bind_result($uid, $uname, $upass, $urole, $ufacility);
            $user = null;
            if ($stmt->fetch()) {
                $user = ['id' => $uid, 'username' => $uname, 'password_hash' => $upass, 'role' => $urole, 'assigned_facility' => $ufacility];
            }
            $stmt->close();
            $db->close();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'staff';
                $_SESSION['assigned_facility'] = $user['assigned_facility'] ?? null;
                header('Location: index.php');
                exit;
            }

            $error = 'Invalid username or password.';
        } catch (Throwable $e) {
            $error = 'Login error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHO CONSO HFDP - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .login-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-hover); padding: 40px; width: 100%; max-width: 400px; }
        .login-card h1 { color: var(--green-dark); margin-bottom: 8px; font-size: 22px; }
        .login-card .subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 28px; }
        .login-card .form-group { margin-bottom: 20px; }
        .login-card label { display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-dark); }
        .login-card input[type="text"], .login-card input[type="password"] { width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 16px; }
        .login-card input:focus { outline: none; border-color: var(--green-mid); box-shadow: 0 0 0 2px var(--pho-focus-ring); }
        .login-card .btn { width: 100%; padding: 14px; margin-top: 8px; font-size: 16px; cursor: pointer; border: none; border-radius: var(--radius-sm); font-weight: 600; }
        .login-card .btn-primary { background: var(--green-mid); color: var(--white); }
        .login-card .btn-primary:hover { opacity: 0.95; }
        .login-card .message.error { background: var(--pho-danger-bg); color: var(--pho-danger); padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
        .login-logo { text-align: center; margin-bottom: 24px; }
        .login-logo img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; }
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
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo">
                <img src="assets/images/pho-logo.png" alt="PHO" onerror="this.style.display='none'">
            </div>
            <h1>PHO CONSO HFDP</h1>
            <p class="subtitle">Provincial Health Office · Province of Palawan</p>
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
        </div>
    </div>
    <script src="assets/js/preloader.js"></script>
</body>
</html>
