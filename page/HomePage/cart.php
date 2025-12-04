<?php
// FILE: page/HomePage/cart.php
session_start();
require_once __DIR__ . '/../../config.php';
require_once ROOT_PATH . '/db/db.php';

// XỬ LÝ POST (BACKEND)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Xóa 1 sản phẩm
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $pid = $_POST['pid'];
        unset($_SESSION['cart'][$pid]);
    }
    // 2. Xóa nhiều sản phẩm
    if (isset($_POST['action']) && $_POST['action'] == 'delete_selected') {
        if (isset($_POST['pids']) && is_array($_POST['pids'])) {
            foreach ($_POST['pids'] as $pid_to_del) {
                unset($_SESSION['cart'][$pid_to_del]);
            }
        }
        exit; 
    }
    // 3. Cập nhật số lượng
    if (isset($_POST['action']) && $_POST['action'] == 'update_qty') {
        $pid = $_POST['pid'];
        $qty = $_POST['qty'];
        if ($qty > 0) $_SESSION['cart'][$pid]['qty'] = $qty;
        exit;
    }
}

function formatMoney($number)
{
    return number_format($number, 0, ',', '.') . '₫';
}
function getImgUrl($path)
{
    if (strpos($path, 'http') === 0) return $path;
    return "../../" . ltrim($path, '/');
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style/homepage.css?v=4">
    <link rel="stylesheet" href="style/cart.css?v=1">
    
    <style>
        /* CSS CHO TRẠNG THÁI GIỎ HÀNG TRỐNG (MỚI) */
        .empty-cart-box {
            background: #fff;
            text-align: center;
            padding: 50px 20px;
            margin-top: 20px;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.05);
            border-radius: 2px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .empty-icon {
            font-size: 80px;
            color: #e0e0e0;
            margin-bottom: 20px;
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
                        <a href="https://www.facebook.com/ShopeeVN" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://www.instagram.com/Shopee_VN" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <div class="top-bar-right">
                    <?php if (isset($_SESSION['aid'])): ?>
                        <span class="auth-link" style="color: white;">
                            Xin chào, 
                            <a href="<?php echo BASE_URL; ?>/page/HomePage/profile.php" style="color: white; text-decoration: none;">
                                <strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></strong>
                            </a>
                        </span>
                        <span style="color: white; margin: 0 5px;">|</span>
                        <a href="<?php echo BASE_URL; ?>/page/HomePage/LoginPage/logout.php" class="auth-link">Đăng Xuất</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/page/HomePage/SignupPage/signup.php" class="auth-link">Đăng Ký</a>
                        <a href="<?php echo BASE_URL; ?>/page/HomePage/LoginPage/login.php" class="auth-link">Đăng Nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <header class="lairai-header">
            <div class="container header-content">
                <div class="logo"><a href="homepage.php"><img src="../../images/logo.png" alt="LaiRaiShop Logo"></a></div>

                <div class="search-box">
                    <form action="search.php" method="GET">
                        <input type="text" name="keyword" placeholder="Bao ship 0Đ - Đăng ký ngay để nhận ưu đãi hấp dẫn!">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    <div class="search-keywords">
                        <?php
                        $keywords = ["Sục Crocs", "Áo Khoác Nam", "iPhone 15 Pro Max", "Dép Lào", "Quần Bò Ống Rộng", "Túi Xách Nữ", "MacBook Air M3", "Nồi Cơm Điện", "Cây Cảnh"];
                        foreach ($keywords as $kw) {
                            echo '<a href="search.php?keyword=' . urlencode($kw) . '">' . $kw . '</a>';
                        }
                        ?>
                    </div>
                </div>

                <div class="cart-icon has-dropdown">
                    <i class="fas fa-shopping-cart"></i>

                    <?php
                    $total_qty_header = 0;
                    if (isset($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $c_item) {
                            $total_qty_header += $c_item['qty'];
                        }
                    }
                    ?>
                    <?php if ($total_qty_header > 0): ?>
                        <span class="cart-badge" style="position: absolute; top: -5px; right: -8px; background: #ee4d2d; color: #fff; border-radius: 50%; padding: 0 5px; font-size: 12px; line-height: 16px;"><?= $total_qty_header ?></span>
                    <?php endif; ?>

                    <div class="lairai-dropdown-menu cart-dropdown">
                        <?php if (!empty($_SESSION['cart'])): ?>
                            <div class="cart-list-wrapper" style="max-height: 300px; overflow-y: auto;">
                                <p style="padding: 10px; color: #999; margin: 0; font-size: 14px;">Sản phẩm mới thêm</p>
                                <?php foreach ($_SESSION['cart'] as $cart_id => $cart_item): ?>
                                    <div class="popup-item" style="display: flex; padding: 10px; align-items: center;">
                                        <img src="<?= getImgUrl($cart_item['image']) ?>" alt="img" style="width: 40px; height: 40px; border: 1px solid #e5e5e5; margin-right: 10px; object-fit: cover;">
                                        <div class="popup-info" style="flex: 1; overflow: hidden;">
                                            <div class="popup-name" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 13px; color: #333;"><?= htmlspecialchars($cart_item['name']) ?></div>
                                            <div class="popup-price" style="color: #ee4d2d; font-size: 13px;">
                                                <?= formatMoney($cart_item['price']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="popup-action" style="padding: 10px; text-align: right; background: #f8f8f8;">
                                <a href="cart.php" class="btn-view-cart" style="background: #ee4d2d; color: #fff; padding: 8px 15px; text-decoration: none; font-size: 14px; border-radius: 2px;">Xem Giỏ Hàng</a>
                            </div>
                        <?php else: ?>
                            <div class="cart-empty-icon-wrapper"><i class="fas fa-shopping-bag"></i></div>
                            <p>Chưa Có Sản Phẩm</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">

        <?php if (!empty($_SESSION['cart'])): ?>
            
            <div class="cart-header">
                <div style="width: 40%">
                    <input type="checkbox" id="check-all-top" onchange="toggleAll(this)">
                    <span class="ml-3">Sản Phẩm</span>
                </div>
                <div style="width: 15%; text-align: center;">Đơn Giá</div>
                <div style="width: 15%; text-align: center;">Số Lượng</div>
                <div style="width: 15%; text-align: center;">Số Tiền</div>
                <div style="width: 15%; text-align: center;">Thao Tác</div>
            </div>

            <div class="cart-shop-group">
                <div class="shop-header">
                    <input type="checkbox" class="shop-check" onchange="checkShop(this)">
                    <span class="badge-mall">Yêu thích+</span>
                    <span style="font-weight: 500;">Sài Gòn New (LaiRai Official)</span>
                    <i class="fas fa-comment-dots text-danger ml-2"></i>
                </div>

                <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                    <div class="cart-item" id="row-<?= $id ?>">
                        <div class="item-info">
                            <input type="checkbox" class="item-check" value="<?= $id ?>" data-price="<?= $item['price'] ?>" data-qty="<?= $item['qty'] ?>" onchange="updateTotal()">
                            <img src="<?= getImgUrl($item['image']) ?>" class="item-img">
                            <div>
                                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="item-classification"><i class="fas fa-sort-down"></i> Phân loại: Mặc định</div>
                                <img src="https://down-vn.img.susercontent.com/file/vn-50009109-c7a2e1ae720f9704f92f72c6cef24d7d" height="18">
                            </div>
                        </div>

                        <div class="item-price">
                            <span class="new-price"><?= formatMoney($item['price']) ?></span>
                        </div>

                        <div class="item-qty">
                            <button class="qty-btn" onclick="changeQty(<?= $id ?>, -1)">-</button>
                            <input type="text" class="qty-input" id="qty-<?= $id ?>" value="<?= $item['qty'] ?>" readonly>
                            <button class="qty-btn" onclick="changeQty(<?= $id ?>, 1)">+</button>
                        </div>

                        <div class="item-total" id="total-<?= $id ?>">
                            <?= formatMoney($item['price'] * $item['qty']) ?>
                        </div>

                        <div class="item-action" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <form method="POST" style="margin-bottom: 5px;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="pid" value="<?= $id ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">Xóa</button>
                            </form>
                            <a href="search.php?keyword=<?= urlencode($item['name']) ?>" style="color: #ee4d2d; font-size: 12px; text-decoration: none; display: block;">
                                Tìm sản phẩm tương tự <i class="fas fa-caret-down"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            </div>

            <div class="cart-footer">
                <div class="footer-left">
                    <input type="checkbox" id="check-all-bot" onchange="toggleAll(this)">
                    <span class="ml-2 mr-3">Chọn Tất Cả (<span id="count-items">0</span>)</span>

                    <span class="mr-3" style="cursor: pointer;" onclick="deleteSelected()">Xóa</span>

                    <span style="color: #ee4d2d; cursor: pointer;">Lưu vào mục Đã thích</span>
                </div>
                <div class="footer-right">
                    <div style="text-align: right; margin-right: 20px;">
                        <div class="d-flex align-items-center justify-content-end">
                            <span class="total-text">Tổng thanh toán (<span id="count-items-2">0</span> sản phẩm):</span>
                            <span class="total-price-large" id="grand-total">0₫</span>
                        </div>
                        <div style="font-size: 12px; color: #ee4d2d;">Tiết kiệm ₫0</div>
                    </div>
                    <button class="btn-checkout" onclick="processCheckout()">Mua Hàng</button>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-cart-box">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h5 class="text-muted">Giỏ hàng của bạn còn trống</h5>
                <p class="text-muted mb-4">Hãy chọn thêm sản phẩm để mua sắm nhé</p>
                <a href="homepage.php" class="btn btn-danger px-4">Mua Ngay</a>
            </div>
        <?php endif; ?>

    </div>

    <footer class="lairai-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>CHĂM SÓC KHÁCH HÀNG</h3>
                    <ul>
                        <li><a href="#">Trung Tâm Trợ Giúp</a></li>
                        <li><a href="#">Hướng Dẫn Mua Hàng</a></li>
                        <li><a href="#">Hướng Dẫn Bán Hàng</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>LAIRAISHOP VIỆT NAM</h3>
                    <ul>
                        <li><a href="#">Về LaiRaiShop</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>THANH TOÁN</h3>
                    <div class="payment-icons">
                        <img src="https://down-vn.img.susercontent.com/file/d4bbea4570b93bfd5fc652ca82a262a8" alt="Visa">
                        <img src="https://down-vn.img.susercontent.com/file/a0a9062ebe19b45c1ae0506f16af5c16" alt="MasterCard">
                        <img src="https://down-vn.img.susercontent.com/file/2c46b83d84111ddc32cfd3b5995d9281" alt="COD">
                    </div>
                </div>
                <div class="footer-column">
                    <h3>TẢI ỨNG DỤNG</h3>
                    <div class="download-app">
                        <div class="qr-code">
                            <img src="https://down-vn.img.susercontent.com/file/a5e589e8e118e937dc660f224b9a1472" alt="QR Code">
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="copyright">© 2025 LaiRaiShop. Tất cả các quyền được bảo lưu.</div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/homepage.js?v=4"></script>
    <script>
        // Hàm định dạng tiền tệ JS
        function formatMoneyJS(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        // Chọn tất cả
        function toggleAll(source) {
            var checkboxes = document.querySelectorAll('.item-check, .shop-check, #check-all-top, #check-all-bot');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
            updateTotal();
        }

        // Chọn shop
        function checkShop(source) {
            var checkboxes = source.closest('.cart-shop-group').querySelectorAll('.item-check');
            checkboxes.forEach(cb => cb.checked = source.checked);
            updateTotal();
        }

        // Cập nhật tổng tiền khi tick checkbox
        function updateTotal() {
            var total = 0;
            var count = 0;
            var checks = document.querySelectorAll('.item-check:checked');

            checks.forEach(function(checkbox) {
                var price = parseInt(checkbox.getAttribute('data-price'));
                var qty = parseInt(checkbox.getAttribute('data-qty'));
                total += price * qty;
                count += qty;
            });

            document.getElementById('grand-total').innerText = formatMoneyJS(total);
            document.getElementById('count-items').innerText = count;
            document.getElementById('count-items-2').innerText = count;
        }

        // Tăng giảm số lượng
        function changeQty(pid, change) {
            var input = document.getElementById('qty-' + pid);
            var currentQty = parseInt(input.value);
            var newQty = currentQty + change;

            if (newQty < 1) return;

            input.value = newQty;

            // Cập nhật attribute data-qty cho checkbox để tính lại tổng
            var checkbox = document.querySelector(`.item-check[value="${pid}"]`);
            if(checkbox) checkbox.setAttribute('data-qty', newQty);

            // Cập nhật tiền từng món (Item Total)
            var price = parseInt(checkbox ? checkbox.getAttribute('data-price') : 0);
            if(checkbox) document.getElementById('total-' + pid).innerText = formatMoneyJS(price * newQty);

            // Gọi AJAX cập nhật Session
            $.post('cart.php', {
                action: 'update_qty',
                pid: pid,
                qty: newQty
            });

            // Tính lại tổng tiền
            updateTotal();
        }

        // Hàm xóa sản phẩm đã chọn
        function deleteSelected() {
            var checks = document.querySelectorAll('.item-check:checked');
            if (checks.length === 0) {
                alert("Vui lòng chọn sản phẩm cần xóa!");
                return;
            }

            if (confirm("Bạn có chắc chắn muốn xóa " + checks.length + " sản phẩm đã chọn?")) {
                var pids = [];
                checks.forEach(function(checkbox) {
                    pids.push(checkbox.value);
                });

                $.post('cart.php', {
                    action: 'delete_selected',
                    pids: pids
                }, function(response) {
                    location.reload();
                });
            }
        }

        // Hàm xử lý thanh toán (Mua Hàng)
        function processCheckout() {
            var checks = document.querySelectorAll('.item-check:checked');
            
            if(checks.length === 0) {
                alert("Vui lòng chọn ít nhất một sản phẩm để mua!");
                return;
            }

            var selectedIds = [];
            checks.forEach(function(checkbox) {
                selectedIds.push(checkbox.value);
            });

            window.location.href = 'checkout.php?ids=' + selectedIds.join(',');
        }
    </script>
</body>

</html>