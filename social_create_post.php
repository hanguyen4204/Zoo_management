<?php
session_start();
include "connection.php";

// Check login
if (!isset($_SESSION['id_user'])) { header("Location: zoo_social.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Bài Viết Mới</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f4f6f4; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .create-card {
            background: #fff; width: 100%; max-width: 600px;
            border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header { background: #0b3d2e; color: #fff; padding: 20px; font-weight: 700; font-size: 18px; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 30px; }
        .close-btn { color: #fff; opacity: 0.7; transition: 0.3s; }
        .close-btn:hover { color: #f4f91d; opacity: 1; text-decoration: none; }
        
        .form-control { border-radius: 10px; padding: 15px; border: 1px solid #ddd; background: #fafafa; }
        .form-control:focus { background: #fff; border-color: #0b3d2e; box-shadow: none; }
        
        .upload-box {
            border: 2px dashed #ddd; border-radius: 10px; padding: 20px;
            text-align: center; margin-top: 20px; cursor: pointer; transition: 0.3s;
        }
        .upload-box:hover { border-color: #0b3d2e; background: #f0fdf4; }
        
        .btn-submit {
            background: #0b3d2e; color: #fff; width: 100%; padding: 12px;
            border-radius: 50px; font-weight: 700; margin-top: 20px; border: none; transition: 0.3s;
        }
        .btn-submit:hover { background: #f4f91d; color: #0b3d2e; }
    </style>
</head>
<body>

    <div class="create-card">
        <div class="card-header">
            <span><i class="fas fa-pen-fancy"></i> Tạo bài viết mới</span>
            <a href="zoo_social.php" class="close-btn"><i class="fas fa-times"></i> Đóng</a>
        </div>
        
        <div class="card-body">
            <form action="social_ajax.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="post_status">
                
                <div class="form-group">
                    <textarea name="content" class="form-control" rows="5" placeholder="Chia sẻ câu chuyện thú vị về động vật của bạn..." required></textarea>
                </div>

                <label class="upload-box w-100" for="file-upload">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <div class="text-muted">Nhấn để thêm ảnh (Nếu có)</div>
                    <input type="file" name="image" id="file-upload" style="display:none" accept="image/*" onchange="previewImage(this)">
                </label>
                
                <div id="preview-area" class="mt-3 text-center" style="display:none;">
                    <img id="img-preview" src="" style="max-height: 200px; border-radius: 10px;">
                </div>

                <button type="submit" class="btn-submit">ĐĂNG BÀI VIẾT</button>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('img-preview').src = e.target.result;
                    document.getElementById('preview-area').style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>