<?php
include 'db.php';

echo "Drugs in database:\n";
$res = $conn->query("SELECT id, name, quantity FROM drugs WHERE status='active' ORDER BY id");
while($row = $res->fetch_assoc()){
    echo "ID: {$row['id']}, Name: {$row['name']}, Quantity: {$row['quantity']}\n";
}
?>
