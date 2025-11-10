<?php
include 'header.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>

<section id="dashboard" style="padding: 2rem 0;">
    <div class="container">
        <h2 style="text-align: center;">Welcome to your Dashboard, <?php echo htmlspecialchars($_SESSION['email']); ?>!</h2>
        <div class="card-grid">
            <div class="card">
                <div class="card-content">
                    <h3>My Adoption Applications</h3>
                    <p>View the status of your adoption applications.</p>
                    <a href="#" class="btn">View Applications</a>
                </div>
            </div>
            <div class="card">
                <div class="card-content">
                    <h3>My Volunteer Schedule</h3>
                    <p>Check your upcoming volunteer dates and tasks.</p>
                    <a href="#" class="btn">View Schedule</a>
                </div>
            </div>
            <div class="card">
                <div class="card-content">
                    <h3>My Reports</h3>
                    <p>See the reports you have submitted.</p>
                    <a href="#" class="btn">View Reports</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
