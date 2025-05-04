<?php
include '../addphp/navbar.php';
require_once '../config/db_config.php';

// Function to format currency
function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}

// Determine which page to show
$page = isset($_GET['page']) ? $_GET['page'] : 'sales';

// Sales Summary Data - Only fetch if on sales page or if needed
if ($page == 'sales') {
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
}

// Profit & Loss Data - Only fetch if on profit page or if needed
if ($page == 'profit') {
    // Fetch profit & loss data from database
    // You can replace this with actual database queries to your ProfitLoss table
    $profit_loss = [
        'Gross Revenue' => 613325.00,
        'Cost of Goods Sold (COGS)' => 84762.00,
        'Gross Profit' => 528563.00,
        'Total Expenses' => 321320.00,
        'Net Income (Profit)' => 207243.00
    ];
    
    // Revenue Breakdown
    $revenue_breakdown = [
        'Fabric Sales' => 585460.00,
        'Tailoring Services' => 27865.00
    ];
    
    // Gross Profit Margin
    $gross_profit_margin = 86.2; // (Gross Profit / Gross Revenue)
    
    // Expense Analysis
    $expense_analysis = [
        'Wages & Benefits' => ['amount' => 225000.00, 'percentage' => 70.0],
        'Rent / Mortgage' => ['amount' => 18500.00, 'percentage' => 5.8],
        'Utilities' => ['amount' => 11442.00, 'percentage' => 3.6],
        'Office Supplies' => ['amount' => 2500.00, 'percentage' => 0.8],
        'Internet & Phone' => ['amount' => 7800.00, 'percentage' => 2.4],
        'Insurance' => ['amount' => 2648.00, 'percentage' => 0.8],
        'Other Expenses' => ['amount' => 52430.00, 'percentage' => 16.3]
    ];
    
    // Key Insights
    $key_insights = [
        'High Gross Margin (86%)',
        'Strong pricing strategy on fabrics/tailoring',
        'Wages = 70% of Expenses',
        'Consider outsourcing or part-time staffing',
        'Low COGS (13.8% of revenue)',
        'Good inventory cost control'
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Business Report</title>
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

        .payment-distribution, .revenue-breakdown {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .payment-distribution h3, .revenue-breakdown h3, .key-insights h3 {
            margin-top: 0;
            color: #333;
        }

        .payment-distribution ul, .key-insights ul {
            list-style-type: none;
            padding-left: 10px;
        }

        .payment-distribution li, .key-insights li {
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

        .key-insights li::before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }

        .top-days, .key-insights {
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
        
        .page-navigation {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .page-navigation a {
            display: inline-block;
            padding: 10px 20px;
            background-color: rgba(255, 123, 0, 0.89);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .page-navigation a:hover {
            background-color: #e66c00;
        }
        
        .positive {
            color: #28a745;
        }
        
        .negative {
            color: #dc3545;
        }
        
        .profit-loss-summary {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="content">
    <?php if ($page == 'sales'): ?>
    <!-- SALES SUMMARY PAGE -->
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
    
    <?php elseif ($page == 'profit'): ?>
    <!-- PROFIT & LOSS PAGE -->
    <h1>Profit & Loss Summary</h1>
    
    <div class="profit-loss-summary">
        <h2>Financial Overview</h2>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Gross Revenue</th>
                    <th>Cost of Goods Sold (COGS)</th>
                    <th>Gross Profit</th>
                    <th>Total Expenses</th>
                    <th>Net Income (Profit)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Amount (Rs)</td>
                    <td><?php echo number_format($profit_loss['Gross Revenue'], 2); ?></td>
                    <td><?php echo number_format($profit_loss['Cost of Goods Sold (COGS)'], 2); ?></td>
                    <td class="positive"><?php echo number_format($profit_loss['Gross Profit'], 2); ?></td>
                    <td><?php echo number_format($profit_loss['Total Expenses'], 2); ?></td>
                    <td class="positive"><?php echo number_format($profit_loss['Net Income (Profit)'], 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="report-container">
        <div class="report-left">
            <div class="revenue-breakdown">
                <h3>Revenue Breakdown</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Amount (Rs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenue_breakdown as $category => $amount): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category); ?></td>
                            <td><?php echo number_format($amount, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px;">
                    <p><strong>Gross Profit Margin</strong></p>
                    <p><?php echo number_format($gross_profit_margin, 1); ?>% (Gross Profit / Gross Revenue)</p>
                </div>
            </div>
            
            <div class="key-insights">
                <h3>Key Insights & Recommendations</h3>
                <ul>
                    <?php foreach ($key_insights as $insight): ?>
                    <li><?php echo htmlspecialchars($insight); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="report-right">
            <h2>Expense Analysis</h2>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Amount (Rs)</th>
                        <th>% of Expenses</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expense_analysis as $category => $data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category); ?></td>
                        <td><?php echo number_format($data['amount'], 2); ?></td>
                        <td><?php echo number_format($data['percentage'], 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Navigation buttons -->
    <div class="page-navigation">
        <?php if ($page == 'sales'): ?>
            <a href="?page=profit"><i class="fas fa-chart-line"></i> View Profit & Loss Report</a>
        <?php else: ?>
            <a href="?page=sales"><i class="fas fa-shopping-cart"></i> View Sales Summary Report</a>
        <?php endif; ?>
    </div>
</div>

<script>
    // You can add any JavaScript functionality here if needed
</script>

<?php 
$conn->close();
?>
</body>
</html>