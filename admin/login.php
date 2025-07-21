<?php
require_once '../config/database.php';

// Jika sudah login, alihkan ke dashboard
if (isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Cek apakah ada pesan sukses logout
if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $success = 'Anda telah berhasil logout. Silakan login kembali.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    // Login berhasil
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $username;
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Username atau password salah!";
                }
            } else {
                $error = "Username atau password salah!";
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - Tamaniku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login Admin Tamaniku</h2>
        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if ($error):?>
            <p class="error"><?php echo htmlspecialchars($error);?></p>
        <?php endif;?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Masukkan username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="Masukkan password">
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>