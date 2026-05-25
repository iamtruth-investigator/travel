<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<div style="background:#f59e0b; padding: 6rem 0 3rem; text-align:center; color:white;">
    <h1 style="font-size:3rem; margin-bottom:1rem;">City Rides</h1>
    <p style="opacity:0.9;">Reliable and safe taxi services on demand</p>
</div>

<div class="container section-padding">
    <div class="grid grid-3">
        <?php
        $stmt = $pdo->query("SELECT * FROM taxis ORDER BY location ASC");
        while($taxi = $stmt->fetch()):
        ?>
        <div class="card">
            <div class="card-content" style="text-align:center;">
                <div style="font-size:3rem; color:var(--text-muted); margin-bottom:1rem;"><i class="fas fa-taxi"></i></div>
                <h3 class="card-title"><?php echo htmlspecialchars($taxi->car_name); ?></h3>
                <p style="color:var(--text-muted); margin-bottom:0.5rem;"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($taxi->driver_name); ?></p>
                <div class="card-location" style="justify-content:center;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($taxi->location); ?></div>
                <div class="card-footer">
                    <span class="card-price">₹<?php echo htmlspecialchars($taxi->price); ?></span>
                    <a href="payment.php?type=taxi&id=<?php echo $taxi->id; ?>" class="btn btn-outline">Hire</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
