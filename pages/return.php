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
    $returnID = $_GET['id'];
    
    // Proceed with return deletion
    $delete_stmt = $conn->prepare("DELETE FROM Returns WHERE ID = ?");
    $delete_stmt->bind_param("i", $returnID);
    
    if ($delete_stmt->execute()) {
        $message = 'Return record deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error deleting return record: ' . $delete_stmt->error;
        $messageType = 'error';
    }
    $delete_stmt->close();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Handle return creation
        $productID = $_POST['ProductID'];
        $returnDate = $_POST['ReturnDate'];
        $reason = $_POST['Reason'];
        $refundAmount = $_POST['RefundAmount'];
        
        $sql = "INSERT INTO Returns (ProductID, ReturnDate, Reason, RefundAmount) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssd", $productID, $returnDate, $reason, $refundAmount);
            if ($stmt->execute()) {
                $message = 'Return record created successfully!';
                $messageType = 'success';
                // Reset to first page after creation
                $current_page = 1;
                $offset = 0;
            } else {
                $message = 'Error creating return record: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Database error: ' . $conn->error;
            $messageType = 'error';
        }
    } elseif (isset($_POST['update'])) {
        // Handle return update
        $returnID = $_POST['ID'];
        $productID = $_POST['ProductID'];
        $returnDate = $_POST['ReturnDate'];
        $reason = $_POST['Reason'];
        $refundAmount = $_POST['RefundAmount'];
        
        $sql = "UPDATE Returns SET 
                ProductID = ?, 
                ReturnDate = ?, 
                Reason = ?, 
                RefundAmount = ?
                WHERE ID = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssdi", $productID, $returnDate, $reason, $refundAmount, $returnID);
            if ($stmt->execute()) {
                $message = 'Return record updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating return record: ' . $stmt->error;
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
$count_result = $conn->query("SELECT COUNT(*) AS total FROM Returns");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch paginated return details
$sql_returns = "SELECT * FROM Returns LIMIT $offset, $records_per_page";
$result = $conn->query($sql_returns);

// Get reason color class based on reason value
function getReasonColorClass($reason) {
    switch($reason) {
        case 'Size':
            return 'reason-size';
        case 'Quality':
            return 'reason-quality';
        case 'Color':
            return 'reason-color';
        case 'Other':
            return 'reason-other';
        default:
            return '';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Returns Management</title>
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
        
        .reason-indicator {
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        
        .reason-size {
            background-color:rgba(78, 114, 223, 0.81);
        }
        
        .reason-quality {
            background-color:rgba(246, 194, 62, 0.84);
        }
        
        .reason-color {
            background-color:rgba(54, 184, 204, 0.83);
        }
        
        .reason-other {
            background-color:rgba(133, 135, 150, 0.81);
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
    <h2>Returns Management</h2>
    <button class="btn btn-create" onclick="document.getElementById('createModal').style.display='flex'">
        <i class="fas fa-plus"></i> Add New Return
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
            <th>Product ID</th>
            <th>Return Date</th>
            <th>Reason</th>
            <th>Refund Amount</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $reasonClass = getReasonColorClass($row['Reason']);
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ProductID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ReturnDate']); ?></td>
                    <td>
                        <div class="reason-indicator <?php echo $reasonClass; ?>">
                            <?php echo htmlspecialchars($row['Reason']); ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars(number_format($row['RefundAmount'], 2)); ?></td>
                    <td class="action-buttons">
                        <button class="btn btn-edit" onclick="openEditModal(
                            '<?php echo $row['ID']; ?>',
                            '<?php echo $row['ProductID']; ?>',
                            '<?php echo $row['ReturnDate']; ?>',
                            '<?php echo $row['Reason']; ?>',
                            '<?php echo $row['RefundAmount']; ?>'
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
                <td colspan="6">No return records found</td>
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
        <h3>Edit Return Record</h3>
        <form method="POST" action="">
            <input type="hidden" name="update" value="1">
            <input type="hidden" id="editID" name="ID">
            
            <div class="form-group">
                <label for="editProductID">Product ID:</label>
                <input type="text" id="editProductID" name="ProductID" required>
            </div>
            
            <div class="form-group">
                <label for="editReturnDate">Return Date:</label>
                <input type="datetime-local" id="editReturnDate" name="ReturnDate" required>
            </div>
            
            <div class="form-group">
                <label for="editReason">Reason:</label>
                <select id="editReason" name="Reason" required>
                    <option value="Size">Size</option>
                    <option value="Quality">Quality</option>
                    <option value="Color">Color</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="editRefundAmount">Refund Amount:</label>
                <input type="number" id="editRefundAmount" name="RefundAmount" step="0.01" required>
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
        <h3>Add New Return</h3>
        <form method="POST" action="">
            <input type="hidden" name="create" value="1">
            
            <div class="form-group">
                <label for="createProductID">Product ID:</label>
                <input type="text" id="createProductID" name="ProductID" required>
            </div>
            
            <div class="form-group">
                <label for="createReturnDate">Return Date:</label>
                <input type="datetime-local" id="createReturnDate" name="ReturnDate" required>
            </div>
            
            <div class="form-group">
                <label for="createReason">Reason:</label>
                <select id="createReason" name="Reason" required>
                    <option value="Size" selected>Size</option>
                    <option value="Quality">Quality</option>
                    <option value="Color">Color</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="createRefundAmount">Refund Amount:</label>
                <input type="number" id="createRefundAmount" name="RefundAmount" step="0.01" required>
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
// Function to format datetime for input fields
function formatDateTimeForInput(dateTimeStr) {
    if (!dateTimeStr) return '';
    
    // Create a Date object from the datetime string
    const date = new Date(dateTimeStr);
    
    // Format the date to YYYY-MM-DDThh:mm (format required by datetime-local input)
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Function to open edit modal with data
function openEditModal(id, productId, returnDate, reason, refundAmount) {
    document.getElementById('editID').value = id;
    document.getElementById('editProductID').value = productId;
    document.getElementById('editReturnDate').value = formatDateTimeForInput(returnDate);
    document.getElementById('editReason').value = reason;
    document.getElementById('editRefundAmount').value = refundAmount;
    
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
    if (confirm('Are you sure you want to delete this return record? This action cannot be undone.')) {
        window.location.href = '?delete=1&id=' + id;
    }
}

// Set current date and time for the create form
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('createReturnDate').value = formattedDateTime;
});
</script>

<?php 
$conn->close();
?>