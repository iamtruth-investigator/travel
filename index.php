<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<!-- Debug: Page Load Start -->";
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg">
        <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1920&q=80" alt="Travel Background">
    </div>
    <div class="container hero-content">
        <h1>Discover Your Next Adventure</h1>
        <p>AI-Powered insights for smarter travel planning and seamless experiences around the globe.</p>
        
        <form action="destinations.php" method="GET" class="glass search-box" style="display:flex; align-items:flex-end;">
            <div class="form-group" style="flex:1;">
                <label>Where to?</label>
                <input type="text" name="search" class="form-control" placeholder="Search destinations...">
            </div>
            <div class="form-group" style="flex:1;">
                <label>Check In</label>
                <input type="date" name="checkin" class="form-control">
            </div>
            <div class="form-group" style="flex:1;">
                <label>Guests</label>
                <select name="guests" class="form-control">
                    <option value="1">1 Explorer</option>
                    <option value="2">2 Explorers</option>
                    <option value="family">Family</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height:48px;"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</section>

<!-- Featured Destinations -->
<section class="section-padding" style="background: var(--light-bg)">
    <div class="container">
        <div class="section-header">
            <h2>Popular Destinations</h2>
            <p>Explore the most breathtaking locations carefully curated for your next journey.</p>
        </div>
        
        <div class="grid grid-3">
            <?php
            $stmt = $pdo->query("SELECT * FROM destinations ORDER BY id DESC LIMIT 3");
            while($dest = $stmt->fetch()):
            ?>
            <div class="card">
                <div class="card-img">
                    <img src="<?php echo htmlspecialchars($dest->image); ?>" alt="<?php echo htmlspecialchars($dest->title); ?>">
                </div>
                <div class="card-content">
                    <h3 class="card-title"><?php echo htmlspecialchars($dest->title); ?></h3>
                    <div class="card-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($dest->country); ?></div>
                    <div class="card-footer">
                        <span class="card-price">₹<?php echo htmlspecialchars($dest->price); ?> <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted)">/person</span></span>
                        <a href="destinations.php" class="btn btn-outline" style="padding: 0.5rem 1rem">Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="destinations.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Explore All Destinations <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- AI Features Teaser -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <h2>Experience AI-Powered Travel</h2>
            <p>We leverage advanced AI to provide personalized recommendations tailored exactly to your preferences.</p>
        </div>
        <div class="grid grid-3" style="gap: 3rem;">
            <div style="text-align: center;">
                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"><i class="fas fa-robot"></i></div>
                <h3>Smart Recommendations</h3>
                <p style="color: var(--text-muted); margin-top: 1rem;">Let our AI craft the perfect itinerary based on your travel history and interests.</p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 1rem;"><i class="fas fa-wallet"></i></div>
                <h3>Dynamic Budgeting</h3>
                <p style="color: var(--text-muted); margin-top: 1rem;">Real-time price tracking and smart budget planner for cost-effective journeys.</p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 1rem;"><i class="fas fa-headset"></i></div>
                <h3>24/7 Virtual Assistant</h3>
                <p style="color: var(--text-muted); margin-top: 1rem;">Instant support, translation, and localized tips available right in your pocket.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
