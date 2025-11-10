<?php include 'header.php'; ?>

<section id="contact-page" style="padding: 2rem 0;">
    <div class="container">
        <h2 style="text-align: center;">Get in Touch</h2>
        <div class="contact-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 2rem;">
            <div class="contact-info">
                <h3>Shelter Information</h3>
                <p><strong>Main Shelter:</strong><br>
                    Cross Streat 02,<br>
                    Colombo, Sri Lanka</p>
                <p><strong>Email:</strong> contact@whiskerlink.org</p>
                <p><strong>Phone:</strong> (123) 456-7890</p>

                <h3>Hours of Operation</h3>
                <p>Monday - Friday: 10am - 6pm<br>
                    Saturday - Sunday: 10am - 4pm</p>
            </div>
            <div class="contact-form">
                <h3>Send us a Message</h3>
                <form action="contact.php" method="post">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Your Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-accent">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>