<?php
session_start();
include "connection.php";

// ================= 1. LOGIC USER (GIỐNG HOMESCREEN) =================
$my_avatar = 'uploads/default_user.png';
$my_name = 'Guest';

// Kiểm tra login
if (isset($_SESSION['id_user'])) {
    $current_user_id = $_SESSION['id_user'];
    $user_query = mysqli_query($link, "SELECT * FROM users WHERE id_user = '$current_user_id'");
    
    if ($current_user = mysqli_fetch_array($user_query)) {
        $my_name = $current_user['username'];
        if (!empty($current_user['photo'])) {
            $my_avatar = $current_user['photo'];
        }
    }
}

// ================= 2. LOGIC TÌM KIẾM ĐỘNG VẬT =================
$search_term = "";
$sql = "SELECT * FROM table1"; // Mặc định lấy hết

if (isset($_GET['search']) && !empty($_GET['q'])) {
    $search_term = mysqli_real_escape_string($link, $_GET['q']);
    // Tìm theo Tên HOẶC Loài
    $sql = "SELECT * FROM table1 
            WHERE AName LIKE '%$search_term%' 
            OR Species LIKE '%$search_term%'";
}

$res = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Our Animals | Zoo Explorer</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<style>
    /* ================= GLOBAL ================= */
    body { background-color: #f4f6f4; font-family: 'Segoe UI', sans-serif; }

    /* ================= HEADER STYLE (CHUẨN TỪ HOMESCREEN) ================= */
    .zoo-header { background: #fff; padding: 15px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; }
    .zoo-nav { display: flex; align-items: center; justify-content: space-between; }
    .zoo-logo { font-size: 24px; font-weight: 800; color: #0b3d2e; text-decoration: none; }
    .zoo-logo:hover { color: #0b3d2e; text-decoration: none; }

    /* MENU */
    .zoo-menu { display: flex; gap: 30px; }
    .menu-item > a { 
        text-decoration: none; color: #222; font-weight: 600; font-size: 16px;
        padding: 10px 0; display: inline-block; transition: 0.2s;
    }
    .menu-item > a:hover { color: #0b3d2e; }

    /* ACTIONS */
    .zoo-actions { display: flex; align-items: center; gap: 20px; }
    .buy-ticket {
        background: #f4f91d; color: #0b3d2e; font-weight: 700;
        padding: 10px 25px; border-radius: 999px; text-decoration: none;
        transition: 0.25s ease; white-space: nowrap;
    }
    .buy-ticket:hover { background: #e6eb00; text-decoration: none; color: #0b3d2e; }

    /* USER AVATAR */
    .user-menu { position: relative; }
    .user-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        background: #0b3d2e; color: #fff;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; user-select: none;
        overflow: hidden; border: 2px solid #eee;
    }
    .user-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .user-dropdown {
        position: absolute; top: 52px; right: 0; width: 240px;
        background: #fff; border-radius: 14px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.15); padding: 10px 0;
        opacity: 0; visibility: hidden; transform: translateY(10px);
        transition: 0.25s ease; z-index: 2000;
    }
    .user-menu:hover .user-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
    .user-dropdown a { display: block; padding: 12px 20px; color: #222; text-decoration: none; font-weight: 600; }
    .user-dropdown a:hover { background: #f4f6f4; color: #0b3d2e; }

    /* ================= SEARCH HERO SECTION ================= */
    .animal-hero {
        /*  Background context */
        background: linear-gradient(rgba(11, 61, 46, 0.8), rgba(11, 61, 46, 0.7)), url('uploads/Screenshot\ 2025-11-18\ 122311.png') center/cover;
        padding: 80px 0;
        text-align: center;
        color: white;
        margin-bottom: 40px;
    }
    .animal-hero h1 { font-weight: 900; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; }
    
    .search-box { max-width: 600px; margin: 0 auto; position: relative; }
    .search-input {
        width: 100%; padding: 15px 25px; border-radius: 50px;
        border: none; font-size: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); outline: none;
    }
    .search-btn {
        position: absolute; right: 5px; top: 5px;
        background: #f4f91d; color: #0b3d2e; border: none;
        width: 45px; height: 45px; border-radius: 50%;
        cursor: pointer; transition: 0.3s;
    }
    .search-btn:hover { background: #e6eb00; transform: scale(1.05); }

    /* ================= ANIMAL GRID CARDS ================= */
    .animal-card-wrapper { margin-bottom: 30px; }
    .animal-card {
        background: #fff; border-radius: 15px; overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%; display: flex; flex-direction: column;
    }
    .animal-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
    
    .card-img-top { height: 220px; width: 100%; object-fit: cover; }
    
    .card-body { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
    
    .species-tag {
        font-size: 12px; font-weight: 700; color: #f4f91d; background: #0b3d2e;
        padding: 4px 10px; border-radius: 20px; align-self: flex-start;
        margin-bottom: 10px; text-transform: uppercase;
    }
    
    .animal-name { font-size: 20px; font-weight: 800; color: #333; margin-bottom: 8px; }
    .animal-area { color: #666; font-size: 14px; margin-bottom: 15px; display: flex; align-items: center; gap: 5px; }
    
    .btn-view {
        margin-top: auto; display: block; text-align: center;
        background: #f4f6f4; color: #0b3d2e; font-weight: 700;
        padding: 10px; border-radius: 10px; text-decoration: none; transition: 0.2s;
    }
    .btn-view:hover { background: #0b3d2e; color: #f4f91d; text-decoration: none; }

    footer { background: #0b3d2e; color: white; padding: 30px 0; margin-top: 50px; text-align: center; }
</style>
</head>

<body>

<header class="zoo-header">
    <div class="container zoo-nav">
        <a href="homescreen.php" class="zoo-logo">🐾 Zoo Explorer</a>

        <nav class="zoo-menu d-none d-md-flex">
             <div class="menu-item"><a href="homescreen.php" style="color:#0b3d2e; font-weight:700;">Home</a></div>
            <div class="menu-item"><a href="Animal-display.php">Animals</a></div> 
            <div class="menu-item"><a href="activity.php">Activities</a></div>
            <div class="menu-item"><a href="zone.php">Zones</a></div>
            <div class="menu-item"><a href="zoo_social.php">Community</a></div>
            <div class="menu-item"><a href="about_us.php">About us</a></div>
        </nav>

        <div class="zoo-actions">
            <a href="ticket.php" class="buy-ticket d-none d-sm-block">BUY TICKETS</a>

            <div class="user-menu">
                <div class="user-avatar">
                    <img src="<?= $my_avatar ?>" alt="Avatar">
                </div>

                <div class="user-dropdown">
                    <div style="padding: 10px 20px; border-bottom:1px solid #eee; color:#0b3d2e; font-weight:bold;">
                        Hello, <?= $my_name ?>
                    </div>
                    <a href="Profile_user.php"><i class="fas fa-user-circle"></i> Account info</a>
                    <a href="ticket_history.php"><i class="fas fa-ticket-alt"></i> Ticket history</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="animal-hero">
    <div class="container">
        <h1>Meet Our Animals</h1>
        <p>Discover the amazing wildlife we care for</p>
        
        <form action="" method="GET" class="search-box">
            <input type="text" name="q" class="search-input" 
                   placeholder="Search for animals (e.g. Lion, Mammal)..." 
                   value="<?= htmlspecialchars($search_term) ?>">
            <button type="submit" name="search" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</section>

<div class="container">
    
    <?php if(!empty($search_term)): ?>
        <h4 style="margin-bottom: 30px; color:#555;">Kết quả tìm kiếm cho: "<strong><?= $search_term ?></strong>"</h4>
    <?php endif; ?>

    <div class="row">
        <?php
        if(mysqli_num_rows($res) > 0) {
            while($row = mysqli_fetch_array($res)){
                // Xử lý ảnh
                $img = !empty($row["photo"]) ? $row["photo"] : "uploads/animal_default.png";
                
                // Cắt ngắn mô tả cho gọn
                $desc = (strlen($row["des"]) > 80) ? substr($row["des"],0,80)."..." : $row["des"];
        ?>
        
        <div class="col-md-4 col-sm-6 animal-card-wrapper">
            <div class="animal-card">
                <div style="overflow:hidden;">
                    <img src="<?= $img ?>" class="card-img-top" alt="<?= $row['AName'] ?>">
                </div>
                
                <div class="card-body">
                    <span class="species-tag"><?= $row['Species'] ?></span>
                    
                    <h3 class="animal-name"><?= $row['AName'] ?></h3>
                    
                    <div class="animal-area">
                        <i class="fas fa-map-marker-alt text-success"></i> <?= $row['Area'] ?>
                    </div>
                    
                    <p style="color:#777; font-size:14px; flex-grow:1;"><?= $desc ?></p>
                    
                    <a href="view_animal_user.php?id=<?= $row['id'] ?>" class="btn-view">
                        View Details <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <?php 
            } // End While
        } else {
            echo "<div class='col-12 text-center py-5'>
                    <i class='fas fa-paw fa-3x text-muted mb-3'></i>
                    <h4>Không tìm thấy con vật nào phù hợp.</h4>
                    <a href='Animal-display.php' class='btn btn-link'>Xem tất cả</a>
                  </div>";
        }
        ?>
    </div>
</div>

<footer>
    <p>© 2026 Zoo Explorer – Educational Project</p>
</footer>

</body>
</html>