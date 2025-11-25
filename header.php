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
<<<<<<< HEAD
    <link rel="stylesheet" href="css/header.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon.png">
=======
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Header Styles */
        header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        header.scrolled {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #667eea;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        .logo .brand {
            color: #764ba2;
        }
        
        .logo-icon {
            font-size: 32px;
        }
        
        /* Navigation */
        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 5px;
        }
        
        nav ul li {
            position: relative;
        }
        
        nav ul li a {
            padding: 8px 16px;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        
        nav ul li a:hover {
            background: #f5f5f5;
            color: #667eea;
        }
        
        /* Active Page Indicator */
        nav ul li a.active {
            color: #667eea;
        }
        
        nav ul li a.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 16px;
            right: 16px;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 3px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: scaleX(0);
                opacity: 0;
            }
            to {
                transform: scaleX(1);
                opacity: 1;
            }
        }
        
        .admin-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            font-weight: 600 !important;
        }
        
        .admin-link:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 5px;
        }
        
        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
        }
        
        .profile-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s;
            border: 3px solid transparent;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .profile-button:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.5);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .profile-button.active {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 55px;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 15px;
            width: 12px;
            height: 12px;
            background: white;
            transform: rotate(45deg);
        }
        
        .dropdown-header {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        }
        
        .dropdown-header p {
            font-size: 14px;
            color: #666;
            margin-top: 4px;
        }
        
        .dropdown-header strong {
            color: #333;
            font-size: 16px;
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .dropdown-menu a:hover {
            background: linear-gradient(90deg, #667eea10 0%, #764ba210 100%);
            color: #667eea;
            padding-left: 20px;
        }
        
        .dropdown-menu a.active {
            background: linear-gradient(90deg, #667eea15 0%, #764ba215 100%);
            color: #667eea;
            border-left: 3px solid #667eea;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #f0f0f0;
            margin: 4px 0;
        }
        
        .dropdown-menu a.logout-link {
            color: #e74c3c;
            border-top: 1px solid #f0f0f0;
        }
        
        .dropdown-menu a.logout-link:hover {
            background: #fee;
            color: #c0392b;
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 10px;
        }
        
        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: #667eea;
            border-radius: 3px;
            transition: all 0.3s;
        }
        
        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }
        
        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .mobile-menu-toggle {
                display: flex;
            }
            
            nav {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: white;
                transition: left 0.3s ease;
                overflow-y: auto;
            }
            
            nav.active {
                left: 0;
            }
            
            nav ul {
                flex-direction: column;
                padding: 20px;
                gap: 10px;
                align-items: stretch;
            }
            
            nav ul li {
                width: 100%;
            }
            
            nav ul li a {
                width: 100%;
                padding: 15px;
                justify-content: flex-start;
            }
            
            .profile-dropdown {
                width: 100%;
            }
            
            .profile-button {
                width: 100%;
                height: 50px;
                border-radius: 8px;
                justify-content: flex-start;
                padding: 0 15px;
                gap: 10px;
            }
            
            .profile-button::after {
                content: '▼';
                margin-left: auto;
                font-size: 12px;
                transition: transform 0.3s;
            }
            
            .profile-button.active::after {
                transform: rotate(180deg);
            }
            
            .dropdown-menu {
                position: static;
                box-shadow: none;
                border-radius: 0;
                margin-top: 10px;
                background: #f9f9f9;
            }
            
            .dropdown-menu::before {
                display: none;
            }
            
            nav ul li a.active::after {
                bottom: 0;
                left: 0;
                right: auto;
                width: 3px;
                height: 100%;
            }
        }
    </style>
>>>>>>> e67c2d345e83a7950acdbcbcbb5afb30ada1bb3b
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