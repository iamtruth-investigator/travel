<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<div style="background:var(--secondary-color); padding: 6rem 0 3rem; text-align:center; color:white;">
    <h1 style="font-size:3rem; margin-bottom:1rem;">Premium Hotels</h1>
    <p style="opacity:0.9;">Book the finest stays with smart recommendations</p>
</div>

<div class="container section-padding">
    <div class="grid grid-3">
        <?php
        $stmt = $pdo->query("SELECT * FROM hotels ORDER BY rating DESC");
        while($hotel = $stmt->fetch()):
        ?>
        <div class="card">
            <div class="card-img">
                <img src="<?php echo htmlspecialchars($hotel->image); ?>" alt="<?php echo htmlspecialchars($hotel->name); ?>">
            </div>
            <div class="card-content">
                <div style="float:right; background:var(--primary-color); color:white; padding:0.25rem 0.5rem; border-radius:var(--radius-md); font-size:0.8rem; font-weight:bold;">
                    <i class="fas fa-star"></i> <?php echo htmlspecialchars($hotel->rating); ?>
                </div>
                <h3 class="card-title"><?php echo htmlspecialchars($hotel->name); ?></h3>
                <div class="card-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel->location); ?></div>
                <div class="card-footer">
                    <span class="card-price">₹<?php echo htmlspecialchars($hotel->price); ?> <span style="font-size:0.8rem; color:var(--text-muted);">/night</span></span>
                    <a href="payment.php?type=hotel&id=<?php echo $hotel->id; ?>" class="btn btn-primary">Book</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
