<?php
session_start();
include "connection.php";

// ================= 1. LOGIC LẤY THÔNG TIN USER (HEADER) =================
$my_avatar = 'uploads/default_user.png'; 
$my_name = 'Guest';

if (isset($_SESSION['id_user'])) {
    $current_user_id = $_SESSION['id_user'];
    // Query lấy thông tin user
    $user_query = mysqli_query($link, "SELECT * FROM users WHERE id_user = '$current_user_id'");
    if ($current_user = mysqli_fetch_array($user_query)) {
        $my_name = $current_user['username'];
        if (!empty($current_user['photo'])) {
            $my_avatar = $current_user['photo'];
        }
    }
}

// ================= 2. LOGIC LẤY DANH SÁCH ACTIVITY =================
$sql = "SELECT 
            activity.id,
            activity.name,
            activity.description,
            activity.time,
            activity.file_link,
            activity.image,
            zones.zone_name 
        FROM activity 
        LEFT JOIN zones ON activity.zone_id = zones.id 
        ORDER BY activity.time ASC";

$res = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Daily Activities | Zoo Explorer</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<style>
    /* ================= GLOBAL STYLES ================= */
    body{ margin:0; font-family:'Segoe UI',sans-serif; background:#f4f6f4; color: #333; }

    /* ================= HEADER STYLE (ĐỒNG BỘ) ================= */
    .zoo-header { background: #fff; padding: 15px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; }
    .zoo-nav { display: flex; align-items: center; justify-content: space-between; }
    .zoo-logo { font-size: 24px; font-weight: 800; color: #0b3d2e; text-decoration: none; }
    .zoo-logo:hover { color: #0b3d2e; text-decoration: none; }

    .zoo-menu { display: flex; gap: 30px; }
    .menu-item > a { 
        text-decoration: none; color: #222; font-weight: 600; font-size: 16px;
        padding: 10px 0; display: inline-block; transition: 0.2s;
    }
    .menu-item > a:hover { color: #0b3d2e; }

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

    /* ================= ACTIVITY SPECIFIC STYLES ================= */
    .hero-activity {
        position: relative;
        min-height: 45vh; 
        background: linear-gradient(rgba(11, 61, 46, 0.8), rgba(11, 61, 46, 0.6)), 
                    url("uploads/Gemini_Generated_Image_bb10h0bb10h0bb10.png") center/cover no-repeat;
        display: flex; align-items: center; justify-content: center;
        text-align: center; color: #fff; margin-bottom: 50px;
    }
    .hero-activity h1 {
        font-size: clamp(32px, 5vw, 56px); font-weight: 900; text-transform: uppercase;
        letter-spacing: 1px; margin-bottom: 15px;
    }
    .hero-activity p { font-size: 18px; max-width: 700px; margin: 0 auto; opacity: 0.9; }

    .container-activity { max-width: 900px; margin: 0 auto; padding: 0 20px 80px 20px; }

    .date-badge {
        background: #0b3d2e; color: #f4f91d;
        display: inline-block; padding: 10px 25px;
        border-radius: 99px; font-weight: 700;
        margin-bottom: 30px; box-shadow: 0 5px 15px rgba(11, 61, 46, 0.2);
    }

    /* ITEM CARD */
    .activity-link-wrapper { text-decoration: none !important; color: inherit; display: block; margin-bottom: 24px; }
    
    .activity-card {
        background: #fff; border-radius: 16px; overflow: hidden;
        display: flex; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease; border: 1px solid transparent;
    }

    .activity-link-wrapper:hover .activity-card {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        border-color: #f4f91d;
    }

    .card-time {
        background: #e9f0ec; min-width: 120px;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        padding: 15px; border-right: 3px solid #f4f91d;
    }
    .time-text { font-size: 24px; font-weight: 800; color: #0b3d2e; }
    .time-label { font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase; }

    .card-thumb { width: 180px; position: relative; overflow: hidden; }
    .card-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
    .activity-link-wrapper:hover .card-thumb img { transform: scale(1.1); }

    .card-content { flex-grow: 1; padding: 20px 25px; display: flex; flex-direction: column; justify-content: center; }

    .zone-tag {
        font-size: 13px; font-weight: 700; color: #0b3d2e;
        display: flex; align-items: center; gap: 6px; margin-bottom: 8px;
    }
    .zone-tag i { color: #f4f91d; }

    .card-title { font-size: 20px; font-weight: 700; margin: 0 0 8px 0; color: #222; }
    .card-desc { color: #666; font-size: 14px; margin: 0; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* Responsive */
    @media (max-width: 768px) {
        .activity-card { flex-direction: column; }
        .card-thumb { width: 100%; height: 150px; }
        .card-time { 
            flex-direction: row; justify-content: flex-start; gap: 10px; 
            background: #0b3d2e; color: white; border-right: none; border-bottom: 3px solid #f4f91d;
        }
        .time-text { color: white; font-size: 18px; }
        .time-label { color: #f4f91d; }
    }

    footer { background:#0b3d2e; color:white; padding:30px 0; margin-top: auto; }
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

<section class="hero-activity">
    <div class="container">
        <h1>Daily Schedule</h1>
        <p>Don't miss out on our keeper talks, feeds and animal demonstrations.</p>
    </div>
</section>

<div class="container-activity">
    
    <div class="text-center">
        <div class="date-badge">
            <i class="far fa-calendar-alt"></i> Today, <?= date('d F Y') ?>
        </div>
    </div>

    <?php 
    if(mysqli_num_rows($res) > 0) {
        while($row = mysqli_fetch_array($res)){ 
            // 1. Xử lý thời gian
            $timeDisplay = date('H:i', strtotime($row['time']));
            
            // 2. Xử lý ảnh
            $imgUrl = !empty($row["image"]) ? $row["image"] : "uploads/Screenshot 2026-01-02 164611.png";

            // 3. Xử lý Zone
            $zoneName = !empty($row["zone_name"]) ? $row["zone_name"] : "General Area";

            // 4. Lấy link file chi tiết
            $linkUrl = !empty($row["file_link"]) ? $row["file_link"] : "#";
    ?>
        
        <a href="<?= $linkUrl ?>" class="activity-link-wrapper">
            <div class="activity-card">
                <div class="card-time">
                    <span class="time-text"><?= $timeDisplay ?></span>
                    <span class="time-label">Daily</span>
                </div>

                <div class="card-thumb">
                    <img src="<?= $imgUrl ?>" alt="<?= $row['name'] ?>">
                </div>
                
                <div class="card-content">
                    <div class="zone-tag">
                        <i class="fas fa-map-marker-alt"></i> <?= $zoneName ?>
                    </div>
                    <h3 class="card-title"><?= $row["name"] ?></h3>
                    <p class="card-desc"><?= $row["description"] ?></p>
                </div>
            </div>
        </a>

    <?php 
        } 
    } else {
        echo "<div class='alert alert-warning text-center'>No activities found today.</div>";
    }
    ?>

</div>

<footer class="text-center">
    <p>© 2026 Zoo Explorer – Educational Project</p>
</footer>

</body>
</html>