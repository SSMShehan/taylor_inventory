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
    $saleID = $_GET['id'];
    
    // Delete the sale record
    $delete_stmt = $conn->prepare("DELETE FROM Sales WHERE ID = ?");
    $delete_stmt->bind_param("i", $saleID);
    
    if ($delete_stmt->execute()) {
        $message = 'Sale record deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error deleting sale record: ' . $delete_stmt->error;
        $messageType = 'error';
    }
    $delete_stmt->close();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Handle sale creation
        $orderID = $_POST['OrderID'];
        $saleDate = $_POST['SaleDate'];
        $totalAmount = $_POST['TotalAmount'];
        $paymentMethod = $_POST['PaymentMethod'];
        
        $sql = "INSERT INTO Sales (OrderID, SaleDate, TotalAmount, PaymentMethod) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssds", $orderID, $saleDate, $totalAmount, $paymentMethod);
            if ($stmt->execute()) {
                $message = 'Sale record created successfully!';
                $messageType = 'success';
                // Reset to first page after creation
                $current_page = 1;
                $offset = 0;
            } else {
                $message = 'Error creating sale record: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Database error: ' . $conn->error;
            $messageType = 'error';
        }
    } elseif (isset($_POST['update'])) {
        // Handle sale update
        $saleID = $_POST['SaleID'];
        $orderID = $_POST['OrderID'];
        $saleDate = $_POST['SaleDate'];
        $totalAmount = $_POST['TotalAmount'];
        $paymentMethod = $_POST['PaymentMethod'];
        
        $sql = "UPDATE Sales SET 
                OrderID = ?, 
                SaleDate = ?, 
                TotalAmount = ?, 
                PaymentMethod = ?
                WHERE ID = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssdsi", $orderID, $saleDate, $totalAmount, $paymentMethod, $saleID);
            if ($stmt->execute()) {
                $message = 'Sale record updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating sale record: ' . $stmt->error;
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
$count_result = $conn->query("SELECT COUNT(*) AS total FROM Sales");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch all order IDs for dropdowns
$sql_orders = "SELECT OrderID FROM Orders ORDER BY OrderID";
$order_result = $conn->query($sql_orders);
$orders = [];
if ($order_result && $order_result->num_rows > 0) {
    while ($order_row = $order_result->fetch_assoc()) {
        $orders[] = $order_row['OrderID'];
    }
}

// Fetch paginated sales details
$sql_sales = "SELECT * FROM Sales ORDER BY ID LIMIT $offset, $records_per_page";
$result = $conn->query($sql_sales);

// Get payment method color class based on payment method value
function getPaymentMethodColorClass($paymentMethod) {
    switch($paymentMethod) {
        case 'Cash':
            return 'payment-cash';
        case 'Card':
            return 'payment-card';
        case 'Bank Transfer':
            return 'payment-bank';
        default:
            return '';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content{
            padding: 50px;
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
        
        .payment-indicator {
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        
        .payment-cash {
            background-color:rgba(30, 219, 74, 0.8);
        }
        
        .payment-card {
            background-color:rgba(0, 123, 255, 0.67);
        }
        
        .payment-bank {
            background-color:rgba(108, 117, 125, 0.85);
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
    <h2>Sales Management</h2>
    <button class="btn btn-create" onclick="document.getElementById('createModal').style.display='flex'">
        <i class="fas fa-plus"></i> Add New Sale
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
            <th>ID</th>
            <th>Order</th>
            <th>Date</th>
            <th>Total Amount</th>
            <th>Payment Method</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $paymentClass = getPaymentMethodColorClass($row['PaymentMethod']);
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                    <td><?php echo htmlspecialchars($row['SaleDate']); ?></td>
                    <td>Rs.<?php echo number_format($row['TotalAmount'], 2); ?></td>
                    <td>
                        <div class="payment-indicator <?php echo $paymentClass; ?>">
                            <?php echo htmlspecialchars($row['PaymentMethod']); ?>
                        </div>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-edit" onclick="openEditModal(
                            '<?php echo $row['ID']; ?>',
                            '<?php echo $row['OrderID']; ?>',
                            '<?php echo $row['SaleDate']; ?>',
                            '<?php echo $row['TotalAmount']; ?>',
                            '<?php echo $row['PaymentMethod']; ?>'
                        )">
                            <i class="fas fa-edit"></i> 
                        </button>
                        <button class="btn btn-delete" onclick="confirmDelete('<?php echo $row['ID']; ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No sales found</td>
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
        <h3>Edit Sale</h3>
        <form method="POST" action="">
            <input type="hidden" name="update" value="1">
            <input type="hidden" id="editSaleID" name="SaleID">
            
            <div class="form-group">
                <label for="editOrderID">Order ID:</label>
                <select id="editOrderID" name="OrderID" required>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?php echo htmlspecialchars($order); ?>"><?php echo htmlspecialchars($order); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="editSaleDate">Sale Date:</label>
                <input type="datetime-local" id="editSaleDate" name="SaleDate" required>
            </div>
            
            <div class="form-group">
                <label for="editTotalAmount">Total Amount:</label>
                <input type="number" id="editTotalAmount" name="TotalAmount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="editPaymentMethod">Payment Method:</label>
                <select id="editPaymentMethod" name="PaymentMethod" required>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
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
        <h3>Add New Sale</h3>
        <form method="POST" action="">
            <input type="hidden" name="create" value="1">
            
            <div class="form-group">
                <label for="createOrderID">Order ID:</label>
                <select id="createOrderID" name="OrderID" required>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?php echo htmlspecialchars($order); ?>"><?php echo htmlspecialchars($order); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="createSaleDate">Sale Date:</label>
                <input type="datetime-local" id="createSaleDate" name="SaleDate" required>
            </div>
            
            <div class="form-group">
                <label for="createTotalAmount">Total Amount:</label>
                <input type="number" id="createTotalAmount" name="TotalAmount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="createPaymentMethod">Payment Method:</label>
                <select id="createPaymentMethod" name="PaymentMethod" required>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
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
function openEditModal(id, orderID, saleDate, totalAmount, paymentMethod) {
    document.getElementById('editSaleID').value = id;
    
    // Set the correct Order ID option as selected
    const orderSelect = document.getElementById('editOrderID');
    for (let i = 0; i < orderSelect.options.length; i++) {
        if (orderSelect.options[i].value === orderID) {
            orderSelect.selectedIndex = i;
            break;
        }
    }
    
    // Format datetime for the input field
    let formattedDate = saleDate.replace(' ', 'T');
    document.getElementById('editSaleDate').value = formattedDate;
    
    document.getElementById('editTotalAmount').value = totalAmount;
    document.getElementById('editPaymentMethod').value = paymentMethod;
    
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
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this sale record? This action cannot be undone.')) {
        window.location.href = '?delete=1&id=' + id;
    }
}

// Set default date and time for create form
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('createSaleDate').value = formattedDateTime;
});
</script>

<?php 
$conn->close();
?>