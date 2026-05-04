<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Fetch all drugs from database
$result = $conn->query("SELECT * FROM drugs ORDER BY exp_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Drugs - MedTrack Lite</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin:20px; }
        h2 { color:#333; }
        a { text-decoration: none; color: #3498db; margin-right:10px; }
        table { border-collapse: collapse; width: 100%; margin-top:20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align:center; }
        th { background-color: #3498db; color: white; }
        tr.expired { background-color: #e74c3c; color: white; }
        tr.near-expiry { background-color: #f1c40f; color: black; }
        tr.safe { background-color: #1abc9c; color: white; }
        input[type="text"] { padding: 5px; width: 200px; margin-bottom: 10px; }
        .edit-btn, .delete-btn { padding: 5px 10px; border-radius: 5px; color: white; }
        .edit-btn { background-color: #2980b9; }
        .delete-btn { background-color: #c0392b; }
    </style>
</head>
<body>
<h2>All Drugs & Expiry Alerts</h2>
<a href="dashboard.php">Back to Dashboard</a>
<a href="add_drug.php">Add New Drug</a>

<input type="text" id="searchInput" placeholder="Search by drug name...">

<table id="drugsTable">
    <thead>
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Supplier</th>
            <th>Batch</th>
            <th>MFG Date</th>
            <th>Expiry Date</th>
            <th>Price (KES)</th>
            <th>Days Left</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()): 
        $today = new DateTime();
        $expDate = new DateTime($row['exp_date']);
        $diff = $today->diff($expDate)->format("%r%a"); // difference in days
        $diffInt = (int)$diff;

        if($diffInt < 0){
            $rowClass = "expired";
        } elseif($diffInt <= 30){
            $rowClass = "near-expiry";
        } else {
            $rowClass = "safe";
        }
    ?>
        <tr class="<?php echo $rowClass; ?>">
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['category']; ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td><?php echo $row['supplier']; ?></td>
            <td><?php echo $row['batch_no']; ?></td>
            <td><?php echo $row['mfg_date']; ?></td>
            <td><?php echo $row['exp_date']; ?></td>
            <td><?php echo $row['price']; ?></td>
            <td class="days-left"><?php echo $diffInt; ?> days</td>
            <td>
                <a href="edit_drug.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a> 
                <a href="delete_drug.php?id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this drug?');">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<script>
// Real-time search/filter
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#drugsTable tbody tr');
    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        if(name.includes(filter)){
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
