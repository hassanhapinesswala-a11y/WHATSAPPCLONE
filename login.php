<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        // update last_seen
        $pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")->execute([$user['id']]);
        echo "<script>window.location='chat.php';</script>"; exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - ChatClone</title>
<style>
body{font-family: Inter, Arial; background: linear-gradient(135deg,#07133b,#0ea5a9); color:#fff; display:flex; align-items:center; justify-content:center; height:100vh; margin:0;}
.card{background:rgba(255,255,255,0.05); padding:28px; border-radius:16px; width:360px; box-shadow:0 10px 30px rgba(2,6,23,0.6); backdrop-filter: blur(6px);}
h2{margin:0 0 14px 0; font-size:22px; text-align:center;}
input{width:100%; padding:12px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.08); margin-bottom:12px; background:transparent; color:#fff;}
button{width:100%; padding:12px; border-radius:10px; border:none; cursor:pointer; font-weight:600; background:linear-gradient(90deg,#06b6d4,#3b82f6);}
.small{font-size:13px; text-align:center; margin-top:10px; color:#e2f7fb;}
.error{background:#ff00002b; color:#fff; padding:8px; border-radius:8px; margin-bottom:10px; text-align:center;}
</style>
</head>
<body>
<div class="card">
  <h2>Login — ChatClone</h2>
  <?php if(!empty($error)): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif;?>
  <form method="post">
    <input name="username" placeholder="Username" required maxlength="30" />
    <input name="password" placeholder="Password" type="password" required />
    <button type="submit">Login</button>
  </form>
  <div class="small">No account? <a href="register.php" style="color:#fff;text-decoration:underline;">Create one</a></div>
</div>
</body>
</html
