<?php
// Advanced ML Analytics API
include 'db.php';
header('Content-Type: application/json');

// Fetch all drugs
$drugs_arr = [];
$res = $conn->query("SELECT id, name, quantity, price, exp_date FROM drugs WHERE status='active'");
while($row = $res->fetch_assoc()){
    $days_left = null;
    if(!empty($row['exp_date'])){
        $exp = new DateTime($row['exp_date']);
        $now = new DateTime();
        $interval = $now->diff($exp);
        $days_left = (int)$interval->format('%r%a');
    }
    
    $drugs_arr[] = [
        'id' => intval($row['id']),
        'name' => $row['name'],
        'quantity' => intval($row['quantity']),
        'price' => floatval($row['price']),
        'exp_date' => $row['exp_date'],
        'days_left' => $days_left
    ];
}

// Fetch sales data (last 90 days)
$sales_arr = [];
$ninety_ago = date('Y-m-d', strtotime('-90 days'));
$res = $conn->query("
    SELECT s.drug_id, s.quantity_sold as qty, s.sold_by as staff, 
           s.total_price as revenue, s.sold_at, u.username
    FROM sales s
    LEFT JOIN users u ON s.sold_by = u.id
    WHERE s.sold_at >= '$ninety_ago'
    ORDER BY s.sold_at DESC
");
while($row = $res->fetch_assoc()){
    $sales_arr[] = [
        'drug_id' => intval($row['drug_id']),
        'qty' => intval($row['qty']),
        'staff' => $row['username'] ?? 'Unknown',
        'revenue' => floatval($row['revenue'] ?? 0),
        'sold_at' => $row['sold_at']
    ];
}

// Prepare input file
$payload = [
    'drugs' => $drugs_arr,
    'sales' => $sales_arr
];

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ml_input_' . uniqid() . '.json';
file_put_contents($tmp, json_encode($payload));

// Run Python ML engine
$python = 'C:\\Users\\USER\\AppData\\Local\\Programs\\Python\\Python311\\python.exe';
$script = __DIR__ . DIRECTORY_SEPARATOR . 'ml_analytics.py';
$cmd = escapeshellcmd($python) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($tmp) . ' 2>&1';
$output = shell_exec($cmd);

// Clean up
@unlink($tmp);

if($output === null){
    echo json_encode(['error' => 'Failed to execute ML engine']);
    exit();
}

$decoded = json_decode($output, true);
if($decoded === null){
    echo json_encode(['error' => 'Invalid ML output', 'raw' => $output]);
    exit();
}

echo json_encode($decoded);
