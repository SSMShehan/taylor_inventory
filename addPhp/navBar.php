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
        
        .menu-item:hover, .menu-item.active {
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

    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <div class="brand-icon"><i class="fas fa-fire"></i></div>
            <div class="brand-name">Nadeeka Taylar</div>
        </div>
        
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search">
            <span class="search-icon"><i class="fas fa-search"></i></span>
        </div>
        
        <nav class="menu">
            <a href="../pages/dashboard.php" class="menu-item active" >
                <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                <span class="menu-text">Dashboard</span>
            </a>
            <a href="../pages/customer.php" class="menu-item" >
                <span class="menu-icon"><i class="fas fa-user"></i></span>
                <span class="menu-text">Customer</span>
            </a>
            <a href="../pages/order.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-shopping-cart"></i></span>
                <span class="menu-text">Order</span>
            </a>
            <a href="../pages/sales.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                <span class="menu-text">Sales</span>
            </a>
            <a href="../pages/stock.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-box"></i></span>
                <span class="menu-text">Stock</span>
            </a>
            <a href="../pages/supplier.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-truck"></i></span>
                <span class="menu-text">Supplier</span>
            </a>
            <a href="../pages/payment and billing.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-credit-card"></i></span>
                <span class="menu-text">Payment & Billing</span>
            </a>
            <a href="../pages/return.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-exchange-alt"></i></span>
                <span class="menu-text">Returns</span>
            </a>
            
            <div class="menu-divider"></div>
            
            <a href="../pages/setting.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-cog"></i></span>
                <span class="menu-text">Setting</span>
            </a>
            <a href="../pages/setting.php" class="menu-item">
                <span class="menu-icon"><i class="fa-solid fa-newspaper"></i></span>
                <span class="menu-text">report</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h1 class="page-title">Sales Management System</h1>
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-name">Claudia Alves</div>
                    <div class="user-role">Administrator</div>
                </div>
                <div class="user-avatar">
                    <img src="../img/profile.avif" alt="User Avatar">
                </div>
            </div>
        </div>

    

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Add event listeners for menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>