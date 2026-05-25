<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<div style="background:var(--primary-color); padding: 6rem 0 3rem; text-align:center; color:white;">
    <h1 style="font-size:3rem; margin-bottom:1rem;">Explore Destinations</h1>
    <p style="opacity:0.9;">Find your perfect getaways with our AI curated list</p>
</div>

<div class="container section-padding">
    <div class="grid grid-3">
        <?php
        $search = $_GET['search'] ?? '';
        if ($search) {
            $stmt = $pdo->prepare("SELECT * FROM destinations WHERE title LIKE ? OR country LIKE ? OR description LIKE ? ORDER BY title ASC");
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM destinations ORDER BY title ASC");
        }
        
        if($stmt->rowCount() > 0):
            while($dest = $stmt->fetch()):
        ?>
        <div class="card">
            <div class="card-img">
                <img src="<?php echo htmlspecialchars($dest->image); ?>" alt="<?php echo htmlspecialchars($dest->title); ?>">
            </div>
            <div class="card-content">
                <h3 class="card-title"><?php echo htmlspecialchars($dest->title); ?></h3>
                <div class="card-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($dest->country); ?></div>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1rem;"><?php echo htmlspecialchars(substr($dest->description, 0, 100)); ?>...</p>
                <div class="card-footer">
                    <span class="card-price">₹<?php echo htmlspecialchars($dest->price); ?></span>
                    <a href="book_trip.php?dest=<?php echo urlencode($dest->title); ?>" class="btn btn-primary" style="padding: 0.5rem 1rem">Book Now</a>
                </div>
            </div>
        </div>
        <?php 
            endwhile; 
        else:
        ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
                <h3>No destinations found matching your search.</h3>
                <a href="destinations.php" class="btn btn-outline" style="margin-top: 1rem; display: inline-block;">Clear Search</a>
            </div>
        <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
