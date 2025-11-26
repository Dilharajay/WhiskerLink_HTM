<?php 
session_start(); // Add this at the very top
include 'header.php'; 
?>

<section id="hero" style="text-align: center; padding: 4rem 0; background-color: var(--primary-color); color: var(--white-color);">
    <div class="container">
        <h1>Welcome to Animal Rescue Connect</h1>
        <p>Connecting hearts, one paw at a time.</p>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <p style="margin-bottom: 1rem;">Welcome back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?>!</p>
            <a href="find-volunteers.php" class="btn btn-accent">Find a volunters</a>
            <a href="volunteer.php" class="btn btn-secondary">Become a Volunteer</a>
        <?php else: ?>
        <?php endif; ?>
    </div>
</section>

<section id="about-website" style="padding: 3rem 0; background-color: #f8f9fa;">
    <div class="container">
        <div style="max-width: 900px; margin: 0 auto; text-align: center;">
            <h2 style="margin-bottom: 1.5rem; color: var(--primary-color);">About Animal Rescue Connect</h2>
            <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 2rem; color: #555;">
                Animal Rescue Connect is a compassionate community dedicated to helping lost, stray, and abandoned animals find their way home or discover loving new families. We bridge the gap between animal lovers, volunteers, and rescue organizations to create a network of care and support.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3rem;">
            <!-- Feature 1 -->
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🐾</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Report & Reunite</h3>
                <p style="color: #666; line-height: 1.6;">
                    Found a lost pet? Report it quickly and help reunite animals with their worried families. Every second counts in bringing them home safely.
                </p>
            </div>

            <!-- Feature 2 -->
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">❤️</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Volunteer Network</h3>
                <p style="color: #666; line-height: 1.6;">
                    Join our community of caring volunteers. Whether you can foster, transport, or help with rescue efforts, your contribution makes a difference.
                </p>
            </div>

            <!-- Feature 3 -->
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🏠</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Find Help Fast</h3>
                <p style="color: #666; line-height: 1.6;">
                    Connect with local volunteers and rescue organizations in your area. Get immediate assistance when an animal needs urgent care or shelter.
                </p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 8px; color: white;">
            <h3 style="margin-bottom: 1rem; color: #ABE0F0">Our Mission</h3>
            <p style="font-size: 1.1rem; line-height: 1.8; max-width: 700px; margin: 0 auto; color:#f8f9fa">
                We believe every animal deserves a chance at a safe, loving life. Through technology and community collaboration, we're making rescue efforts more efficient and giving every stray, lost, or abandoned animal the best possible chance at finding their forever home.
            </p>
        </div>
    </div>
</section>



<?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <section id="cta-section" style="text-align: center; padding: 2rem 0; background-color: 393D7E;">
        <div class="container">
            <h2>Have you found a lost or stray animal?</h2>
            <p>You can help by reporting it to us. Your report can save a life.</p>
            <a href="report.php" class="btn btn-accent">Report an Animal</a>
        </div>
    </section>
<?php endif; ?>

<?php include 'footer.php'; ?>