<?php 
include 'header.php'; 

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>

<section id="report-form" style="padding: 2rem 0;">
    <div class="container" style="max-width: 600px;">
        <h2 style="text-align: center;">Report a Rescued or Lost Animal</h2>
        <p style="text-align: center;">Thank you for helping an animal in need. Please provide as much detail as possible.</p>
        <form action="report.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="report-type">Report Type</label>
                <select id="report-type" name="report-type">
                    <option value="found">I Found an Animal</option>
                    <option value="lost">I Lost my Pet</option>
                </select>
            </div>
            <div class="form-group">
                <label for="animal-type">Type of Animal</label>
                <input type="text" id="animal-type" name="animal-type" placeholder="e.g., Dog, Cat, Bird" required>
            </div>
            <div class="form-group">
                <label for="location">Location Found/Lost</label>
                <input type="text" id="location" name="location" placeholder="City, State, or specific address" required>
            </div>
            <div class="form-group">
                <label for="date">Date Found/Lost</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
                <label for="description">Description of Animal</label>
                <textarea id="description" name="description" rows="5" placeholder="Include breed, color, size, and any distinguishing marks."></textarea>
            </div>
            <div class="form-group">
                <label for="photo">Upload a Photo</label>
                <input type="file" id="photo" name="photo">
            </div>
            <div class="form-group">
                <label for="contact-name">Your Name</label>
                <input type="text" id="contact-name" name="contact-name" required>
            </div>
            <div class="form-group">
                <label for="contact-email">Your Email</label>
                <input type="email" id="contact-email" name="contact-email" required>
            </div>
            <button type="submit" class="btn btn-accent" style="width: 100%;">Submit Report</button>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>
