<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit(); }

$result = $conn->query("
    SELECT * FROM drugs
    WHERE exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND exp_date >= CURDATE()
    ORDER BY exp_date ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expiring Soon Drugs</title>
    <style>
        body{font-family:Arial,sans-serif; background:#f4f6f8; margin:20px;}
        h2{color:#f1c40f;}
        table{width:100%; border-collapse: collapse; margin-top:20px;}
        th, td{border:1px solid #ccc; padding:10px;}
        th{background:#f1f1f1;}
        tr:nth-child(even){background:#fafafa;}
        a.back{display:inline-block;margin-top:20px;text-decoration:none;color:#3498db;font-weight:bold;}
    </style>
</head>
<body>

<h2>⚠️ Expiring Soon Drugs (Next 30 Days)</h2>
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
    if($result->num_rows>0){
        $i=1;
        while($row=$result->fetch_assoc()){
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
        echo "<tr><td colspan='6' style='text-align:center;'>No expiring drugs</td></tr>";
    }
    ?>
</table>

</body>
</html>
