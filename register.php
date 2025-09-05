<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (!$username || !$password) {
        $error = "Both fields required.";
    } else {
        // check existence
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username already taken.";
        } else {
            $passhash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $passhash]);
            // auto-login
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            echo "<script>window.location='chat.php';</script>"; exit;
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Register - ChatClone</title>
<style>
/* internal CSS - stylish */
body{font-family: Inter, Arial; background: linear-gradient(135deg,#0f172a,#0ea5a9); color:#fff; display:flex; align-items:center; justify-content:center; height:100vh; margin:0;}
.card{background:rgba(255,255,255,0.06); padding:28px; border-radius:16px; width:360px; box-shadow:0 10px 30px rgba(2,6,23,0.6); backdrop-filter: blur(6px);}
h2{margin:0 0 14px 0; font-size:22px; text-align:center;}
input{width:100%; padding:12px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.08); margin-bottom:12px; background:transparent; color:#fff;}
button{width:100%; padding:12px; border-radius:10px; border:none; cursor:pointer; font-weight:600; background:linear-gradient(90deg,#06b6d4,#3b82f6);}
.small{font-size:13px; text-align:center; margin-top:10px; color:#e2f7fb;}
.error{background:#ff00002b; color:#fff; padding:8px; border-radius:8px; margin-bottom:10px; text-align:center;}
</style>
</head>
<body>
<div class="card">
  <h2>Sign up — ChatClone</h2>
  <?php if(!empty($error)): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif;?>
  <form method="post">
    <input name="username" placeholder="Username" required maxlength="30" />
    <input name="password" placeholder="Password" type="password" required />
    <button type="submit">Create account</button>
  </form>
  <div class="small">Already have an account? <a href="login.php" style="color:#fff;text-decoration:underline;">Login</a></div>
</div>
</body>
</html>
 
