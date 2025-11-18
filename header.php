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
        }
        
        nav ul li a:hover {
            background: #f5f5f5;
            color: #667eea;
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
        }
    </style>
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
                    <li><a href="index.php">🏠 Home</a></li>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <?php if ($is_admin): ?>
                            <li>
                                <a href="admin-dashboard.php" class="admin-link">
                                    👑 Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><a href="rescue-reports.php">🐕 Animals</a></li>
                        <li><a href="find-volunteers.php">🤝 Volunteers</a></li>
                        <li><a href="my-submissions.php">📋 My Submissions</a></li>
                        <li><a href="volunteer.php">✨ Join</a></li>
                        <li><a href="report.php">📋 Report</a></li>
                        <li>
                            <a href="logout.php">
                                🚪 Logout
                                <?php if ($is_admin): ?>
                                    <span class="admin-badge">ADMIN</span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php">🔑 Login</a></li>
                        <li><a href="register.php">✨ Register</a></li>
                    <?php endif; ?>
                    <li><a href="contact.php">📧 Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>