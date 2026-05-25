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
            <a href="bookings.php" class="sidebar-link active"><i class="fas fa-suitcase"></i> My Bookings</a>
            <a href="payment.php" class="sidebar-link"><i class="fas fa-credit-card"></i> Payments</a>
            <a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> Profile Settings</a>
            <a href="logout.php" class="sidebar-link" style="color: #ef4444; margin-top: 2rem;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <div class="dash-header">
            <h2>My Bookings</h2>
        </div>
        <div class="card table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY id DESC");
                    $stmt->execute([$_SESSION['user_id']]);
                    while($bk = $stmt->fetch()):
                    ?>
                    <tr>
                        <td>#<?php echo $bk->id; ?></td>
                        <td><?php echo ucfirst($bk->type); ?></td>
                        <td>₹<?php echo $bk->total_price; ?></td>
                        <td><span class="badge badge-<?php echo $bk->status == 'confirmed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($bk->status); ?></span></td>
                        <td>
                            <?php if($bk->type == 'trip_package'): ?>
                                <a href="view_trip.php?id=<?php echo $bk->item_id; ?>" class="btn btn-outline" style="padding:0.25rem 0.5rem; font-size:0.8rem;">View Package</a>
                            <?php else: ?>
                                <button class="btn btn-outline" style="padding:0.25rem 0.5rem; font-size:0.8rem;">View</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
