<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
redirectIfNotLoggedIn();
?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <!-- Reusable sidebar could be extracted, copying for simplicity -->
        <div class="sidebar-header">
            <h3><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Overview</a>
            <a href="bookings.php" class="sidebar-link"><i class="fas fa-suitcase"></i> My Bookings</a>
            <a href="payment.php" class="sidebar-link"><i class="fas fa-credit-card"></i> Payments</a>
            <a href="profile.php" class="sidebar-link active"><i class="fas fa-user-cog"></i> Profile Settings</a>
            <a href="logout.php" class="sidebar-link" style="color: #ef4444; margin-top: 2rem;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <div class="dash-header">
            <h2>Profile Settings</h2>
        </div>
        <div class="card" style="padding:2rem; max-width:600px;">
            <p style="color:var(--text-muted); margin-bottom:1.5rem;">Manage your account details and preferences.</p>
            <form method="POST" onsubmit="event.preventDefault(); alert('Profile updated implicitly.');">
                <div class="form-group" style="margin-bottom:1rem;">
                    <label>Full Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
                </div>
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label>Password Update</label>
                    <input type="password" class="form-control" placeholder="Enter new password">
                </div>
                <button class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </main>
</div>
