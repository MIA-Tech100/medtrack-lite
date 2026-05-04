<?php
session_start();
include "db.php";   

$error = "";

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MedTrack Lite | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body{
            margin:0;
            height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg,#3498db,#2ecc71);
            font-family:Arial, sans-serif;
        }

        .login-box{
            width:100%;
            max-width:380px;
            padding:35px;
            background:white;
            box-shadow:0 15px 40px rgba(0,0,0,0.2);
            border-radius:12px;
            animation: slideIn 0.7s ease;
        }

        @keyframes slideIn{
            from{opacity:0; transform:translateY(20px);}
            to{opacity:1; transform:translateY(0);}
        }

        h2{
            text-align:center;
            margin-bottom:5px;
            color:#2c3e50;
        }

        p.sub{
            text-align:center;
            color:#7f8c8d;
            font-size:14px;
            margin-bottom:25px;
        }

        input{
            width:100%;
            padding:12px;
            margin:10px 0;
            border-radius:6px;
            border:1px solid #ccc;
            font-size:15px;
        }

        input:focus{
            outline:none;
            border-color:#3498db;
        }

        button{
            width:100%;
            padding:12px;
            background:#2ecc71;
            border:none;
            color:white;
            font-size:16px;
            border-radius:6px;
            cursor:pointer;
            transition:0.3s;
        }

        button:hover{
            background:#27ae60;
        }

        .error{
            background:#ffe6e6;
            color:#c0392b;
            padding:10px;
            border-radius:6px;
            text-align:center;
            margin-bottom:15px;
            font-weight:bold;
        }

        .footer{
            text-align:center;
            margin-top:20px;
            font-size:13px;
            color:#95a5a6;
        }

        .badge{
            display:inline-block;
            background:#3498db;
            color:white;
            padding:5px 12px;
            border-radius:20px;
            font-size:12px;
            margin-bottom:15px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>MedTrack Lite</h2>
    <p class="sub">Secure Admin Access</p>

    <div class="badge">Inventory Management System</div>

    <?php if($error != "") echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <div style="position:relative;">
    <input type="password" name="password" id="password" placeholder="Password" required>

    <span onclick="togglePassword()" 
          style="
            position:absolute;
            right:12px;
            top:50%;
            transform:translateY(-50%);
            cursor:pointer;
            font-size:14px;
            color:#3498db;
            font-weight:bold;
          ">
        👁
    </span>
</div>

        <button name="login">Login</button>
    </form>

    <div class="footer">
        © <?php echo date('Y'); ?> MedTrack Lite<br>
        Pharmacy Inventory System
    </div>
</div>
<script>
function togglePassword(){
    const pass = document.getElementById("password");
    if(pass.type === "password"){
        pass.type = "text";
    } else {
        pass.type = "password";
    }
}
</script>

</body>
</html>
