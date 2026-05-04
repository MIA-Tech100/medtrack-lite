<?php
session_start();
include 'db.php';

/* ✅ Auto-mark expired drugs as inactive */
$conn->query("UPDATE drugs SET status='inactive' WHERE exp_date < CURDATE() AND status='active'");

/* ✅ Staff-only access */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: staff_login.php");
    exit();
}

/* =========================
   BULK SALE (ONE CUSTOMER)
========================= */
if (isset($_POST['ajax_bulk_sell'])) {
    $cart = json_decode($_POST['cart'], true);

    if (!$cart || count($cart) == 0) {
        echo json_encode(["status"=>"error","message"=>"Cart is empty"]);
        exit();
    }

    $receiptRows = "";
    $grandTotal = 0;
    
$sold_by = (int) $_SESSION['user_id'];

    foreach ($cart as $item) {
        $drug_id = intval($item['id']);
        $qty     = intval($item['qty']);

        $stmt = $conn->prepare("SELECT * FROM drugs WHERE id=? AND status='active'");
        $stmt->bind_param("i", $drug_id);
        $stmt->execute();
        $drug = $stmt->get_result()->fetch_assoc();

        if (!$drug) {
            echo json_encode(["status"=>"error","message"=>"Drug not found"]);
            exit();
        }

        if ($qty > $drug['quantity']) {
            echo json_encode(["status"=>"error","message"=>"Insufficient stock for {$drug['name']}"]);
            exit();
        }

        /* Deduct stock */
        $stmt = $conn->prepare("UPDATE drugs SET quantity = quantity - ? WHERE id=?");
        $stmt->bind_param("ii", $qty, $drug_id);
        $stmt->execute();

        /* Insert into sales table */
$unit_price  = floatval($drug['price']);
$total_price = $unit_price * $qty;

$stmt = $conn->prepare("
    INSERT INTO sales 
    (drug_id, quantity_sold, unit_price, total_price, sold_by, sale_date, sold_at)
    VALUES (?, ?, ?, ?, ?, CURDATE(), NOW())
");

$stmt->bind_param(
    "iiddi",
    $drug_id,
    $qty,
    $unit_price,
    $total_price,
    $sold_by
);

if(!$stmt->execute()){
    echo json_encode([
        "status"=>"error",
        "message"=>"Sales insert failed: ".$stmt->error
    ]);
    exit();
}

        /* Build receipt */
        $grandTotal += $total_price;
        $receiptRows .= "
            <tr>
                <td>{$drug['name']}</td>
                <td>{$qty}</td>
                <td>{$unit_price} KES</td>
                <td><b>{$total_price} KES</b></td>
            </tr>";
    }

    /* Receipt */
    $receipt = "
    <h3 style='text-align:center;'>🧾 MedTrack Lite Receipt</h3>
    <hr>

    <table width='100%' border='1' cellspacing='0' cellpadding='6'>
        <tr>
            <th>Drug</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
        {$receiptRows}
    </table>

    <h3 style='text-align:right;'>Grand Total: {$grandTotal} KES</h3>

    <p><strong>Served By:</strong> {$_SESSION['username']}</p>
    <p><strong>Date:</strong> ".date("Y-m-d H:i:s")."</p>

    <hr>

    <div style='text-align:center; margin-top:15px;'>
        <button onclick='printReceipt()'
                style='padding:8px 15px; margin-right:10px;
                       background:#2ecc71; color:white;
                       border:none; border-radius:6px; cursor:pointer;'>
            🖨 Print
        </button>

        <button onclick='closeReceipt()'
                style='padding:8px 15px;
                       background:#e74c3c; color:white;
                       border:none; border-radius:6px; cursor:pointer;'>
            ❌ Close
        </button>
    </div>

    <p style='text-align:center; margin-top:10px;'>
        Thank you for choosing MedTrack Lite 💊
    </p>
";

    echo json_encode([
        "status"=>"success",
        "receipt"=>$receipt
    ]);
    exit();
}

/* Fetch active drugs */
$drugs = $conn->query("SELECT * FROM drugs WHERE status='active' AND quantity > 0 ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Staff Dashboard - MedTrack Lite</title>
<style>
body { font-family:'Segoe UI',Arial,sans-serif; background:#eef2f7; margin:0; }
.header { background:linear-gradient(135deg,#3498db,#2ecc71); color:white; padding:20px; display:flex; justify-content:space-between; align-items:center; }
.header a { color:white; text-decoration:none; background:rgba(255,255,255,0.2); padding:8px 15px; border-radius:20px; }
.container { padding:25px; }
#searchInput { width:300px; padding:10px; border-radius:20px; border:1px solid #ccc; margin-bottom:15px; }
table { width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
th { background:#2c3e50; color:white; padding:12px; }
td { padding:10px; text-align:center; }
tr:nth-child(even){ background:#f9f9f9; }
tr:hover{ background:#eef6ff; }
button { background:#3498db; color:white; border:none; padding:6px 14px; border-radius:20px; cursor:pointer; }
button:hover{ background:#2ecc71; }
#cart { margin-top:20px; background:white; padding:15px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
#receiptPopup { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; width:380px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.3); z-index:1000; }
.cart-item { margin-bottom:5px; }
.remove-btn { color:red; cursor:pointer; margin-left:10px; }
</style>
</head>
<body>

<div class="header">
    <h2>👩‍⚕️ Staff Dashboard – MedTrack Lite</h2>
    <a href="staff_logout.php">Logout</a>
</div>

<div class="container">
<input type="text" id="searchInput" placeholder="🔍 Search drug by name..." onkeyup="searchDrugs()">

<table>
<tr>
    <th>Drug</th>
    <th>Category</th>
    <th>Price (KES)</th>
    <th>Available</th>
    <th>Qty</th>
    <th>Action</th>
</tr>
<?php while($row=$drugs->fetch_assoc()): ?>
<tr class="drug-row">
    <td><?= $row['name']; ?></td>
    <td><?= $row['category']; ?></td>
    <td><?= number_format($row['price'],2); ?></td>
    <td><?= $row['quantity']; ?></td>
    <td><input type="number" min="1" id="q<?= $row['id']; ?>"></td>
    <td><button onclick="addToCart(<?= $row['id']; ?>,'<?= addslashes($row['name']); ?>',<?= $row['price']; ?>)">Add</button></td>
</tr>
<?php endwhile; ?>
</table>

<div id="cart">
    <h3>🛒 Customer Cart</h3>
    <div id="cartItems">No items added</div>
    <br>
    <button onclick="checkout()">✅ Complete Sale</button>
</div>
</div>

<div id="receiptPopup"></div>

<script>
let cart = [];

/* Add drug to cart */
function addToCart(id,name,price){
    let qtyInput = document.getElementById('q'+id);
    let qty = parseInt(qtyInput.value);
    if(!qty || qty<=0){ alert("Enter valid quantity"); return; }

    let exists = cart.find(i=>i.id===id);
    if(exists){
        exists.qty += qty;
    } else {
        cart.push({id:id,name:name,price:price,qty:qty});
    }
    qtyInput.value="";
    renderCart();
}

/* Remove item from cart */
function removeFromCart(index){
    cart.splice(index,1);
    renderCart();
}

/* Render cart */
function renderCart(){
    let container = document.getElementById('cartItems');
    if(cart.length===0){
        container.innerHTML="No items added";
        return;
    }
    let html="";
    cart.forEach((i,index)=>{
        html += `<div class="cart-item">${i.name} — ${i.qty} x ${i.price} KES
                 <span class="remove-btn" onclick="removeFromCart(${index})">✖</span></div>`;
    });
    container.innerHTML = html;
}

/* Checkout */
function checkout(){
    if(cart.length===0){ alert("Cart is empty"); return; }

    let fd = new FormData();
    fd.append('ajax_bulk_sell',1);
    fd.append('cart',JSON.stringify(cart));

    fetch('staff_dashboard.php',{
        method:'POST',
        body:fd
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.status==='success'){
            document.getElementById('receiptPopup').innerHTML = d.receipt;
            document.getElementById('receiptPopup').style.display='block';
            cart=[];
            renderCart();
        } else {
            alert(d.message);
        }
    });
}

/* Print receipt */
function printReceipt(){
    let c=document.getElementById('receiptPopup').innerHTML;
    let w=window.open('','_blank');
    w.document.write(c);
    w.print();
    w.close();
}

/* Close receipt */
function closeReceipt(){
    document.getElementById('receiptPopup').style.display='none';
    document.getElementById('receiptPopup').innerHTML='';
}

/* Search */
function searchDrugs(){
    let v=document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.drug-row').forEach(r=>{
        r.style.display = r.innerText.toLowerCase().includes(v) ? '' : 'none';
    });
}
</script>

</body>
</html>
