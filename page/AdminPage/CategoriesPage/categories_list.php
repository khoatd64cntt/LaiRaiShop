<?php
// File: page/AdminPage/CategoriesPage/categories_list.php
require_once '../Layout/header.php';

$message = '';

// --- XỬ LÝ: THÊM MỚI ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $parent_id = !empty($_POST['parent_id']) ? "'" . $conn->real_escape_string($_POST['parent_id']) . "'" : 'NULL';

    $sql = "INSERT INTO categories (name, parent_id) VALUES ('$name', $parent_id)";
    if ($conn->query($sql) === TRUE) {
        $message = "<div class='alert alert-success'>Thêm danh mục thành công!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// --- XỬ LÝ: CẬP NHẬT (SỬA) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_category'])) {
    $cid = $conn->real_escape_string($_POST['cid']);
    $name = $conn->real_escape_string($_POST['name']);
    $parent_id = !empty($_POST['parent_id']) ? "'" . $conn->real_escape_string($_POST['parent_id']) . "'" : 'NULL';

    // Tránh việc danh mục tự làm cha của chính nó
    if ($parent_id !== 'NULL' && str_replace("'", "", $parent_id) == $cid) {
        $message = "<div class='alert alert-danger'>Lỗi: Danh mục không thể là cha của chính nó.</div>";
    } else {
        $sql = "UPDATE categories SET name = '$name', parent_id = $parent_id WHERE cid = '$cid'";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='alert alert-success'>Cập nhật thành công!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
}

// --- XỬ LÝ: XÓA ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['cid'])) {
    $cid = $conn->real_escape_string($_GET['cid']);
    
    // Kiểm tra ràng buộc trước khi xóa
    $has_products = $conn->query("SELECT 1 FROM products WHERE cid = '$cid'")->num_rows > 0;
    $has_children = $conn->query("SELECT 1 FROM categories WHERE parent_id = '$cid'")->num_rows > 0;

    if ($has_products) {
        $message = "<div class='alert alert-warning'>Không thể xóa: Danh mục đang chứa sản phẩm.</div>";
    } elseif ($has_children) {
        $message = "<div class='alert alert-warning'>Không thể xóa: Danh mục đang chứa danh mục con.</div>";
    } else {
        if ($conn->query("DELETE FROM categories WHERE cid = '$cid'")) {
            $message = "<div class='alert alert-success'>Đã xóa danh mục.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi SQL: " . $conn->error . "</div>";
        }
    }
}

// --- TRUY VẤN HIỂN THỊ ---
$sql = "SELECT c1.cid, c1.name, c1.parent_id, c2.name AS parent_name 
        FROM categories c1 
        LEFT JOIN categories c2 ON c1.parent_id = c2.cid 
        ORDER BY c1.cid DESC";
$result = $conn->query($sql);

// Lấy danh sách cha để đổ vào Select option
$parents = $conn->query("SELECT cid, name FROM categories WHERE parent_id IS NULL");
?>

<h1 class="h3 mb-4 text-gray-800">Quản Lý Danh Mục</h1>
<?= $message ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6></div>
            <div class="card-body">
                <form method="POST" id="catForm">
                    <input type="hidden" name="cid" id="cid">
                    <div class="form-group">
                        <label>Tên Danh mục</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Danh mục cha</label>
                        <select name="parent_id" id="parent_id" class="form-control">
                            <option value="">-- Là danh mục gốc --</option>
                            <?php if($parents) while($p = $parents->fetch_assoc()): ?>
                                <option value="<?= $p['cid'] ?>"><?= $p['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_category" id="btnSubmit" class="btn btn-primary btn-block">Thêm Mới</button>
                    <button type="button" id="btnCancel" class="btn btn-secondary btn-block d-none" onclick="resetForm()">Hủy</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách hiện có</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th>ID</th><th>Tên</th><th>Cha</th><th>Hành động</th></tr></thead>
                        <tbody>
                            <?php if($result) while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['cid'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= $row['parent_name'] ?? '<span class="text-muted">Gốc</span>' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="editCat(<?= $row['cid'] ?>, '<?= $row['name'] ?>', '<?= $row['parent_id'] ?>')">Sửa</button>
                                    <a href="?action=delete&cid=<?= $row['cid'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa danh mục này?')">Xóa</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editCat(cid, name, parent_id) {
    document.getElementById('cid').value = cid;
    document.getElementById('name').value = name;
    document.getElementById('parent_id').value = parent_id;
    
    document.getElementById('btnSubmit').innerText = "Cập nhật";
    document.getElementById('btnSubmit').name = "edit_category";
    document.getElementById('btnSubmit').classList.replace('btn-primary', 'btn-warning');
    document.getElementById('btnCancel').classList.remove('d-none');
}

function resetForm() {
    document.getElementById('catForm').reset();
    document.getElementById('cid').value = '';
    
    document.getElementById('btnSubmit').innerText = "Thêm Mới";
    document.getElementById('btnSubmit').name = "add_category";
    document.getElementById('btnSubmit').classList.replace('btn-warning', 'btn-primary');
    document.getElementById('btnCancel').classList.add('d-none');
}
</script>

<?php require_once '../Layout/footer.php'; ?>