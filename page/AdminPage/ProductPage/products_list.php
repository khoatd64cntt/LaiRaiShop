<?php
// File: page/AdminPage/ProductPage/products_list.php
require_once '../Layout/header.php';
$message = '';

// --- 1. XỬ LÝ UPDATE TRẠNG THÁI ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $pid = $conn->real_escape_string($_POST['pid']);
    $status = $conn->real_escape_string($_POST['status']);

    if ($conn->query("UPDATE products SET status = '$status' WHERE pid = '$pid'")) {
        $message = "<div class='alert alert-success'>Cập nhật trạng thái SP #$pid thành công!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// --- 2. XỬ LÝ XÓA SẢN PHẨM & XÓA ẢNH ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['pid'])) {
    $pid = $conn->real_escape_string($_GET['pid']);

    // a. Xóa file ảnh vật lý
    $res_img = $conn->query("SELECT main_image FROM products WHERE pid = '$pid'");
    if ($res_img && $row_img = $res_img->fetch_assoc()) {
        $img_url = $row_img['main_image'];
        // Chỉ xóa nếu là ảnh nội bộ (không xóa link online)
        if (!filter_var($img_url, FILTER_VALIDATE_URL)) {
            $physical_path = ROOT_PATH . $img_url;
            if (file_exists($physical_path)) {
                unlink($physical_path);
            }
        }
    }

    // b. Xóa DB
    if ($conn->query("DELETE FROM products WHERE pid = '$pid'")) {
        $message = "<div class='alert alert-success'>Đã xóa sản phẩm #$pid và ảnh liên quan.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi xóa: " . $conn->error . "</div>";
    }
}

// --- 3. TRUY VẤN ---
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
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách tất cả sản phẩm</h6>
    </div>
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
                    <?php if ($result) while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['pid'] ?></td>
                            <td>
                                <?php
                                $img_src = $row['main_image'];
                                if (!filter_var($img_src, FILTER_VALIDATE_URL)) {
                                    $img_src = BASE_URL . $img_src;
                                }
                                ?>
                                <img src="<?= $img_src ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['shop_name']) ?></td>
                            <td><?= number_format($row['price']) ?>đ</td>
                            <td>
                                <?php
                                $colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'hidden' => 'secondary'];
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
                                    onclick="return confirm('CẢNH BÁO: Xóa vĩnh viễn sản phẩm?')">
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
                    <h5 class="modal-title">Cập nhật SP #<span id="m_pid_text"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pid" id="m_pid">
                    <div class="form-group">
                        <label>Tên sản phẩm:</label>
                        <input type="text" id="m_name" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái:</label>
                        <select name="status" id="m_status" class="form-control">
                            <option value="pending">Chờ duyệt</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="rejected">Từ chối</option>
                            <option value="hidden">Ẩn</option>
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