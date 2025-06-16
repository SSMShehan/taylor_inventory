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
    $customerID = $_GET['id'];
    
    // Check if customer has related orders before deletion
    $check_orders = $conn->prepare("SELECT COUNT(*) AS order_count FROM Orders WHERE CustomerID = ?");
    $check_orders->bind_param("s", $customerID);
    $check_orders->execute();
    $order_result = $check_orders->get_result();
    $order_count = $order_result->fetch_assoc()['order_count'];
    
    if ($order_count > 0) {
        $message = 'Cannot delete customer: Customer has ' . $order_count . ' related order(s). Please delete the orders first.';
        $messageType = 'error';
    } else {
        // Proceed with customer deletion
        $delete_stmt = $conn->prepare("DELETE FROM Customers WHERE CustomerID = ?");
        $delete_stmt->bind_param("s", $customerID);
        
        if ($delete_stmt->execute()) {
            $message = 'Customer deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting customer: ' . $delete_stmt->error;
            $messageType = 'error';
        }
        $delete_stmt->close();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Handle customer creation
        $firstName = $_POST['FirstName'];
        $lastName = $_POST['LastName'];
        $phone = $_POST['Phone'];
        $email = $_POST['Email'];
        $address = $_POST['Address'];
        $status = $_POST['Status'];
        
        // Generate a unique CustomerID (format: CUST-xxx)
        $customerID = 'CUST-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO Customers (CustomerID, FirstName, LastName, Phone, Email, Address, RegistrationDate, Status) 
                VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssssss", $customerID, $firstName, $lastName, $phone, $email, $address, $status);
            if ($stmt->execute()) {
                $message = 'Customer created successfully!';
                $messageType = 'success';
                // Reset to first page after creation
                $current_page = 1;
                $offset = 0;
            } else {
                $message = 'Error creating customer: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Database error: ' . $conn->error;
            $messageType = 'error';
        }
    } elseif (isset($_POST['update'])) {
        // Handle customer update
        $customerID = $_POST['CustomerID'];
        $firstName = $_POST['FirstName'];
        $lastName = $_POST['LastName'];
        $phone = $_POST['Phone'];
        $email = $_POST['Email'];
        $address = $_POST['Address'];
        $status = $_POST['Status'];
        
        $sql = "UPDATE Customers SET 
                FirstName = ?, 
                LastName = ?, 
                Phone = ?, 
                Email = ?, 
                Address = ?,
                Status = ?
                WHERE CustomerID = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssssss", $firstName, $lastName, $phone, $email, $address, $status, $customerID);
            if ($stmt->execute()) {
                $message = 'Customer updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating customer: ' . $stmt->error;
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
$count_result = $conn->query("SELECT COUNT(*) AS total FROM Customers");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch paginated customer details
$sql_customers = "SELECT * FROM Customers LIMIT $offset, $records_per_page";
$result = $conn->query($sql_customers);

// Get status color class based on status value
function getStatusColorClass($status) {
    switch($status) {
        case 'Active':
            return 'status-active';
        case 'Inactive':
            return 'status-inactive';
        default:
            return '';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customer Management</title>
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
        
        .status-indicator {
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        
        .status-active {
            background-color:rgba(31, 200, 70, 0.83);
        }
        
        .status-inactive {
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
    <h2>Customer Management</h2>
    <button class="btn btn-create" onclick="document.getElementById('createModal').style.display='flex'">
        <i class="fas fa-plus"></i> Add New Customer
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
            <th>Customer ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Address</th>
            <th>Registration Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $statusClass = getStatusColorClass($row['Status']);
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['CustomerID']); ?></td>
                    <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
                    <td><?php echo htmlspecialchars($row['LastName']); ?></td>
                    <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                    <td><?php echo htmlspecialchars($row['Address']); ?></td>
                    <td><?php echo htmlspecialchars($row['RegistrationDate']); ?></td>
                    <td>
                        <div class="status-indicator <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($row['Status']); ?>
                        </div>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-edit" onclick="openEditModal(
                            '<?php echo $row['CustomerID']; ?>',
                            '<?php echo $row['FirstName']; ?>',
                            '<?php echo $row['LastName']; ?>',
                            '<?php echo $row['Phone']; ?>',
                            '<?php echo $row['Email']; ?>',
                            '<?php echo addslashes($row['Address']); ?>',
                            '<?php echo $row['Status']; ?>'
                        )">
                            <i class="fas fa-edit"></i> 
                        </button>
                        <button class="btn btn-delete" onclick="confirmDelete('<?php echo $row['CustomerID']; ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No customers found</td>
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
        <h3>Edit Customer</h3>
        <form method="POST" action="">
            <input type="hidden" name="update" value="1">
            <input type="hidden" id="editCustomerID" name="CustomerID">
            
            <div class="form-group">
                <label for="editFirstName">First Name:</label>
                <input type="text" id="editFirstName" name="FirstName" required>
            </div>
            
            <div class="form-group">
                <label for="editLastName">Last Name:</label>
                <input type="text" id="editLastName" name="LastName" required>
            </div>
            
            <div class="form-group">
                <label for="editPhone">Phone:</label>
                <input type="text" id="editPhone" name="Phone" required>
            </div>
            
            <div class="form-group">
                <label for="editEmail">Email:</label>
                <input type="email" id="editEmail" name="Email" required>
            </div>
            
            <div class="form-group">
                <label for="editAddress">Address:</label>
                <input type="text" id="editAddress" name="Address" required>
            </div>
            
            <div class="form-group">
                <label for="editStatus">Status:</label>
                <select id="editStatus" name="Status" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
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
        <h3>Add New Customer</h3>
        <form method="POST" action="">
            <input type="hidden" name="create" value="1">
            
            <div class="form-group">
                <label for="createFirstName">First Name:</label>
                <input type="text" id="createFirstName" name="FirstName" required>
            </div>
            
            <div class="form-group">
                <label for="createLastName">Last Name:</label>
                <input type="text" id="createLastName" name="LastName" required>
            </div>
            
            <div class="form-group">
                <label for="createPhone">Phone:</label>
                <input type="text" id="createPhone" name="Phone" required>
            </div>
            
            <div class="form-group">
                <label for="createEmail">Email:</label>
                <input type="email" id="createEmail" name="Email" required>
            </div>
            
            <div class="form-group">
                <label for="createAddress">Address:</label>
                <input type="text" id="createAddress" name="Address" required>
            </div>
            
            <div class="form-group">
                <label for="createStatus">Status:</label>
                <select id="createStatus" name="Status" required>
                    <option value="Active" selected>Active</option>
                    <option value="Inactive">Inactive</option>
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
function openEditModal(customerId, firstName, lastName, phone, email, address, status) {
    document.getElementById('editCustomerID').value = customerId;
    document.getElementById('editFirstName').value = firstName;
    document.getElementById('editLastName').value = lastName;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editEmail').value = email;
    document.getElementById('editAddress').value = address;
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

// Function to confirm deletion
function confirmDelete(customerId) {
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        window.location.href = '?delete=1&id=' + customerId;
    }
}
</script>

<?php 
$conn->close();
?>