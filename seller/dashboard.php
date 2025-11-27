<?php
// seller/dashboard.php
require_once 'types/seller_session.php'; // Bắt buộc phải có dòng này ở đầu
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Kênh Người Bán - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 bg-dark text-white min-vh-100 p-3">
                <h4><?php echo $_SESSION['shop_name']; ?></h4>
                <hr>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white active" href="dashboard.php">Tổng quan</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="ProductPage/products.php">Quản lý Sản phẩm</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#">Quản lý Đơn hàng</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="../HomePage/homepage.php">Quay lại Web</a></li>
                </ul>
            </div>

            <div class="col-md-10 p-4">
                <h2>Tổng quan Shop</h2>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Sản phẩm</h5>
                                <p class="card-text display-4">
                                    <?php 
                                    $sid = $_SESSION['shop_id'];
                                    echo $conn->query("SELECT count(*) as c FROM products WHERE sid=$sid")->fetch_assoc()['c']; 
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Doanh thu</h5>
                                <p class="card-text display-4">0đ</p> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>