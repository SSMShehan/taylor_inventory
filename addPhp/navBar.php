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
    <title>Nadeeka Taylor - Sales Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
/* Additional CSS for the search functionality */
.search-container {
    position: relative;
    margin: 20px;
}

.search-input {
    width: 100%;
    padding: 10px 15px;
    padding-right: 40px;
    border-radius: 8px;
    border: 1px solid #444;
    background-color: #252525;
    color: #fff;
    font-size: 16px;
    transition: all 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: #e67e22;
    box-shadow: 0 0 0 2px rgba(230, 126, 34, 0.2);
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    transition: color 0.3s;
}

.search-input:focus + .search-icon {
    color: #e67e22;
}

.search-results {
    background-color: #252525;
    border: 1px solid #444;
    border-radius: 8px;
    margin-top: 5px;
}

.search-result-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #aaa;
    text-decoration: none;
    transition: all 0.3s;
    border-bottom: 1px solid #333;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover,
.search-result-item:focus {
    background-color: #333;
    color: #e67e22;
    outline: none;
}

.search-result-item:focus-visible {
    box-shadow: 0 0 0 2px #e67e22 inset;
}

/* Animation for search results */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.search-results {
    animation: fadeIn 0.2s ease-out;
}



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
            <div class="brand-name"><?php echo htmlspecialchars($companyName); ?></div>
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
                <span class="menu-text">Report</span>
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


// Enhanced sidebar search functionality
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.search-input');
    const menuItems = document.querySelectorAll('.menu-item:not(#logout)');
    const sidebar = document.querySelector('.sidebar');
    
    // Create a container for search results
    const searchResultsContainer = document.createElement('div');
    searchResultsContainer.classList.add('search-results');
    searchResultsContainer.style.display = 'none';
    searchResultsContainer.style.position = 'absolute';
    searchResultsContainer.style.backgroundColor = '#252525';
    searchResultsContainer.style.width = 'calc(100% - 40px)';
    searchResultsContainer.style.borderRadius = '8px';
    searchResultsContainer.style.marginTop = '5px';
    searchResultsContainer.style.boxShadow = '0 4px 8px rgba(0,0,0,0.3)';
    searchResultsContainer.style.zIndex = '100';
    searchResultsContainer.style.maxHeight = '300px';
    searchResultsContainer.style.overflowY = 'auto';
    
    // Insert the search results container after the search input
    document.querySelector('.search-container').appendChild(searchResultsContainer);
    
    // Create menu map for searching
    const menuMap = [];
    menuItems.forEach(item => {
        const menuText = item.querySelector('.menu-text').textContent.trim();
        const menuIcon = item.querySelector('.menu-icon').innerHTML;
        const menuLink = item.getAttribute('href');
        const dataPage = item.getAttribute('data-page');
        
        menuMap.push({
            text: menuText,
            icon: menuIcon,
            link: menuLink,
            dataPage: dataPage
        });
    });
    
    // Function to filter menu items based on search input
    function filterMenuItems(searchTerm) {
        searchTerm = searchTerm.toLowerCase();
        
        if (!searchTerm) {
            searchResultsContainer.style.display = 'none';
            searchResultsContainer.innerHTML = '';
            return;
        }
        
        const filteredItems = menuMap.filter(item => 
            item.text.toLowerCase().includes(searchTerm)
        );
        
        renderSearchResults(filteredItems);
    }
    
    // Function to render search results
    function renderSearchResults(items) {
        searchResultsContainer.innerHTML = '';
        
        if (items.length === 0) {
            searchResultsContainer.style.display = 'block';
            const noResults = document.createElement('div');
            noResults.style.padding = '15px';
            noResults.style.color = '#aaa';
            noResults.style.textAlign = 'center';
            noResults.textContent = 'No results found';
            searchResultsContainer.appendChild(noResults);
            return;
        }
        
        searchResultsContainer.style.display = 'block';
        
        items.forEach(item => {
            const resultItem = document.createElement('a');
            resultItem.href = item.link;
            resultItem.classList.add('search-result-item');
            resultItem.style.display = 'flex';
            resultItem.style.alignItems = 'center';
            resultItem.style.padding = '12px 15px';
            resultItem.style.color = '#aaa';
            resultItem.style.textDecoration = 'none';
            resultItem.style.transition = 'all 0.3s';
            
            resultItem.innerHTML = `
                <span class="menu-icon" style="margin-right: 15px; font-size: 16px; width: 24px; text-align: center;">
                    ${item.icon}
                </span>
                <span class="menu-text" style="font-size: 16px;">
                    ${item.text}
                </span>
            `;
            
            resultItem.addEventListener('mouseover', function() {
                this.style.backgroundColor = '#333';
                this.style.color = '#e67e22';
            });
            
            resultItem.addEventListener('mouseout', function() {
                this.style.backgroundColor = 'transparent';
                this.style.color = '#aaa';
            });
            
            searchResultsContainer.appendChild(resultItem);
        });
    }
    
    // Add event listener for search input
    searchInput.addEventListener('input', function() {
        filterMenuItems(this.value);
    });
    
    // Close search results when clicking outside
    document.addEventListener('click', function(event) {
        if (!searchResultsContainer.contains(event.target) && event.target !== searchInput) {
            searchResultsContainer.style.display = 'none';
        }
    });
    
    // Show results again when focusing on input
    searchInput.addEventListener('focus', function() {
        if (this.value) {
            filterMenuItems(this.value);
        }
    });
    
    // Handle keyboard navigation in search results
    searchInput.addEventListener('keydown', function(e) {
        if (searchResultsContainer.style.display === 'none') return;
        
        const resultItems = searchResultsContainer.querySelectorAll('.search-result-item');
        if (resultItems.length === 0) return;
        
        const firstResult = resultItems[0];
        const lastResult = resultItems[resultItems.length - 1];
        
        // Find currently focused item
        const focusedItem = document.activeElement;
        let currentIndex = -1;
        
        for (let i = 0; i < resultItems.length; i++) {
            if (resultItems[i] === focusedItem) {
                currentIndex = i;
                break;
            }
        }
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (currentIndex === -1) {
                    firstResult.focus();
                } else if (currentIndex < resultItems.length - 1) {
                    resultItems[currentIndex + 1].focus();
                }
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                if (currentIndex > 0) {
                    resultItems[currentIndex - 1].focus();
                } else if (currentIndex === 0) {
                    searchInput.focus();
                }
                break;
                
            case 'Escape':
                e.preventDefault();
                searchResultsContainer.style.display = 'none';
                searchInput.focus();
                break;
                
            case 'Enter':
                if (currentIndex !== -1) {
                    e.preventDefault();
                    resultItems[currentIndex].click();
                }
                break;
        }
    });
    
    // Initialize menu active state
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