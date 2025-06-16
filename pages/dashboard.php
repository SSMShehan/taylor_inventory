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
    .dashboard-container {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      max-width: 1900px;
    }

    .dashboard-header {
      position: absolute;
      text-align: center;
      margin: 30px;
      padding: auto;
    }

    .cards-grid {
      margin-left: 30px;
      margin-top: 100px;
      padding: auto;
      display: flex;
      flex-wrap: wrap;
      position: absolute;
      gap: 20px;
    }

    .cards-grid a {
      list-style: none;
      text-decoration: none;
    }

    .dashboard-card {
      display: flex;
      width: 280px;
      height: 100px;
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .dashboard-card:hover {
      transform: translateY(-5px);
    }

    .card-icon1 { font-size: 2rem; color: #FFE102; margin-bottom: 10px; }
    .card-icon2 { font-size: 2rem; color: #3CA200; margin-bottom: 10px; }
    .card-icon3 { font-size: 2rem; color: #002366; margin-bottom: 10px; }
    .card-icon4 { font-size: 2rem; color: #808080; margin-bottom: 10px; }
    .card-icon5 { font-size: 2rem; color: #008080; margin-bottom: 10px; }
    .card-icon6 { font-size: 2rem; color: #FF7F50; margin-bottom: 10px; }
    .card-icon7 { font-size: 2rem; color: #bdbdff; margin-bottom: 10px; }
    .card-icon8 { font-size: 2rem; color: #000000; margin-bottom: 10px; }
    .card-icon9 { font-size: 2rem; color: #FF0000; margin-bottom: 10px; }

    .card-content h3 {
      margin: 5px 0px 0px 10px;
      color: #333;
    }

    .card-value {
      font-size: 1rem;
      font-weight: bold;
      margin-left: 10px;
      color: #2c3e50;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1>Dashboard</h1>
    </div>

    <div class="cards-grid">
      <a href="customer.php">
        <div class="dashboard-card">
          <div class="card-icon1"><i class="fas fa-user"></i></div>
          <div class="card-content">
            <h3>Customer</h3>
          </div>
        </div>
      </a>

      <a href="order.php">
        <div class="dashboard-card">
          <div class="card-icon2"><i class="fas fa-shopping-cart"></i></div>
          <div class="card-content">
            <h3>Order</h3>
          </div>
        </div>
      </a>

      <a href="sales.php">
        <div class="dashboard-card">
          <div class="card-icon3"><i class="fas fa-chart-line"></i></div>
          <div class="card-content">
            <h3>Sales</h3>
          </div>
        </div>
      </a>

      <a href="stock.php">
        <div class="dashboard-card">
          <div class="card-icon4"><i class="fas fa-box"></i></div>
          <div class="card-content">
            <h3>Stock</h3>
          </div>
        </div>
      </a>

      <a href="supplier.php">
        <div class="dashboard-card">
          <div class="card-icon5"><i class="fas fa-truck"></i></div>
          <div class="card-content">
            <h3>Supplier</h3>
          </div>
        </div>
      </a>

      <a href="payment-billing.php">
        <div class="dashboard-card">
          <div class="card-icon6"><i class="fas fa-credit-card"></i></div>
          <div class="card-content">
            <h3>Payment and Billing</h3>
          </div>
        </div>
      </a>

      <a href="return.php">
        <div class="dashboard-card">
          <div class="card-icon7"><i class="fas fa-exchange-alt"></i></div>
          <div class="card-content">
            <h3>Returns</h3>
          </div>
        </div>
      </a>

      <a href="setting.php">
        <div class="dashboard-card">
          <div class="card-icon8"><i class="fas fa-cog"></i></div>
          <div class="card-content">
            <h3>Setting</h3>
          </div>
        </div>
      </a>
    </div>
  </div>
</body>
</html>
