<?php
include 'db.php';

// Fetch updated stock statistics
$total_drugs = $conn->query("SELECT COUNT(*) AS total FROM drugs")->fetch_assoc()['total'];
$expiring = $conn->query("
    SELECT COUNT(*) AS total 
    FROM drugs 
    WHERE exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
    AND exp_date >= CURDATE()
")->fetch_assoc()['total'];
$expired = $conn->query("
    SELECT COUNT(*) AS total 
    FROM drugs 
    WHERE exp_date < CURDATE()
")->fetch_assoc()['total'];
$low_stock = $conn->query("
    SELECT COUNT(*) AS total 
    FROM drugs 
    WHERE quantity < 10
")->fetch_assoc()['total'];

echo json_encode([
    'total' => $total_drugs,
    'expiring' => $expiring,
    'expired' => $expired,
    'low_stock' => $low_stock
]);
?>
