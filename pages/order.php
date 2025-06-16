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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Handle order creation
        $customerID = $_POST['CustomerID'];
        $totalAmount = $_POST['TotalAmount'];
        $discount = $_POST['Discount'];
        $taxAmount = $_POST['TaxAmount'];
        $estimateDeliveryDate = $_POST['EstimateDeliveryDate'];
        $status = $_POST['Status'];
        
        // Generate a unique OrderID (format: ORD-xxxxx)
        $orderID = 'ORD-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        // Validate foreign keys before insertion
        if (!validateCustomerExists($conn, $customerID)) {
            $message = 'Error: Customer ID does not exist';
            $messageType = 'error';
        } else {
            $sql = "INSERT INTO Orders (OrderID, CustomerID, OrderDate, TotalAmount, Discount, TaxAmount, EstimateDeliveryDate, Status) 
                    VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssdddss", $orderID, $customerID, $totalAmount, $discount, $taxAmount, $estimateDeliveryDate, $status);
                if ($stmt->execute()) {
                    $message = 'Order created successfully!';
                    $messageType = 'success';
                    // Reset to first page after creation
                    $current_page = 1;
                    $offset = 0;
                } else {
                    $message = 'Error creating order: ' . $stmt->error;
                    $messageType = 'error';
                }
                $stmt->close();
            } else {
                $message = 'Database error: ' . $conn->error;
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['update'])) {
        // Handle order update
        $orderID = $_POST['OrderID'];
        $customerID = $_POST['CustomerID'];
        $totalAmount = $_POST['TotalAmount'];
        $discount = $_POST['Discount'];
        $taxAmount = $_POST['TaxAmount'];
        $estimateDeliveryDate = $_POST['EstimateDeliveryDate'];
        $deliveryDate = !empty($_POST['DeliveryDate']) ? $_POST['DeliveryDate'] : null;
        $status = $_POST['Status'];
        
        // Validate foreign keys before update
        if (!validateCustomerExists($conn, $customerID)) {
            $message = 'Error: Customer ID does not exist';
            $messageType = 'error';
        } else {
            // SQL statement with conditional DeliveryDate handling
            if ($deliveryDate) {
                $sql = "UPDATE Orders SET 
                        CustomerID = ?, 
                        TotalAmount = ?, 
                        Discount = ?, 
                        TaxAmount = ?, 
                        EstimateDeliveryDate = ?,
                        DeliveryDate = ?,
                        Status = ?
                        WHERE OrderID = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdddssss", $customerID, $totalAmount, $discount, $taxAmount, $estimateDeliveryDate, $deliveryDate, $status, $orderID);
            } else {
                $sql = "UPDATE Orders SET 
                        CustomerID = ?, 
                        TotalAmount = ?, 
                        Discount = ?, 
                        TaxAmount = ?, 
                        EstimateDeliveryDate = ?,
                        DeliveryDate = NULL,
                        Status = ?
                        WHERE OrderID = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdddsss", $customerID, $totalAmount, $discount, $taxAmount, $estimateDeliveryDate, $status, $orderID);
            }
            
            if ($stmt) {
                if ($stmt->execute()) {
                    $message = 'Order updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating order: ' . $stmt->error;
                    $messageType = 'error';
                }
                $stmt->close();
            } else {
                $message = 'Database error: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
}

// Fetch total number of records
$count_result = $conn->query("SELECT COUNT(*) AS total FROM Orders");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch paginated order details
$sql_orders = "SELECT * FROM Orders LIMIT $offset, $records_per_page";
$result = $conn->query($sql_orders);

// Fetch customers for dropdowns
$customers = $conn->query("SELECT CustomerID, CONCAT(FirstName, ' ', LastName) AS Name FROM Customers");

// Validation functions
function validateCustomerExists($conn, $customerID) {
    $stmt = $conn->prepare("SELECT CustomerID FROM Customers WHERE CustomerID = ?");
    $stmt->bind_param("s", $customerID);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

// Helper function to get customer name
function getCustomerName($conn, $customerID) {
    $stmt = $conn->prepare("SELECT CONCAT(FirstName, ' ', LastName) AS Name FROM Customers WHERE CustomerID = ?");
    $stmt->bind_param("s", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['Name'] ?? 'Unknown';
}

// Get status color class based on status value
function getStatusColorClass($status) {
    switch($status) {
        case 'Delivered':
            return 'status-delivered';
        case 'In Progress':
        case 'Processing':
            return 'status-in-progress';
        case 'Pending':
            return 'status-pending';
        case 'Shipped':
            return 'status-shipped';
        case 'Cancelled':
            return 'status-cancelled';
        default:
            return '';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

        .content{
            flex: auto;
            padding: 20px;
            transition: margin-left 0.3s;
            margin-top: 70px; 
        }
 
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
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
        
        .status-indicator {
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        
        .status-delivered {
            background-color:rgba(30, 219, 74, 0.8);
        }
        
        .status-in-progress {
            background-color:rgb(50, 198, 220);
        }
        
        .status-pending {
            background-color:rgba(255, 193, 7, 0.8);
            color:rgb(56, 63, 70);
        }
        
        .status-shipped {
            background-color:rgba(0, 73, 152, 0.79);
        }
        
        .status-cancelled {
            background-color:rgba(255, 0, 25, 0.71);
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
            background-color: #007bff;
            color: white;
        }
        
        .btn-create:hover {
            background-color:  #0056b3;
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
    <h2>Order Management</h2>
    <button class="btn btn-create" onclick="document.getElementById('createModal').style.display='flex'">
        <i class="fas fa-plus"></i> Create New Order
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
            <th>Order ID</th>
            <th>Customer</th>
            <th>Order Date</th>
            <th>Total Amount</th>
            <th>Discount</th>
            <th>Tax Amount</th>
            <th>Est. Delivery</th>
            <th>Delivery Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $customerName = getCustomerName($conn, $row['CustomerID']);
                $statusClass = getStatusColorClass($row['Status']);
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                    <td><?php echo htmlspecialchars($customerName); ?></td>
                    <td><?php echo htmlspecialchars($row['OrderDate']); ?></td>
                    <td>$<?php echo number_format($row['TotalAmount'], 2); ?></td>
                    <td>$<?php echo number_format($row['Discount'], 2); ?></td>
                    <td>$<?php echo number_format($row['TaxAmount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['EstimateDeliveryDate']); ?></td>
                    <td><?php echo $row['DeliveryDate'] ? htmlspecialchars($row['DeliveryDate']) : 'Pending'; ?></td>
                    <td>
                        <div class="status-indicator <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($row['Status']); ?>
                        </div>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-edit" onclick="openEditModal(
                            '<?php echo $row['OrderID']; ?>',
                            '<?php echo $row['CustomerID']; ?>',
                            '<?php echo $row['TotalAmount']; ?>',
                            '<?php echo $row['Discount']; ?>',
                            '<?php echo $row['TaxAmount']; ?>',
                            '<?php echo $row['EstimateDeliveryDate']; ?>',
                            '<?php echo $row['DeliveryDate']; ?>',
                            '<?php echo $row['Status']; ?>'
                        )">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No orders found</td>
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
        <h3>Edit Order</h3>
        <form method="POST" action="">
            <input type="hidden" name="update" value="1">
            <input type="hidden" id="editOrderID" name="OrderID">
            
            <div class="form-group">
                <label for="editCustomerID">Customer:</label>
                <select id="editCustomerID" name="CustomerID" required>
                    <?php 
                    // Reset pointer for customers result
                    $customers->data_seek(0);
                    while ($customer = $customers->fetch_assoc()): ?>
                        <option value="<?php echo $customer['CustomerID']; ?>">
                            <?php echo htmlspecialchars($customer['Name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="editTotalAmount">Total Amount:</label>
                <input type="number" id="editTotalAmount" name="TotalAmount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="editDiscount">Discount:</label>
                <input type="number" id="editDiscount" name="Discount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="editTaxAmount">Tax Amount:</label>
                <input type="number" id="editTaxAmount" name="TaxAmount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="editEstimateDeliveryDate">Estimated Delivery Date:</label>
                <input type="date" id="editEstimateDeliveryDate" name="EstimateDeliveryDate" required>
            </div>
            
            <div class="form-group">
                <label for="editDeliveryDate">Actual Delivery Date (leave blank if pending):</label>
                <input type="date" id="editDeliveryDate" name="DeliveryDate">
            </div>
            
            <div class="form-group">
                <label for="editStatus">Status:</label>
                <select id="editStatus" name="Status" required>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
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
        <h3>Create New Order</h3>
        <form method="POST" action="">
            <input type="hidden" name="create" value="1">
            
            <div class="form-group">
                <label for="createCustomerID">Customer:</label>
                <select id="createCustomerID" name="CustomerID" required>
                    <?php 
                    // Reset pointer for customers result
                    $customers->data_seek(0);
                    while ($customer = $customers->fetch_assoc()): ?>
                        <option value="<?php echo $customer['CustomerID']; ?>">
                            <?php echo htmlspecialchars($customer['Name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="createTotalAmount">Total Amount:</label>
                <input type="number" id="createTotalAmount" name="TotalAmount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="createDiscount">Discount:</label>
                <input type="number" id="createDiscount" name="Discount" step="0.01" value="0.00" required>
            </div>
            
            <div class="form-group">
                <label for="createTaxAmount">Tax Amount:</label>
                <input type="number" id="createTaxAmount" name="TaxAmount" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="createEstimateDeliveryDate">Estimated Delivery Date:</label>
                <input type="date" id="createEstimateDeliveryDate" name="EstimateDeliveryDate" required>
            </div>
            
            <div class="form-group">
                <label for="createStatus">Status:</label>
                <select id="createStatus" name="Status" required>
                    <option value="Pending" selected>Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
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
function openEditModal(orderId, customerId, totalAmount, discount, taxAmount, estDeliveryDate, deliveryDate, status) {
    document.getElementById('editOrderID').value = orderId;
    document.getElementById('editCustomerID').value = customerId;
    document.getElementById('editTotalAmount').value = totalAmount;
    document.getElementById('editDiscount').value = discount;
    document.getElementById('editTaxAmount').value = taxAmount;
    document.getElementById('editEstimateDeliveryDate').value = estDeliveryDate;
    document.getElementById('editDeliveryDate').value = deliveryDate || '';
    document.getElementById('editStatus').value = status;
    
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
</script>

<?php 
$conn->close();
?>