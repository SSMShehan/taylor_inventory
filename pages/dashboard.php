<?php
include '../addPhp/navBar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Sales Management System</title>
    <style>
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

/* Dashboard grid styles */
.dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 30px;
}

.card {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 180px;
    text-decoration: none;
    color: #333;
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.card-icon {
    font-size: 64px;
    margin-bottom: 15px;
}

.card-title {
    font-size: 18px;
    font-weight: bold;
    text-align: center;
}

/* Icons */
.icon {
    display: inline-block;
    width: 24px;
    height: 24px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
}

.dashboard .icon {
    width: 64px;
    height: 64px;
}
    </style>
</head>
<body>
        <!-- Dashboard Section -->
        <div id="dashboard-section" class="dashboard section active">
            <a href="customer.php" class="card" data-section="customer">
                <div class="card-icon">
                    <span class="icon"><i class="fas fa-user"></i></span>
                </div>
                <div class="card-title">Customer</div>
            </a>
            
            <a href="order.php" class="card" data-section="order">
                <div class="card-icon">
                    <span class="icon"><i class="fas fa-shopping-cart"></i></span>
                </div>
                <div class="card-title">Order</div>
            </a>
            
            <a href="sales.php" class="card" data-section="sales">
                <div class="card-icon">
                    <span class="icon"><i class="fas fa-chart-line"></i></span>
                </div>
                <div class="card-title">Sales</div>
            </a>
            
            <a href="stock.php" class="card" data-section="stock">
                <div class="card-icon">
                <span class="icon"><i class="fas fa-box"></i></span>
                </div>
                <div class="card-title">Stock</div>
            </a>
            
            <a href="supplier.php" class="card" data-section="supplier">
                <div class="card-icon">
                    <span class="icon"><i class="fas fa-truck"></i></span>
                </div>
                <div class="card-title">Supplier</div>
            </a>
            
            <a href="payment and billing.php" class="card" data-section="payment">
                <div class="card-icon">
                    <span class="icon"><i class="fas fa-credit-card"></i></span>
                </div>
                <div class="card-title">Payment and Billing</div>
            </a>
            
            <a href="return.php" class="card" data-section="returns">
                <div class="card-icon">
                    <span class="icon"><i class="fas fa-exchange-alt"></i></span>
                </div>
                <div class="card-title">Returns</div>
            </a>
            
            <a href="setting.php" class="card" data-section="settings">
                <div class="card-icon">
                    <span class="icon"><i class="fas fa-cog"></i></span>
                </div>
                <div class="card-title">Setting</div>
            </a>
        </div>
        
        <!-- Content placeholder for other sections -->
        <div id="customer-section" class="section" style="display:none;">
            <div style="padding: 30px;">
                <h2>Customer Management</h2>
                <p>Customer details and management interface would go here.</p>
            </div>
        </div>
        
        <div id="order-section" class="section" style="display:none;">
            <div style="padding: 30px;">
                <h2>Order Management</h2>
                <p>Order details and management interface would go here.</p>
            </div>
        </div>
        
        <div id="sales-section" class="section" style="display:none;">
            <div style="padding: 30px;">
                <h2>Sales Analytics</h2>
                <p>Sales reports and analytics would go here.</p>
            </div>
        </div>
        
        <div id="stock-section" class="section" style="display:none;">
            <div style="padding: 30px;">
                <h2>Stock Management</h2>
                <p>Inventory control and stock management would go here.</p>
            </div>
        </div>
        
        <div id="supplier-section" class="section" style="display:none;">
            <div style="padding: 30px;">
                <h2>Supplier Management</h2>
                <p>Supplier details and management interface would go here.</p>
            </div>
        </div>
        
        <div id="payment-section" class="section" style="display:none;">
            <div style="padding: 30px;">
                <h2>Payment and Billing</h2>
                <p>Payment processing and billing management would go here.</p>
            </div>
        </div>
        
        <div id="returns-section" class="section" style="display:none;">
            <div style="padding: 30px;">
                <h2>Returns Management</h2>
                <p>Product returns and refund processing would go here.</p>
            </div>
        </div>
        
        <div id="settings-section" class="section" style="display:none;">
            <div style="padding: 30px;">
                <h2>System Settings</h2>
                <p>Application settings and configuration options would go here.</p>
            </div>
        </div>
    </div>
    </div>
    <script>
       
    </script>
</body>
</html>