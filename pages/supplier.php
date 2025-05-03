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
    $supplierID = $_GET['id'];
    
    // For safety, you might want to check if supplier has related records before deletion
    // This is a placeholder - update based on your actual database relationships
    $can_delete = true;
    
    if ($can_delete) {
        // Proceed with supplier deletion
        $delete_stmt = $conn->prepare("DELETE FROM Suppliers WHERE ID = ?");
        $delete_stmt->bind_param("i", $supplierID);
        
        if ($delete_stmt->execute()) {
            $message = 'Supplier deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting supplier: ' . $delete_stmt->error;
            $messageType = 'error';
        }
        $delete_stmt->close();
    } else {
        $message = 'Cannot delete supplier: Supplier has related records.';
        $messageType = 'error';
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Handle supplier creation
        $supplierName = $_POST['supplier_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $specialty = $_POST['specialty'];
        
        $sql = "INSERT INTO Suppliers (SupplierName, Phone, Address, Specialty) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $supplierName, $phone, $address, $specialty);
            if ($stmt->execute()) {
                $message = 'Supplier created successfully!';
                $messageType = 'success';
                // Reset to first page after creation
                $current_page = 1;
                $offset = 0;
            } else {
                $message = 'Error creating supplier: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Database error: ' . $conn->error;
            $messageType = 'error';
        }
    } elseif (isset($_POST['update'])) {
        // Handle supplier update
        $supplierID = $_POST['supplier_id'];
        $supplierName = $_POST['supplier_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $specialty = $_POST['specialty'];
        
        $sql = "UPDATE Suppliers SET 
                SupplierName = ?, 
                Phone = ?, 
                Address = ?,
                Specialty = ?
                WHERE ID = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssi", $supplierName, $phone, $address, $specialty, $supplierID);
            if ($stmt->execute()) {
                $message = 'Supplier updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating supplier: ' . $stmt->error;
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
$count_result = $conn->query("SELECT COUNT(*) AS total FROM Suppliers");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch paginated supplier details
$sql_suppliers = "SELECT * FROM Suppliers LIMIT $offset, $records_per_page";
$result = $conn->query($sql_suppliers);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Supplier Management</title>
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
        
        .specialty-badge {
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        
        .specialty-fabric {
            background-color:rgba(30, 219, 74, 0.8);
        }
        
        .specialty-trim {
            background-color:rgba(33, 149, 243, 0.69);
        }
        
        .specialty-accessory {
            background-color:rgba(155, 39, 176, 0.67);
        }
        
        .specialty-other {
            background-color: #FF9800;
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
    <h2>Supplier Management</h2>
    <button class="btn btn-create" onclick="document.getElementById('createModal').style.display='flex'">
        <i class="fas fa-plus"></i> Add New Supplier
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
            <th>Supplier Name</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Specialty</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $specialtyClass = 'specialty-other';
                if(strtolower($row['Specialty']) == 'fabric') {
                    $specialtyClass = 'specialty-fabric';
                } else if(strtolower($row['Specialty']) == 'trim') {
                    $specialtyClass = 'specialty-trim';
                } else if(strtolower($row['Specialty']) == 'accessory') {
                    $specialtyClass = 'specialty-accessory';
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                    <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['Address']); ?></td>
                    <td>
                        <div class="specialty-badge <?php echo $specialtyClass; ?>">
                            <?php echo htmlspecialchars($row['Specialty']); ?>
                        </div>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-edit" onclick="openEditModal(
                            '<?php echo $row['ID']; ?>',
                            '<?php echo addslashes($row['SupplierName']); ?>',
                            '<?php echo addslashes($row['Phone']); ?>',
                            '<?php echo addslashes($row['Address']); ?>',
                            '<?php echo $row['Specialty']; ?>'
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
                <td colspan="6">No suppliers found</td>
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
        <h3>Edit Supplier</h3>
        <form method="POST" action="">
            <input type="hidden" name="update" value="1">
            <input type="hidden" id="editSupplierID" name="supplier_id">
            
            <div class="form-group">
                <label for="editSupplierName">Supplier Name:</label>
                <input type="text" id="editSupplierName" name="supplier_name" required>
            </div>
            
            <div class="form-group">
                <label for="editPhone">Phone:</label>
                <input type="text" id="editPhone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="editAddress">Address:</label>
                <input type="text" id="editAddress" name="address" required>
            </div>
            
            <div class="form-group">
                <label for="editSpecialty">Specialty:</label>
                <select id="editSpecialty" name="specialty" required>
                    <option value="Fabric">Fabric</option>
                    <option value="Trim">Trim</option>
                    <option value="Accessory">Accessory</option>
                    <option value="Other">Other</option>
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
        <h3>Add New Supplier</h3>
        <form method="POST" action="">
            <input type="hidden" name="create" value="1">
            
            <div class="form-group">
                <label for="createSupplierName">Supplier Name:</label>
                <input type="text" id="createSupplierName" name="supplier_name" required>
            </div>
            
            <div class="form-group">
                <label for="createPhone">Phone:</label>
                <input type="text" id="createPhone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="createAddress">Address:</label>
                <input type="text" id="createAddress" name="address" required>
            </div>
            
            <div class="form-group">
                <label for="createSpecialty">Specialty:</label>
                <select id="createSpecialty" name="specialty" required>
                    <option value="Fabric" selected>Fabric</option>
                    <option value="Trim">Trim</option>
                    <option value="Accessory">Accessory</option>
                    <option value="Other">Other</option>
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
function openEditModal(id, supplierName, phone, address, specialty) {
    document.getElementById('editSupplierID').value = id;
    document.getElementById('editSupplierName').value = supplierName;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editAddress').value = address;
    document.getElementById('editSpecialty').value = specialty;
    
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
    if (confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
        window.location.href = '?delete=1&id=' + id;
    }
}
</script>

<?php 
$conn->close();
?>