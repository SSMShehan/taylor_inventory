<?php
require_once '../config/db_config.php';
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

// Get logged-in user's ID
$user_id = $_SESSION["id"];

// Fetch the logged-in user's details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$sqlforname = "SELECT system_short_name FROM system_settings WHERE id = 1";
$resultforname = $conn->query($sqlforname);

if ($resultforname && $resultforname->num_rows > 0) {
    $row = $resultforname->fetch_assoc();
    $companyName = $row['system_short_name'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($companyName); ?> - Sales Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #ECF6FE;
             display: flex;
            min-height: 100vh;
            color: #333;
        }

        /* Top Navigation Bar */
        .navbar {
            background: #2e2e2e;
            color: #ECF6FE;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .left-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logo-icon {
            background: #fff;
            color: #2e2e2e;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .logo-text {
            font-size: 14px;
            font-weight: bold;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: none;
        }

        .right-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            width: 200px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            width: 250px;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
        }

        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .notif-btn, .message-btn {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #d4af37;
            color: #2e2e2e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .profile-name {
            font-weight: 500;
        }

        /* Dashboard container */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            padding-top: 56px; /* To account for fixed navbar */
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: #2e2e2e;
            color: white;
            padding: 20px 0;
            transition: all 0.3s;
            position: fixed;
            height: calc(100vh - 56px);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .menu-items {
            padding: 15px 0;
        }

        .menu-category {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: #7f8c8d;
            margin: 20px 20px 10px;
            letter-spacing: 1px;
        }

        .menu-item {
            list-style: none;
        }

        .menu-link {
            display: flex;
            align-items: center;
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 20px;
            margin: 0 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .menu-link:hover {
            background-color: #ECF6FE;
            color: #000000;
            border-radius: 100px 0px 0px 100px;
        }

        .menu-link.active {
            background-color: #ECF6FE;
            color: #000000;
            border-radius: 100px 0px 0px 100px;
            font-weight: 500;
        }

        .menu-icon {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .menu-text {
            font-size: 15px;
        }

        /* Main content area */
        .main-content {
            flex-grow: 1;
            margin-left: 250px;
            padding: 20px;
            background-color: #ECF6FE;
            min-height: calc(100vh - 56px);
            transition: all 0.3s;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
        }

        .page-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 900;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .search-input {
                width: 150px;
            }

            .search-input:focus {
                width: 180px;
            }

            .profile-name {
                display: none;
            }
        }

        /* Logout link style */
        .logout-link {
            color: #e74c3c;
        }

        .logout-link:hover {
            color: #c0392b;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar">
        <div class="left-section">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="logo-text"><?php echo htmlspecialchars($companyName); ?></div>
            </div>
        </div>

        <div class="right-section">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search...">
                <i class="fas fa-search search-icon"></i>
            </div>
            <button class="notif-btn">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <button class="message-btn">
                <i class="fas fa-envelope"></i>
                <span class="notification-badge">5</span>
            </button>
            <div class="profile">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr(htmlspecialchars($user['username']), 0, 1)); ?>
                </div>
                <span class="profile-name"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Menu</h2>
            </div>
            
            <ul class="menu-items">
                <li class="menu-item">
                    <a href="../pages/dashboard.php" class="menu-link" data-page="dashboard">
                        <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-category">Management</li>
                
                <li class="menu-item">
                    <a href="../pages/customer.php" class="menu-link" data-page="customer">
                        <span class="menu-icon"><i class="fas fa-users"></i></span>
                        <span class="menu-text">Customers</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../pages/order.php" class="menu-link" data-page="order">
                        <span class="menu-icon"><i class="fas fa-shopping-cart"></i></span>
                        <span class="menu-text">Orders</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../pages/sales.php" class="menu-link" data-page="sales">
                        <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="menu-text">Sales</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../pages/stock.php" class="menu-link" data-page="stock">
                        <span class="menu-icon"><i class="fas fa-boxes"></i></span>
                        <span class="menu-text">Inventory</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../pages/supplier.php" class="menu-link" data-page="supplier">
                        <span class="menu-icon"><i class="fas fa-truck"></i></span>
                        <span class="menu-text">Suppliers</span>
                    </a>
                </li>
                
                <li class="menu-category">Transactions</li>
                
                <li class="menu-item">
                    <a href="../pages/payment-billing.php" class="menu-link" data-page="payment">
                        <span class="menu-icon"><i class="fas fa-credit-card"></i></span>
                        <span class="menu-text">Payments</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../pages/return.php" class="menu-link" data-page="return">
                        <span class="menu-icon"><i class="fas fa-exchange-alt"></i></span>
                        <span class="menu-text">Returns</span>
                    </a>
                </li>
                
                <li class="menu-category">System</li>
                
                <li class="menu-item">
                    <a href="../pages/report.php" class="menu-link" data-page="report">
                        <span class="menu-icon"><i class="fas fa-chart-pie"></i></span>
                        <span class="menu-text">Reports</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../pages/setting.php" class="menu-link" data-page="setting">
                        <span class="menu-icon"><i class="fas fa-cog"></i></span>
                        <span class="menu-text">Settings</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../pages/login.php" class="menu-link logout-link">
                        <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>
                        <span class="menu-text">Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content" id="mainContent">
               
            
            <!-- Your page content will go here -->
            <div class="content-area">
                <!-- Dashboard widgets, tables, etc. -->
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on mobile
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Set active menu item based on current page
        const menuItems = document.querySelectorAll('.menu-link:not(.logout-link)');
        const currentPage = window.location.pathname.split('/').pop().toLowerCase();
        
        // Map menu items to their corresponding pages
        const pageMap = {
            'dashboard': 'dashboard.php',
            'customer': 'customer.php',
            'order': 'order.php',
            'sales': 'sales.php',
            'stock': 'stock.php',
            'supplier': 'supplier.php',
            'payment': 'payment-billing.php',
            'return': 'return.php',
            'setting': 'setting.php',
            'report': 'report.php'
        };
        
        menuItems.forEach(item => {
            const pageId = item.getAttribute('data-page');
            
            // Reset active state
            item.classList.remove('active');
            
            // If this is the current page, highlight it
            if (pageMap[pageId] && currentPage.includes(pageMap[pageId])) {
                item.classList.add('active');
            }
        });
        
        // Search functionality
        const searchInput = document.querySelector('.search-input');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnMenuToggle = menuToggle.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnMenuToggle) {
                    sidebar.classList.remove('active');
                }
            }
        });
    });
    </script>
</body>
</html>