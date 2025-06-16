<?php
// File: print_item.php
require_once '../config/db_config.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo "No item ID provided";
    exit;
}

$itemID = $_GET['id'];

// Fetch item details
$sql = "SELECT * FROM PaymentBilling WHERE ItemID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $itemID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Item not found";
    exit;
}

$item = $result->fetch_assoc();

// Format currency for display
function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}

// Format date
$formattedDate = date('F j, Y, g:i a', strtotime($item['DeliveryDate']));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo htmlspecialchars($item['ItemID']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #6b4c35;
        }
        .company-details {
            flex: 1;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color:#007bff;
            margin-bottom: 5px;
        }
        .company-address {
            color: #777;
            margin-bottom: 5px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-id {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color:#007bff;
        }
        .date {
            color: #777;
        }
        .customer-details {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color:#007bff;
        }
        .customer-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .amount-table {
            width: 300px;
            margin-left: auto;
        }
        .amount-row td {
            padding: 8px;
        }
        .total-row {
            font-weight: bold;
            font-size: 18px;
        }
        .total-cell {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #777;
            font-size: 14px;
        }
        .print-button {
            margin-top: 20px;
            text-align: center;
        }
        .btn-print {
            background-color:#007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-print:hover {
            background-color:#0056b3;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                padding: 0;
            }
            .invoice-container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-details">
                <div class="company-name">GSM Garment</div>
                <div class="company-address">102 Business Street</div>
                <div class="company-address">Colombo, North, 1200</div>
                <div class="company-address">Phone: (+94) 11 222 2222</div>
                <div class="company-address">Email: gsmgarment@company.com</div>
            </div>
            <div class="invoice-details">
                <div class="invoice-id">INVOICE #<?php echo htmlspecialchars($item['ItemID']); ?></div>
                <div class="date">Date: <?php echo date('F j, Y'); ?></div>
                <div class="date">Delivery Date: <?php echo $formattedDate; ?></div>
            </div>
        </div>
        
        <div class="customer-details">
            <div class="section-title">Bill To:</div>
            <div class="customer-name"><?php echo htmlspecialchars($item['CustomerName']); ?></div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($item['ItemID']); ?></td>
                    <td><?php echo htmlspecialchars($item['Name']); ?></td>
                    <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                    <td><?php echo formatCurrency($item['BasePrice']); ?></td>
                    <td><?php echo formatCurrency($item['TotalAmount']); ?></td>
                </tr>
            </tbody>
        </table>
        
        <table class="amount-table">
            <tr class="amount-row">
                <td>Subtotal:</td>
                <td><?php echo formatCurrency($item['TotalAmount']); ?></td>
            </tr>
            <tr class="amount-row">
                <td>Tax (0%):</td>
                <td><?php echo formatCurrency(0); ?></td>
            </tr>
            <tr class="amount-row total-row">
                <td class="total-cell">Total:</td>
                <td class="total-cell"><?php echo formatCurrency($item['TotalAmount']); ?></td>
            </tr>
        </table>
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Payment is due within 30 days.</p>
        </div>
        
        <div class="print-button">
            <button class="btn-back" onclick="window.location.href='payment-billing.php'">Back</button>
            <button class="btn-print" onclick="window.print()">Print Invoice</button>
        </div>
    </div>
</body>
</html>