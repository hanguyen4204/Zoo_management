<?php
include "connection.php";

if(!isset($_GET['id'])){
    die("Invalid request");
}

$id_order = intval($_GET['id']);

/* Lấy thông tin đơn mua */
$res = mysqli_query($link,"
    SELECT name, quantity, visit_date, order_date
    FROM ticket_orders
    WHERE id_order = $id_order
");

if(mysqli_num_rows($res) == 0){
    die("Order not found");
}

$order = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Ticket Purchased Successfully</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
body{
    background:#f4f6f4;
    font-family:'Segoe UI',sans-serif;
}

.success-box{
    max-width:600px;
    margin:100px auto;
    background:#fff;
    padding:50px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    text-align:center;
}

.success-box h1{
    font-size:34px;
    font-weight:800;
    color:#0b3d2e;
    margin-bottom:30px;
}

.info{
    text-align:left;
    margin-top:30px;
}

.info p{
    font-size:16px;
    margin-bottom:12px;
}

.label{
    display:inline-block;
    min-width:180px;
    font-weight:700;
    color:#333;
}

.btn-home{
    margin-top:30px;
    display:inline-block;
    padding:12px 30px;
    border-radius:999px;
    background:#f4f91d;
    color:#0b3d2e;
    font-weight:800;
    text-decoration:none;
}
.btn-home:hover{
    background:#e6eb00;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="success-box">

    <!-- HEADLINE -->
    <h1>🎉 Congratulations! Your ticket purchase was successful</h1>

    <!-- INFO -->
    <div class="info">
        <p>
            <span class="label">Full name:</span>
            <?= htmlspecialchars($order['name']) ?>
        </p>
        <p>
            <span class="label">Number of tickets:</span>
            <?= $order['quantity'] ?>
        </p>
        <p>
            <span class="label">Visit date:</span>
            <?= date("d/m/Y", strtotime($order['visit_date'])) ?>
        </p>
        <p>
            <span class="label">Order time:</span>
            <?= date("d/m/Y H:i", strtotime($order['order_date'])) ?>
        </p>
    </div>

    <!-- BACK HOME -->
    <a href="homescreen.php" class="btn-home">
        Back to Home
    </a>

</div>

</body>
</html>
