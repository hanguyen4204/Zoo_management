<?php
session_start();
include "connection.php";

/* Chỉ cho user đã login */
if (empty($_SESSION['id_user'])) {
    header("Location: homescreen.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* Lấy lịch sử đặt vé của user */
$res = mysqli_query($link, "
    SELECT id_order, quantity, visit_date, order_date
    FROM ticket_orders
    WHERE id_customer = $id_user
    ORDER BY order_date DESC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Ticket Booking History</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
body{
    background:#f4f6f4;
    font-family:'Segoe UI',sans-serif;
}

.history-box{
    max-width:900px;
    margin:80px auto;
    background:#fff;
    padding:40px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.history-box h2{
    text-align:center;
    font-weight:800;
    color:#0b3d2e;
    margin-bottom:30px;
}

.table th{
    background:#f4f6f4;
    font-weight:700;
}

.btn-back{
    margin-top:20px;
    display:inline-block;
    padding:10px 26px;
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

<div class="history-box">

    <h2>🎟 Ticket Booking History</h2>

    <?php if (mysqli_num_rows($res) == 0): ?>
        <p class="text-center">You have not booked any tickets yet.</p>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#Order ID</th>
                    <th>Number of tickets</th>
                    <th>Visit date</th>
                    <th>Order time</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?= $row['id_order'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= date("d/m/Y", strtotime($row['visit_date'])) ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($row['order_date'])) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="text-center">
        <a href="homescreen.php" class="btn-back">← Back to Home</a>
    </div>

</div>

</body>
</html>
