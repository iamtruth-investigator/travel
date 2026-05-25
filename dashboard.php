<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Get user stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_bookings = $stmt->fetch()->total;

$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND status='completed'");
$stmt->execute([$user_id]);
$total_spent = $stmt->fetch()->total ?? 0;

?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link active"><i class="fas fa-home"></i> Overview</a>
            <a href="bookings.php" class="sidebar-link"><i class="fas fa-suitcase"></i> My Bookings</a>
            <a href="book_trip.php" class="sidebar-link"><i class="fas fa-globe-americas"></i> Book a Trip</a>
            <a href="payment.php" class="sidebar-link"><i class="fas fa-credit-card"></i> Payments</a>
            <a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> Profile Settings</a>
            <a href="logout.php" class="sidebar-link" style="color: #ef4444; margin-top: 2rem;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="main-content">
        <div class="dash-header">
            <h2>Dashboard Overview</h2>
            <div><?php echo date('l, F j, Y'); ?></div>
        </div>
        
        <div class="grid grid-3">
            <div class="card" style="padding: 1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="color:var(--text-muted); font-weight:600;">Active Trips</p>
                        <h3 style="font-size:2rem; margin-top:0.5rem;"><?php echo $total_bookings; ?></h3>
                    </div>
                    <div style="font-size:2.5rem; color:var(--primary-color); opacity:0.2;">
                        <i class="fas fa-plane"></i>
                    </div>
                </div>
            </div>
            
            <div class="card" style="padding: 1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="color:var(--text-muted); font-weight:600;">Total Spent</p>
                        <h3 style="font-size:2rem; margin-top:0.5rem;">₹<?php echo number_format($total_spent, 2); ?></h3>
                    </div>
                    <div style="font-size:2.5rem; color:var(--secondary-color); opacity:0.2;">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
            
            <div class="card" style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); color:white; padding: 1.5rem;">
                <h4><i class="fas fa-suitcase-rolling"></i> Book a Complete Trip</h4>
                <p style="opacity:0.9; margin-top:0.5rem; font-size:0.9rem;">Flight + Hotel + Taxi in one package. AI plans it all!</p>
                <a href="book_trip.php" class="btn" style="background:white; color:#7c3aed; padding:0.5rem 1rem; margin-top:1rem; font-size:0.875rem; display:inline-block; text-decoration:none;"><i class="fas fa-rocket"></i> Book Now</a>
            </div>
        </div>

        <h3 style="margin-top: 3rem; margin-bottom: 1rem;">Recent Activity</h3>
        <div class="card table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY id DESC LIMIT 5");
                    $stmt->execute([$user_id]);
                    if($stmt->rowCount() > 0):
                        while($bk = $stmt->fetch()):
                    ?>
                    <tr>
                        <td>#GLB-<?php echo str_pad($bk->id, 4, '0', STR_PAD_LEFT); ?></td>
                        <td style="text-transform: capitalize;"><?php echo htmlspecialchars($bk->type); ?></td>
                        <td><?php echo date('d M Y', strtotime($bk->created_at)); ?></td>
                        <td><span class="badge badge-<?php echo $bk->status == 'confirmed' ? 'success' : ($bk->status == 'pending' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($bk->status); ?></span></td>
                    </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">No recent bookings found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <h3 style="margin-top: 3rem; margin-bottom: 1rem;"><i class="fas fa-robot" style="color:var(--primary-color);"></i> AI Recommended for You</h3>
        <div class="grid grid-3">
            <?php
            // Simple AI logic: Fetch random destinations based on implicit collaborative filtering (e.g. random for now)
            $stmt = $pdo->query("SELECT * FROM destinations ORDER BY RAND() LIMIT 3");
            while($dest = $stmt->fetch()):
            ?>
            <div class="card">
                <div class="card-img" style="height: 150px;">
                    <img src="<?php echo htmlspecialchars($dest->image); ?>" alt="<?php echo htmlspecialchars($dest->title); ?>" style="width:100%; height:100%; object-fit:cover;">
                </div>
                <div class="card-content" style="padding: 1rem;">
                    <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($dest->title); ?></h4>
                    <div style="color:var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($dest->country); ?></div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-weight:bold; color:var(--primary-color);">₹<?php echo htmlspecialchars($dest->price); ?></span>
                        <a href="book_trip.php?dest=<?php echo urlencode($dest->title); ?>" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Book</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </main>
</div>
