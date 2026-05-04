<?php
session_start();
include 'db.php';

// Only allow admin to add drugs
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit();
}

// Handle form submission
if(isset($_POST['add_drug'])){
    $name = $_POST['name'];
    $category = $_POST['category'];
    $quantity = intval($_POST['quantity']);
    $supplier = $_POST['supplier'];
    $batch_no = $_POST['batch_no'];
    $mfg_date = $_POST['mfg_date'];
    $exp_date = $_POST['exp_date'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);

    // Basic validation
    if(empty($name) || empty($category) || empty($quantity) || empty($supplier) || empty($batch_no) || empty($mfg_date) || empty($exp_date) || empty($price)){
        $error = "All fields are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO drugs (name, category, quantity, supplier, batch_no, mfg_date, exp_date, description, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssisssssd', $name, $category, $quantity, $supplier, $batch_no, $mfg_date, $exp_date, $description, $price);

        if($stmt->execute()){
            $success = "Drug added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Drug - MedTrack Lite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Add Drug</h2>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<div class="modal">
<form method="post">
    <label>Drug Name:</label>
    <input type="text" name="name" required><br><br>

    <label>Category:</label>
    <input type="text" name="category" required><br><br>

    <label>Quantity:</label>
    <input type="number" name="quantity" min="1" required><br><br>

    <label>Supplier:</label>
    <input type="text" name="supplier" required><br><br>

    <label>Batch Number:</label>
    <input type="text" name="batch_no" required><br><br>

    <label>Manufacture Date:</label>
    <input type="date" name="mfg_date" required><br><br>

    <label>Expiry Date:</label>
    <input type="date" name="exp_date" required><br><br>

    <label>Price (KES):</label>
    <input type="number" name="price" min="0" step="0.01" required><br><br>

    <label>Description:</label>
    <textarea name="description"></textarea><br><br>

    <button type="submit" name="add_drug">Add Drug</button>
    <a href="dashboard.php">Back to Dashboard</a>
</form>
</div>
</body>
</html>
