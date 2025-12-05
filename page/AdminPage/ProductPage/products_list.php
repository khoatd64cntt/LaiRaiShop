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

// --- 2. XỬ LÝ ẨN SẢN PHẨM (SOFT DELETE) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['pid'])) {
    $pid = $conn->real_escape_string($_GET['pid']);
    $sql_soft_delete = "UPDATE products SET status = 'hidden' WHERE pid = '$pid'";
    if ($conn->query($sql_soft_delete)) {
        $message = "<div class='alert alert-success'>Đã ẩn sản phẩm #$pid.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// --- 3. XỬ LÝ TÌM KIẾM & TRUY VẤN ---
$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

// Tạo câu lệnh SQL cơ bản
$sql = "SELECT p.*, s.shop_name, c.name AS cat_name 
        FROM products p
        LEFT JOIN shops s ON p.sid = s.sid
        LEFT JOIN categories c ON p.cid = c.cid
        WHERE 1=1";

// Nếu có từ khóa tìm kiếm, thêm điều kiện WHERE
if (!empty($search)) {
    $sql .= " AND (p.name LIKE '%$search%' OR s.shop_name LIKE '%$search%')";
}

$sql .= " ORDER BY p.pid DESC";
$result = $conn->query($sql);
?>

<h1 class="h3 mb-4 text-gray-800">Kiểm Duyệt Sản Phẩm</h1>
<?= $message ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách sản phẩm</h6>

        <form method="GET" class="form-inline">
            <div class="input-group">
                <input type="text" name="q" class="form-control small"
                    placeholder="Tìm tên SP, Shop..."
                    value="<?= htmlspecialchars($search) ?>"
                    style="border: 1px solid #d1d3e2; background-color: #fff;">

                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="products_list.php" class="btn btn-secondary" title="Xóa tìm kiếm"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%">
                <thead class="thead-light">
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
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr style="<?= $row['status'] == 'hidden' ? 'opacity: 0.6; background: #f8f9fa;' : '' ?>">
                                <td><?= $row['pid'] ?></td>
                                <td>
                                    <?php
                                    $img_src = $row['main_image'];
                                    if (!filter_var($img_src, FILTER_VALIDATE_URL)) $img_src = BASE_URL . $img_src;
                                    ?>
                                    <img src="<?= $img_src ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><span class="badge badge-light border"><?= htmlspecialchars($row['shop_name']) ?></span></td>
                                <td class="font-weight-bold text-danger"><?= number_format($row['price']) ?>đ</td>
                                <td>
                                    <?php
                                    $colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'hidden' => 'secondary'];
                                    $st = $row['status'];
                                    ?>
                                    <span class="badge badge-<?= $colors[$st] ?>"><?= ucfirst($st) ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" title="Sửa trạng thái"
                                        data-toggle="modal" data-target="#modalProd"
                                        onclick="viewProd('<?= $row['pid'] ?>', '<?= htmlspecialchars($row['name']) ?>', '<?= $row['status'] ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <?php if ($row['status'] !== 'hidden'): ?>
                                        <a href="?action=delete&pid=<?= $row['pid'] ?>" class="btn btn-sm btn-danger" title="Ẩn SP"
                                            onclick="return confirm('CẢNH BÁO: Ẩn sản phẩm này khỏi trang bán hàng?')">
                                            <i class="fas fa-eye-slash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Không tìm thấy sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
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
                            <option value="hidden">Ẩn (Xóa mềm)</option>
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