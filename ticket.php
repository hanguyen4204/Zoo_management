<?php
session_start();
include "connection.php";

/*
  Giả sử:
  - User đã login
  - Bảng users có khóa chính: id_user
*/
$id_customer = $_SESSION['id_user'] ?? 1;

$PRICE = 80000;

if(isset($_POST['confirm'])){

    $name  = mysqli_real_escape_string($link, $_POST['name']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $phone = mysqli_real_escape_string($link, $_POST['phone']);
    $qty   = intval($_POST['quantity']);
    $visit = $_POST['visit_date'];

    // INSERT đơn mua vé (KHỚP DB)
    mysqli_query($link,"
        INSERT INTO ticket_orders
        (id_customer, name, email, phone, quantity, visit_date)
        VALUES
        ($id_customer,'$name','$email','$phone',$qty,'$visit')
    ");

    // Lấy id_order vừa tạo
    $id_order = mysqli_insert_id($link);

    // Redirect sang trang thành công
    header("Location: ticket_success.php?id=$id_order");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Buy Ticket</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
body{
    background:#f4f6f4;
    font-family:'Segoe UI',sans-serif;
}

.ticket-box{
    max-width:600px;
    margin:80px auto;
    background:#fff;
    padding:40px;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.ticket-box h2{
    text-align:center;
    font-weight:800;
    color:#0b3d2e;
    margin-bottom:30px;
}

.price-box{
    background:#fffdf2;
    padding:20px;
    border-radius:12px;
    margin-top:20px;
}

.total{
    font-size:22px;
    font-weight:800;
    color:#0b3d2e;
}

.btn-confirm{
    background:#f4f91d;
    color:#0b3d2e;
    font-weight:800;
    border:none;
    padding:14px;
    width:100%;
    border-radius:999px;
    margin-top:25px;
}

.btn-back{
    display:block;
    text-align:center;
    margin-top:12px;
    padding:12px;
    border-radius:999px;
    border:2px solid #0b3d2e;
    color:#0b3d2e;
    font-weight:700;
    text-decoration:none;
}
.btn-back:hover{
    background:#0b3d2e;
    color:#fff;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="ticket-box">

    <h2>🎟️ Buy Ticket</h2>

    <form method="post">

        <div class="form-group">
            <label>Họ và tên</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Số lượng vé</label>
            <input type="number" id="qty" name="quantity"
                   class="form-control" value="1" min="1" required>
        </div>

        <div class="form-group">
            <label>Ngày tham quan</label>
            <input type="date" name="visit_date" class="form-control" required>
        </div>

        <div class="price-box">
            <p>Giá 1 vé: <strong>80.000 đ</strong></p>
            <p class="total">
                Tổng tiền:
                <span id="total">80.000</span> đ
            </p>
        </div>

        <button type="submit" name="confirm" class="btn-confirm">
            Confirm mua
        </button>

        <a href="homescreen.php" class="btn-back">
            ← Back to Home
        </a>

    </form>

</div>

<script>
const PRICE = 80000;
const qty = document.getElementById('qty');
const total = document.getElementById('total');

qty.addEventListener('input', () => {
    total.innerText = (qty.value * PRICE).toLocaleString('vi-VN');
});
</script>

</body>
</html>
