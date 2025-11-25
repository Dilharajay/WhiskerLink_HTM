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

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhiskerLink - Animal Rescue Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon.png">
</head>

<body>
    <header id="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">🐾</span>
                <span>Whisker<span class="brand">Link</span></span>
            </a>

            <div class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <nav id="main-nav">
                <ul>
                    <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">🏠 Home</a></li>

                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <?php if ($is_admin): ?>
                            <li>
                                <a href="admin-dashboard.php" class="admin-link <?php echo $current_page == 'admin-dashboard.php' ? 'active' : ''; ?>">
                                    👑 Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><a href="rescue-reports.php" class="<?php echo $current_page == 'rescue-reports.php' ? 'active' : ''; ?>">🐕 Animals</a></li>
                        <li><a href="find-volunteers.php" class="<?php echo $current_page == 'find-volunteers.php' ? 'active' : ''; ?>">🤝 Volunteers</a></li>

                        <!-- Profile Dropdown -->
                        <li class="profile-dropdown">
                            <div class="profile-button" id="profile-button">
                                👤
                            </div>
                            <div class="dropdown-menu" id="dropdown-menu">
                                <div class="dropdown-header">
                                    <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></strong>
                                    <?php if ($is_admin): ?>
                                        <span class="admin-badge">ADMIN</span>
                                    <?php endif; ?>
                                    <p>Rescue Hero</p>
                                </div>

                                <?php if ($is_admin): ?>
                                    <a href="admin-dashboard.php" class="<?php echo $current_page == 'admin-dashboard.php' ? 'active' : ''; ?>">
                                        👑 Admin Dashboard
                                    </a>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>

                                <a href="my-submissions.php" class="<?php echo $current_page == 'my-submissions.php' ? 'active' : ''; ?>">
                                    📋 My Submissions
                                </a>
                                <a href="volunteer.php" class="<?php echo $current_page == 'volunteer.php' ? 'active' : ''; ?>">
                                    ✨ Join as Volunteer
                                </a>
                                <a href="report.php" class="<?php echo $current_page == 'report.php' ? 'active' : ''; ?>">
                                    📋 Report Animal
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="logout-link">
                                    🚪 Logout
                                </a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php" class="<?php echo $current_page == 'login.php' ? 'active' : ''; ?>">🔑 Login</a></li>
                        <li><a href="register.php" class="<?php echo $current_page == 'register.php' ? 'active' : ''; ?>">✨ Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <script>
        // Profile Dropdown Hover
        const profileDropdown = document.querySelector('.profile-dropdown');
        const profileButton = document.getElementById('profile-button');
        const dropdownMenu = document.getElementById('dropdown-menu');

        if (profileDropdown && profileButton && dropdownMenu) {
            // Show dropdown on hover
            profileDropdown.addEventListener('mouseenter', function() {
                dropdownMenu.classList.add('show');
                profileButton.classList.add('active');
            });

            // Hide dropdown when mouse leaves
            profileDropdown.addEventListener('mouseleave', function() {
                dropdownMenu.classList.remove('show');
                profileButton.classList.remove('active');
            });

            // Also support click for touch devices
            profileButton.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
                profileButton.classList.toggle('active');
            });
        }

        // Mobile Menu Toggle
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mainNav = document.getElementById('main-nav');

        if (mobileMenuToggle && mainNav) {
            mobileMenuToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                mainNav.classList.toggle('active');
            });
        }

        // Header scroll effect
        const header = document.getElementById('main-header');
        let lastScroll = 0;

        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });
    </script>
    <main>