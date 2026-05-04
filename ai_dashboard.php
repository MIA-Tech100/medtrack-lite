<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header('Location: login.php');
    exit();
}

// Call ML API via HTTP
$ml_url = 'http://' . $_SERVER['HTTP_HOST'] . '/medtrack_lite/ml_api.php';
$ml_data = @file_get_contents($ml_url);
$results = json_decode($ml_data, true);

// Check if results loaded successfully
if(!$results || !is_array($results)){
    $results = [
        'demand_forecast' => [],
        'anomalies' => [],
        'expiry_analysis' => [],
        'staff_performance' => []
    ];
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Smart Analytics Dashboard - MedTrack Lite</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 28px; }
        .section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section h2 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #667eea; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f8f9fa; }
        .high { background: #f8d7da; }
        .medium { background: #fff3cd; }
        .low { background: #d4edda; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-urgent { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-good { background: #28a745; color: white; }
        .stat-box { display: inline-block; background: #f8f9fa; padding: 15px 20px; border-radius: 5px; margin-right: 15px; margin-bottom: 10px; }
        .stat-number { font-size: 24px; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; font-size: 12px; text-transform: uppercase; }
        .alert { padding: 15px; margin-bottom: 15px; border-radius: 5px; border-left: 4px solid; }
        .alert-danger { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .alert-warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        .alert-success { background: #d4edda; border-color: #28a745; color: #155724; }
        .back-link { margin-bottom: 20px; }
        .back-link a { color: #667eea; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="back-link">
    <a href="dashboard.php">← Back to Dashboard</a>
</div>

<div class="header">
    <h1>AI-Powered Analytics Dashboard</h1>
    <p>Smart insights for pharmacy inventory management</p>
</div>

<?php if(isset($results['error'])): ?>
    <div class="section alert alert-danger">
        <strong>Error:</strong> <?php echo htmlspecialchars($results['error']); ?>
    </div>
<?php else: ?>

<!-- 1. DEMAND FORECASTING -->
<div class="section">
    <h2>Demand Forecasting</h2>
    <p>Machine Learning prediction of drug demand for next 7 and 14 days</p>
    
    <div class="stat-box">
        <div class="stat-number"><?php echo count($results['demand_forecast'] ?? []); ?></div>
        <div class="stat-label">Drugs Analyzed</div>
    </div>
    <div class="stat-box">
        <div class="stat-number"><?php 
            $count_urgent = 0;
            $demand_data = $results['demand_forecast'] ?? [];
            if(is_array($demand_data)) {
                $count_urgent = count(array_filter($demand_data, function($d) { 
                    return is_array($d) && isset($d['recommendation']) && strpos($d['recommendation'], 'URGENT') !== false; 
                }));
            }
            echo $count_urgent;
        ?></div>
        <div class="stat-label">Urgent Restocks Needed</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Drug Name</th>
                <th>Current Stock</th>
                <th>Avg Daily Sales</th>
                <th>7-Day Forecast</th>
                <th>14-Day Forecast</th>
                <th>Trend</th>
                <th>Confidence</th>
                <th>Recommendation</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($results['demand_forecast'] ?? [] as $f): ?>
            <?php if(is_array($f)): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($f['name'] ?? 'Unknown'); ?></strong></td>
                <td><?php echo $f['current_stock'] ?? 0; ?> units</td>
                <td><?php echo $f['avg_daily_sales'] ?? 0; ?> units</td>
                <td><?php echo $f['7day_forecast'] ?? 0; ?> units</td>
                <td><?php echo $f['14day_forecast'] ?? 0; ?> units</td>
                <td>
                    <?php if(($f['trend'] ?? '') == 'increasing'): ?>
                        <span class="badge" style="background: #17a2b8; color: white;">📈 Increasing</span>
                    <?php elseif(($f['trend'] ?? '') == 'decreasing'): ?>
                        <span class="badge" style="background: #6c757d; color: white;">📉 Decreasing</span>
                    <?php else: ?>
                        <span class="badge" style="background: #6f42c1; color: white;">➡️ Stable</span>
                    <?php endif; ?>
                </td>
                <td><?php echo round(($f['confidence'] ?? 0) * 100, 1) . '%'; ?></td>
                <td>
                    <?php 
                    $rec = $f['recommendation'] ?? 'No data';
                    if(is_string($rec)):
                        if(strpos($rec, 'URGENT') !== false): ?>
                            <span class="badge badge-urgent"><?php echo htmlspecialchars($rec); ?></span>
                        <?php elseif(strpos($rec, 'WARNING') !== false): ?>
                            <span class="badge badge-warning"><?php echo htmlspecialchars($rec); ?></span>
                        <?php else: ?>
                            <span class="badge badge-good"><?php echo htmlspecialchars($rec); ?></span>
                        <?php endif;
                    else:
                        echo '<span class="badge badge-good">✅ No data</span>';
                    endif;
                    ?>
                </td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 2. FRAUD DETECTION -->
<div class="section">
    <h2>Fraud Detection (Anomaly Detection)</h2>
    <p>Detecting unusual transaction patterns that may indicate fraud or errors</p>
    
    <?php if(empty($results['anomalies'])): ?>
        <div class="alert alert-success">✅ No anomalies detected - All transactions appear normal</div>
    <?php else: ?>
        <div class="alert alert-warning">⚠️ <?php echo count($results['anomalies']); ?> suspicious transactions detected</div>
        <table>
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Quantity Sold</th>
                    <th>Average Quantity</th>
                    <th>Deviation</th>
                    <th>Risk Level</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($results['anomalies'] as $a): ?>
                <?php if(is_array($a)): ?>
                <tr class="<?php echo ($a['risk_level'] ?? 'medium') == 'high' ? 'high' : 'medium'; ?>">
                    <td><?php echo htmlspecialchars($a['staff'] ?? 'Unknown'); ?></td>
                    <td><strong><?php echo $a['quantity'] ?? 0; ?> units</strong></td>
                    <td><?php echo $a['avg_quantity'] ?? 0; ?> units</td>
                    <td><strong><?php echo $a['deviation'] ?? 0; ?>%</strong></td>
                    <td>
                        <span class="badge <?php echo ($a['risk_level'] ?? 'medium') == 'high' ? 'badge-urgent' : 'badge-warning'; ?>">
                            <?php echo strtoupper($a['risk_level'] ?? 'medium'); ?>
                        </span>
                    </td>
                    <td>Review transaction</td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- 3. INVENTORY OPTIMIZATION (ABC ANALYSIS) -->
<!-- 3. EXPIRY RISK ANALYSIS -->
<div class="section">
    <h2>Expiry Risk Analysis</h2>
    <p>Analysis of stock expiry risk by drug (days to expiry, risk score and recommended action)</p>

    <?php $expiry = $results['expiry_analysis'] ?? []; ?>

    <?php if(empty($expiry)): ?>
        <div class="alert alert-success">✅ No expiry data available</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Drug</th>
                    <th>Days Left</th>
                    <th>Current Stock</th>
                    <th>Avg Daily Sales</th>
                    <th>Risk</th>
                    <th>Recommendation</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($expiry as $e): ?>
                <?php $risk = $e['risk_level'] ?? 'low'; ?>
                <tr class="<?php echo $risk; ?>">
                    <td><strong><?php echo htmlspecialchars($e['name'] ?? 'Unknown'); ?></strong></td>
                    <td><?php echo isset($e['days_left']) ? intval($e['days_left']) : 'N/A'; ?></td>
                    <td><?php echo intval($e['quantity'] ?? 0); ?></td>
                    <td><?php echo round(floatval($e['avg_daily_sales'] ?? 0), 2); ?> units</td>
                    <td>
                        <span class="badge <?php echo $risk == 'high' ? 'badge-urgent' : ($risk == 'medium' ? 'badge-warning' : 'badge-good'); ?>">
                            <?php echo strtoupper($risk); ?> (<?php echo round(floatval($e['risk_score'] ?? 0) * 100, 1); ?>%)
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($e['recommendation'] ?? 'No action'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- 5. STAFF PERFORMANCE ANALYTICS -->
<div class="section">
    <h2>Staff Performance Analytics</h2>
    <p>AI analysis of staff behavior and sales performance</p>
    
    <table>
        <thead>
            <tr>
                <th>Staff Member</th>
                <th>Transactions</th>
                <th>Avg Qty/Sale</th>
                <th>Total Sold</th>
                <th>Total Revenue</th>
                <th>Performance Score</th>
                <th>Consistency</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($results['staff_performance'] ?? [] as $s): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($s['staff']); ?></strong></td>
                <td><?php echo $s['transactions']; ?></td>
                <td><?php echo $s['avg_quantity_per_sale']; ?> units</td>
                <td><?php echo $s['total_quantity_sold']; ?> units</td>
                <td><?php echo number_format($s['total_revenue'], 2); ?> KES</td>
                <td>
                    <?php 
                    $score = $s['performance_score'];
                    $color = $score >= 80 ? 'green' : ($score >= 60 ? 'orange' : 'red');
                    $color_map = ['green' => '#28a745', 'orange' => '#ffc107', 'red' => '#dc3545'];
                    ?>
                    <span class="badge" style="background: <?php echo $color_map[$color]; ?>; color: white;">
                        <?php echo round($score); ?>/100
                    </span>
                </td>
                <td><?php echo $s['consistency']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #666;">
    <p><strong>Dashboard Updated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    <p style="font-size: 12px;"></p>
</div>

</body>
</html>
