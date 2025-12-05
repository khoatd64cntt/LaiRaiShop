<?php
// File: page/AdminPage/OrderPage/orders_list.php

// BƯỚC 1: NHÚNG HEADER
require_once '../Layout/header.php';

$message = '';

// BƯỚC 2: XỬ LÝ CẬP NHẬT TRẠNG THÁI (CÓ LOGIC KHO HÀNG + TÍNH HOA HỒNG)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
    $oid = $conn->real_escape_string($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);

    // 1. Lấy trạng thái CŨ và TỔNG TIỀN của đơn hàng
    $query_info = $conn->query("SELECT status, total_amount FROM orders WHERE oid = '$oid'");
    $order_info = ($query_info && $query_info->num_rows > 0) ? $query_info->fetch_assoc() : null;

    $old_status = $order_info['status'] ?? '';
    $total_amount = $order_info['total_amount'] ?? 0;

    // 2. Cập nhật trạng thái MỚI
    $valid_statuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];

    if (in_array($new_status, $valid_statuses)) {

        // [LOGIC TÍNH HOA HỒNG CHUẨN]
        $commission_sql_part = "";

        if ($new_status == 'completed') {
            $fee = $total_amount * 0.05;
            $commission_sql_part = ", commission_fee = '$fee'";
        } else {
            // [MỚI] Reset phí về 0 nếu chuyển trạng thái khác Completed
            $commission_sql_part = ", commission_fee = 0";
        }

        $sql_update = "UPDATE orders SET status = '$new_status' $commission_sql_part WHERE oid = '$oid'";

        // ... (Các đoạn code xử lý kho phía sau giữ nguyên)
        if ($conn->query($sql_update) === TRUE) {

            // --- LOGIC XỬ LÝ KHO HÀNG ---
            // A. Hủy đơn -> Hoàn kho
            if ($new_status == 'cancelled' && $old_status != 'cancelled') {
                $items = $conn->query("SELECT pid, quantity FROM order_items WHERE oid = '$oid'");
                while ($item = $items->fetch_assoc()) {
                    $pid = $item['pid'];
                    $qty = $item['quantity'];
                    $conn->query("UPDATE products SET stock = stock + $qty WHERE pid = $pid");
                }
                $message = "<div class='alert alert-success'>Đã hủy đơn #$oid và hoàn kho.</div>";
            }
            // B. Khôi phục đơn hủy -> Trừ kho
            elseif ($old_status == 'cancelled' && $new_status != 'cancelled') {
                $items = $conn->query("SELECT pid, quantity FROM order_items WHERE oid = '$oid'");
                while ($item = $items->fetch_assoc()) {
                    $pid = $item['pid'];
                    $qty = $item['quantity'];
                    $conn->query("UPDATE products SET stock = stock - $qty WHERE pid = $pid");
                }
                $message = "<div class='alert alert-success'>Đã khôi phục đơn #$oid và trừ kho.</div>";
            } else {
                $message = "<div class='alert alert-success'>Cập nhật trạng thái đơn #$oid thành **$new_status** thành công.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Lỗi cập nhật: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Trạng thái không hợp lệ.</div>";
    }
}

// BƯỚC 3: XỬ LÝ LỌC VÀ TRUY VẤN
$filter_status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$where_clause = "WHERE 1=1";
if ($filter_status && $filter_status !== 'all') {
    $where_clause .= " AND o.status = '$filter_status'";
}

// Query lấy thêm tên Shop
$sql = "SELECT 
            o.*, 
            CONCAT(a.afname, ' ', a.alname) AS customer_name,
            GROUP_CONCAT(DISTINCT s.shop_name SEPARATOR ', ') as shop_names
        FROM orders o
        JOIN acc a ON o.aid = a.aid
        LEFT JOIN order_items oi ON o.oid = oi.oid
        LEFT JOIN products p ON oi.pid = p.pid
        LEFT JOIN shops s ON p.sid = s.sid
        $where_clause
        GROUP BY o.oid
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<h1 class="mt-4 text-gray-800">Quản Lý Đơn Hàng (Admin)</h1>
<?php echo $message; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Bộ Lọc & Tìm Kiếm</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <label class="mr-2">Trạng thái:</label>
            <select name="status" class="form-control mr-3">
                <option value="all" <?php if ($filter_status === 'all' || $filter_status === '') echo 'selected'; ?>>Tất cả</option>
                <option value="pending" <?php if ($filter_status === 'pending') echo 'selected'; ?>>Chờ xử lý (Pending)</option>
                <option value="paid" <?php if ($filter_status === 'paid') echo 'selected'; ?>>Đã thanh toán (Paid)</option>
                <option value="shipped" <?php if ($filter_status === 'shipped') echo 'selected'; ?>>Đang giao (Shipped)</option>
                <option value="completed" <?php if ($filter_status === 'completed') echo 'selected'; ?>>Hoàn thành (Completed)</option>
                <option value="cancelled" <?php if ($filter_status === 'cancelled') echo 'selected'; ?>>Đã hủy (Cancelled)</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách Đơn hàng (<?php echo ($result) ? $result->num_rows : 0; ?>)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Khách hàng</th>
                        <th>Shop Bán</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['oid']; ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td style="color: #4e73df; font-weight: 600;"><?php echo htmlspecialchars($row['shop_names']); ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($row['order_date'])); ?></td>
                                <td class="text-danger font-weight-bold">
                                    <?php echo function_exists('formatCurrency') ? formatCurrency($row['total_amount']) : number_format($row['total_amount']) . 'đ'; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'pending' => 'badge-warning',
                                        'paid' => 'badge-info',
                                        'shipped' => 'badge-primary',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-danger'
                                    ];
                                    echo '<span class="badge ' . ($status_class[$row['status']] ?? 'badge-secondary') . '">' . ucfirst($row['status']) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <a href="order_detail.php?oid=<?php echo $row['oid']; ?>&ref_status=<?php echo htmlspecialchars($filter_status); ?>"
                                        class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>

                                    <button type="button" class="btn btn-sm btn-warning update-status-btn"
                                        title="Cập nhật trạng thái nhanh"
                                        data-toggle="modal" data-target="#updateStatusModal"
                                        data-id="<?php echo $row['oid']; ?>"
                                        data-current-status="<?php echo $row['status']; ?>">
                                        <i class="fas fa-edit"></i> Xử lý
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Không tìm thấy đơn hàng nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Cập nhật Đơn hàng #<span id="orderIdDisplay"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="modalOrderId">

                    <div class="alert alert-info" style="font-size: 13px;">
                        <i class="fas fa-info-circle"></i> <b>Lưu ý hệ thống:</b>
                        <ul style="margin-bottom: 0; padding-left: 20px;">
                            <li>Chuyển sang <b>Cancelled</b>: Hoàn lại kho.</li>
                            <li>Từ Cancelled sang khác: Trừ lại kho.</li>
                            <li>Chuyển sang <b>Completed</b>: Tự động tính phí sàn (5%).</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label for="new_status">Trạng thái Mới:</label>
                        <select name="new_status" id="new_status" class="form-control">
                            <option value="pending">Pending (Chờ xử lý)</option>
                            <option value="paid">Paid (Đã thanh toán)</option>
                            <option value="shipped">Shipped (Đang giao)</option>
                            <option value="completed">Completed (Hoàn thành)</option>
                            <option value="cancelled">Cancelled (Đã hủy)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" name="update_order" class="btn btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('.update-status-btn').on('click', function() {
        var orderId = $(this).data('id');
        var currentStatus = $(this).data('current-status');
        $('#orderIdDisplay').text(orderId);
        $('#modalOrderId').val(orderId);
        $('#new_status').val(currentStatus);
    });
</script>

<?php include '../Layout/footer.php'; ?>