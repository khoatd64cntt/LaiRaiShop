<?php
// FILE: page/HomePage/profile.php
session_start();
require_once __DIR__ . '/../../config.php';
require_once ROOT_PATH . '/db/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['aid'])) {
    header("Location: LoginPage/login.php");
    exit();
}
$aid = $_SESSION['aid'];
$msg = "";

// 2. Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $afname = $_POST['afname'];
    $alname = $_POST['alname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Update DB
    $sql_update = "UPDATE acc SET afname=?, alname=?, email=?, phone=? WHERE aid=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssssi", $afname, $alname, $email, $phone, $aid);
    
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Cập nhật hồ sơ thành công!</div>";
        // Cập nhật lại Session hiển thị
        $_SESSION['fullname'] = $afname . " " . $alname;
    } else {
        $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// 3. Lấy thông tin User
$sql = "SELECT * FROM acc WHERE aid = $aid";
$user = $conn->query($sql)->fetch_assoc();

// Hàm hiển thị ảnh đại diện (Random màu theo tên nếu chưa có ảnh)
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($user['username']) . "&background=random";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ Sơ Của Tôi | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/homepage.css?v=4">
    <link rel="stylesheet" href="style/profile.css?v=1">
    <link rel="icon" href="../../images/icon.png" />
</head>
<body>
    <div class="sticky-header-wrapper">
        <header class="lairai-header">
            <div class="container header-content">
                <div class="logo"><a href="homepage.php"><img src="../../images/logo.png" alt="Logo"></a></div>
                <div class="top-bar-right" style="margin-left: auto;">
                     <a href="homepage.php" class="text-white">Trang Chủ</a> | 
                     <a href="LoginPage/logout.php" class="text-white">Đăng Xuất</a>
                </div>
            </div>
        </header>
    </div>

    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-md-3 d-none d-md-block">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <img src="<?= $avatarUrl ?>" alt="Avatar">
                    </div>
                    <div class="profile-info">
                        <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
                        <a href="profile.php" class="profile-edit"><i class="fas fa-pen"></i> Sửa hồ sơ</a>
                    </div>
                </div>

                <ul class="sidebar-menu">
                    <li class="active">
                        <a href="profile.php"><i class="fas fa-user text-primary mr-2"></i> Tài Khoản Của Tôi</a>
                    </li>
                    <li>
                        <a href="purchase.php"><i class="fas fa-file-alt text-primary mr-2"></i> Đơn Mua</a>
                    </li>
                </ul>
            </div>

            <div class="col-md-9">
                <div class="profile-main-card">
                    <div class="profile-header">
                        <h3>Hồ Sơ Của Tôi</h3>
                        <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
                    </div>
                    <hr>
                    <?= $msg ?>
                    
                    <div class="profile-body">
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label text-right">Tên đăng nhập</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-plaintext font-weight-bold"><?= htmlspecialchars($user['username']) ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label text-right">Họ</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="afname" class="form-control" value="<?= htmlspecialchars($user['afname']) ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label text-right">Tên</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="alname" class="form-control" value="<?= htmlspecialchars($user['alname']) ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label text-right">Email</label>
                                        <div class="col-sm-9">
                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label text-right">Số điện thoại</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row mt-4">
                                        <div class="col-sm-9 offset-sm-3">
                                            <button type="submit" class="btn btn-danger px-4">Lưu</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>