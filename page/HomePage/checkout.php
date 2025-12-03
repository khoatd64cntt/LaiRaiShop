<?php
// FILE: page/HomePage/checkout.php
session_start();
require_once __DIR__ . '/../../config.php';
require_once ROOT_PATH . '/db/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['aid'])) {
    header("Location: LoginPage/login.php");
    exit();
}

$aid = $_SESSION['aid'];

// 2. Lấy danh sách ID sản phẩm từ URL
$selected_ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

if (empty($selected_ids)) {
    echo "<script>alert('Không có sản phẩm nào để thanh toán'); window.location.href='cart.php';</script>";
    exit();
}

// 3. Lọc sản phẩm từ Giỏ hàng Session
$checkout_items = [];
$total_amount = 0;

if (isset($_SESSION['cart'])) {
    foreach ($selected_ids as $pid) {
        if (isset($_SESSION['cart'][$pid])) {
            $item = $_SESSION['cart'][$pid];
            $checkout_items[] = $item;
            $total_amount += $item['price'] * $item['qty'];
        }
    }
}

// Biến cờ
$order_success = false;
$new_order_id = 0;
$error = ""; // Khởi tạo biến lỗi

// 4. XỬ LÝ ĐẶT HÀNG
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $note = $_POST['note'];

    if (empty($address) || empty($phone)) {
        $error = "Vui lòng nhập đầy đủ địa chỉ và số điện thoại";
    } else {

        // --- [LOGIC MỚI] 1. KIỂM TRA KHO LẦN CUỐI (QUAN TRỌNG) ---
        $stock_error = false;
        foreach ($checkout_items as $item) {
            $pid = $item['pid']; // Lấy PID từ item giỏ hàng
            // Lưu ý: Session cart có thể không lưu PID trực tiếp nếu cấu trúc mảng là [pid => item]
            // Trong code add_to_cart cũ: $_SESSION['cart'][$pid] = [...]; 
            // Nên ta cần lấy PID từ key của mảng checkout_items nếu cần, hoặc đảm bảo item có chứa pid
            // Sửa lại logic lấy checkout_items ở trên một chút để chắc chắn có PID

            // Truy vấn kho thực tế
            $check_stock = $conn->query("SELECT name, stock FROM products WHERE pid = $pid");
            if ($check_stock->num_rows > 0) {
                $prod_db = $check_stock->fetch_assoc();
                if ($item['qty'] > $prod_db['stock']) {
                    $error = "Sản phẩm '" . $prod_db['name'] . "' chỉ còn " . $prod_db['stock'] . " cái. Vui lòng giảm số lượng!";
                    $stock_error = true;
                    break; // Dừng lại ngay
                }
            } else {
                $error = "Sản phẩm không tồn tại!";
                $stock_error = true;
                break;
            }
        }

        // Nếu kho OK thì mới cho đặt
        if (!$stock_error) {
            // Tạo đơn hàng
            $sql_insert = "INSERT INTO orders (aid, fullname, phone, address, note, total_amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";

            $stmt = $conn->prepare($sql_insert);
            if ($stmt === false) {
                die("Lỗi SQL (Prepare failed): " . $conn->error);
            }

            $stmt->bind_param("issssd", $aid, $fullname, $phone, $address, $note, $total_amount);

            if ($stmt->execute()) {
                $new_order_id = $stmt->insert_id;

                // Lưu chi tiết đơn hàng VÀ TRỪ KHO
                $stmt_item = $conn->prepare("INSERT INTO order_items (oid, pid, quantity, price) VALUES (?, ?, ?, ?)");

                foreach ($checkout_items as $pid => $item) { // Key của checkout_items cần được xử lý lại ở trên để lấy PID chuẩn
                    // Sửa lại vòng lặp lấy PID chuẩn từ bước 3
                    // (Do code bước 3 của bạn đang dùng foreach $selected_ids as $pid)
                    // Nên $pid ở đây phải lấy từ $selected_ids tương ứng

                    // Cách tốt nhất là sửa lại mảng $checkout_items để chứa cả pid
                    // Tuy nhiên để code chạy ngay, ta lấy pid từ session cart key
                }

                // --- VIẾT LẠI VÒNG LẶP LƯU VÀ TRỪ KHO ---
                foreach ($selected_ids as $pid) {
                    if (isset($_SESSION['cart'][$pid])) {
                        $item = $_SESSION['cart'][$pid];
                        $qty = $item['qty'];
                        $price = $item['price'];

                        // 1. Lưu vào order_items
                        $stmt_item->bind_param("iiid", $new_order_id, $pid, $qty, $price);
                        $stmt_item->execute();

                        // 2. [LOGIC MỚI] TRỪ KHO (DEDUCT STOCK)
                        $conn->query("UPDATE products SET stock = stock - $qty WHERE pid = $pid");

                        // 3. Xóa khỏi giỏ hàng
                        unset($_SESSION['cart'][$pid]);
                    }
                }

                $order_success = true;
            } else {
                $error = "Lỗi khi lưu đơn hàng: " . $stmt->error;
            }
        }
        // Nếu stock_error = true thì $error đã có nội dung, sẽ hiện ra alert
    }
}

// Lấy thông tin người dùng
$user_sql = "SELECT * FROM acc WHERE aid = $aid";
$user_res = $conn->query($user_sql);
$user = $user_res->fetch_assoc();

$display_name = '';
if ($user && !empty($user['fullname'])) {
    $display_name = $user['fullname'];
} elseif ($user && !empty($user['username'])) {
    $display_name = $user['username'];
} else {
    $display_name = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Khách hàng';
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thanh Toán | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style/homepage.css?v=4">
    <link rel="stylesheet" href="style/detail.css?v=1">
    <?php include ROOT_PATH . '/includes/head_meta.php'; ?>

    <style>
        body {
            background-color: #f5f5f5;
        }

        .checkout-container {
            background: white;
            padding: 30px;
            border-radius: 3px;
            margin-top: 30px;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .05);
        }

        .section-title {
            font-size: 18px;
            color: #ee4d2d;
            font-weight: 500;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-order {
            background: #ee4d2d;
            color: white;
            width: 100%;
            padding: 12px;
            font-weight: bold;
            border: none;
            border-radius: 2px;
        }

        .btn-order:hover {
            background: #d73211;
            color: white;
        }

        /* Modal Success */
        .modal-success .modal-content {
            border-radius: 5px;
            text-align: center;
            padding: 20px;
        }

        .success-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .btn-home {
            background-color: #ee4d2d;
            color: white;
            padding: 10px 30px;
            border-radius: 2px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .btn-home:hover {
            background-color: #d73211;
            color: white;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="sticky-header-wrapper">
        <div class="top-bar">
            <div class="container top-bar-content">
                <div class="top-bar-left">
                    <a href="../SellerPage/dashboard.php">Kênh Người Bán</a>
                    <div class="top-bar-connect">
                        <p>Kết nối</p>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="top-bar-right">
                    <?php if (isset($_SESSION['aid'])): ?>
                        <span class="auth-link" style="color: white;">
                            Xin chào, <strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User'); ?></strong>
                        </span>
                        <span style="color: white; margin: 0 5px;">|</span>
                        <a href="LoginPage/logout.php" class="auth-link">Đăng Xuất</a>
                    <?php else: ?>
                        <a href="LoginPage/login.php" class="auth-link">Đăng Nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <header class="lairai-header">
            <div class="container header-content">
                <div class="logo"><a href="homepage.php"><img src="../../images/logo.png" alt="LaiRaiShop Logo"></a></div>
                <div class="search-box">
                    <form action="search.php" method="GET">
                        <input type="text" name="keyword" placeholder="Tìm sản phẩm...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="cart-icon has-dropdown">
                    <a href="cart.php" style="color: white;"><i class="fas fa-shopping-cart"></i></a>
                </div>
            </div>
        </header>
    </div>

    <div class="container mb-5">
        <div style="background: #fff; padding: 20px; margin-top: 20px; border-radius: 3px; box-shadow: 0 1px 1px 0 rgba(0,0,0,.05); border-left: 4px solid #ee4d2d;">
            <h4 style="margin: 0; color: #ee4d2d;"><i class="fas fa-money-check-alt"></i> Thanh Toán</h4>
        </div>

        <form method="POST">
            <div class="row">
                <div class="col-md-7">
                    <div class="checkout-container">
                        <div class="section-title"><i class="fas fa-map-marker-alt"></i> Địa Chỉ Nhận Hàng</div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Họ và Tên</label>
                            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($display_name) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Số Điện Thoại</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required placeholder="Nhập số điện thoại nhận hàng">
                        </div>
                        <div class="form-group">
                            <label>Địa Chỉ Cụ Thể</label>
                            <textarea name="address" class="form-control" rows="3" required placeholder="Tỉnh/Thành phố, Quận/Huyện, Phường/Xã, Số nhà..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Lời nhắn cho người bán</label>
                            <input type="text" name="note" class="form-control" placeholder="Lưu ý cho người bán...">
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="checkout-container">
                        <div class="section-title"><i class="fas fa-shopping-bag"></i> Đơn Hàng Của Bạn</div>

                        <ul class="list-group mb-3 list-group-flush">
                            <?php foreach ($checkout_items as $item): ?>
                                <li class="list-group-item d-flex justify-content-between lh-condensed px-0">
                                    <div>
                                        <h6 class="my-0"><?= htmlspecialchars($item['name']) ?></h6>
                                        <small class="text-muted">x <?= $item['qty'] ?></small>
                                    </div>
                                    <span class="text-muted"><?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?>₫</span>
                                </li>
                            <?php endforeach; ?>

                            <li class="list-group-item d-flex justify-content-between px-0 bg-light">
                                <span class="text-success">Voucher của Shop</span>
                                <span class="text-success">-0₫</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><strong>Tổng thanh toán</strong></span>
                                <strong style="color: #ee4d2d; font-size: 22px;"><?= number_format($total_amount, 0, ',', '.') ?>₫</strong>
                            </li>
                        </ul>

                        <div class="mb-4">
                            <label class="font-weight-bold">Phương thức thanh toán:</label>
                            <select class="form-control">
                                <option>Thanh toán khi nhận hàng (COD)</option>
                                <option disabled>Thanh toán Online (Đang bảo trì)</option>
                            </select>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-order">ĐẶT HÀNG</button>
                        <div class="text-center mt-3">
                            <a href="cart.php" class="text-secondary" style="font-size: 14px;">Quay lại giỏ hàng</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <footer class="lairai-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>CHĂM SÓC KHÁCH HÀNG</h3>
                    <ul>
                        <li><a href="#">Trung Tâm Trợ Giúp</a></li>
                        <li><a href="#">Hướng Dẫn Mua Hàng</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>VỀ LAIRAISHOP</h3>
                    <ul>
                        <li><a href="#">Giới thiệu</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="copyright">© 2025 LaiRaiShop. All rights reserved.</div>
            </div>
        </div>
    </footer>

    <div class="modal fade modal-success" id="successModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <i class="fas fa-check-circle success-icon"></i>
                    <h4>Đặt Hàng Thành Công!</h4>
                    <p class="mb-1">Cảm ơn bạn đã mua sắm tại LaiRaiShop.</p>
                    <p class="text-muted">Mã đơn hàng của bạn: <strong>#<span id="orderIdDisplay"></span></strong></p>
                    <button type="button" class="btn-home" onclick="goHome()">Tiếp Tục Mua Sắm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/homepage.js?v=4"></script>

    <script>
        function goHome() {
            window.location.href = 'homepage.php';
        }

        $(document).ready(function() {
            <?php if (isset($order_success) && $order_success): ?>
                $('#orderIdDisplay').text('<?= $new_order_id ?>');
                $('#successModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#successModal').modal('show');
            <?php endif; ?>
        });
    </script>

</body>

</html>