<?php
session_start();
require_once '../../db/db.php';

// Xử lý cập nhật/xóa (Backend)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $pid = $_POST['pid'];
        unset($_SESSION['cart'][$pid]);
    }
    // Cập nhật số lượng (dành cho xử lý JS gọi về)
    if (isset($_POST['action']) && $_POST['action'] == 'update_qty') {
        $pid = $_POST['pid'];
        $qty = $_POST['qty'];
        if($qty > 0) $_SESSION['cart'][$pid]['qty'] = $qty;
        exit; // Kết thúc để trả về cho AJAX
    }
}

function formatMoney($number) {
    return number_format($number, 0, ',', '.') . '₫';
}
function getImgUrl($path) {
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
    
    <style>
        body { background-color: #f5f5f5; color: #333; font-size: 14px; }
        
        /* HEADER GIỎ HÀNG */
        .cart-header {
            background: #fff;
            padding: 15px 20px;
            border-radius: 3px;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.05);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            color: #888;
            font-size: 14px;
        }

        /* KHỐI SẢN PHẨM CỦA SHOP */
        .cart-shop-group {
            background: #fff;
            border-radius: 3px;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.05);
            margin-bottom: 15px;
        }
        
        .shop-header {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,.09);
            display: flex;
            align-items: center;
        }
        
        .badge-mall {
            background-color: #d0011b;
            color: #fff;
            font-size: 10px;
            padding: 2px 4px;
            border-radius: 2px;
            margin-right: 5px;
            margin-left: 10px;
        }

        /* DÒNG SẢN PHẨM */
        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid rgba(0,0,0,.09);
        }
        
        .item-info { display: flex; align-items: flex-start; width: 40%; }
        .item-img { width: 80px; height: 80px; object-fit: cover; border: 1px solid #e8e8e8; margin: 0 10px; }
        .item-name { 
            line-height: 16px; 
            overflow: hidden; 
            display: -webkit-box; 
            -webkit-box-orient: vertical; 
            -webkit-line-clamp: 2; 
            margin-bottom: 5px;
        }
        .item-classification { color: #888; font-size: 12px; }
        
        .item-price { width: 15%; text-align: center; }
        .old-price { text-decoration: line-through; color: #999; font-size: 13px; margin-right: 5px; }
        .new-price { color: #333; font-weight: 500; }
        
        .item-qty { width: 15%; text-align: center; display: flex; justify-content: center; }
        .qty-btn { border: 1px solid rgba(0,0,0,.09); background: transparent; width: 30px; height: 30px; cursor: pointer; }
        .qty-input { border: 1px solid rgba(0,0,0,.09); border-left: 0; border-right: 0; width: 50px; height: 30px; text-align: center; }
        
        .item-total { width: 15%; text-align: center; color: #ee4d2d; font-weight: bold; }
        .item-action { width: 15%; text-align: center; }
        .btn-delete { color: #333; cursor: pointer; background: none; border: none;}
        .btn-delete:hover { color: #ee4d2d; }

        /* THANH THANH TOÁN (STICKY FOOTER) */
        .cart-footer {
            background: #fff;
            position: sticky;
            bottom: 0;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 -2px 5px rgba(0,0,0,.05);
            margin-top: 20px;
            border-top: 1px solid rgba(0,0,0,.09);
        }
        
        .footer-left { display: flex; align-items: center; }
        .footer-right { display: flex; align-items: center; }
        
        .total-text { font-size: 16px; margin-right: 10px; }
        .total-price-large { color: #ee4d2d; font-size: 24px; font-weight: bold; margin-right: 20px; }
        
        .btn-checkout {
            background: #ee4d2d;
            color: #fff;
            border: none;
            padding: 10px 40px;
            border-radius: 2px;
            font-size: 16px;
            text-transform: capitalize;
        }
        .btn-checkout:hover { background: #f05d40; color: #fff; }

        /* CHECKBOX CUSTOM */
        input[type="checkbox"] { transform: scale(1.2); cursor: pointer; }
    </style>
</head>

<body>
    <div style="height: 60px; background: #ee4d2d; margin-bottom: 20px;">
        <div class="container h-100 d-flex align-items-center">
            <h3 class="text-white m-0">Giỏ Hàng</h3>
        </div>
    </div>

    <div class="container">
        
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

        <?php if (!empty($_SESSION['cart'])): ?>
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
                            <span class="old-price"><?= formatMoney($item['price'] * 1.2) ?></span>
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
                        
                        <div class="item-action">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="pid" value="<?= $id ?>">
                                <button type="submit" class="btn-delete">Xóa</button>
                            </form>
                            <div style="color: #ee4d2d; font-size: 12px; margin-top: 5px; cursor: pointer;">
                                Tìm sản phẩm tương tự <i class="fas fa-caret-down"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="padding: 15px 20px; border-top: 1px solid #eee; color: #ee4d2d; font-size: 13px;">
                    <i class="fas fa-shipping-fast"></i> Giảm ₫300.000 phí vận chuyển đơn tối thiểu ₫0 <span style="color: #0055aa; cursor: pointer;">Tìm hiểu thêm</span>
                </div>
            </div>

            <div class="cart-footer">
                <div class="footer-left">
                    <input type="checkbox" id="check-all-bot" onchange="toggleAll(this)">
                    <span class="ml-2 mr-3">Chọn Tất Cả (<span id="count-items">0</span>)</span>
                    <span class="mr-3" style="cursor: pointer;">Xóa</span>
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
                    <button class="btn-checkout">Mua Hàng</button>
                </div>
            </div>

        <?php else: ?>
            <div class="text-center p-5 bg-white">
                <img src="https://deo.shopeemobile.com/shopee/shopee-pcmall-live-sg/9bdd8040b334d31946f4.png" width="100">
                <p class="mt-3 text-muted">Giỏ hàng của bạn còn trống</p>
                <a href="../../index.php" class="btn btn-danger">Mua Ngay</a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script>
        // Hàm định dạng tiền tệ JS
        function formatMoneyJS(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }

        // Chọn tất cả
        function toggleAll(source) {
            var checkboxes = document.querySelectorAll('.item-check, .shop-check, #check-all-top, #check-all-bot');
            for(var i=0; i<checkboxes.length; i++) {
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

        // Tăng giảm số lượng (Có AJAX cập nhật session ngầm)
        function changeQty(pid, change) {
            var input = document.getElementById('qty-' + pid);
            var currentQty = parseInt(input.value);
            var newQty = currentQty + change;
            
            if (newQty < 1) return;

            input.value = newQty;
            
            // Cập nhật attribute data-qty cho checkbox để tính lại tổng
            var checkbox = document.querySelector(`.item-check[value="${pid}"]`);
            checkbox.setAttribute('data-qty', newQty);
            
            // Cập nhật tiền từng món (Item Total)
            var price = parseInt(checkbox.getAttribute('data-price'));
            document.getElementById('total-' + pid).innerText = formatMoneyJS(price * newQty);

            // Gọi AJAX cập nhật Session
            $.post('cart.php', { action: 'update_qty', pid: pid, qty: newQty });

            // Tính lại tổng tiền
            updateTotal();
        }
    </script>
</body>
</html>