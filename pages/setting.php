<?php
include '../addPhp/navBar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Information</title>
    <style>
 
        
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
        
        input[type="email"],
        input[type="password"],
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
    </style>
</head>
<body>
    <div class="update-container">
        <h1>UPDATE</h1>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" placeholder="Enter email">
            <p class="hint">We'll never share your email with anyone else.</p>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" placeholder="Enter Password">
        </div>
        
        <div class="form-group">
            <label for="otp">OTP</label>
            <input type="text" id="otp" placeholder="Enter OTP">
        </div>
        
        <div class="button-group">
            <button type="button" class="clear-btn">CLEAR</button>
            <button type="submit" class="submit-btn">SUBMIT</button>
        </div>
    </div>

    <script>
        // JavaScript for form functionality
        document.querySelector('.clear-btn').addEventListener('click', function() {
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('otp').value = '';
        });

        document.querySelector('.submit-btn').addEventListener('click', function() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const otp = document.getElementById('otp').value;
            
            // Basic validation
            if (!email || !password || !otp) {
                alert('Please fill in all fields');
                return;
            }
            
            // Here you would typically send the data to a server
            console.log('Submitting:', { email, password, otp });
            alert('Update request submitted!');
        });
    </script>
</body>
</html>