<?php
// Include config file
require_once "../config/db_config.php";

// Define variables and initialize with empty values
$username = $new_password = $confirm_password = "";
$username_err = $new_password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        $username = trim($_POST["username"]);
    }

    // Validate new password
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Please enter the new password.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Password must have at least 6 characters.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm the password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }

    // If there are no errors, proceed with the password reset
    if(empty($username_err) && empty($new_password_err) && empty($confirm_password_err)){
        
        // Prepare a select statement to check if username exists
        $sql = "SELECT id FROM users WHERE username = ?";

        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            // Execute the query
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                // Check if username exists
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $user_id);
                    if(mysqli_stmt_fetch($stmt)){
                        // Username exists, proceed with updating the password
                        $sql_update = "UPDATE users SET password = ? WHERE id = ?";

                        if($stmt_update = mysqli_prepare($conn, $sql_update)){
                            mysqli_stmt_bind_param($stmt_update, "si", $param_password, $param_id);

                            // Set parameters
                            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $param_id = $user_id;

                            // Execute the statement
                            if(mysqli_stmt_execute($stmt_update)){
                                // Password updated successfully. Redirect to login page
                                session_destroy();
                                header("location: login.php");
                                exit();
                            } else{
                                echo "Oops! Something went wrong. Please try again later.";
                            }

                            // Close update statement
                            mysqli_stmt_close($stmt_update);
                        }

                    }
                } else{
                    $username_err = "No account found with that username.";
                }
            }

            // Close select statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($conn);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            height: 100vh;
            overflow: hidden;
        }
        
        .container {
            display: flex;
            height: 100vh;
            width: 100%;
        }
        
        .left-panel {
            width: 50%;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            border-right: 2px solid #eaeaea;
        }
        
        .right-panel {
            width: 50%;
            background-color: #1a1a1a;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        
        .illustration {
            max-width: 85%;
            height: auto;
        }
        
        .reset-form {
            width: 100%;
            max-width: 400px;
        }
        
        .reset-title {
            color: #e67e22;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 400;
            color: white;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: white;
            font-size: 14px;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .invalid-feedback {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #4a332e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            margin-top: 20px;
        }
        
        .btn:hover {
            background-color: #3a2a26;
        }
        
        .form-footer {
            margin-top: 20px;
            text-align: center;
            color: white;
        }
        
        .form-footer a {
            color: #e67e22;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .action-links {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }
        
        .action-links a {
            color: #aaa;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }
        
        .action-links a:hover {
            color: #e67e22;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <img src="../img/tailor-illustration.png" alt="Tailor Illustration" class="illustration">
        </div>
        <div class="right-panel">
            <div class="reset-form">
                <h1 class="reset-title">NADEEKA TAYLOR</h1>
                
                <p style="text-align: center; margin-bottom: 20px;">Reset Your Password</p>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                        <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                    
                    <input type="submit" class="btn" value="Reset Password">
                    
                    <div class="action-links">
                        <a href="login.php">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>