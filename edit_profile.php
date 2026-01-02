<?php
session_start();
include "connection.php";

/* ================= AUTH CHECK ================= */
if (empty($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = intval($_SESSION['id_user']);

$success = "";
$error   = "";

/* ================= GET USER DATA ================= */
$res = mysqli_query($link, "SELECT username, email, photo FROM users WHERE id_user = $id_user");
$user = mysqli_fetch_assoc($res);

$username = $user['username'];
$email    = $user['email'];
$photo    = $user['photo'];

/* ================= UPDATE PROFILE ================= */
if (isset($_POST["update"])) {

    $new_username = mysqli_real_escape_string($link, $_POST["username"]);
    $new_email    = mysqli_real_escape_string($link, $_POST["email"]);

    /* --- CHECK EMAIL TRÙNG (TRỪ CHÍNH USER HIỆN TẠI) --- */
    $check = mysqli_query($link,"
        SELECT id_user FROM users 
        WHERE email = '$new_email' AND id_user != $id_user
    ");

    if (mysqli_num_rows($check) > 0) {
        $error = "This email address is already in use.";
    } else {

        $sql = "UPDATE users SET 
                username = '$new_username',
                email    = '$new_email'";

        /* --- UPLOAD AVATAR --- */
        if (!empty($_FILES["photo"]["name"])) {

            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $file_name = "user_" . $id_user . "_" . time() . "_" . basename($_FILES["photo"]["name"]);
            $target_file = $target_dir . $file_name;

            $check_img = getimagesize($_FILES["photo"]["tmp_name"]);
            if ($check_img !== false) {
                move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
                $sql .= ", photo = '$target_file'";
                $photo = $target_file;
            }
        }

        $sql .= " WHERE id_user = $id_user";

        if (mysqli_query($link, $sql)) {
            $_SESSION['username'] = $new_username;
            $_SESSION['email']    = $new_email;

            $username = $new_username;
            $email    = $new_email;

            $success = "Profile updated successfully.";
        } else {
            $error = "Database error. Please try again.";
        }
    }
}

/* Avatar mặc định */
$avatar = !empty($photo) ? $photo : "uploads/default-avatar.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
body{
    background:linear-gradient(135deg,#d4fc79,#96e6a1);
    min-height:100vh;
    font-family:'Segoe UI',sans-serif;
}
.edit-card{
    background:#fff;
    padding:30px;
    border-radius:14px;
    box-shadow:0 15px 40px rgba(0,0,0,0.18);
    margin-top:60px;
}
h2{
    text-align:center;
    color:#2e7d32;
    font-weight:700;
    margin-bottom:20px;
}
.avatar-preview{
    text-align:center;
    margin-bottom:20px;
}
.avatar-preview img{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #4caf50;
}
.alert{
    margin-bottom:15px;
}
.btn-update{
    width:100%;
    background:#4caf50;
    color:#fff;
    border-radius:8px;
    font-weight:600;
}
.btn-update:hover{
    background:#388e3c;
}
.btn-back{
    width:100%;
    margin-top:10px;
    border-radius:8px;
    font-weight:600;
}
</style>
</head>

<body>

<div class="container">
<div class="row">
<div class="col-lg-4 col-lg-offset-4 edit-card">

    <h2>👤 Edit Profile</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        
    <?php endif; ?>

    <div class="avatar-preview">
        <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar">
    </div>

    <form method="post" enctype="multipart/form-data">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control"
                   value="<?= htmlspecialchars($username) ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="form-group">
            <label>Avatar</label>
            <input type="file" name="photo" class="form-control">
        </div>

        <button type="submit" name="update" class="btn btn-update">
            <span class="glyphicon glyphicon-save"></span> Update profile
        </button>

        <a href="profile.php" class="btn btn-primary btn-back">
            <span class="glyphicon glyphicon-arrow-left"></span> Back to profile
        </a>

    </form>

</div>
</div>
</div>

<?php if ($success): ?>
<script>
    alert("Profile updated successfully.");
    window.location.href = "Profile_user.php";
</script>
<?php endif; ?>

</body>
</html>
