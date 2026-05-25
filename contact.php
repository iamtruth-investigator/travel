<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$success = '';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // In a real app, you would save this to a 'contacts' table or send an email.
    // For this demo, we'll just show a success message.
    $success = "Thank you for contacting us! We will get back to you soon.";
}
?>

<div style="background:var(--dark-bg); padding: 6rem 0 3rem; text-align:center; color:white;">
    <h1 style="font-size:3rem; margin-bottom:1rem;">Contact Us</h1>
    <p style="opacity:0.9;">Got questions? We're here to help you 24/7.</p>
</div>

<div class="container section-padding">
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 4rem;">
        <div>
            <h2 style="margin-bottom: 2rem;">Get in Touch</h2>
            <p style="color:var(--text-muted); margin-bottom:2rem;">Fill out the form and our team will reach out to you within 24 hours.</p>
            
            <div style="margin-bottom: 1.5rem; display:flex; gap:1rem; align-items:center;">
                <div style="width:50px; height:50px; background:var(--primary-color); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h4 style="color:var(--text-main);">Email</h4>
                    <p style="color:var(--text-muted);">support@globexa.com</p>
                </div>
            </div>
            
            <div style="margin-bottom: 1.5rem; display:flex; gap:1rem; align-items:center;">
                <div style="width:50px; height:50px; background:var(--secondary-color); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                    <i class="fas fa-phone"></i>
                </div>
                <div>
                    <h4 style="color:var(--text-main);">Phone</h4>
                    <p style="color:var(--text-muted);">+1 (555) 000-0000</p>
                </div>
            </div>
            
            <div style="margin-bottom: 1.5rem; display:flex; gap:1rem; align-items:center;">
                <div style="width:50px; height:50px; background:rgb(245, 158, 11); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <h4 style="color:var(--text-main);">Office</h4>
                    <p style="color:var(--text-muted);">MCA Block, Guwahati, Assam</p>
                </div>
            </div>
        </div>
        
        <div class="card" style="padding: 2.5rem;">
            <?php if($success): ?>
                <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Full Name</label>
                    <input type="text" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Email Address</label>
                    <input type="email" class="form-control" placeholder="john@example.com" required>
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Subject</label>
                    <input type="text" class="form-control" placeholder="Inquiry about..." required>
                </div>
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label>Message</label>
                    <textarea class="form-control" rows="5" placeholder="Your message here..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
