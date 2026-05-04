<?php
session_start();
include 'db.php';
include 'expiry_helpers.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit();
}

$cart = json_decode($_POST['cart'] ?? '', true);

if (!$cart || count($cart) === 0) {
    echo json_encode(["status"=>"error","message"=>"Empty cart"]);
    exit();
}

$receiptRows = "";
$grandTotal = 0;

$conn->begin_transaction();

try {
    foreach ($cart as $item) {
        $drug_id = (int)$item['id'];
        $qty = (int)$item['qty'];

        $drug = $conn->query("SELECT * FROM drugs WHERE id=$drug_id FOR UPDATE")->fetch_assoc();
        if (!$drug) {
            throw new Exception("Drug not found");
        }

        // Central expiry check (blocks expired / insufficient stock)
        $check = expiry_check($conn, $drug_id, $qty);
        log_expiry_check($conn, $drug_id, $check);
        if(isset($check['error'])){
            throw new Exception('Expiry check error');
        }
        if(!$check['allowed']){
            throw new Exception("Cannot sell '{$drug['name']}' - " . $check['message']);
        }

        $conn->query("UPDATE drugs SET quantity = quantity - $qty WHERE id=$drug_id");

        $unit = $drug['price'];
        $total = $unit * $qty;

        $stmt = $conn->prepare("
            INSERT INTO sales
            (drug_id, quantity_sold, unit_price, total_price, sold_by, sale_date, sold_at)
            VALUES (?, ?, ?, ?, ?, CURDATE(), NOW())
        ");
        $stmt->bind_param("iidds", $drug_id, $qty, $unit, $total, $_SESSION['username']);
        $stmt->execute();

        $grandTotal += $total;

        $receiptRows .= "
        <tr>
            <td>{$drug['name']}</td>
            <td>$qty</td>
            <td>$unit</td>
            <td>$total</td>
        </tr>";
    }

    $conn->commit();

    echo json_encode([
        "status"=>"success",
        "receipt"=>"
        <h3>🧾 MedTrack Lite Receipt</h3>
        <table border='1' width='100%'>
            <tr><th>Drug</th><th>Qty</th><th>Price</th><th>Total</th></tr>
            $receiptRows
        </table>
        <h3>Grand Total: $grandTotal KES</h3>
        <p>Staff: {$_SESSION['username']}</p>"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}
exit();
