<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// If redirectIfNotLoggedIn() exists in the application, use it or skip it based on project structure.
if (function_exists('redirectIfNotLoggedIn')) {
    redirectIfNotLoggedIn();
}

// Create table if not exists (Optional feature implemented)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_query TEXT,
        ai_response TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // Ignore error if we cannot create table, or log it
}

$recommendation = '';
$loading = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['get_recommendation'])) {
    $user_query = trim($_POST['user_query'] ?? '');
    
    if (empty($user_query)) {
        $error_message = "Please enter your travel preferences.";
    } else {
        // Sanitize input
        $safe_query = htmlspecialchars($user_query);
        
        $prompt = "You are an expert AI travel planner for GLOBEXA. Based on the following user query, create a comprehensive travel plan:
        User Query: \"$safe_query\"
        
        Please provide the following sections:
        1. Destination Overview
        2. Day-wise Itinerary
        3. Budget Breakdown (Provide exclusively in INR (₹) regardless of the destination)
        4. Hotel Suggestions
        5. Transport Suggestions
        6. Food Recommendations
        7. Travel Tips
        
        Format your response using clean HTML. Use tags like <h3>, <ul>, <li>, <p>, and <strong> for proper structure and spacing. Do not include markdown blocks like ```html. Ensure it is visually appealing and ready to render in a browser.";

        $ch = curl_init();
        
        $data = [
            "model" => "openai/gpt-4o-mini",
            "messages" => [
                ["role" => "system", "content" => "You are a highly skilled travel planner assistant. Always output clean, structured, and beautiful HTML without markdown wrappers."],
                ["role" => "user", "content" => $prompt]
            ]
        ];
        
        curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        // Use the API key from db.php (Assuming defined as OPENROUTER_API_KEY)
        $api_key = defined('OPENROUTER_API_KEY') ? OPENROUTER_API_KEY : '';
        
        $headers = [
            "Authorization: Bearer " . $api_key,
            "HTTP-Referer: http://localhost/Globxa",
            "X-Title: Globexa Travel Planner",
            "Content-Type: application/json"
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpcode == 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                $recommendation = $result['choices'][0]['message']['content'];
                
                // Save to database
                try {
                    $stmt = $pdo->prepare("INSERT INTO ai_plans (user_query, ai_response) VALUES (?, ?)");
                    $stmt->execute([$user_query, $recommendation]);
                } catch (PDOException $e) {
                    // Fail silently for DB insert to not break the user experience
                }
            } else {
                $error_message = "Invalid response format from the AI provider.";
            }
        } else {
            $error_message = "Failed to connect to the AI service. Please try again later. (Error Code: $httpcode)";
        }
    }
}
?>

<div style="background:var(--primary-color, #2563eb); padding: 4rem 0 2rem; text-align:center; color:white;">
    <h1 style="font-size:2.5rem; margin-bottom:0.5rem;"><i class="fas fa-robot"></i> AI Travel Planner</h1>
    <p style="opacity:0.9;">Describe your dream trip, and let our AI craft the perfect itinerary for you.</p>
</div>

<div class="container section-padding" style="margin-top: 2rem; margin-bottom: 4rem;">
    <div class="row justify-content-center">
        
        <!-- Planner Form -->
        <div class="col-md-10">
            <div class="card shadow-sm" style="padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1.5rem;">Where do you want to go?</h3>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" style="color: #842029; background-color: #f8d7da; border-color: #f5c2c7; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="ai-planner-form">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="user_query" style="display:block; margin-bottom:0.5rem; font-weight: 500;">Your Travel Query</label>
                        <textarea name="user_query" id="user_query" rows="4" class="form-control" placeholder="e.g., I want to go to Kerala for 5 days with my family. My budget is mid-range. I love nature and backwaters." required style="width:100%; padding: 1rem; border-radius: 8px; border: 1px solid #ced4da; font-size: 1rem;"></textarea>
                    </div>
                    
                    <button type="submit" name="get_recommendation" id="submit-btn" class="btn btn-primary" style="width:100%; padding: 1rem; font-size:1.1rem; border-radius: 8px; background-color: #2563eb; color: white; border: none; cursor: pointer; transition: background-color 0.3s;">
                        <i class="fas fa-magic"></i> Generate My Trip
                    </button>
                    
                    <!-- Loading is handled globally -->
                </form>
            </div>
        </div>

        <!-- AI Response Area -->
        <?php if (!empty($recommendation)): ?>
            <div class="col-md-10">
                <div class="card shadow" style="padding: 2.5rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; border-top: 5px solid #2563eb;">
                    <h2 style="margin-bottom: 2rem; color: #1e293b; text-align: center;"><i class="fas fa-map-marked-alt" style="color: #2563eb;"></i> Your Personalized AI Itinerary</h2>
                    
                    <div class="ai-content" style="line-height: 1.8; color: #334155; font-size: 1.05rem;">
                        <?php echo $recommendation; ?>
                    </div>
                    
                    <div style="margin-top: 3rem; text-align: center; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
                        <button onclick="window.print()" class="btn btn-outline-secondary" style="margin-right: 1rem; padding: 0.6rem 1.5rem; border-radius: 6px;"><i class="fas fa-print"></i> Print Plan</button>
                        <a href="destinations.php" class="btn btn-primary" style="padding: 0.6rem 1.5rem; border-radius: 6px; background-color: #2563eb; color: white; text-decoration: none;">Book This Trip <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
/* CSS to ensure the HTML from AI looks beautiful */
.ai-content h1, .ai-content h2, .ai-content h3 { 
    color: #1e293b; 
    margin-top: 2rem; 
    margin-bottom: 1rem; 
    font-weight: 600;
}
.ai-content h3 {
    font-size: 1.4rem;
    color: #2563eb;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}
.ai-content ul, .ai-content ol { 
    padding-left: 1.5rem; 
    margin-bottom: 1.5rem; 
}
.ai-content li { 
    margin-bottom: 0.5rem; 
}
.ai-content p { 
    margin-bottom: 1.5rem; 
}
.ai-content strong { 
    color: #0f172a; 
    font-weight: 600;
}

</style>

<script>
document.getElementById('ai-planner-form').addEventListener('submit', function() {
    // Only show loading if the textarea is not empty
    if(document.getElementById('user_query').value.trim() !== '') {
        document.getElementById('submit-btn').style.display = 'none';
        showGlobalLoader('Crafting your perfect AI journey...');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
