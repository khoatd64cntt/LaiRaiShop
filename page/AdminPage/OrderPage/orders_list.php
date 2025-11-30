<?php
// BƯỚC 1: NHÚNG HEADER
require_once '../Layout/header.php';

// BƯỚC 2: XỬ LÝ CẬP NHẬT TRẠNG THÁI (Giữ nguyên để phòng trường hợp cần dùng)
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
    $oid = $conn->real_escape_string($_POST['oid']);
    $status = $conn->real_escape_string($_POST['status']);
    
    if($conn->query("UPDATE orders SET status = '$status' WHERE oid = '$oid'")){
        $message = "<div class='alert alert-success'>Cập nhật đơn hàng #$oid thành công!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// BƯỚC 3: TRUY VẤN (ĐÃ CHỈNH SỬA)
// 1. Thêm JOIN với bảng order_items -> products -> shops để lấy tên Shop
// 2. Thêm điều kiện WHERE o.status = 'completed' để chỉ hiện đơn đã xong
// 3. Dùng GROUP BY vì một đơn hàng có thể có nhiều sản phẩm, tránh bị lặp dòng
$sql = "SELECT 
            o.*, 
            CONCAT(a.afname, ' ', a.alname) as customer_name,
            GROUP_CONCAT(DISTINCT s.shop_name SEPARATOR ', ') as shop_names
        FROM orders o 
        JOIN acc a ON o.aid = a.aid 
        JOIN order_items oi ON o.oid = oi.oid
        JOIN products p ON oi.pid = p.pid
        JOIN shops s ON p.sid = s.sid
        WHERE o.status = 'completed'
        GROUP BY o.oid
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<h1 class="h3 mb-4 text-gray-800">Lịch Sử Đơn Hàng (Đã Hoàn Thành)</h1>
<?= $message ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng đã hoàn tất</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th style="color: #e74a3b;">Shop Bán</th> <th>Ngày Đặt</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['oid'] ?></td>
                            <td style="font-weight: bold;"><?= htmlspecialchars($row['customer_name']) ?></td>
                            
                            <td style="color: #4e73df; font-weight: 500;">
                                <?= htmlspecialchars($row['shop_names']) ?>
                            </td>

                            <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                            <td class="text-danger font-weight-bold"><?= number_format($row['total_amount']) ?>đ</td>
                            <td>
                                <span class="badge badge-success">Completed</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" 
                                        data-toggle="modal" data-target="#modalOrder"
                                        onclick="editOrder('<?= $row['oid'] ?>', '<?= $row['status'] ?>')">
                                    <i class="fas fa-edit"></i> Chi tiết / Sửa
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Chưa có đơn hàng nào hoàn thành.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalOrder" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật Đơn hàng #<span id="m_oid_text"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="oid" id="m_oid">
                    <div class="form-group">
                        <label>Trạng thái:</label>
                        <select name="status" id="m_status" class="form-control">
                            <option value="pending">Pending (Chờ xử lý)</option>
                            <option value="paid">Paid (Đã thanh toán)</option>
                            <option value="shipped">Shipped (Đang giao)</option>
                            <option value="completed">Completed (Hoàn thành)</option>
                            <option value="cancelled">Cancelled (Hủy)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_order" class="btn btn-primary">Lưu thay đổi</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editOrder(oid, status) {
    document.getElementById('m_oid').value = oid;
    document.getElementById('m_oid_text').innerText = oid;
    document.getElementById('m_status').value = status;
}
</script>

<?php require_once '../Layout/footer.php'; ?>