<?php
include '../addphp/navbar.php';
require_once '../config/db_config.php';

// Function to format currency
function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}

// Fetch sales metrics
$metrics = array();

// Total Sales
$total_sales_query = $conn->query("SELECT SUM(TotalAmount) AS total_sales FROM Sales");
$metrics['Total Sales'] = $total_sales_query->fetch_assoc()['total_sales'] ?? 0;

// Average Order Value
$avg_order_query = $conn->query("SELECT AVG(TotalAmount) AS avg_order FROM Sales");
$metrics['Average Order Value'] = $avg_order_query->fetch_assoc()['avg_order'] ?? 0;

// Cash Payments
$cash_payments_query = $conn->query("SELECT SUM(TotalAmount) AS cash_total FROM Sales WHERE PaymentMethod = 'Cash'");
$metrics['Cash Payments'] = $cash_payments_query->fetch_assoc()['cash_total'] ?? 0;

// Card Payments
$card_payments_query = $conn->query("SELECT SUM(TotalAmount) AS card_total FROM Sales WHERE PaymentMethod = 'Card'");
$metrics['Card Payments'] = $card_payments_query->fetch_assoc()['card_total'] ?? 0;

// Highest Sale
$highest_sale_query = $conn->query("SELECT MAX(TotalAmount) AS highest_sale FROM Sales");
$metrics['Highest Sale'] = $highest_sale_query->fetch_assoc()['highest_sale'] ?? 0;

// Lowest Sale
$lowest_sale_query = $conn->query("SELECT MIN(TotalAmount) AS lowest_sale FROM Sales WHERE TotalAmount > 0");
$metrics['Lowest Sale'] = $lowest_sale_query->fetch_assoc()['lowest_sale'] ?? 0;

// Fetch sales trend (recent orders)
$sales_trend_query = $conn->query("
    SELECT 
        DATE_FORMAT(SaleDate, '%Y-%m-%d %H:%i:%s') AS formatted_date,
        OrderID,
        TotalAmount,
        PaymentMethod
    FROM 
        Sales
    ORDER BY 
        SaleDate DESC
    LIMIT 3
");

$sales_trend = array();
while ($row = $sales_trend_query->fetch_assoc()) {
    $sales_trend[] = $row;
}

// Calculate payment method distribution
$payment_distribution_query = $conn->query("
    SELECT 
        PaymentMethod,
        COUNT(*) AS count,
        COUNT(*) * 100.0 / (SELECT COUNT(*) FROM Sales) AS percentage
    FROM 
        Sales
    GROUP BY 
        PaymentMethod
    ORDER BY 
        count DESC
");

$payment_distribution = array();
while ($row = $payment_distribution_query->fetch_assoc()) {
    $payment_distribution[$row['PaymentMethod']] = $row['percentage'];
}

// Get top performing days
$top_days_query = $conn->query("
    SELECT 
        DATE(SaleDate) AS sale_date,
        SUM(TotalAmount) AS daily_total
    FROM 
        Sales
    GROUP BY 
        DATE(SaleDate)
    ORDER BY 
        daily_total DESC
    LIMIT 3
");

$top_days = array();
while ($row = $top_days_query->fetch_assoc()) {
    $top_days[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Summary Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .content {
            padding: 50px;
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }

        h2 {
            font-size: 22px;
            color:rgba(255, 123, 0, 0.89);
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #333;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .report-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 30px;
        }

        .report-left {
            flex: 1;
            min-width: 300px;
        }

        .report-right {
            flex: 1;
            min-width: 300px;
        }

        .payment-distribution {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .payment-distribution h3 {
            margin-top: 0;
            color: #333;
        }

        .payment-distribution ul {
            list-style-type: none;
            padding-left: 10px;
        }

        .payment-distribution li {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .payment-distribution li::before {
            content: "•";
            color: #4e73df;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }

        .top-days {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .top-days h3 {
            margin-top: 0;
            color: #333;
        }

        .top-days ol {
            padding-left: 25px;
        }

        .top-days li {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .highlight {
            font-weight: bold;
        }

        .date-time {
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
<div class="content">
    <h1>Sales Summary Report</h1>

    <h2>Sales Metrics</h2>
    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Total Sales</th>
                <th>Average Order Value</th>
                <th>Cash Payments</th>
                <th>Card Payments</th>
                <th>Highest Sale</th>
                <th>Lowest Sale</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Value</td>
                <td><?php echo formatCurrency($metrics['Total Sales']); ?></td>
                <td><?php echo formatCurrency($metrics['Average Order Value']); ?></td>
                <td><?php echo formatCurrency($metrics['Cash Payments']); ?></td>
                <td><?php echo formatCurrency($metrics['Card Payments']); ?></td>
                <td><?php echo formatCurrency($metrics['Highest Sale']); ?></td>
                <td><?php echo formatCurrency($metrics['Lowest Sale']); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="report-container">
        <div class="report-left">
            <h2>Sales Trend</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Amount (Rs)</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales_trend as $sale): ?>
                    <tr>
                        <td class="date-time"><?php echo htmlspecialchars($sale['formatted_date']); ?></td>
                        <td><?php echo htmlspecialchars($sale['OrderID']); ?></td>
                        <td><?php echo number_format($sale['TotalAmount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($sale['PaymentMethod']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="report-right">
            <div class="payment-distribution">
                <h3>Payment Method Distribution</h3>
                <ul>
                    <?php 
                    // Display known payment methods first
                    $methods = ['Cash', 'Card', 'Bank Transfer'];
                    foreach ($methods as $method):
                        $percentage = isset($payment_distribution[$method]) ? 
                            number_format($payment_distribution[$method], 1) : '0.0';
                    ?>
                    <li><?php echo $method; ?>: <?php echo $percentage; ?>%</li>
                    <?php endforeach; ?>
                    
                    <?php 
                    // Display any other payment methods not in the predefined list
                    foreach ($payment_distribution as $method => $percentage):
                        if (!in_array($method, $methods)):
                    ?>
                    <li><?php echo htmlspecialchars($method); ?>: <?php echo number_format($percentage, 1); ?>%</li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
            </div>

            <div class="top-days">
                <h3>Top Performing Days</h3>
                <ol>
                    <?php foreach ($top_days as $index => $day): ?>
                    <li>
                        <?php 
                        $formatted_date = date('Y-m-d', strtotime($day['sale_date']));
                        echo $formatted_date . ' – Rs ' . number_format($day['daily_total'], 0);
                        if ($index === 0) echo ' (Highest Single Sale)';
                        ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
    // If you need any JavaScript functionality, add it here
</script>

<?php 
$conn->close();
?>
</body>
</html>