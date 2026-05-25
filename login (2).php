<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if(isLoggedIn()){
    $role = $_SESSION['user_role'] ?? 'user';
    if($role === 'admin' && strpos($_SERVER['REQUEST_URI'], 'admin/login.php') !== false) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if($email && $password){
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user->password)){
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['user_role'] = $user->role;
            
            if($user->role === 'admin'){
                header("Location: admin/dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GLOBEXA</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="glass auth-box">
        <a href="index.php" class="logo">GLOB<span>EXA</span></a>
        <h2 style="margin-bottom: 2rem; color:var(--text-main);">Welcome Back</h2>
        
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; text-align:left;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 2rem;">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
        
        <p style="margin-top: 2rem; color: var(--text-muted);">Don't have an account? <a href="register.php" style="color: var(--primary-color); font-weight:600;">Sign Up</a></p>
    </div>
</body>
</html>
