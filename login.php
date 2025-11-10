<?php
// In a real application, you would connect to a database and verify credentials.
// For this example, we'll use a dummy user.
$users = [
    'user@example.com' => password_hash('password123', PASSWORD_DEFAULT)
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (isset($users[$email]) && password_verify($password, $users[$email])) {
        session_start();
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $email;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}

include 'header.php';
?>

<section id="login-form" style="padding: 2rem 0;">
    <div class="container" style="max-width: 600px;">
        <h2 style="text-align: center;">Login to Your Account</h2>
        <?php if ($error): ?>
            <p style="color: red; text-align: center;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-accent" style="width: 100%;">Login</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;">
            Don't have an account? <a href="register.php">Register here</a>.
        </p>
    </div>
</section>

<?php include 'footer.php'; ?>
