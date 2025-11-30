<?php
// File: page/AdminPage/OrderPage/orders_list.php
require_once '../Layout/header.php';
$message = '';

// --- XỬ LÝ: UPDATE STATUS + HOÀN KHO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
    $oid = $conn->real_escape_string($_POST['oid']);
    $new_status = $conn->real_escape_string($_POST['status']);

    // 1. Lấy trạng thái cũ để so sánh
    $res_check = $conn->query("SELECT status FROM orders WHERE oid = '$oid'");
    $old_status = ($res_check->num_rows > 0) ? $res_check->fetch_assoc()['status'] : '';

    $flag_ok = true;

    // 2. Logic hoàn kho: Nếu chuyển sang Cancelled mà trước đó chưa hủy
    if ($new_status == 'cancelled' && $old_status != 'cancelled') {
        $items = $conn->query("SELECT pid, quantity FROM order_items WHERE oid = '$oid'");
        while ($item = $items->fetch_assoc()) {
            $pid = $item['pid'];
            $qty = $item['quantity'];
            // Cộng lại vào kho
            if (!$conn->query("UPDATE products SET stock = stock + $qty WHERE pid = '$pid'")) {
                $flag_ok = false;
            }
        }
    }
    // 3. Logic trừ kho lại: Nếu đang Cancelled mà chuyển sang trạng thái khác (phục hồi đơn)
    elseif ($old_status == 'cancelled' && $new_status != 'cancelled') {
        $items = $conn->query("SELECT pid, quantity FROM order_items WHERE oid = '$oid'");
        while ($item = $items->fetch_assoc()) {
            $pid = $item['pid'];
            $qty = $item['quantity'];
            // Trừ kho lại
            if (!$conn->query("UPDATE products SET stock = stock - $qty WHERE pid = '$pid'")) {
                $flag_ok = false;
            }
        }
    }

    if ($flag_ok) {
        if ($conn->query("UPDATE orders SET status = '$new_status' WHERE oid = '$oid'")) {
            $message = "<div class='alert alert-success'>Cập nhật đơn #$oid ($old_status -> $new_status) thành công!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi SQL: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Lỗi cập nhật kho hàng!</div>";
    }
}

// --- TRUY VẤN ---
$sql = "SELECT o.*, CONCAT(a.afname, ' ', a.alname) as customer_name 
        FROM orders o 
        JOIN acc a ON o.aid = a.aid 
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>

<h1 class="h3 mb-4 text-gray-800">Quản Lý Đơn Hàng</h1>
<?= $message ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng toàn hệ thống</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th>Ngày Đặt</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result) while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['oid'] ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                            <td class="text-danger font-weight-bold"><?= number_format($row['total_amount']) ?>đ</td>
                            <td>
                                <?php
                                $st_colors = ['pending' => 'warning', 'paid' => 'info', 'shipped' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
                                $st = $row['status'];
                                ?>
                                <span class="badge badge-<?= $st_colors[$st] ?>"><?= ucfirst($st) ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info"
                                    data-toggle="modal" data-target="#modalOrder"
                                    onclick="editOrder('<?= $row['oid'] ?>', '<?= $row['status'] ?>')">
                                    <i class="fas fa-edit"></i> Cập nhật
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
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
                    <h5 class="modal-title">Cập nhật Đơn #<span id="m_oid_text"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="oid" id="m_oid">
                    <div class="alert alert-warning small">
                        <i class="fas fa-info-circle"></i> Nếu chọn <b>Cancelled</b>, hệ thống sẽ tự động hoàn trả tồn kho.
                    </div>
                    <div class="form-group">
                        <label>Trạng thái mới:</label>
                        <select name="status" id="m_status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="shipped">Shipped</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled (Hủy & Hoàn kho)</option>
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