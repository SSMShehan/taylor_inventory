<?php
include '../addphp/navbar.php';
require_once '../config/db_config.php';

// Fetch current system settings
$sql = "SELECT * FROM system_settings WHERE id = 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $systemName = $row['system_name'];
    $systemShortName = $row['system_short_name'];
} else {
    // Default values if no settings exist
    $systemName = "Tailor Management System";
    $systemShortName = "TMS";
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $systemName = $_POST["system_name"];
    $systemShortName = $_POST["system_short_name"];

    // Prepare update statement
    $sql = "UPDATE system_settings SET 
            system_name = ?, 
            system_short_name = ? 
            WHERE id = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $systemName, $systemShortName);

    if ($stmt->execute()) {
        echo "<script>alert('System information updated successfully!'); window.location.href = 'Setting.php';</script>";
    } else {
        echo "<script>alert('Error updating system information: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .update-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            margin-left: 320px;
            margin-top: 50px;
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .hint {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
        }
        
        button {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .clear-btn {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .clear-btn:hover {
            background-color: #e0e0e0;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
        }
        
        .submit-btn:hover {
            background-color: #45a049;
        }
        
        .system-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="update-container">
        <h1 class="system-title"><?php echo htmlspecialchars($systemName); ?></h1>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="system_name">System Name</label>
                <input type="text" id="system_name" name="system_name" 
                       value="<?php echo htmlspecialchars($systemName); ?>" 
                       placeholder="Enter system name">
            </div>
            
            <div class="form-group">
                <label for="system_short_name">System Short Name</label>
                <input type="text" id="system_short_name" name="system_short_name" 
                       value="<?php echo htmlspecialchars($systemShortName); ?>" 
                       maxlength="16" placeholder="Enter short name">
                <p class="hint">Maximum 16 characters</p>
            </div>
            
            <div class="button-group">
                <button type="button" class="clear-btn" onclick="clearForm()">CLEAR</button>
                <button type="submit" class="submit-btn">UPDATE</button>
            </div>
        </form>
    </div>

    <script>
        function clearForm() {
            document.getElementById('system_name').value = '';
            document.getElementById('system_short_name').value = '';
        }
    </script>
</body>
</html>