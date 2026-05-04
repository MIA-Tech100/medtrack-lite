<?php
session_start();
include 'db.php';

/* ADMIN ONLY */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* ADD STAFF */
$msg = "";
if (isset($_POST['add_staff'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, status) 
         VALUES (?, ?, 'staff', 'active')"
    );
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        $msg = "✅ Staff added successfully";
    } else {
        $msg = "❌ Username already exists";
    }
}

/* CHANGE STAFF PASSWORD */
if (isset($_POST['change_password'])) {
    $id = intval($_POST['staff_id']);
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=? AND role='staff'");
    $stmt->bind_param("si", $new_password, $id);

    if ($stmt->execute()) {
        $msg = "✅ Password updated successfully for staff ID $id";
    } else {
        $msg = "❌ Failed to update password";
    }
}

/* ACTIVATE / DEACTIVATE STAFF */
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);

    $conn->query(
        "UPDATE users 
         SET status = IF(status='active','inactive','active') 
         WHERE id=$id AND role='staff'"
    );

    header("Location: manage_staff.php");
    exit();
}

/* FETCH STAFF */
$staff = $conn->query(
    "SELECT * FROM users WHERE role='staff' ORDER BY username"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Staff - Admin</title>

<style>
body {
    font-family: Arial;
    background:#eef2f7;
    margin:0;
}
.container {
    width:90%;
    margin:30px auto;
}
h2 {
    color:#2c3e50;
}
form {
    background:white;
    padding:20px;
    border-radius:10px;
    margin-bottom:25px;
}
input {
    padding:10px;
    margin:5px;
}
button {
    padding:10px 15px;
    border:none;
    border-radius:20px;
    background:#3498db;
    color:white;
    cursor:pointer;
}
table {
    width:100%;
    background:white;
    border-collapse:collapse;
    border-radius:10px;
    overflow:hidden;
}
th {
    background:#2c3e50;
    color:white;
    padding:12px;
}
td {
    padding:12px;
    text-align:center;
}
.active { color:green; font-weight:bold; }
.inactive { color:red; font-weight:bold; }
a {
    text-decoration:none;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="container">

<h2>👥 Manage Staff</h2>

<?php if($msg): ?>
<p><b><?= $msg ?></b></p>
<?php endif; ?>

<!-- ADD STAFF -->
<form method="POST">
    <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
    <h3>➕ Add Staff</h3>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button name="add_staff">Add Staff</button>
</form>

<!-- STAFF LIST -->
<table>
<tr>
    <th>#</th>
    <th>Username</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php $i=1; while($row=$staff->fetch_assoc()): ?>
<tr>
    <td><?= $i++; ?></td>
    <td><?= $row['username']; ?></td>
    <td class="<?= $row['status']; ?>">
        <?= strtoupper($row['status']); ?>
    </td>
    <td>
        <a href="?toggle=<?= $row['id']; ?>">
            <?= $row['status']=='active' ? '🚫 Deactivate' : '✅ Activate'; ?>
        </a> |
        <!-- CHANGE PASSWORD FORM (inline) -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="staff_id" value="<?= $row['id']; ?>">
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit" name="change_password">🔑 Change</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>
</body>
</html>
