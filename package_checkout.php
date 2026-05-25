<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: book_trip.php');
    exit();
}

$destination  = trim($_POST['destination'] ?? '');
$travel_date  = $_POST['travel_date'] ?? '';
$return_date   = $_POST['return_date'] ?? '';
$travelers    = intval($_POST['travelers'] ?? 1);
$budget       = trim($_POST['budget'] ?? '');
$ai_plan      = $_POST['ai_plan'] ?? '';

$flight_id    = intval($_POST['flight_id'] ?? 0);
$flight_price = floatval($_POST['flight_price'] ?? 0);
$flight_name  = trim($_POST['flight_name'] ?? '');

$hotel_id     = intval($_POST['hotel_id'] ?? 0);
$hotel_price  = floatval($_POST['hotel_price'] ?? 0);
$hotel_name   = trim($_POST['hotel_name'] ?? '');

$taxi_id      = intval($_POST['taxi_id'] ?? 0);
$taxi_price   = floatval($_POST['taxi_price'] ?? 0);
$taxi_name    = trim($_POST['taxi_name'] ?? '');

// Fetch full details from DB for verification
$flight_desc = $flight_name;
$hotel_desc  = $hotel_name;
$taxi_desc   = $taxi_name;

if ($flight_id) {
    $st = $pdo->prepare("SELECT * FROM flights WHERE id=?"); $st->execute([$flight_id]); $fl = $st->fetch();
    if ($fl) { $flight_price = $fl->price; $flight_desc = $fl->airline.' '.$fl->flight_number.' ('.$fl->departure_city.' → '.$fl->arrival_city.', '.ucfirst($fl->class).')'; }
}
if ($hotel_id) {
    $st = $pdo->prepare("SELECT * FROM hotels WHERE id=?"); $st->execute([$hotel_id]); $ht = $st->fetch();
    if ($ht) { $hotel_price = $ht->price; $hotel_desc = $ht->name.' ('.$ht->location.')'; }
}
if ($taxi_id) {
    $st = $pdo->prepare("SELECT * FROM taxis WHERE id=?"); $st->execute([$taxi_id]); $tx = $st->fetch();
    if ($tx) { $taxi_price = $tx->price; $taxi_desc = $tx->car_name.' – '.$tx->driver_name.' ('.$tx->location.')'; }
}

$total_price = $flight_price + $hotel_price + $taxi_price;

// Process payment
$payment_success = false;
if (isset($_POST['confirm_payment'])) {
    try {
        $pdo->beginTransaction();

        // 1. Create trip package
        $st = $pdo->prepare("INSERT INTO trip_packages (user_id,destination,travel_date,return_date,travelers,budget,ai_plan,flight_id,hotel_id,taxi_id,flight_price,hotel_price,taxi_price,total_price,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,'confirmed')");
        $st->execute([$_SESSION['user_id'],$destination,$travel_date,$return_date,$travelers,$budget,$ai_plan,$flight_id,$hotel_id,$taxi_id,$flight_price,$hotel_price,$taxi_price,$total_price]);
        $pkg_id = $pdo->lastInsertId();

        // 2. Create booking record
        $st = $pdo->prepare("INSERT INTO bookings (user_id,type,item_id,total_price,status) VALUES (?,'trip_package',?,?,'confirmed')");
        $st->execute([$_SESSION['user_id'],$pkg_id,$total_price]);

        // 3. Create payment record
        $txn_id = 'TXN'.date('Ymd').rand(100000,999999);
        $st = $pdo->prepare("INSERT INTO payments (user_id,amount,payment_method,transaction_id,status) VALUES (?,?,?,?,'completed')");
        $st->execute([$_SESSION['user_id'],$total_price,'Credit Card',$txn_id]);

        $pdo->commit();
        $payment_success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $payment_error = 'Payment failed: '.$e->getMessage();
    }
}
?>

<?php if ($payment_success): ?>
<!-- ===== SUCCESS PAGE ===== -->
<div class="auth-page" style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);">
    <div class="glass auth-box" style="max-width:560px;text-align:center;">
        <div style="font-size:4rem;color:#10b981;margin-bottom:1rem;"><i class="fas fa-check-circle"></i></div>
        <h2 style="color:#166534;margin-bottom:0.5rem;">Payment Successful!</h2>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;">Your complete trip package to <strong><?php echo htmlspecialchars($destination); ?></strong> has been booked.</p>
        <div style="background:#f0fdf4;padding:1rem;border-radius:8px;margin-bottom:1.5rem;text-align:left;">
            <p><strong>Transaction ID:</strong> <?php echo $txn_id; ?></p>
            <p><strong>Total Paid:</strong> ₹<?php echo number_format($total_price,2); ?></p>
            <p><strong>Status:</strong> <span class="badge badge-success">Confirmed</span></p>
        </div>
        <a href="dashboard.php" class="btn btn-primary" style="width:100%;"><i class="fas fa-home"></i> Go to Dashboard</a>
    </div>
</div>
<?php else: ?>
<!-- ===== CHECKOUT PAGE ===== -->
<div style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);padding:5rem 0 2.5rem;text-align:center;color:#fff;">
    <h1 style="font-size:2.5rem;margin-bottom:0.5rem;"><i class="fas fa-shopping-cart"></i> Package Checkout</h1>
    <p style="opacity:0.8;">Review your complete trip package and pay securely</p>
</div>

<div class="container" style="max-width:900px;margin:0 auto;padding:2.5rem 1.5rem 4rem;">

    <!-- Trip Summary -->
    <div class="card" style="padding:1.5rem;margin-bottom:2rem;border-left:4px solid var(--primary-color);">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
            <div>
                <h3 style="margin-bottom:0.25rem;"><i class="fas fa-map-marker-alt" style="color:var(--primary-color);"></i> <?php echo htmlspecialchars($destination); ?></h3>
                <span style="color:var(--text-muted);font-size:0.9rem;"><?php echo date('d M Y',strtotime($travel_date)); ?> – <?php echo date('d M Y',strtotime($return_date)); ?> · <?php echo $travelers; ?> traveler(s) · <?php echo ucfirst($budget); ?> budget</span>
            </div>
            <span class="badge badge-warning" style="font-size:0.85rem;padding:0.4rem 1rem;">Pending Payment</span>
        </div>
    </div>

    <!-- AI Plan (Collapsible) -->
    <?php if ($ai_plan): ?>
    <div class="ai-plan-box" style="margin-bottom:2rem;">
        <div class="ai-plan-toggle" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none';">
            <span><i class="fas fa-robot" style="color:var(--primary-color);"></i> AI Trip Plan</span>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div class="ai-plan-content" style="display:none;"><?php echo $ai_plan; ?></div>
    </div>
    <?php endif; ?>

    <!-- Itemized Cost Table -->
    <h3 style="margin-bottom:1rem;"><i class="fas fa-receipt" style="color:var(--primary-color);"></i> Cost Breakdown</h3>
    <div style="border-radius:12px;overflow:hidden;margin-bottom:2rem;">
        <table class="checkout-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th style="text-align:right;">Price (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td><span class="item-icon">✈️</span> <strong>Flight</strong></td>
                    <td><?php echo htmlspecialchars($flight_desc); ?></td>
                    <td style="text-align:right;font-weight:600;">₹<?php echo number_format($flight_price,2); ?></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td><span class="item-icon">🏨</span> <strong>Hotel</strong></td>
                    <td><?php echo htmlspecialchars($hotel_desc); ?></td>
                    <td style="text-align:right;font-weight:600;">₹<?php echo number_format($hotel_price,2); ?></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td><span class="item-icon">🚕</span> <strong>Taxi</strong></td>
                    <td><?php echo htmlspecialchars($taxi_desc); ?></td>
                    <td style="text-align:right;font-weight:600;">₹<?php echo number_format($taxi_price,2); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">PACKAGE TOTAL</td>
                    <td style="text-align:right;font-size:1.3rem;">₹<?php echo number_format($total_price,2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if(isset($payment_error)): ?>
        <div style="background:#fee2e2;color:#991b1b;padding:1rem;border-radius:8px;margin-bottom:1.5rem;"><strong>Error:</strong> <?php echo htmlspecialchars($payment_error); ?></div>
    <?php endif; ?>

    <!-- Payment Form -->
    <div class="card" style="padding:2rem;border-radius:12px;">
        <h3 style="margin-bottom:1.5rem;"><i class="fas fa-lock" style="color:var(--secondary-color);"></i> Secure Payment</h3>
        <form method="POST">
            <!-- Pass through all data -->
            <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
            <input type="hidden" name="travel_date" value="<?php echo htmlspecialchars($travel_date); ?>">
            <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>">
            <input type="hidden" name="travelers" value="<?php echo $travelers; ?>">
            <input type="hidden" name="budget" value="<?php echo htmlspecialchars($budget); ?>">
            <input type="hidden" name="ai_plan" value="<?php echo htmlspecialchars($ai_plan); ?>">
            <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
            <input type="hidden" name="flight_price" value="<?php echo $flight_price; ?>">
            <input type="hidden" name="flight_name" value="<?php echo htmlspecialchars($flight_name); ?>">
            <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
            <input type="hidden" name="hotel_price" value="<?php echo $hotel_price; ?>">
            <input type="hidden" name="hotel_name" value="<?php echo htmlspecialchars($hotel_name); ?>">
            <input type="hidden" name="taxi_id" value="<?php echo $taxi_id; ?>">
            <input type="hidden" name="taxi_price" value="<?php echo $taxi_price; ?>">
            <input type="hidden" name="taxi_name" value="<?php echo htmlspecialchars($taxi_name); ?>">

            <div class="form-group" style="margin-bottom:1rem;">
                <label>Cardholder Name</label>
                <input type="text" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label>Card Number</label>
                <input type="text" class="form-control" placeholder="**** **** **** ****" required>
            </div>
            <div style="display:flex;gap:1rem;margin-bottom:2rem;">
                <div class="form-group" style="flex:1;"><label>Expiry</label><input type="text" class="form-control" placeholder="MM/YY" required></div>
                <div class="form-group" style="flex:1;"><label>CVV</label><input type="text" class="form-control" placeholder="***" required></div>
            </div>
            <button type="submit" name="confirm_payment" class="btn btn-primary" style="width:100%;padding:1rem;font-size:1.1rem;">
                <i class="fas fa-shield-alt"></i> Pay ₹<?php echo number_format($total_price,2); ?> Securely
            </button>
            <p style="text-align:center;color:var(--text-muted);font-size:0.8rem;margin-top:1rem;">
                <i class="fas fa-lock"></i> Your payment is encrypted and secure
            </p>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
