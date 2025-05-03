<?php
include '../addphp/navbar.php';
require_once '../config/db_config.php';

// Initialize message variables
$message = '';
$messageType = ''; // 'success' or 'error'

// Pagination setup
$records_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

// Handle delete request
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $itemID = $_GET['id'];
    
    // Check if item has related orders before deletion
    $check_orders = $conn->prepare("SELECT COUNT(*) AS order_count FROM Orders WHERE OrderID = ?");
    $check_orders->bind_param("s", $itemID);
    $check_orders->execute();
    $order_result = $check_orders->get_result();
    $order_count = $order_result->fetch_assoc()['order_count'];
    
    if ($order_count > 0) {
        $message = 'Cannot delete item: Item has ' . $order_count . ' related order(s). Please delete the orders first.';
        $messageType = 'error';
    } else {
        // Proceed with item deletion
        $delete_stmt = $conn->prepare("DELETE FROM PaymentBilling WHERE ItemID = ?");
        $delete_stmt->bind_param("s", $itemID);
        
        if ($delete_stmt->execute()) {
            $message = 'Item deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting item: ' . $delete_stmt->error;
            $messageType = 'error';
        }
        $delete_stmt->close();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Handle item creation
        $name = $_POST['Name'];
        $quantity = $_POST['Quantity'];
        $deliveryDate = $_POST['DeliveryDate'];
        $customerName = $_POST['CustomerName'];
        $basePrice = $_POST['BasePrice'];
        
        // Calculate total amount
        $totalAmount = $basePrice * $quantity;
        
        // Generate a unique ItemID (format: 100, 101, etc.)
        $result = $conn->query("SELECT MAX(CAST(ItemID AS UNSIGNED)) as max_id FROM PaymentBilling");
        $row = $result->fetch_assoc();
        $maxID = $row['max_id'];
        $itemID = $maxID ? $maxID + 1 : 101;
        
        $sql = "INSERT INTO PaymentBilling (ItemID, Name, Quantity, DeliveryDate, CustomerName, BasePrice, TotalAmount) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sissddd", $itemID, $name, $quantity, $deliveryDate, $customerName, $basePrice, $totalAmount);
            if ($stmt->execute()) {
                $message = 'Item created successfully!';
                $messageType = 'success';
                // Reset to first page after creation
                $current_page = 1;
                $offset = 0;
            } else {
                $message = 'Error creating item: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Database error: ' . $conn->error;
            $messageType = 'error';
        }
    } elseif (isset($_POST['update'])) {
        // Handle item update
        $itemID = $_POST['ItemID'];
        $name = $_POST['Name'];
        $quantity = $_POST['Quantity'];
        $deliveryDate = $_POST['DeliveryDate'];
        $customerName = $_POST['CustomerName'];
        $basePrice = $_POST['BasePrice'];
        
        // Calculate total amount
        $totalAmount = $basePrice * $quantity;
        
        $sql = "UPDATE PaymentBilling SET 
                Name = ?, 
                Quantity = ?, 
                DeliveryDate = ?, 
                CustomerName = ?, 
                BasePrice = ?,
                TotalAmount = ?
                WHERE ItemID = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sisdids", $name, $quantity, $deliveryDate, $customerName, $basePrice, $totalAmount, $itemID);
            if ($stmt->execute()) {
                $message = 'Item updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating item: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Database error: ' . $conn->error;
            $messageType = 'error';
        }
    }
}

// Fetch total number of records
$count_result = $conn->query("SELECT COUNT(*) AS total FROM PaymentBilling");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch paginated payment and billing details
$sql_items = "SELECT * FROM PaymentBilling LIMIT $offset, $records_per_page";
$result = $conn->query($sql_items);

// Format currency for display
function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment & Billing Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content{
            padding: 40px;
        }
     
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 40px;
        }
        
        .pagination a, .pagination span, .pagination-button {
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #333;
            border-radius: 4px;
        }
        
        .pagination a:hover, .pagination-button:hover {
            background-color: #f5f5f5;
        }
        
        .pagination .active {
            background-color: #6b4c35;
            color: white;
            border-color: #6b4c35;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        
        .pagination .disabled {
            color: #aaa;
            pointer-events: none;
            cursor: default;
        }
        
        /* New pagination container style */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 30px;
            padding: 5px;
            background-color: #fff;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }
        
        .pagination-info {
            margin: 0 15px;
            font-size: 16px;
        }
        
        .pagination-button {
            background-color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            padding: 10px 20px;
        }
        
        .pagination-button:hover {
            background-color: #f5f5f5;
        }
        
        .pagination-button.disabled {
            color: #aaa;
            cursor: default;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .btn-create {
            background-color: #28a745;
            color: white;
        }
        
        .btn-create:hover {
            background-color: #218838;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
        }
        
        .btn-submit {
            background-color: #007bff;
            color: white;
        }
        
        .btn-submit:hover {
            background-color: #0056b3;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        
        .btn-delete {
            background-color:rgba(220, 53, 70, 0.83);
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #bd2130;
        }
        
        .btn-print {
            background-color: #22c55e;
            color: white;
            padding: 2px 10px;
            border-radius: 15px;
            font-weight: bold;
        }
        
        .btn-print:hover {
            background-color: #16a34a;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: #fff;
            width: 80%;
            max-width: 500px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .message {
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
<div class="content">
<div class="header-container">
    <h2>Payment & Billing Management</h2>
    <button class="btn btn-create" onclick="document.getElementById('createModal').style.display='flex'">
        <i class="fas fa-plus"></i> Add New Item
    </button>
</div>

<?php if ($message): ?>
    <div class="message <?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Item</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Delivery Date</th>
            <th>Customer Name</th>
            <th>Base Price</th>
            <th>Total Amount</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ItemID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($row['DeliveryDate'])); ?></td>
                    <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                    <td><?php echo formatCurrency($row['BasePrice']); ?></td>
                    <td><?php echo formatCurrency($row['TotalAmount']); ?></td>
                    <td class="action-buttons">
                        <button class="btn btn-edit" onclick="openEditModal(
                            '<?php echo $row['ItemID']; ?>',
                            '<?php echo addslashes($row['Name']); ?>',
                            '<?php echo $row['Quantity']; ?>',
                            '<?php echo date('Y-m-d\TH:i', strtotime($row['DeliveryDate'])); ?>',
                            '<?php echo addslashes($row['CustomerName']); ?>',
                            '<?php echo $row['BasePrice']; ?>'
                        )">
                            <i class="fas fa-edit"></i> 
                        </button>
                        <button class="btn btn-delete" onclick="confirmDelete('<?php echo $row['ItemID']; ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="btn btn-print" onclick="window.location.href='print_item.php?id=<?php echo $row['ItemID']; ?>'">
                             <i class="fas fa-print"></i> Print
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No items found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Updated Pagination Navigation -->
<div class="pagination-container">
    <button class="pagination-button <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>" 
        <?php echo ($current_page <= 1) ? 'disabled' : 'onclick="window.location.href=\'?page=' . ($current_page - 1) . '\'"'; ?>>
        Previous
    </button>
    
    <div class="pagination-info">
        <span class="active"><?php echo $current_page; ?></span>
    </div>
    
    <button class="pagination-button <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>" 
        <?php echo ($current_page >= $total_pages) ? 'disabled' : 'onclick="window.location.href=\'?page=' . ($current_page + 1) . '\'"'; ?>>
        Next
    </button>
</div>

<div class="pagination-info" style="text-align: center; margin-top: 10px;">
    Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Item</h3>
        <form method="POST" action="">
            <input type="hidden" name="update" value="1">
            <input type="hidden" id="editItemID" name="ItemID">
            
            <div class="form-group">
                <label for="editName">Name:</label>
                <input type="text" id="editName" name="Name" required>
            </div>
            
            <div class="form-group">
                <label for="editQuantity">Quantity:</label>
                <input type="number" id="editQuantity" name="Quantity" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="editDeliveryDate">Delivery Date:</label>
                <input type="datetime-local" id="editDeliveryDate" name="DeliveryDate" required>
            </div>
            
            <div class="form-group">
                <label for="editCustomerName">Customer Name:</label>
                <input type="text" id="editCustomerName" name="CustomerName" required>
            </div>
            
            <div class="form-group">
                <label for="editBasePrice">Base Price:</label>
                <input type="number" id="editBasePrice" name="BasePrice" step="0.01" min="0" required>
            </div>
            
            <div class="form-group action-buttons">
                <button type="submit" class="btn btn-submit">Update</button>
                <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <h3>Add New Item</h3>
        <form method="POST" action="">
            <input type="hidden" name="create" value="1">
            
            <div class="form-group">
                <label for="createName">Name:</label>
                <input type="text" id="createName" name="Name" required>
            </div>
            
            <div class="form-group">
                <label for="createQuantity">Quantity:</label>
                <input type="number" id="createQuantity" name="Quantity" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="createDeliveryDate">Delivery Date:</label>
                <input type="datetime-local" id="createDeliveryDate" name="DeliveryDate" required>
            </div>
            
            <div class="form-group">
                <label for="createCustomerName">Customer Name:</label>
                <input type="text" id="createCustomerName" name="CustomerName" required>
            </div>
            
            <div class="form-group">
                <label for="createBasePrice">Base Price:</label>
                <input type="number" id="createBasePrice" name="BasePrice" step="0.01" min="0" required>
            </div>
            
            <div class="form-group action-buttons">
                <button type="submit" class="btn btn-submit">Create</button>
                <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
// Function to open edit modal with data
function openEditModal(itemId, name, quantity, deliveryDate, customerName, basePrice) {
    document.getElementById('editItemID').value = itemId;
    document.getElementById('editName').value = name;
    document.getElementById('editQuantity').value = quantity;
    document.getElementById('editDeliveryDate').value = deliveryDate;
    document.getElementById('editCustomerName').value = customerName;
    document.getElementById('editBasePrice').value = basePrice;
    
    document.getElementById('editModal').style.display = 'flex';
}

// Function to close any modal
function closeModal() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        closeModal();
    }
}

// Function to confirm deletion
function confirmDelete(itemId) {
    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        window.location.href = '?delete=1&id=' + itemId;
    }
}
</script>

<?php 
$conn->close();
?>