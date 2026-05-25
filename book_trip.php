<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

// Auto-create tables
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS flights (
        id INT AUTO_INCREMENT PRIMARY KEY, airline VARCHAR(100), flight_number VARCHAR(20),
        departure_city VARCHAR(100), arrival_city VARCHAR(100), departure_time TIME, arrival_time TIME,
        price DECIMAL(10,2), class ENUM('economy','business','first') DEFAULT 'economy', image VARCHAR(255)
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS trip_packages (
        id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, destination VARCHAR(200),
        travel_date DATE, return_date DATE, travelers INT DEFAULT 1, budget VARCHAR(50),
        ai_plan TEXT, flight_id INT, hotel_id INT, taxi_id INT,
        flight_price DECIMAL(10,2) DEFAULT 0, hotel_price DECIMAL(10,2) DEFAULT 0, taxi_price DECIMAL(10,2) DEFAULT 0,
        total_price DECIMAL(10,2) DEFAULT 0, status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    // Seed flights if empty
    $fc = $pdo->query("SELECT COUNT(*) as c FROM flights")->fetch()->c;
    if ($fc == 0) {
        $pdo->exec("INSERT INTO flights (airline,flight_number,departure_city,arrival_city,departure_time,arrival_time,price,class,image) VALUES
        ('Air India','AI-302','New Delhi','Paris','06:30:00','12:45:00',35000,'economy','https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=600&q=80'),
        ('Air India','AI-303','New Delhi','Paris','14:00:00','20:15:00',52000,'business','https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=600&q=80'),
        ('IndiGo','6E-1447','Mumbai','Santorini','08:15:00','15:30:00',28000,'economy','https://images.unsplash.com/photo-1556388158-158ea5ccacbd?auto=format&fit=crop&w=600&q=80'),
        ('IndiGo','6E-1448','Mumbai','Santorini','22:00:00','05:15:00',45000,'business','https://images.unsplash.com/photo-1556388158-158ea5ccacbd?auto=format&fit=crop&w=600&q=80'),
        ('Vistara','UK-819','Bangalore','Kyoto','10:00:00','22:30:00',32000,'economy','https://images.unsplash.com/photo-1529074963764-98f45c47344b?auto=format&fit=crop&w=600&q=80'),
        ('Vistara','UK-820','Bangalore','Kyoto','16:30:00','05:00:00',68000,'first','https://images.unsplash.com/photo-1529074963764-98f45c47344b?auto=format&fit=crop&w=600&q=80'),
        ('SpiceJet','SG-401','Delhi','Goa','07:00:00','09:30:00',4500,'economy','https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?auto=format&fit=crop&w=600&q=80'),
        ('Air India','AI-505','Chennai','London','23:45:00','06:10:00',42000,'economy','https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=600&q=80'),
        ('Emirates','EK-507','Mumbai','Dubai','02:30:00','04:45:00',18000,'economy','https://images.unsplash.com/photo-1540339832862-474599807836?auto=format&fit=crop&w=600&q=80'),
        ('Emirates','EK-508','Mumbai','Dubai','09:00:00','11:15:00',55000,'business','https://images.unsplash.com/photo-1540339832862-474599807836?auto=format&fit=crop&w=600&q=80')");
    }
} catch(PDOException $e) { /* tables may already exist */ }

// Handle AI planning (Step 1 -> Step 2)
$ai_plan = '';
$ai_error = '';
$step = 1;
$trip_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_plan'])) {
    $trip_data = [
        'destination' => trim($_POST['destination'] ?? ''),
        'from_city'   => trim($_POST['from_city'] ?? ''),
        'travel_date' => $_POST['travel_date'] ?? '',
        'return_date'  => $_POST['return_date'] ?? '',
        'travelers'   => intval($_POST['travelers'] ?? 1),
        'budget'      => trim($_POST['budget'] ?? 'mid-range')
    ];

    if (empty($trip_data['destination'])) {
        $ai_error = 'Please enter a destination.';
    } else {
        $safe = htmlspecialchars($trip_data['destination']);
        $prompt = "You are an expert AI travel planner for GLOBEXA. Plan a trip to \"$safe\" for {$trip_data['travelers']} traveler(s), budget: {$trip_data['budget']}, from {$trip_data['travel_date']} to {$trip_data['return_date']}.
Provide: 1. Destination Overview 2. Day-wise Itinerary 3. Budget Breakdown exclusively in INR (₹) 4. Hotel Suggestions 5. Transport Tips 6. Food Recommendations 7. Travel Tips.
Format using clean HTML tags (h3, ul, li, p, strong). No markdown blocks.";

        $ch = curl_init();
        $data = ["model"=>"openai/gpt-4o-mini","messages"=>[
            ["role"=>"system","content"=>"You are a skilled travel planner. Output clean HTML."],
            ["role"=>"user","content"=>$prompt]
        ]];
        $api_key = defined('OPENROUTER_API_KEY') ? OPENROUTER_API_KEY : '';
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://openrouter.ai/api/v1/chat/completions",
            CURLOPT_RETURNTRANSFER => 1, CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ["Authorization: Bearer ".$api_key,"HTTP-Referer: http://localhost/Globxa","X-Title: Globexa","Content-Type: application/json"],
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code == 200 && $resp) {
            $r = json_decode($resp, true);
            if (isset($r['choices'][0]['message']['content'])) {
                $ai_plan = $r['choices'][0]['message']['content'];
                $step = 2;
            } else { $ai_error = 'Invalid AI response.'; }
        } else { $ai_error = "AI service error (code: $code). Try again."; }
    }
}

// Fetch data for selection steps
$flights = $pdo->query("SELECT * FROM flights ORDER BY price ASC")->fetchAll();
$hotels  = $pdo->query("SELECT * FROM hotels ORDER BY rating DESC")->fetchAll();
$taxis   = $pdo->query("SELECT * FROM taxis ORDER BY price ASC")->fetchAll();
?>

<!-- Hero Banner -->
<div style="background:linear-gradient(135deg,#2563eb 0%,#7c3aed 100%);padding:5rem 0 2.5rem;text-align:center;color:#fff;">
    <h1 style="font-size:2.5rem;margin-bottom:0.5rem;"><i class="fas fa-suitcase-rolling"></i> Book a Complete Trip</h1>
    <p style="opacity:0.9;font-size:1.1rem;">Flight + Hotel + Taxi — AI planned, one payment</p>
</div>

<div class="container" style="padding:2.5rem 0 4rem;max-width:960px;margin:0 auto;">

<!-- Wizard Stepper -->
<div class="wizard-stepper" id="wizardStepper">
    <div class="wizard-step active" data-step="1"><span class="step-num">1</span><span class="step-label">Trip Details</span></div>
    <div class="step-connector"></div>
    <div class="wizard-step" data-step="2"><span class="step-num">2</span><span class="step-label">AI Plan</span></div>
    <div class="step-connector"></div>
    <div class="wizard-step" data-step="3"><span class="step-num">3</span><span class="step-label">Flight</span></div>
    <div class="step-connector"></div>
    <div class="wizard-step" data-step="4"><span class="step-num">4</span><span class="step-label">Hotel</span></div>
    <div class="step-connector"></div>
    <div class="wizard-step" data-step="5"><span class="step-num">5</span><span class="step-label">Taxi</span></div>
</div>

<!-- ===== STEP 1: Trip Details ===== -->
<div class="wizard-panel <?php echo $step==1?'active':''; ?>" id="step1">
    <div class="card" style="padding:2rem;border-radius:12px;">
        <h3 style="margin-bottom:1.5rem;"><i class="fas fa-map-marked-alt" style="color:var(--primary-color);"></i> Where do you want to go?</h3>
        <?php if($ai_error): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:1rem;border-radius:8px;margin-bottom:1rem;"><strong>Error:</strong> <?php echo htmlspecialchars($ai_error); ?></div>
        <?php endif; ?>
        <form method="POST" id="tripForm">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div class="form-group"><label>From City</label><input type="text" name="from_city" class="form-control" placeholder="e.g. New Delhi" required></div>
                <div class="form-group"><label>Destination</label><input type="text" name="destination" class="form-control" placeholder="e.g. Paris, France" value="<?php echo htmlspecialchars($_GET['dest'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Budget Level</label>
                    <select name="budget" class="form-control"><option value="budget">Budget</option><option value="mid-range" selected>Mid-Range</option><option value="luxury">Luxury</option></select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                <div class="form-group"><label>Travel Date</label><input type="date" name="travel_date" class="form-control" required></div>
                <div class="form-group"><label>Return Date</label><input type="date" name="return_date" class="form-control" required></div>
                <div class="form-group"><label>Travelers</label><input type="number" name="travelers" class="form-control" value="1" min="1" max="10"></div>
            </div>
            <button type="submit" name="generate_plan" class="btn btn-primary" style="width:100%;padding:1rem;font-size:1.1rem;" id="planBtn">
                <i class="fas fa-magic"></i> Generate AI Trip Plan
            </button>
            <!-- Loading is handled globally -->
        </form>
    </div>
</div>

<!-- ===== STEP 2: AI Plan ===== -->
<div class="wizard-panel <?php echo $step==2?'active':''; ?>" id="step2">
    <div class="ai-plan-box">
        <h3 style="color:var(--primary-color);margin-bottom:1rem;"><i class="fas fa-robot"></i> Your AI Trip Plan</h3>
        <div class="ai-plan-content" id="aiPlanContent"><?php echo $ai_plan; ?></div>
    </div>
    <div class="wizard-actions">
        <button class="btn btn-outline" onclick="goStep(1)"><i class="fas fa-arrow-left"></i> Back</button>
        <button class="btn btn-primary" onclick="goStep(3)">Select Flight <i class="fas fa-arrow-right"></i></button>
    </div>
</div>

<!-- ===== STEP 3: Flights ===== -->
<div class="wizard-panel" id="step3">
    <h3 style="margin-bottom:1.5rem;"><i class="fas fa-plane" style="color:var(--primary-color);"></i> Select Your Flight</h3>
    <div class="select-grid">
        <?php foreach($flights as $f): ?>
        <div class="select-card" data-type="flight" data-id="<?php echo $f->id; ?>" data-price="<?php echo $f->price; ?>" data-name="<?php echo htmlspecialchars($f->airline.' '.$f->flight_number); ?>" data-departure="<?php echo htmlspecialchars(strtolower($f->departure_city)); ?>" data-arrival="<?php echo htmlspecialchars(strtolower($f->arrival_city)); ?>" onclick="selectCard(this,'flight')">
            <h4><?php echo htmlspecialchars($f->airline); ?> <span style="color:var(--text-muted);font-weight:400;"><?php echo htmlspecialchars($f->flight_number); ?></span></h4>
            <div class="meta"><i class="fas fa-plane-departure"></i> <?php echo htmlspecialchars($f->departure_city); ?> → <?php echo htmlspecialchars($f->arrival_city); ?></div>
            <div class="meta"><i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($f->departure_time)); ?> – <?php echo date('h:i A', strtotime($f->arrival_time)); ?></div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.75rem;">
                <span class="price-tag">₹<?php echo number_format($f->price,0); ?></span>
                <span class="badge badge-<?php echo $f->class=='economy'?'success':($f->class=='business'?'warning':'danger'); ?>"><?php echo ucfirst($f->class); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="wizard-actions">
        <button class="btn btn-outline" onclick="goStep(2)"><i class="fas fa-arrow-left"></i> Back</button>
        <button class="btn btn-primary" onclick="goStep(4)" id="toHotelBtn" disabled>Select Hotel <i class="fas fa-arrow-right"></i></button>
    </div>
</div>

<!-- ===== STEP 4: Hotels ===== -->
<div class="wizard-panel" id="step4">
    <h3 style="margin-bottom:1.5rem;"><i class="fas fa-hotel" style="color:var(--secondary-color);"></i> Select Your Hotel</h3>
    <div class="select-grid">
        <?php foreach($hotels as $h): ?>
        <div class="select-card" data-type="hotel" data-id="<?php echo $h->id; ?>" data-price="<?php echo $h->price; ?>" data-name="<?php echo htmlspecialchars($h->name); ?>" data-location="<?php echo htmlspecialchars(strtolower($h->location)); ?>" onclick="selectCard(this,'hotel')">
            <div style="height:140px;border-radius:8px;overflow:hidden;margin-bottom:0.75rem;">
                <img src="<?php echo htmlspecialchars($h->image); ?>" alt="<?php echo htmlspecialchars($h->name); ?>" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <h4><?php echo htmlspecialchars($h->name); ?></h4>
            <div class="meta"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($h->location); ?></div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="price-tag">₹<?php echo number_format($h->price,0); ?><span style="font-size:0.75rem;color:var(--text-muted);font-weight:400;">/night</span></span>
                <span style="color:#f59e0b;font-weight:600;"><i class="fas fa-star"></i> <?php echo $h->rating; ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="wizard-actions">
        <button class="btn btn-outline" onclick="goStep(3)"><i class="fas fa-arrow-left"></i> Back</button>
        <button class="btn btn-primary" onclick="goStep(5)" id="toTaxiBtn" disabled>Select Taxi <i class="fas fa-arrow-right"></i></button>
    </div>
</div>

<!-- ===== STEP 5: Taxis ===== -->
<div class="wizard-panel" id="step5">
    <h3 style="margin-bottom:1.5rem;"><i class="fas fa-taxi" style="color:#f59e0b;"></i> Select Your Taxi</h3>
    <div class="select-grid">
        <?php foreach($taxis as $t): ?>
        <div class="select-card" data-type="taxi" data-id="<?php echo $t->id; ?>" data-price="<?php echo $t->price; ?>" data-name="<?php echo htmlspecialchars($t->car_name.' – '.$t->driver_name); ?>" data-location="<?php echo htmlspecialchars(strtolower($t->location)); ?>" onclick="selectCard(this,'taxi')">
            <div style="font-size:2.5rem;color:var(--text-muted);text-align:center;margin-bottom:0.75rem;"><i class="fas fa-taxi"></i></div>
            <h4><?php echo htmlspecialchars($t->car_name); ?></h4>
            <div class="meta"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($t->driver_name); ?></div>
            <div class="meta"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($t->location); ?></div>
            <span class="price-tag">₹<?php echo number_format($t->price,0); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="wizard-actions">
        <button class="btn btn-outline" onclick="goStep(4)"><i class="fas fa-arrow-left"></i> Back</button>
        <button class="btn btn-primary" onclick="submitPackage()" id="checkoutBtn" disabled><i class="fas fa-shopping-cart"></i> Proceed to Checkout</button>
    </div>
</div>

<!-- Hidden form for checkout submission -->
<form id="checkoutForm" method="POST" action="package_checkout.php" style="display:none;">
    <input type="hidden" name="destination" value="<?php echo htmlspecialchars($trip_data['destination'] ?? ''); ?>">
    <input type="hidden" name="from_city" value="<?php echo htmlspecialchars($trip_data['from_city'] ?? ''); ?>">
    <input type="hidden" name="travel_date" value="<?php echo htmlspecialchars($trip_data['travel_date'] ?? ''); ?>">
    <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($trip_data['return_date'] ?? ''); ?>">
    <input type="hidden" name="travelers" value="<?php echo intval($trip_data['travelers'] ?? 1); ?>">
    <input type="hidden" name="budget" value="<?php echo htmlspecialchars($trip_data['budget'] ?? ''); ?>">
    <input type="hidden" name="ai_plan" value="<?php echo htmlspecialchars($ai_plan); ?>">
    <input type="hidden" name="flight_id" id="hFlightId">
    <input type="hidden" name="flight_price" id="hFlightPrice">
    <input type="hidden" name="flight_name" id="hFlightName">
    <input type="hidden" name="hotel_id" id="hHotelId">
    <input type="hidden" name="hotel_price" id="hHotelPrice">
    <input type="hidden" name="hotel_name" id="hHotelName">
    <input type="hidden" name="taxi_id" id="hTaxiId">
    <input type="hidden" name="taxi_price" id="hTaxiPrice">
    <input type="hidden" name="taxi_name" id="hTaxiName">
</form>
</div>

<style>

.ai-plan-content h3{color:#2563eb;font-size:1.2rem;border-bottom:2px solid #e2e8f0;padding-bottom:0.4rem;margin-top:1.5rem;}
.ai-plan-content ul{padding-left:1.5rem;margin-bottom:1rem;}
.ai-plan-content li{margin-bottom:0.4rem;}
.ai-plan-content p{margin-bottom:1rem;}
</style>

<script>
let currentStep = <?php echo $step; ?>;
const selections = {flight:{id:null,price:0,name:''},hotel:{id:null,price:0,name:''},taxi:{id:null,price:0,name:''}};

// Pass flights from PHP to JS
const flightsData = <?php echo json_encode(array_map(function($f) {
    return [
        'departure_city' => strtolower($f->departure_city),
        'arrival_city' => strtolower($f->arrival_city)
    ];
}, $flights)); ?>;

// Trip details from server (for filtering)
const tripDest = '<?php echo strtolower(addslashes($trip_data['destination'] ?? '')); ?>';
const tripFrom = '<?php echo strtolower(addslashes($trip_data['from_city'] ?? '')); ?>';

// If AI plan was generated, set step to 2
if(currentStep === 2) updateStepper(2);

// Show loading on form submit
const tf = document.getElementById('tripForm');
if(tf) tf.addEventListener('submit', function(e){
    const destinationInput = document.querySelector('[name=destination]').value.trim().toLowerCase();
    const fromCityInput = document.querySelector('[name=from_city]').value.trim().toLowerCase();
    
    if(destinationInput){
        // Check if any flight matches the destination (arrival city)
        const hasMatchingFlight = flightsData.some(f => {
            const arr = f.arrival_city;
            return destinationInput.includes(arr) || arr.includes(destinationInput);
        });

        if(!hasMatchingFlight){
            e.preventDefault();
            Swal.fire({
                title: 'Soon to Introduce',
                text: 'Flights for this destination will be introduced soon!',
                icon: 'info',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        document.getElementById('planBtn').style.display='none';
        showGlobalLoader('Globxa is planning your perfect trip...');
    }
});

// Filter cards to show only those matching destination/from city
function filterCards(stepNum) {
    if(stepNum === 3) {
        // Filter flights: match departure_city with from_city AND arrival_city with destination
        let found = 0;
        document.querySelectorAll('#step3 .select-card').forEach(card => {
            const dep = card.dataset.departure || '';
            const arr = card.dataset.arrival || '';
            const matchDep = !tripFrom || dep.includes(tripFrom) || tripFrom.includes(dep);
            const matchArr = !tripDest || arr.includes(tripDest) || tripDest.includes(arr);
            if(matchDep && matchArr) { card.style.display = ''; found++; }
            else { card.style.display = 'none'; }
        });
        // Show "no results" message
        let noMsg = document.getElementById('noFlights');
        if(!noMsg) { noMsg = document.createElement('div'); noMsg.id='noFlights'; noMsg.style.cssText='grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-muted);'; document.querySelector('#step3 .select-grid').appendChild(noMsg); }
        if(found === 0) { 
            noMsg.innerHTML = '<i class="fas fa-plane" style="font-size:2rem;margin-bottom:0.5rem;"></i><p>Soon to introduce: No flights are available for this route yet.</p>';
            noMsg.style.display='block'; 
            document.querySelectorAll('#step3 .select-card').forEach(c=>c.style.display='none');
            Swal.fire({
                title: 'Soon to Introduce',
                text: 'Flights for this trip will be introduced soon!',
                icon: 'info',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                goStep(1);
            });
        }
        else { noMsg.style.display='none'; }
    }
    if(stepNum === 4) {
        let found = 0;
        document.querySelectorAll('#step4 .select-card').forEach(card => {
            const loc = card.dataset.location || '';
            if(!tripDest || loc.includes(tripDest) || tripDest.includes(loc)) { card.style.display = ''; found++; }
            else { card.style.display = 'none'; }
        });
        let noMsg = document.getElementById('noHotels');
        if(!noMsg) { noMsg = document.createElement('div'); noMsg.id='noHotels'; noMsg.style.cssText='grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-muted);'; noMsg.innerHTML='<i class="fas fa-hotel" style="font-size:2rem;margin-bottom:0.5rem;"></i><p>No hotels found at this destination. Showing all available hotels.</p>'; document.querySelector('#step4 .select-grid').appendChild(noMsg); }
        if(found === 0) { noMsg.style.display='block'; document.querySelectorAll('#step4 .select-card').forEach(c=>c.style.display=''); }
        else { noMsg.style.display='none'; }
    }
    if(stepNum === 5) {
        let found = 0;
        document.querySelectorAll('#step5 .select-card').forEach(card => {
            const loc = card.dataset.location || '';
            if(!tripDest || loc.includes(tripDest) || tripDest.includes(loc)) { card.style.display = ''; found++; }
            else { card.style.display = 'none'; }
        });
        let noMsg = document.getElementById('noTaxis');
        if(!noMsg) { noMsg = document.createElement('div'); noMsg.id='noTaxis'; noMsg.style.cssText='grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-muted);'; noMsg.innerHTML='<i class="fas fa-taxi" style="font-size:2rem;margin-bottom:0.5rem;"></i><p>No taxis found at this destination. Showing all available taxis.</p>'; document.querySelector('#step5 .select-grid').appendChild(noMsg); }
        if(found === 0) { noMsg.style.display='block'; document.querySelectorAll('#step5 .select-card').forEach(c=>c.style.display=''); }
        else { noMsg.style.display='none'; }
    }
}

function goStep(n) {
    document.querySelectorAll('.wizard-panel').forEach(p=>p.classList.remove('active'));
    document.getElementById('step'+n).classList.add('active');
    currentStep = n;
    updateStepper(n);
    if(n >= 3) filterCards(n);
    window.scrollTo({top:200,behavior:'smooth'});
}

function updateStepper(n) {
    document.querySelectorAll('.wizard-step').forEach(s=>{
        const sn = parseInt(s.dataset.step);
        s.classList.remove('active','completed');
        if(sn < n) s.classList.add('completed');
        if(sn === n) s.classList.add('active');
    });
    document.querySelectorAll('.step-connector').forEach((c,i)=>{
        c.classList.toggle('active', i < n-1);
    });
}

function selectCard(el, type) {
    // Deselect siblings
    el.parentElement.querySelectorAll('.select-card[data-type="'+type+'"]').forEach(c=>c.classList.remove('selected'));
    el.classList.add('selected');
    selections[type] = {id:el.dataset.id, price:parseFloat(el.dataset.price), name:el.dataset.name};
    // Enable next button
    if(type==='flight') document.getElementById('toHotelBtn').disabled=false;
    if(type==='hotel') document.getElementById('toTaxiBtn').disabled=false;
    if(type==='taxi') document.getElementById('checkoutBtn').disabled=false;
}

function submitPackage() {
    document.getElementById('hFlightId').value = selections.flight.id;
    document.getElementById('hFlightPrice').value = selections.flight.price;
    document.getElementById('hFlightName').value = selections.flight.name;
    document.getElementById('hHotelId').value = selections.hotel.id;
    document.getElementById('hHotelPrice').value = selections.hotel.price;
    document.getElementById('hHotelName').value = selections.hotel.name;
    document.getElementById('hTaxiId').value = selections.taxi.id;
    document.getElementById('hTaxiPrice').value = selections.taxi.price;
    document.getElementById('hTaxiName').value = selections.taxi.name;
    document.getElementById('checkoutForm').submit();
}
</script>

<?php require_once 'includes/footer.php'; ?>
