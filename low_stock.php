<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Fetch low stock drugs (quantity < 10)
$result = $conn->query("SELECT * FROM drugs WHERE quantity < 10 ORDER BY quantity ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Low Stock Drugs - MedTrack Lite</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:20px; }
        h2 { color:#e74c3c; }
        table { width:100%; border-collapse: collapse; margin-top:20px; }
        th, td { border:1px solid #ccc; padding:10px; text-align:left; }
        th { background:#f1f1f1; }
        tr:nth-child(even) { background:#fafafa; }
        a.back { display:inline-block; margin-top:20px; text-decoration:none; color:#3498db; font-weight:bold; }
    </style>
</head>
<body>

<h2>⚠️ Low Stock Drugs</h2>
<a href="dashboard.php" class="back">⬅ Back to Dashboard</a>

<table>
    <tr>
        <th>#</th>
        <th>Drug Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Supplier</th>
        <th>Expiry Date</th>
    </tr>
    <?php
    if($result->num_rows > 0){
        $i = 1;
        while($row = $result->fetch_assoc()){
            echo "<tr>";
            echo "<td>".$i."</td>";
            echo "<td>".$row['name']."</td>";
            echo "<td>".$row['category']."</td>";
            echo "<td>".$row['quantity']."</td>";
            echo "<td>".$row['supplier']."</td>";
            echo "<td>".$row['exp_date']."</td>";
            echo "</tr>";
            $i++;
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center;'>No low stock drugs</td></tr>";
    }
    ?>
</table>

</body>
</html>
