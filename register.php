<?php
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password-confirm'];

    if ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        // In a real application, you would save the user to a database.
        // For this example, we'll just show a success message.
        $success = 'Registration successful! You can now <a href="login.php">login</a>.';
        // Here you would typically hash the password and store it, e.g.:
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }
}

include 'header.php';
?>

<section id="register-form" style="padding: 2rem 0;">
    <div class="container" style="max-width: 600px;">
        <h2 style="text-align: center;">Create Your Account</h2>
        <p style="text-align: center;">Join our community to help animals in need.</p>

        <?php if ($error): ?>
            <p style="color: red; text-align: center;"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="color: green; text-align: center;"><?php echo $success; ?></p>
        <?php else: ?>
            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password-confirm">Confirm Password</label>
                    <input type="password" id="password-confirm" name="password-confirm" required>
                </div>
                <button type="submit" class="btn btn-accent" style="width: 100%;">Register</button>
            </form>
            <p style="text-align: center; margin-top: 1rem;">
                Already have an account? <a href="login.php">Login here</a>.
            </p>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
