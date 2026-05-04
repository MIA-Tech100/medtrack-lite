<?php
session_start();
include 'db.php';

$error = "";

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users 
WHERE username=? 
AND role='staff' 
AND status='active'
LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])){
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if($user['role'] === 'admin'){
                header("Location: dashboard.php");
            } else {
                header("Location: staff_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | MedTrack Lite</title>
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

        .login-card{
            background:#fff;
            padding:40px;
            width:100%;
            max-width:380px;
            border-radius:12px;
            box-shadow:0 15px 40px rgba(0,0,0,0.2);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn{
            from{opacity:0; transform:translateY(15px);}
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

        label{
            font-weight:bold;
            color:#34495e;
        }

        input{
            width:100%;
            padding:12px;
            margin-top:5px;
            margin-bottom:15px;
            border-radius:6px;
            border:1px solid #ccc;
            font-size:15px;
        }

        input:focus{
            border-color:#3498db;
            outline:none;
        }

        button{
            width:100%;
            padding:12px;
            background:#3498db;
            color:#fff;
            font-size:16px;
            border:none;
            border-radius:6px;
            cursor:pointer;
            transition:0.3s;
        }

        button:hover{
            background:#2980b9;
        }

        .error{
            background:#ffe6e6;
            color:#c0392b;
            padding:10px;
            border-radius:6px;
            margin-bottom:15px;
            text-align:center;
            font-weight:bold;
        }

        .footer{
            text-align:center;
            margin-top:20px;
            font-size:13px;
            color:#95a5a6;
        }

        .badge{
            background:#2ecc71;
            color:#fff;
            padding:4px 10px;
            border-radius:20px;
            font-size:12px;
            display:inline-block;
            margin-bottom:15px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>MedTrack Lite</h2>
    <p class="sub">Secure Staff Login</p>

    <div class="badge">Role-Based Access</div>

    <?php if($error!="") echo "<div class='error'>$error</div>"; ?>

    <form method="post">
        <label>Username</label>
        <input type="text" name="username" required placeholder="Enter username">

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

        <button type="submit" name="login">Login</button>
    </form>

    <div class="footer">
        © <?php echo date('Y'); ?> MedTrack Lite<br>
        Pharmacy Inventory & Sales System
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
