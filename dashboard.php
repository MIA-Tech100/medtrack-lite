<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Automatically mark expired drugs as inactive
$conn->query("UPDATE drugs SET status='inactive' WHERE exp_date < CURDATE() AND status='active'");

// =======================
// DASHBOARD STATISTICS
// =======================
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

$low_stock_drugs = $conn->query("
    SELECT name, quantity 
    FROM drugs 
    WHERE quantity < 10
");

// =======================
// MANAGE STAFF COUNT
// =======================
$manage_staff = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='staff'")->fetch_assoc()['total'];

// =======================
// NEAR-EXPIRY DRUGS
// =======================
$expiring_drugs = $conn->query("
    SELECT name, DATEDIFF(exp_date, CURDATE()) AS days_left
    FROM drugs
    WHERE exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND exp_date >= CURDATE()
");

$near_expiry = [];
while($row = $expiring_drugs->fetch_assoc()){
    $near_expiry[] = $row['name']." - ".$row['days_left']." days left";
}

// =======================
// EMAIL ALERTS USING PHPMailer (ONCE PER DAY)
// =======================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$today = date('Y-m-d');

// Check if expiry email sent today
$check = $conn->query("SELECT * FROM email_logs WHERE type='expiry' AND last_sent='$today'");
if($check->num_rows == 0){
    // send expiry email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mi006403@gmail.com';
        $mail->Password   = 'xftc fciu cvdl hwhu'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('mi006403@gmail.com', 'MedTrack Lite');
        $mail->addAddress('mi006403@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'Daily MedTrack Alert: Drugs Near Expiry';

        $message = "<h3>🚨 Action Required: Drugs Nearing Expiry (Next 30 Days)</h3><ul>";
        foreach ($near_expiry as $drug) {
            $message .= "<li>$drug</li>";
        }
        $message .= "</ul>";

        $mail->Body = $message;
        $mail->send();

        // Log email sent today
        $conn->query("INSERT INTO email_logs (type, last_sent) VALUES ('expiry', '$today')");

    } catch (Exception $e) {
        // fail silently
    }
}

$check_low = $conn->query("SELECT * FROM email_logs WHERE type='low_stock' AND last_sent='$today'");
if($check_low->num_rows == 0){
    // send low-stock email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mi006403@gmail.com';
        $mail->Password   = 'xftc fciu cvdl hwhu'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('mi006403@gmail.com', 'MedTrack Lite');
        $mail->addAddress('mi006403@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'Low Stock Drugs Alert';

        $message = "<h3>⚠ Low Stock Drugs (Below 10 units)</h3><ul>";

while($row = $low_stock_drugs->fetch_assoc()){
    $message .= "<li>{$row['name']} — {$row['quantity']} left</li>";
}

$message .= "</ul>";

        $mail->Body = $message;
        $mail->send();

        // Log email sent today
        $conn->query("INSERT INTO email_logs (type, last_sent) VALUES ('low_stock', '$today')");

    } catch (Exception $e) {
        // fail silently
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - MedTrack Lite</title>
    <style>
        body { font-family: Arial,sans-serif; background:#f4f6f8; margin:20px; }
        h1 { color:#333; }
        a { text-decoration:none; color:#3498db; margin-right:15px; font-weight:bold; }
        .dashboard-cards { display:flex; gap:20px; flex-wrap:wrap; margin-top:20px; }
        .card { flex:1 1 200px; padding:20px; border-radius:10px; color:white; font-weight:bold; text-align:center; cursor:pointer; }
        .total { background:#1abc9c; }
        .expiring { background:#f1c40f; }
        .expired { background:#e74c3c; }
        .low-stock { background:#3498db; }
        .manage_staff{background:#9b59b6;}
        .alert-box { margin-top:30px; padding:15px; background:#fff3cd; border-left:6px solid #f1c40f; border-radius:8px; animation: blink 1.5s infinite; }
        @keyframes blink { 0%{background:#fff3cd;} 50%{background:#ffe69c;} 100%{background:#fff3cd;} }
        .alert-box ul { margin-top:10px; }
        .alert-box li { margin-bottom:5px; }
    </style>
</head>
<body>

<h1>Welcome, <?php echo $_SESSION['username']; ?></h1>

<a href="view_drugs.php">View Drugs</a>
<a href="add_drug.php">Add Drug</a>
<a href="ai_dashboard.php" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 10px 15px; border-radius: 5px; color: white; font-weight: bold;">Smart Analytics</a>
<a href="reports.php">Reports</a>
<a href="sales_history.php">Sales History</a>
<a href="logout.php">Logout</a>

<div class="dashboard-cards">

    <div class="card total" onclick="window.location.href='view_drugs.php';">
        Total Drugs<br><span id="totalCount"><?php echo $total_drugs; ?></span>
    </div>

    <div class="card expiring" onclick="window.location.href='expiring_drugs.php';">
        Expiring Soon<br><span id="expiringCount"><?php echo $expiring; ?></span>
    </div>

    <div class="card expired" onclick="window.location.href='expired_drugs.php';">
        Expired<br><span id="expiredCount"><?php echo $expired; ?></span>
    </div>

    <div class="card low-stock" onclick="window.location.href='low_stock.php';">
        Low Stock<br><span id="lowStockCount"><?php echo $low_stock; ?></span>
    </div>

    <div class="card manage_staff" onclick="window.location.href='manage_staff.php';">
        Manage Staff<br><span id="manageStaffCount"><?php echo $manage_staff; ?></span>
    </div>

</div>

<?php if(count($near_expiry) > 0): ?>
<div class="alert-box">
    <strong>⚠ Near-Expiry Alert!</strong>
    <p>The following drugs require attention:</p>
    <ul>
        <?php foreach($near_expiry as $drug): ?>
            <li><?php echo $drug; ?></li>
        <?php endforeach; ?>
    </ul>
    <a href="view_drugs.php">➡ View Drug Details</a>
</div>
<?php endif; ?>

<script>
// Auto-update stats every 5 seconds
function updateStock(){
    fetch('fetch_stock.php')
    .then(res=>res.json())
    .then(data=>{
        document.getElementById('totalCount').textContent = data.total;
        document.getElementById('expiringCount').textContent = data.expiring;
        document.getElementById('expiredCount').textContent = data.expired;
        document.getElementById('lowStockCount').textContent = data.low_stock;
    })
    .catch(err=>console.error(err));
}
setInterval(updateStock,5000);
</script>

</body>
</html>
