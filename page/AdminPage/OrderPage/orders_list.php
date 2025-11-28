<?php
// BƯỚC 1: NHÚNG HEADER
require_once '../Layout/header.php';

// BƯỚC 2: XỬ LÝ CẬP NHẬT TRẠNG THÁI
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $conn->real_escape_string($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);

    // Chỉ cho phép cập nhật nếu trạng thái mới là hợp lệ
    $valid_statuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        $sql_update = "UPDATE orders SET status = '$new_status' WHERE oid = '$order_id'";
        if ($conn->query($sql_update) === TRUE) {
            $message = "<div class='alert alert-success'>Cập nhật trạng thái đơn hàng #$order_id thành **$new_status** thành công.</div>";
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

// QUERY CHUẨN: Nối afname và alname thành tên khách hàng
$sql = "SELECT 
            o.*, 
            CONCAT(a.afname, ' ', a.alname) AS customer_name 
        FROM orders o
        JOIN acc a ON o.aid = a.aid
        $where_clause
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<h1 class="mt-4">Quản Lý Đơn Hàng</h1>
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
                <option value="pending" <?php if ($filter_status === 'pending') echo 'selected'; ?>>Chờ xử lý</option>
                <option value="paid" <?php if ($filter_status === 'paid') echo 'selected'; ?>>Đã thanh toán</option>
                <option value="shipped" <?php if ($filter_status === 'shipped') echo 'selected'; ?>>Đang giao</option>
                <option value="completed" <?php if ($filter_status === 'completed') echo 'selected'; ?>>Hoàn thành</option>
                <option value="cancelled" <?php if ($filter_status === 'cancelled') echo 'selected'; ?>>Đã hủy</option>
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
                                <td><?php echo date('d-m-Y H:i', strtotime($row['order_date'])); ?></td>
                                <td class="text-danger font-weight-bold"><?php echo formatCurrency($row['total_amount']); ?></td>
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
                                    <button type="button" class="btn btn-sm btn-info update-status-btn"
                                        data-toggle="modal" data-target="#updateStatusModal"
                                        data-id="<?php echo $row['oid']; ?>"
                                        data-current-status="<?php echo $row['status']; ?>">
                                        <i class="fas fa-edit"></i> Cập nhật
                                    </button>
                                    <!-- Nút xem chi tiết (nếu bạn có trang order_detail.php) -->
                                    <a href="#" class="btn btn-sm btn-outline-primary" title="Chức năng đang phát triển"><i class="fas fa-eye"></i> Chi tiết</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Không tìm thấy đơn hàng nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Cập nhật Trạng thái -->
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
                    <div class="form-group">
                        <label for="new_status">Trạng thái Mới:</label>
                        <select name="new_status" id="new_status" class="form-control">
                            <option value="pending">Chờ xử lý</option>
                            <option value="paid">Đã thanh toán</option>
                            <option value="shipped">Đang giao</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript để truyền dữ liệu Đơn hàng vào Modal
    $('.update-status-btn').on('click', function() {
        var orderId = $(this).data('id');
        var currentStatus = $(this).data('current-status');

        $('#orderIdDisplay').text(orderId);
        $('#modalOrderId').val(orderId);
        $('#new_status').val(currentStatus); // Đặt trạng thái hiện tại làm mặc định
    });
</script>

<?php
// BƯỚC 4: NHÚNG FOOTER
include '../Layout/footer.php';
?>