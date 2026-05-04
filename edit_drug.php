<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM drugs WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows != 1){
    echo "Drug not found!";
    exit();
}
$drug = $result->fetch_assoc();

// Handle form submission
if(isset($_POST['update_drug'])){
    $name = $_POST['name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $supplier = $_POST['supplier'];
    $batch_no = $_POST['batch_no'];
    $mfg_date = $_POST['mfg_date'];
    $exp_date = $_POST['exp_date'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $stmt = $conn->prepare(
        "UPDATE drugs 
         SET name=?, category=?, quantity=?, supplier=?, batch_no=?, mfg_date=?, exp_date=?, description=?, price=? 
         WHERE id=?"
    );

    $stmt->bind_param(
        "ssisssssdi",
        $name,
        $category,
        $quantity,
        $supplier,
        $batch_no,
        $mfg_date,
        $exp_date,
        $description,
        $price,
        $id
    );

    if($stmt->execute()){
        header("Location: view_drugs.php?success=updated");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Drug - MedTrack Lite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Edit Drug</h2>
<a href="view_drugs.php">Back to View Drugs</a>

<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<div class="modal">
<form method="post">
    <label>Drug Name:</label>
    <input type="text" name="name" value="<?php echo $drug['name']; ?>" required><br><br>

    <label>Category:</label>
    <input type="text" name="category" value="<?php echo $drug['category']; ?>" required><br><br>

    <label>Quantity:</label>
    <input type="number" name="quantity" value="<?php echo $drug['quantity']; ?>" min="1" required><br><br>

    <label>Supplier:</label>
    <input type="text" name="supplier" value="<?php echo $drug['supplier']; ?>"><br><br>

    <label>Batch Number:</label>
    <input type="text" name="batch_no" value="<?php echo $drug['batch_no']; ?>"><br><br>

    <label>Manufacture Date:</label>
    <input type="date" name="mfg_date" value="<?php echo $drug['mfg_date']; ?>"><br><br>

    <label>Expiry Date:</label>
    <input type="date" name="exp_date" value="<?php echo $drug['exp_date']; ?>" required><br><br>

    <label>Price (KES):</label>
    <input type="number" name="price" value="<?php echo $drug['price']; ?>" step="0.01" min="0" required><br><br>

    <label>Description:</label>
    <textarea name="description"><?php echo $drug['description']; ?></textarea><br><br>

    <button type="submit" name="update_drug">Update Drug</button>
</form>
</div>

</body>
</html>
