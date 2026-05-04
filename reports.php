<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Fetch data for reports
$total_drugs = $conn->query("SELECT COUNT(*) as total FROM drugs")->fetch_assoc()['total'];
$expired = $conn->query("SELECT COUNT(*) as total FROM drugs WHERE exp_date < CURDATE()")->fetch_assoc()['total'];
$expiring = $conn->query("SELECT COUNT(*) as total FROM drugs WHERE exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND exp_date >= CURDATE()")->fetch_assoc()['total'];

// Data for category chart
$category_data = $conn->query("SELECT category, COUNT(*) as count FROM drugs GROUP BY category");
$categories = [];
$counts = [];
while($row = $category_data->fetch_assoc()){
    $categories[] = $row['category'];
    $counts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports - MedTrack Lite</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin:20px; }
        h2 { color:#333; }
        a { text-decoration: none; color: #3498db; margin-right:10px; }
        .report-summary { margin-top:20px; padding:10px; background:white; border-radius:10px; width:400px; }
        .report-summary p { font-size:16px; margin:5px 0; }
        button.print-btn { padding:10px 20px; background:#3498db; color:white; border:none; border-radius:5px; cursor:pointer; margin-top:10px; }
        canvas { margin-top:30px; background:white; padding:10px; border-radius:10px; }
    </style>
</head>
<body>
<h2>Reports - MedTrack Lite</h2>
<a href="dashboard.php">Back to Dashboard</a>

<div class="report-summary">
    <p>Total Drugs: <?php echo $total_drugs; ?></p>
    <p>Expiring Soon (30 days): <?php echo $expiring; ?></p>
    <p>Expired: <?php echo $expired; ?></p>

    <!-- Print Report Button -->
    <button onclick="window.print()" class="print-btn">Print Report</button>
</div>

<canvas id="categoryChart" width="400" height="200"></canvas>

<script>
const ctx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
            label: 'Drugs per Category',
            data: <?php echo json_encode($counts); ?>,
            backgroundColor: 'rgba(52, 152, 219, 0.7)',
            borderColor: 'rgba(41, 128, 185, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: { display: true, text: 'Drugs by Category' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
