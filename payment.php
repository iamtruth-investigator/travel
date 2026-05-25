<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

$item_name = "Unknown Item";
$price = 0;

if($type == 'destination' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if($item){
        $item_name = $item->title . ' (' . $item->country . ')';
        $price = $item->price;
    }
} else if ($type == 'hotel' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if($item){
        $item_name = 'Hotel: ' . $item->name . ' (' . $item->location . ')';
        $price = $item->price;
    }
} else if ($type == 'taxi' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM taxis WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if($item){
        $item_name = 'Taxi: ' . $item->car_name . ' with ' . $item->driver_name . ' (' . $item->location . ')';
        $price = $item->price;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Process Payment
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, 'completed')");
    $stmt->execute([$_SESSION['user_id'], $price, 'Credit Card', 'TXN'.rand(100000,999999)]);
    
    // Create Booking
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, type, item_id, total_price, status) VALUES (?, ?, ?, ?, 'confirmed')");
    $stmt->execute([$_SESSION['user_id'], $type, $id, $price]);
    
    echo "<script>alert('Payment Successful!'); window.location.href='dashboard.php';</script>";
    exit();
}
?>

<div class="auth-page">
    <div class="glass auth-box" style="text-align:left;">
        <h2 style="margin-bottom: 2rem;">Checkout</h2>
        <div style="background:var(--light-bg); padding:1rem; border-radius:var(--radius-md); margin-bottom:1.5rem;">
            <p><strong>Item:</strong> <?php echo htmlspecialchars($item_name); ?></p>
            <p style="font-size:1.5rem; font-weight:700; color:var(--primary-color);">Total: ₹<?php echo number_format($price, 2); ?></p>
        </div>
        
        <form method="POST">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Cardholder Name</label>
                <input type="text" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Card Number</label>
                <input type="text" class="form-control" placeholder="**** **** **** ****" required>
            </div>
            <div style="display:flex; gap:1rem; margin-bottom:2rem;">
                <div class="form-group" style="flex:1;">
                    <label>Expiry</label>
                    <input type="text" class="form-control" placeholder="MM/YY" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>CVV</label>
                    <input type="text" class="form-control" placeholder="***" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Pay Securely</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
