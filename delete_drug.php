<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM drugs WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: view_drugs.php?success=deleted");
exit();
?>
