<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: ../admin_dashboard.php');
    exit;
}
if ($_POST) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if (($user == 'admin' && $pass == 'admin123') || ($user == 'MusasiziFaty' && $pass == 'Musasizi123')) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user;
        header('Location: ../admin_dashboard.php');
        exit;
    }
    $error = 'Invalid login';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial; background: #0f172a; margin: 0; padding: 20px; display: flex; align-items: center; min-height: 100vh; }
        .box { background: rgba(255,255,255,0.1); padding: 40px; border-radius: 10px; max-width: 400px; margin: auto; text-align: center; }
        .logo { width: 60px; height: 60px; border-radius: 50%; margin-bottom: 20px; }
        h1 { color: white; margin: 0 0 10px 0; }
        p { color: #ccc; margin: 0 0 30px 0; }
        input { width: 100%; padding: 15px; margin: 10px 0; border: 1px solid #333; background: rgba(255,255,255,0.1); color: white; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 15px; background: #fbbf24; border: none; border-radius: 5px; color: #0f172a; font-weight: bold; cursor: pointer; }
        button:hover { background: #f59e0b; }
        .error { color: #ff6b6b; margin: 10px 0; }
        a { color: #ccc; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box">
        <img src="../public/logo-icon.jpeg" alt="Logo" class="logo">
        <h1>Admin Portal</h1>
        <p>Salem Dominion Ministries</p>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
        
        <p style="margin-top: 30px;">
            <a href="../index.php">Back to Website</a>
        </p>
    </div>
</body>
</html>
