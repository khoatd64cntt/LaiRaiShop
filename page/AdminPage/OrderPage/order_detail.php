<?php
// File: page/AdminPage/OrderPage/order_detail.php

// 1. NHÚNG HEADER
require_once '../Layout/header.php';

// 2. LẤY ID ĐƠN HÀNG TỪ URL
if (!isset($_GET['oid']) || empty($_GET['oid'])) {
    echo "<script>alert('Không tìm thấy mã đơn hàng!'); window.location.href='orders_list.php';</script>";
    exit;
}
$oid = intval($_GET['oid']);

// 3. TRUY VẤN THÔNG TIN CHUNG ĐƠN HÀNG
$sql_order = "SELECT o.*, CONCAT(a.afname, ' ', a.alname) as customer_name, a.email, a.phone as user_phone
              FROM orders o
              JOIN acc a ON o.aid = a.aid
              WHERE o.oid = $oid";
$result_order = $conn->query($sql_order);

if ($result_order->num_rows == 0) {
    echo "<div class='alert alert-danger'>Đơn hàng không tồn tại.</div>";
    exit;
}
$order = $result_order->fetch_assoc();

// 4. TRUY VẤN CHI TIẾT SẢN PHẨM TRONG ĐƠN (KÈM TÊN SHOP)
$sql_items = "SELECT oi.*, p.name as product_name, p.main_image, s.shop_name
              FROM order_items oi
              JOIN products p ON oi.pid = p.pid
              JOIN shops s ON p.sid = s.sid
              WHERE oi.oid = $oid";
$result_items = $conn->query($sql_items);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 mb-0 text-gray-800">Chi Tiết Đơn Hàng #<?php echo $oid; ?></h1>
        <?php
        // Lấy trạng thái lọc từ URL (nếu có) để quay lại đúng chỗ
        $back_link = "orders_list.php";
        if (isset($_GET['ref_status']) && $_GET['ref_status'] !== '') {
            $back_link .= "?status=" . urlencode($_GET['ref_status']);
        }
        ?>
        <a href="<?php echo $back_link; ?>" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông Tin Khách Hàng</h6>
                </div>
                <div class="card-body">
                    <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>SĐT Tài khoản:</strong> <?php echo htmlspecialchars($order['user_phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <hr>
                    <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['fullname']); ?></p>
                    <p><strong>SĐT Nhận:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <p><strong>Ghi chú:</strong> <em class="text-muted"><?php echo htmlspecialchars($order['note'] ?? 'Không có'); ?></em></p>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trạng Thái Đơn Hàng</h6>
                </div>
                <div class="card-body text-center">
                    <h4 class="mb-3">
                        <span class="badge badge-success px-3 py-2">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </h4>
                    <p class="text-muted">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>

                    <form method="POST" action="orders_list.php" class="mt-4">
                        <input type="hidden" name="order_id" value="<?= $oid ?>">
                        <div class="form-group">
                            <select name="new_status" class="form-control">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" name="update_order" class="btn btn-primary btn-block">Cập nhật trạng thái</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Danh Sách Sản Phẩm</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Shop bán</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-right">Đơn giá</th>
                                    <th class="text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $grand_total = 0;
                                if ($result_items->num_rows > 0):
                                    while ($item = $result_items->fetch_assoc()):
                                        $subtotal = $item['price'] * $item['quantity'];
                                        $grand_total += $subtotal;

                                        // Xử lý ảnh
                                        $imgSrc = $item['main_image'];
                                        // Logic hiển thị ảnh (giống các file khác)
                                        if (strpos($imgSrc, 'http') === 0) $displayImg = $imgSrc;
                                        else $displayImg = BASE_URL . '/' . ltrim($imgSrc, '/');
                                ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $displayImg; ?>" alt="Img" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #ddd; margin-right: 10px;" onerror="this.src='https://via.placeholder.com/50'">
                                                    <span class="font-weight-bold"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><span class="badge badge-info"><?php echo htmlspecialchars($item['shop_name']); ?></span></td>
                                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                                            <td class="text-right"><?php echo formatCurrency($item['price']); ?></td>
                                            <td class="text-right font-weight-bold"><?php echo formatCurrency($subtotal); ?></td>
                                        </tr>
                                <?php endwhile;
                                endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="4" class="text-right font-weight-bold text-uppercase">Tổng thanh toán:</td>
                                    <td class="text-right text-danger font-weight-bold h5">
                                        <?php echo formatCurrency($order['total_amount']); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../Layout/footer.php'; ?>