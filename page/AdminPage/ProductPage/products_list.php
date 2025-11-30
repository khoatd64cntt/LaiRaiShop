<?php
// File: page/AdminPage/ProductPage/products_list.php
require_once '../Layout/header.php';
$message = '';

// --- XỬ LÝ: DUYỆT / TỪ CHỐI (UPDATE STATUS) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $pid = $conn->real_escape_string($_POST['pid']);
    $status = $conn->real_escape_string($_POST['status']);
    
    if($conn->query("UPDATE products SET status = '$status' WHERE pid = '$pid'")){
        $message = "<div class='alert alert-success'>Cập nhật trạng thái sản phẩm #$pid thành công!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// --- XỬ LÝ: XÓA SẢN PHẨM (MỚI) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['pid'])) {
    $pid = $conn->real_escape_string($_GET['pid']);
    // CSDL của bạn có ON DELETE CASCADE ở các bảng liên quan (reviews, order_items...) 
    // nên xóa ở đây là sạch sẽ.
    if ($conn->query("DELETE FROM products WHERE pid = '$pid'")) {
        $message = "<div class='alert alert-success'>Đã xóa vĩnh viễn sản phẩm #$pid.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi xóa: " . $conn->error . "</div>";
    }
}

// --- TRUY VẤN ---
$sql = "SELECT p.*, s.shop_name, c.name AS cat_name 
        FROM products p
        LEFT JOIN shops s ON p.sid = s.sid
        LEFT JOIN categories c ON p.cid = c.cid
        ORDER BY p.pid DESC";
$result = $conn->query($sql);
?>

<h1 class="h3 mb-4 text-gray-800">Kiểm Duyệt Sản Phẩm</h1>
<?= $message ?>

<div class="card shadow mb-4">
    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách tất cả sản phẩm</h6></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình</th>
                        <th>Tên SP</th>
                        <th>Shop</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result) while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['pid'] ?></td>
                        <td>
                            <img src="/LaiRaiShop<?= $row['main_image'] ?>" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['shop_name']) ?></td>
                        <td><?= number_format($row['price']) ?>đ</td>
                        <td>
                            <?php 
                                $colors = ['pending'=>'warning', 'approved'=>'success', 'rejected'=>'danger', 'hidden'=>'secondary'];
                                $st = $row['status'];
                            ?>
                            <span class="badge badge-<?= $colors[$st] ?>"><?= ucfirst($st) ?></span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                    data-toggle="modal" data-target="#modalProd"
                                    onclick="viewProd('<?= $row['pid'] ?>', '<?= htmlspecialchars($row['name']) ?>', '<?= $row['status'] ?>')">
                                <i class="fas fa-edit"></i> Duyệt
                            </button>
                            
                            <a href="?action=delete&pid=<?= $row['pid'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('CẢNH BÁO: Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật trạng thái SP #<span id="m_pid_text"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pid" id="m_pid">
                    <div class="form-group">
                        <label>Tên sản phẩm:</label>
                        <input type="text" id="m_name" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái kiểm duyệt:</label>
                        <select name="status" id="m_status" class="form-control">
                            <option value="pending">Chờ duyệt (Pending)</option>
                            <option value="approved">Đã duyệt (Approved)</option>
                            <option value="rejected">Từ chối (Rejected)</option>
                            <option value="hidden">Ẩn (Hidden)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_status" class="btn btn-primary">Lưu thay đổi</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewProd(pid, name, status) {
    document.getElementById('m_pid').value = pid;
    document.getElementById('m_pid_text').innerText = pid;
    document.getElementById('m_name').value = name;
    document.getElementById('m_status').value = status;
}
</script>

<?php require_once '../Layout/footer.php'; ?>