<?php include 'header.php'; ?>

<section id="hero" style="text-align: center; padding: 4rem 0; background-color: var(--primary-color); color: var(--white-color);">
    <div class="container">
        <h1>Welcome to Animal Rescue Connect</h1>
        <p>Connecting hearts, one paw at a time.</p>
        <?php if (isset($_SESSION['loggedin'])): ?>
            <a href="animals.php" class="btn btn-accent">Find a Friend</a>
            <a href="volunteer.php" class="btn btn-secondary">Become a Volunteer</a>
        <?php else: ?>
            <p>Please <a href="login.php" style="color: white; text-decoration: underline;">login</a> or <a href="register.php" style="color: white; text-decoration: underline;">register</a> to get started.</p>
        <?php endif; ?>
    </div>
</section>

<section id="featured-animals" style="padding: 2rem 0;">
    <div class="container">
        <h2 style="text-align: center;">Featured Friends</h2>
        <div class="card-grid">
            <!-- Placeholder Animal 1 -->
            <div class="card">
                <img src="https://images.pexels.com/photos/45201/kitty-cat-kitten-pet-45201.jpeg" alt="Cute kitten">
                <div class="card-content">
                    <h3>Buddy</h3>
                    <p>Friendly and playful. Loves cuddles!</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
            <!-- Placeholder Animal 2 -->
            <div class="card">
                <img src="https://placedog.net/300/200" alt="Cute dog">
                <div class="card-content">
                    <h3>Lucy</h3>
                    <p>A gentle soul looking for a loving home.</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
            <!-- Placeholder Animal 3 -->
            <div class="card">
                <img src="https://images.pexels.com/photos/15534058/pexels-photo-15534058.jpeg" alt="Another cute kitten">
                <div class="card-content">
                    <h3>Smokey</h3>
                    <p>Curious and independent. Will be your best friend.</p>
                    <a href="animal-detail.php" class="btn">View Details</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (isset($_SESSION['loggedin'])): ?>
    <section id="cta-section" style="text-align: center; padding: 2rem 0; background-color: var(--secondary-color);">
        <div class="container">
            <h2>Have you found a lost or stray animal?</h2>
            <p>You can help by reporting it to us. Your report can save a life.</p>
            <a href="report.php" class="btn btn-accent">Report an Animal</a>
        </div>
    </section>
<?php endif; ?>

<?php include 'footer.php'; ?>