<?php
// Server-side expiry check endpoint for POS and internal use
// Accepts: POST or GET with `drug_id` (int) and optional `qty` (int)
// Response: JSON with fields: allowed (bool), days_left, risk_score, risk_level, recommendation, message

header('Content-Type: application/json');
include 'db.php';
include 'expiry_helpers.php';

$drug_id = intval($_REQUEST['drug_id'] ?? 0);
$qty_request = intval($_REQUEST['qty'] ?? 0);

if($drug_id <= 0){
    echo json_encode(['error' => 'drug_id required']);
    http_response_code(400);
    exit();
}

$res = expiry_check($conn, $drug_id, $qty_request);

// Log to database
log_expiry_check($conn, $drug_id, $res);

echo json_encode($res);
exit();
?>