<?php
// Lightweight ML-like verifier for drug entries
// Provides a function verify_drug($conn, $data) that returns array:
// ['is_valid' => bool, 'flags' => [...], 'score' => 0..1]

function verify_drug($conn, $data){
    $flags = [];
    $score = 1.0; // 1=clean, lower = suspicious

    // required fields
    $name = trim($data['name'] ?? '');
    $batch = trim($data['batch_no'] ?? '');
    $mfg = $data['mfg_date'] ?? null;
    $exp = $data['exp_date'] ?? null;

    // Basic sanity checks
    if(empty($name)){
        $flags[] = 'Missing drug name';
        $score -= 0.4;
    }
    if(empty($batch)){
        $flags[] = 'Missing batch number';
        $score -= 0.2;
    }

    // date checks
    if($mfg && $exp){
        try{
            $m = new DateTime($mfg);
            $e = new DateTime($exp);
            $now = new DateTime();
            if($e < $m){
                $flags[] = 'Expiry earlier than manufacture date';
                $score -= 0.6;
            }
            // cannot add already expired
            if($e < $now){
                $flags[] = 'Expiry date is in the past (already expired)';
                $score -= 0.8;
            }
        }catch(Exception $ex){
            $flags[] = 'Invalid date format';
            $score -= 0.5;
        }
    }else{
        $flags[] = 'Missing manufacture or expiry date';
        $score -= 0.5;
    }

    // Check for existing batch mismatch: if same batch exists with different exp/mfg
    if($batch !== ''){
        $stmt = $conn->prepare("SELECT id, name, batch_no, mfg_date, exp_date FROM drugs WHERE batch_no = ? LIMIT 1");
        if($stmt){
            $stmt->bind_param('s', $batch);
            $stmt->execute();
            $r = $stmt->get_result();
            if($row = $r->fetch_assoc()){
                if(!empty($row['exp_date']) && $exp && $row['exp_date'] !== $exp){
                    $flags[] = 'Batch already exists with different expiry date';
                    $score -= 0.7;
                }
                if($row['name'] !== $name){
                    $flags[] = 'Batch exists but product name differs';
                    $score -= 0.5;
                }
            }
            $stmt->close();
        }
    }

    // Heuristic: check typical shelf-life for same product name in DB
    if(!empty($name) && $mfg && $exp){
        $stmt = $conn->prepare("SELECT mfg_date, exp_date FROM drugs WHERE name = ? AND mfg_date IS NOT NULL AND exp_date IS NOT NULL LIMIT 50");
        if($stmt){
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $res = $stmt->get_result();
            $shelf_days = [];
            while($rw = $res->fetch_assoc()){
                try{
                    $m1 = new DateTime($rw['mfg_date']);
                    $e1 = new DateTime($rw['exp_date']);
                    $interval = $m1->diff($e1)->days;
                    if($interval > 0 && $interval < 3650) $shelf_days[] = $interval;
                }catch(Exception $ex){}
            }
            if(count($shelf_days) >= 3){
                $avg = array_sum($shelf_days)/count($shelf_days);
                // provided shelf life
                $m_obj = new DateTime($mfg);
                $e_obj = new DateTime($exp);
                $given = $m_obj->diff($e_obj)->days;
                // if given shelf-life is wildly different (>50% shorter or longer) flag
                if($given < $avg * 0.5 || $given > $avg * 1.5){
                    $flags[] = 'Reported shelf-life differs from historical average for this product';
                    $score -= 0.4;
                }
            }
            $stmt->close();
        }
    }

    $is_valid = ($score >= 0.5 && count($flags) == 0);
    return ['is_valid' => $is_valid, 'flags' => $flags, 'score' => max(0, round($score,2))];
}

// Allow HTTP POST usage for debugging or external calls
if($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['__internal_call'])){
    header('Content-Type: application/json');
    include 'db.php';
    $data = [];
    // accept form fields
    $data['name'] = $_POST['name'] ?? '';
    $data['batch_no'] = $_POST['batch_no'] ?? '';
    $data['mfg_date'] = $_POST['mfg_date'] ?? '';
    $data['exp_date'] = $_POST['exp_date'] ?? '';
    $res = verify_drug($conn, $data);
    echo json_encode($res);
    exit();
}

?>