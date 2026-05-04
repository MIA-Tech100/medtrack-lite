<?php
// Reusable expiry helper for programmatic checks
// Provides function: expiry_check($conn, $drug_id, $qty_request=0)
// Returns associative array: allowed(bool), days_left(int|null), risk_score(float), risk_level, recommendation, message, quantity, avg_daily

function expiry_check($conn, $drug_id, $qty_request = 0){
    $drug_id = intval($drug_id);
    $qty_request = intval($qty_request);
    if($drug_id <= 0) return ['error'=>'invalid drug_id'];

    $stmt = $conn->prepare("SELECT id, name, batch_no, mfg_date, exp_date, quantity FROM drugs WHERE id = ? LIMIT 1");
    if(!$stmt) return ['error'=>'db_error'];
    $stmt->bind_param('i', $drug_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $drug = $res->fetch_assoc();
    $stmt->close();

    if(!$drug) return ['error'=>'not_found'];

    // days left
    $days_left = null;
    if(!empty($drug['exp_date'])){
        try{
            $exp = new DateTime($drug['exp_date']);
            $now = new DateTime();
            $interval = $now->diff($exp);
            $days_left = (int)$interval->format('%r%a');
        }catch(Exception $e){
            $days_left = null;
        }
    }

    // avg daily sales in last 90 days
    $ninety_ago = date('Y-m-d', strtotime('-90 days'));
    $sstmt = $conn->prepare("SELECT SUM(quantity_sold) as total_qty FROM sales WHERE drug_id = ? AND sold_at >= ?");
    $avg_daily = 0.0;
    if($sstmt){
        $sstmt->bind_param('is', $drug_id, $ninety_ago);
        $sstmt->execute();
        $sr = $sstmt->get_result();
        $rrow = $sr->fetch_assoc();
        $total_qty = intval($rrow['total_qty'] ?? 0);
        $avg_daily = $total_qty > 0 ? round($total_qty / 90, 3) : 0.0;
        $sstmt->close();
    }

    $quantity = intval($drug['quantity'] ?? 0);

    $dl = $days_left !== null ? $days_left : 0;
    $days_factor = max(0.0, min(1.0, (90 - $dl) / 90));
    if($quantity <= 0){
        $stock_factor = 1.0;
    }else{
        $stock_factor = max(0.0, 1.0 - ($avg_daily / ($quantity + 1)));
    }
    $score = 0.6 * $days_factor + 0.4 * $stock_factor;
    $score = max(0.0, min(1.0, $score));

    if($score >= 0.7) $level = 'high';
    elseif($score >= 0.4) $level = 'medium';
    else $level = 'low';

    if($dl <= 0){
        $recommendation = 'DISPOSE - EXPIRED';
        $allowed = false;
        $message = 'Item expired - sale blocked';
    }elseif($level === 'high'){
        $recommendation = 'PRIORITIZE SELL';
        $allowed = true;
        $message = 'High expiry risk';
    }elseif($level === 'medium'){
        $recommendation = 'PROMOTE / RUN DISCOUNT';
        $allowed = true;
        $message = 'Medium expiry risk';
    }else{
        $recommendation = 'Normal';
        $allowed = true;
        $message = 'Low expiry risk';
    }

    if($qty_request > 0 && $qty_request > $quantity){
        $allowed = false;
        $message = 'Insufficient stock';
    }

    return [
        'allowed' => $allowed,
        'days_left' => $days_left,
        'risk_score' => round($score,3),
        'risk_level' => $level,
        'recommendation' => $recommendation,
        'message' => $message,
        'quantity' => $quantity,
        'avg_daily' => $avg_daily,
        'drug_name' => $drug['name']
    ];
}

function log_expiry_check($conn, $drug_id, $result){
    if(isset($result['error'])) return;

    $log_stmt = $conn->prepare("
        INSERT INTO expiry_audit 
        (drug_id, drug_name, days_left, quantity, avg_daily_sales, risk_score, risk_level, recommendation, message, allowed, client_ip)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if($log_stmt){
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $allowed_int = $result['allowed'] ? 1 : 0;
        $log_stmt->bind_param(
            'issiiidssss',
            $drug_id,
            $result['drug_name'],
            $result['days_left'],
            $result['quantity'],
            $result['avg_daily'],
            $result['risk_score'],
            $result['risk_level'],
            $result['recommendation'],
            $result['message'],
            $allowed_int,
            $ip
        );
        @$log_stmt->execute();
        $log_stmt->close();
    }
}

?>
