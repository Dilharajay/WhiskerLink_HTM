</main>
    <footer>
        <div class="container">
            <div class="social-links">
                <a href="#">Facebook</a>
                <a href="#">Twitter</a>
                <a href="#">Instagram</a>
            </div>
            <div class="quick-links">
                <a href="index.php">Home</a>
                <a href="animals.php">Adopt</a>
                <?php if (isset($_SESSION['loggedin'])): ?>
                    <a href="volunteer.php">Volunteer</a>
                <?php endif; ?>
                <a href="contact.php">Contact</a>
            </div>
            <p>&copy; <?php echo date("Y"); ?> WhiskerLink - Animal Rescue Connect</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
