<?php 
include 'header.php'; 

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>

<section id="volunteer-form" style="padding: 2rem 0;">
    <div class="container" style="max-width: 600px;">
        <h2 style="text-align: center;">Join Our Volunteer Team</h2>
        <p style="text-align: center;">Fill out the form below to become a part of our animal rescue community. We appreciate your support!</p>
        <form action="volunteer.php" method="post">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label for="availability">Availability</label>
                <select id="availability" name="availability">
                    <option value="weekdays">Weekdays</option>
                    <option value="weekends">Weekends</option>
                    <option value="flexible">Flexible</option>
                </select>
            </div>
            <div class="form-group">
                <label for="interests">Areas of Interest (select multiple)</label>
                <select id="interests" name="interests[]" multiple size="3">
                    <option value="shelter">Shelter Help</option>
                    <option value="events">Events</option>
                    <option value="transport">Transportation</option>
                </select>
            </div>
            <div class="form-group">
                <label for="message">Why do you want to volunteer?</label>
                <textarea id="message" name="message" rows="5"></textarea>
            </div>
            <button type="submit" class="btn btn-accent" style="width: 100%;">Sign Up</button>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>
