<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
$is_admin = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    include_once 'db.php';
    $user_id = $_SESSION['user_id'];
    $admin_check = $conn->query("SELECT * FROM admin WHERE user_id = $user_id");
    if ($admin_check && $admin_check->num_rows > 0) {
        $is_admin = true;
        $_SESSION['is_admin'] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhiskerLink - Animal Rescue Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 5px;
            vertical-align: middle;
        }
        nav ul li a.admin-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s;
        }
        nav ul li a.admin-link:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo">Whisker<span class="brand">Link</span></a>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <?php if ($is_admin): ?>
                            <li>
                                <a href="admin-dashboard.php" class="admin-link">
                                    👑 Admin Dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><a href="rescue-reports.php">Animals</a></li>
                        <li><a href="find-volunteers.php">Volunteers</a></li>
                        <li><a href="volunteer.php">Join</a></li>
                        <li><a href="report.php">Report</a></li>
                        <li>
                            <a href="logout.php">
                                Logout
                                <?php if ($is_admin): ?>
                                    <span class="admin-badge">ADMIN</span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>