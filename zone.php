<?php
session_start();
include "connection.php";

// ================= 1. LOGIC USER (HEADER) =================
$my_avatar = 'uploads/default_user.png';
$my_name = 'Guest';

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

// ================= 2. XỬ LÝ LOGIC ZONE =================
$selected_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$zone_data = null;
$animals_result = null;

if ($selected_id) {
    // --- A. NẾU ĐANG CHỌN 1 ZONE CỤ THỂ ---
    
    // 1. Lấy thông tin Zone
    $q_zone = mysqli_query($link, "SELECT * FROM zones WHERE id = $selected_id");
    $zone_data = mysqli_fetch_assoc($q_zone);

    // 2. Lấy Animals thuộc Zone đó
    if ($zone_data) {
        $animals_result = mysqli_query($link, "SELECT * FROM table1 WHERE zone_id = $selected_id");
    }
} else {
    // --- B. NẾU CHƯA CHỌN ZONE (HIỂN THỊ DANH SÁCH) ---
    $all_zones = mysqli_query($link, "SELECT * FROM zones");
}

// Hàm helper để map icon cho đẹp (Nếu DB không có cột icon)
function getZoneIcon($name) {
    if (stripos($name, 'cat') !== false || stripos($name, 'predator') !== false) return '🦁';
    if (stripos($name, 'giant') !== false || stripos($name, 'elephant') !== false) return '🐘';
    if (stripos($name, 'bird') !== false || stripos($name, 'reptile') !== false) return '🦜';
    if (stripos($name, 'farm') !== false || stripos($name, 'pet') !== false) return '🐰';
    if (stripos($name, 'water') !== false || stripos($name, 'aqua') !== false) return '🐬';
    return '🌿'; // Default
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Zoo Zones | Zoo Explorer</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<style>
    /* ================= GLOBAL ================= */
    body{ background-color: #f4f6f4; font-family: 'Segoe UI', sans-serif; }

    /* ================= HEADER (ĐỒNG BỘ) ================= */
    .zoo-header { background: #fff; padding: 15px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; }
    .zoo-nav { display: flex; align-items: center; justify-content: space-between; }
    .zoo-logo { font-size: 24px; font-weight: 800; color: #0b3d2e; text-decoration: none; }
    .zoo-logo:hover { color: #0b3d2e; text-decoration: none; }

    .zoo-menu { display: flex; gap: 30px; }
    .menu-item > a { text-decoration: none; color: #222; font-weight: 600; font-size: 16px; padding: 10px 0; display: inline-block; transition: 0.2s; }
    .menu-item > a:hover { color: #0b3d2e; }

    .zoo-actions { display: flex; align-items: center; gap: 20px; }
    .buy-ticket { background: #f4f91d; color: #0b3d2e; font-weight: 700; padding: 10px 25px; border-radius: 999px; text-decoration: none; transition: 0.25s ease; white-space: nowrap; }
    .buy-ticket:hover { background: #e6eb00; text-decoration: none; color: #0b3d2e; }

    /* USER AVATAR */
    .user-menu { position: relative; }
    .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: #0b3d2e; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; user-select: none; overflow: hidden; border: 2px solid #eee; }
    .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .user-dropdown { position: absolute; top: 52px; right: 0; width: 240px; background: #fff; border-radius: 14px; box-shadow: 0 15px 35px rgba(0,0,0,0.15); padding: 10px 0; opacity: 0; visibility: hidden; transform: translateY(10px); transition: 0.25s ease; z-index: 2000; }
    .user-menu:hover .user-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
    .user-dropdown a { display: block; padding: 12px 20px; color: #222; text-decoration: none; font-weight: 600; }
    .user-dropdown a:hover { background: #f4f6f4; color: #0b3d2e; }

    /* ================= ZONE STYLES ================= */
    
    /* 1. HERO DEFAULT (List) */
    .zone-hero-default {
        background: linear-gradient(rgba(11, 61, 46, 0.9), rgba(11, 61, 46, 0.7)), url('uploads/Screenshot\ 2026-01-02\ 174124.png') center/cover;
        padding: 100px 0; text-align: center; color: white; margin-bottom: 50px;
    }
    .zone-hero-default h1 { font-weight: 900; font-size: 48px; margin-bottom: 15px; }

    /* 2. ZONE CARD (List Item) */
    .zone-main-card {
        display: block; background: #fff; border-radius: 20px; padding: 40px 30px;
        text-align: center; text-decoration: none !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s;
        height: 100%; border: 1px solid transparent;
    }
    .zone-main-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); border-color: #f4f91d; }
    .zone-icon { font-size: 60px; margin-bottom: 20px; display: block; }
    .zone-title { color: #0b3d2e; font-weight: 800; font-size: 24px; margin-bottom: 10px; }
    .zone-desc { color: #666; font-size: 15px; }

    /* 3. ACTIVE ZONE HERO (Detail) */
    .active-zone-header {
        background: #0b3d2e; color: white; padding: 60px 0; text-align: center;
        border-bottom: 8px solid #f4f91d; margin-bottom: 50px;
    }
    .btn-back {
        background: rgba(255,255,255,0.1); color: white; padding: 8px 20px;
        border-radius: 50px; text-decoration: none; display: inline-block; margin-top: 20px;
        transition: 0.2s;
    }
    .btn-back:hover { background: white; color: #0b3d2e; text-decoration: none; }

    /* 4. ANIMAL CARDS (Reuse) */
    .animal-card {
        background: #fff; border-radius: 15px; overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s;
        height: 100%; display: flex; flex-direction: column;
    }
    .animal-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
    .animal-card img { width: 100%; height: 220px; object-fit: cover; }
    .animal-info { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
    .animal-info h4 { color: #0b3d2e; font-weight: 800; font-size: 18px; margin-bottom: 10px; }
    .animal-desc { font-size: 14px; color: #666; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 15px; }
    .btn-view { margin-top: auto; color: #0b3d2e; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; }

    /* Mini Zone Navigation */
    .mini-zone-btn {
        display: block; background: white; padding: 12px; border-radius: 10px;
        text-align: center; color: #333; font-weight: 600; text-decoration: none !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.2s; border: 1px solid #eee;
    }
    .mini-zone-btn:hover { background: #f4f91d; color: #0b3d2e; transform: translateY(-3px); }

    footer { background:#0b3d2e; color:white; padding:30px 0; margin-top: 60px; }
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
                    <div style="padding: 10px 20px; border-bottom:1px solid #eee; color:#0b3d2e; font-weight:bold;">Hello, <?= $my_name ?></div>
                    <a href="Profile_user.php">👤 Account info</a>
                    <a href="ticket_history.php">🎟 Ticket history</a>
                    <a href="logout.php">🚪 Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<?php if (!$selected_id): ?>
    
    <section class="zone-hero-default">
        <div class="container">
            <h1>Discover Our Habitats</h1>
            <p style="font-size:18px;">From the dense jungle to the open savannah.</p>
        </div>
    </section>

    <div class="container" style="margin-top: -60px; position: relative; z-index: 10;">
        <div class="row">
            <?php 
            if (mysqli_num_rows($all_zones) > 0) {
                while($z = mysqli_fetch_array($all_zones)) {
                    $icon = getZoneIcon($z['zone_name']);
            ?>
                <div class="col-md-6 col-lg-6 mb-4">
                    <a href="zone.php?id=<?= $z['id'] ?>" class="zone-main-card">
                        <span class="zone-icon"><?= $icon ?></span>
                        <h3 class="zone-title"><?= $z['zone_name'] ?></h3>
                        <p class="zone-desc"><?= $z['description'] ?></p>
                    </a>
                </div>
            <?php 
                } 
            } else {
                echo "<div class='col-12 text-center'><p>No zones available.</p></div>";
            }
            ?>
        </div>
    </div>

<?php else: ?>

    <?php if ($zone_data): ?>
        <div class="active-zone-header">
            <div class="container">
                <div style="font-size: 50px; margin-bottom: 10px;">
                    <?= getZoneIcon($zone_data['zone_name']) ?>
                </div>
                <h1 style="font-weight: 900; text-transform: uppercase;">
                    <?= htmlspecialchars($zone_data['zone_name']) ?>
                </h1>
                <p style="font-size: 18px; max-width: 700px; margin: 0 auto; opacity: 0.9;">
                    <?= nl2br(htmlspecialchars($zone_data['description'])) ?>
                </p>
                <a href="zone.php" class="btn-back"><i class="fas fa-arrow-left"></i> All Zones</a>
            </div>
        </div>

        <div class="container">
            <h3 style="color:#0b3d2e; font-weight:800; margin-bottom:30px; border-left: 5px solid #f4f91d; padding-left: 15px;">
                Animals in this Zone
            </h3>
            
            <div class="row">
                <?php 
                if($animals_result && mysqli_num_rows($animals_result) > 0): 
                    while($row = mysqli_fetch_array($animals_result)): 
                        $img = !empty($row["photo"]) ? $row["photo"] : "uploads/animal_default.png";
                ?>
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="animal-card">
                            <img src="<?= $img ?>">
                            <div class="animal-info">
                                <h4><?= $row["AName"] ?></h4>
                                <p class="animal-desc"><?= $row["des"] ?></p>
                                <a href="view_animal_user.php?id=<?= $row["id"] ?>" class="btn-view">
                                    View details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            Currently, there are no animals listed in this zone.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <hr style="margin: 60px 0 40px;">
            <h5 class="text-center text-muted text-uppercase mb-4" style="font-weight: 700; letter-spacing: 1px;">Explore Other Zones</h5>
            
            <div class="row justify-content-center">
                <?php 
                // Reset pointer query zones để liệt kê lại
                $all_zones_nav = mysqli_query($link, "SELECT * FROM zones LIMIT 4");
                while($z_nav = mysqli_fetch_array($all_zones_nav)):
                    if($z_nav['id'] != $selected_id):
                ?>
                    <div class="col-6 col-md-3">
                        <a href="zone.php?id=<?= $z_nav['id'] ?>" class="mini-zone-btn">
                            <?= getZoneIcon($z_nav['zone_name']) ?> <?= $z_nav['zone_name'] ?>
                        </a>
                    </div>
                <?php endif; endwhile; ?>
            </div>
        </div>

    <?php else: ?>
        <div class="container text-center py-5">
            <h2>Zone not found!</h2>
            <a href="zone.php" class="btn btn-primary mt-3">Back to Zones</a>
        </div>
    <?php endif; ?>

<?php endif; ?>

<footer class="text-center">
    <p>© 2026 Zoo Explorer – Educational Project</p>
</footer>

</body>
</html>