<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* ================= STAFF TOTALS ================= */
function staffTotals($where){
    global $conn;
    $res = $conn->query("
        SELECT u.username,
               SUM(s.quantity_sold) AS total_qty,
               SUM(s.total_price) AS total_amount
        FROM sales s
        JOIN users u ON s.sold_by = u.id
        $where
        GROUP BY u.username
    ");
    return $res->fetch_all(MYSQLI_ASSOC);
}

/* ================= DRUG DETAILS ================= */
function drugDetails($where){
    global $conn;
    $res = $conn->query("
        SELECT d.name AS drug_name,
               s.quantity_sold,
               s.unit_price,
               s.total_price,
               u.username AS staff_name,
               s.sold_at
        FROM sales s
        JOIN drugs d ON s.drug_id = d.id
        JOIN users u ON s.sold_by = u.id
        $where
        ORDER BY s.sold_at DESC
    ");
    return $res->fetch_all(MYSQLI_ASSOC);
}

/* ================= DATA ================= */
$todayStaff   = staffTotals("WHERE DATE(s.sale_date)=CURDATE()");
$monthStaff   = staffTotals("WHERE MONTH(s.sale_date)=MONTH(CURDATE()) AND YEAR(s.sale_date)=YEAR(CURDATE())");
$yearStaff    = staffTotals("WHERE YEAR(s.sale_date)=YEAR(CURDATE())");

$todayDrugs   = drugDetails("WHERE DATE(s.sale_date)=CURDATE()");
$monthDrugs   = drugDetails("WHERE MONTH(s.sale_date)=MONTH(CURDATE()) AND YEAR(s.sale_date)=YEAR(CURDATE())");
$yearDrugs    = drugDetails("WHERE YEAR(s.sale_date)=YEAR(CURDATE())");

/* ================= TOTAL CASH ================= */
function cash($where){
    global $conn;
    return $conn->query("SELECT IFNULL(SUM(total_price),0) t FROM sales $where")
                ->fetch_assoc()['t'];
}

$todayTotal  = cash("WHERE DATE(sale_date)=CURDATE()");
$monthTotal  = cash("WHERE MONTH(sale_date)=MONTH(CURDATE()) AND YEAR(sale_date)=YEAR(CURDATE())");
$yearTotal   = cash("WHERE YEAR(sale_date)=YEAR(CURDATE())");
?>

<script>
const salesData = {
    today: {
        title: "📅 Staff Daily Sales",
        staff: <?=json_encode($todayStaff)?>,
        drugs: <?=json_encode($todayDrugs)?>
    },
    month: {
        title: "📆 Staff Monthly Sales",
        staff: <?=json_encode($monthStaff)?>,
        drugs: <?=json_encode($monthDrugs)?>
    },
    year: {
        title: "📊 Staff Yearly Sales",
        staff: <?=json_encode($yearStaff)?>,
        drugs: <?=json_encode($yearDrugs)?>
    }
};
</script>

<!DOCTYPE html>
<html>
<head>
<title>Sales History | MedTrack Lite</title>

<style>
body{font-family:Arial;background:#f4f6f8;margin:20px}
h2{display:flex;justify-content:space-between;align-items:center}
.cards{display:flex;gap:20px;margin:20px 0}
.card{flex:1;padding:18px;border-radius:12px;color:white;font-weight:bold;text-align:center;cursor:pointer}
.today{background:#1abc9c}
.month{background:#34495e}
.year{background:#9b59b6}
button{padding:7px 14px;border:none;border-radius:6px;cursor:pointer}
.print{background:#3498db;color:white}
.back{background:#7f8c8d;color:white}

table{width:100%;border-collapse:collapse;background:white;margin-top:15px}
th,td{border:1px solid #ccc;padding:10px;text-align:center}
th{background:#34495e;color:white}
tr:nth-child(even){background:#f9f9f9}

#staffBox,#drugBox{display:none}
</style>
</head>

<body>

<h2>
📊 Sales History (Admin)
<div>
<button class="back" onclick="location.href='dashboard.php'">⬅ Back to Dashboard</button>
<button class="print" onclick="window.print()">🖨 Print</button>
</div>
</h2>

<div class="cards">
    <div class="card today" onclick="showSales('today')">
        Today<br>KES <?=number_format($todayTotal,2)?>
    </div>
    <div class="card month" onclick="showSales('month')">
        Month<br>KES <?=number_format($monthTotal,2)?>
    </div>
    <div class="card year" onclick="showSales('year')">
        Year<br>KES <?=number_format($yearTotal,2)?>
    </div>
</div>

<!-- STAFF TOTALS -->
<div id="staffBox">
<h3 id="staffTitle"></h3>
<table>
<tr><th>Staff</th><th>Total Quantity</th><th>Total Amount (KES)</th></tr>
<tbody id="staffBody"></tbody>
</table>
</div>

<!-- DRUG DETAILS -->
<div id="drugBox">
<h3>💊 Drugs Sold</h3>
<table>
<tr>
<th>Time</th>
<th>Drug</th>
<th>Qty</th>
<th>Unit Price</th>
<th>Total</th>
<th>Staff</th>
</tr>
<tbody id="drugBody"></tbody>
</table>
</div>

<script>
function showSales(type){
    const d = salesData[type];

    /* STAFF */
    document.getElementById("staffTitle").innerText = d.title;
    const sb = document.getElementById("staffBody");
    sb.innerHTML = "";
    d.staff.forEach(s=>{
        sb.innerHTML += `
        <tr>
            <td>${s.username}</td>
            <td>${s.total_qty}</td>
            <td><b>KES ${parseFloat(s.total_amount).toFixed(2)}</b></td>
        </tr>`;
    });

    /* DRUGS */
    const db = document.getElementById("drugBody");
    db.innerHTML = "";
    d.drugs.forEach(dr=>{
        db.innerHTML += `
        <tr>
            <td>${dr.sold_at}</td>
            <td>${dr.drug_name}</td>
            <td>${dr.quantity_sold}</td>
            <td>${parseFloat(dr.unit_price).toFixed(2)}</td>
            <td>${parseFloat(dr.total_price).toFixed(2)}</td>
            <td>${dr.staff_name}</td>
        </tr>`;
    });

    document.getElementById("staffBox").style.display = "block";
    document.getElementById("drugBox").style.display = "block";
}
</script>

</body>
</html>
