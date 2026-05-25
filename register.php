<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if(isLoggedIn()){
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if($name && $email && $password){
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try{
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $hashed_password]);
            $success = "Registration successful! You can now <a href='login.php' style='color:var(--primary-color)'>Login</a>";
        } catch(PDOException $e) {
            if($e->getCode() == 23000){ // Duplicate entry
                $error = "Email already exists!";
            } else {
                $error = "Something went wrong!";
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GLOBEXA</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="glass auth-box">
        <a href="index.php" class="logo">GLOB<span>EXA</span></a>
        <h2 style="margin-bottom: 2rem; color:var(--text-main);">Create an Account</h2>
        
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; text-align:left;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if($success): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; text-align:left;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 2rem;">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        
        <p style="margin-top: 2rem; color: var(--text-muted);">Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight:600;">Sign In</a></p>
    </div>
</body>
</html>
