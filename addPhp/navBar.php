<?php
require_once '../config/db_config.php';// Start session
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nadeeka Taylor - Sales Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            
        }
        
        body {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles */
        .sidebar {
            width: 270px;
            background-color: #1a1a1a;
            color: #fff;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        
        .brand {
            display: flex;
            align-items: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid #333;
            margin-bottom: 10px;
        }
        
        .brand-icon {
            color: #e67e22;
            font-size: 24px;
            margin-right: 15px;
        }
        
        .brand-name {
            font-size: 22px;
            font-weight: bold;
            color: #fff;
        }
        
        .search-container {
            position: relative;
            margin: 20px;
        }

        .search-input {
    width: 100%;
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid #444;
    background-color: transparent;
    color: var(--light-text);
    font-size: 16px;
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

/* Menu styles */
.menu {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #aaa;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover {
            color: #e67e22;
            background-color: #252525;
        }
        /* Add this to your CSS */
        .menu-item.active {
        color: #e67e22;
        background-color: #252525;
        }
        
        .menu-icon {
            margin-right: 15px;
            font-size: 16px;
            width: 24px;
            text-align: center;
        }
        
        .menu-text {
            font-size: 16px;
        }
        
        .menu-divider {
            height: 1px;
            background-color: #333;
            margin: 10px 0;
        }

        /* Main content styles */
        .main-content {
            flex-grow: 1;
            background-color: #f0f0f0;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #fff;
            border-bottom: 1px solid #ddd;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: bold;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-info {
            text-align: right;
            margin-right: 15px;
        }
        
        .user-name {
            font-weight: bold;
        }
        
        .user-role {
            color: #777;
            font-size: 14px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #logout{
            color:rgb(255, 120, 120);
        }
        #logout:hover {
            color:rgb(255, 0, 0);
        }
       

    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <div class="brand-icon"><i class="fas fa-fire"></i></div>
            <div class="brand-name">Nadeeka Taylor</div>
        </div>
        
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search">
            <span class="search-icon"><i class="fas fa-search"></i></span>
        </div>
        
        <nav class="menu">
            <a href="../pages/dashboard.php" class="menu-item" data-page="dashboard" >
                <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                <span class="menu-text">Dashboard</span>
            </a>
            <a href="../pages/customer.php" class="menu-item" data-page="customer" >
                <span class="menu-icon"><i class="fas fa-user"></i></span>
                <span class="menu-text">Customer</span>
            </a>
            <a href="../pages/order.php" class="menu-item" data-page="order">
                <span class="menu-icon"><i class="fas fa-shopping-cart"></i></span>
                <span class="menu-text">Order</span>
            </a>
            <a href="../pages/sales.php" class="menu-item" data-page="sales">
                <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                <span class="menu-text">Sales</span>
            </a>
            <a href="../pages/stock.php" class="menu-item" data-page="stock">
                <span class="menu-icon"><i class="fas fa-box"></i></span>
                <span class="menu-text">Stock</span>
            </a>
            <a href="../pages/supplier.php" class="menu-item" data-page="supplier">
                <span class="menu-icon"><i class="fas fa-truck"></i></span>
                <span class="menu-text">Supplier</span>
            </a>
            <a href="../pages/payment-billing.php" class="menu-item" data-page="payment">
                <span class="menu-icon"><i class="fas fa-credit-card"></i></span>
                <span class="menu-text">Payment & Billing</span>
            </a>
            <a href="../pages/return.php" class="menu-item" data-page="return">
                <span class="menu-icon"><i class="fas fa-exchange-alt"></i></span>
                <span class="menu-text">Returns</span>
            </a>
            
            <div class="menu-divider"></div>
            
            <a href="../pages/setting.php" class="menu-item" data-page="setting">
                <span class="menu-icon"><i class="fas fa-cog"></i></span>
                <span class="menu-text">Setting</span>
            </a>
            <a href="../pages/report.php" class="menu-item" data-page="report">
                <span class="menu-icon"><i class="fa-solid fa-newspaper"></i></span>
                <span class="menu-text">report</span>
            </a>
            <a href="../pages/login.php" class="menu-item" id="logout">
            <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>
             <span class="menu-text">Log out</span></a></li>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Sales Management System</h1>
            <div class="user-profile">
                <div class="user-info">
                    <strong>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </strong><br>
                    <samall>
                        <div class="user-role">Administrator</div>
                    </samall>
                </div>
                <div class="user-avatar">
                    <img src="../img/profile.avif" alt="User Avatar">
                </div>
            </div>
        </div>


<script>

document.addEventListener('DOMContentLoaded', function () {
    const menuItems = document.querySelectorAll('.menu-item:not(#logout)');
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
        item.classList.remove('active'); // Reset all
        
        // If this is the current page, highlight it
        if (pageMap[pageId] && currentPage.includes(pageMap[pageId])) {
            item.classList.add('active');
        }
    });

    
    
});
</script>



</body>
</html>