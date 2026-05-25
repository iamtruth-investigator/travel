<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$package_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch package details
$sql = "SELECT tp.*, 
    f.airline, f.flight_number, f.departure_city, f.arrival_city, f.departure_time, f.arrival_time, f.class,
    h.name as hotel_name, h.location as hotel_location,
    t.car_name, t.driver_name
    FROM trip_packages tp
    LEFT JOIN flights f ON tp.flight_id = f.id
    LEFT JOIN hotels h ON tp.hotel_id = h.id
    LEFT JOIN taxis t ON tp.taxi_id = t.id
    WHERE tp.id = ?";

if (!isAdmin()) {
    $sql .= " AND tp.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$package_id, $user_id]);
} else {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$package_id]);
}
$package = $stmt->fetch();

if (!$package) {
    echo "<div class='container section-padding' style='text-align:center;'><h3>Package not found or access denied.</h3><a href='bookings.php' class='btn btn-primary'>Back to Bookings</a></div>";
    require_once 'includes/footer.php';
    exit();
}
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Overview</a>
            <a href="bookings.php" class="sidebar-link active"><i class="fas fa-suitcase"></i> My Bookings</a>
            <a href="book_trip.php" class="sidebar-link"><i class="fas fa-globe-americas"></i> Book a Trip</a>
            <a href="payment.php" class="sidebar-link"><i class="fas fa-credit-card"></i> Payments</a>
            <a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> Profile Settings</a>
            <a href="logout.php" class="sidebar-link" style="color: #ef4444; margin-top: 2rem;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="main-content">
        <div class="dash-header">
            <h2>Trip Package Details</h2>
            <a href="bookings.php" class="btn btn-outline" style="padding: 0.5rem 1rem;"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="grid grid-2" style="grid-template-columns: 1.5fr 1fr; gap: 2rem;">
            <!-- Left Column: Itinerary and Details -->
            <div>
                <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);"><i class="fas fa-robot"></i> AI Generated Plan</h3>
                    <div class="ai-content" style="line-height: 1.8; color: #334155;">
                        <?php echo $package->ai_plan; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Booking Summary -->
            <div>
                <div class="card" style="padding: 1.5rem; margin-bottom: 2rem; border-top: 5px solid var(--primary-color);">
                    <h3 style="margin-bottom: 1.2rem;">Package Summary</h3>
                    <div style="margin-bottom: 1rem;">
                        <p><strong>Destination:</strong> <?php echo htmlspecialchars($package->destination); ?></p>
                        <p><strong>Travel Date:</strong> <?php echo date('d M Y', strtotime($package->travel_date)); ?></p>
                        <p><strong>Return Date:</strong> <?php echo date('d M Y', strtotime($package->return_date)); ?></p>
                        <p><strong>Travelers:</strong> <?php echo $package->travelers; ?></p>
                        <p><strong>Status:</strong> <span class="badge badge-<?php echo $package->status == 'confirmed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($package->status); ?></span></p>
                    </div>
                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0;">
                    
                    <h4 style="margin-bottom: 1rem;"><i class="fas fa-plane"></i> Flight Details</h4>
                    <p><?php echo htmlspecialchars($package->airline . ' ' . $package->flight_number); ?></p>
                    <p style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($package->departure_city . ' → ' . $package->arrival_city); ?></p>
                    <p style="font-size: 0.85rem; color: var(--text-muted);"><?php echo date('h:i A', strtotime($package->departure_time)) . ' - ' . date('h:i A', strtotime($package->arrival_time)); ?> (<?php echo ucfirst($package->class); ?>)</p>
                    
                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0;">
                    
                    <h4 style="margin-bottom: 1rem;"><i class="fas fa-hotel"></i> Hotel Details</h4>
                    <p><?php echo htmlspecialchars($package->hotel_name); ?></p>
                    <p style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($package->hotel_location); ?></p>
                    
                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0;">
                    
                    <h4 style="margin-bottom: 1rem;"><i class="fas fa-taxi"></i> Taxi Details</h4>
                    <p><?php echo htmlspecialchars($package->car_name); ?></p>
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Driver: <?php echo htmlspecialchars($package->driver_name); ?></p>
                    
                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0;">
                    
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h4 style="margin:0;">Total Paid</h4>
                        <h3 style="color:var(--primary-color); margin:0;">₹<?php echo number_format($package->total_price, 2); ?></h3>
                    </div>
                </div>
                
                <button onclick="window.print()" class="btn btn-outline" style="width:100%; margin-bottom: 1rem;">
                    <i class="fas fa-print"></i> Print Details
                </button>
            </div>
        </div>
    </main>
</div>

<style>
.ai-content h3 { color: #1e293b; margin-top: 2rem; margin-bottom: 1rem; font-weight: 600; font-size: 1.4rem; color: #2563eb; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; }
.ai-content ul, .ai-content ol { padding-left: 1.5rem; margin-bottom: 1.5rem; }
.ai-content li { margin-bottom: 0.5rem; }
.ai-content p { margin-bottom: 1.5rem; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
@media (max-width: 992px) {
    .grid-2 { grid-template-columns: 1fr; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
