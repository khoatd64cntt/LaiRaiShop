<?php
// BƯỚC 1: NHÚNG HEADER & KẾT NỐI CSDL
require_once '../Layout/header.php';

// BƯỚC 2: XỬ LÝ CRUD CHO DANH MỤC
$message = '';

// Hàm lấy tất cả danh mục (có kèm tên danh mục cha)
// Sử dụng tên cột 'name' thay vì 'cname'
function getAllCategories($conn)
{
    $sql = "SELECT c1.cid, c1.name, c1.parent_id, c2.name AS parent_name
            FROM categories c1
            LEFT JOIN categories c2 ON c1.parent_id = c2.cid
            ORDER BY c1.parent_id ASC, c1.cid ASC";
    $result = $conn->query($sql);
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

// Xử lý THÊM/SỬA danh mục
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['add_category']) || isset($_POST['edit_category']))) {
    // Sửa lấy POST['name']
    $name = $conn->real_escape_string($_POST['name']);
    $parent_id = !empty($_POST['parent_id']) ? "'" . $conn->real_escape_string($_POST['parent_id']) . "'" : 'NULL';

    if (isset($_POST['add_category'])) {
        // Insert vào cột 'name'
        $sql = "INSERT INTO categories (name, parent_id) VALUES ('$name', $parent_id)";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='alert alert-success'>Thêm danh mục **$name** thành công.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi thêm danh mục: " . $conn->error . "</div>";
        }
    } elseif (isset($_POST['edit_category'])) {
        $cid = $conn->real_escape_string($_POST['cid']);
        // Update cột 'name'
        $sql = "UPDATE categories SET name = '$name', parent_id = $parent_id WHERE cid = '$cid'";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='alert alert-success'>Cập nhật danh mục ID **$cid** thành công.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi cập nhật: " . $conn->error . "</div>";
        }
    }
}

// Xử lý XÓA danh mục
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['cid'])) {
    $cid_to_delete = $conn->real_escape_string($_GET['cid']);

    // 1. Kiểm tra Sản phẩm liên quan
    $check_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE cid = '$cid_to_delete'");
    $product_count = ($check_products) ? $check_products->fetch_assoc()['count'] : 0;

    // 2. Kiểm tra Danh mục con
    $check_children = $conn->query("SELECT COUNT(*) as count FROM categories WHERE parent_id = '$cid_to_delete'");
    $child_count = ($check_children) ? $check_children->fetch_assoc()['count'] : 0;

    if ($product_count > 0) {
        $message = "<div class='alert alert-danger'>Không thể xóa. Danh mục này đang chứa **$product_count** sản phẩm.</div>";
    } elseif ($child_count > 0) {
        $message = "<div class='alert alert-danger'>Không thể xóa. Danh mục này đang chứa **$child_count** danh mục con. Vui lòng xóa danh mục con trước.</div>";
    } else {
        $sql_delete = "DELETE FROM categories WHERE cid = '$cid_to_delete'";
        if ($conn->query($sql_delete) === TRUE) {
            $message = "<div class='alert alert-success'>Xóa danh mục ID **$cid_to_delete** thành công.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi xóa danh mục: " . $conn->error . "</div>";
        }
    }
}

$all_categories = getAllCategories($conn);
// Lấy lại danh sách danh mục cha cho dropdown (Sửa 'cname' -> 'name')
$parent_categories = $conn->query("SELECT cid, name FROM categories WHERE parent_id IS NULL ORDER BY name ASC");
?>

<h1 class="mt-4">Quản Lý Danh Mục Sản Phẩm</h1>
<?php echo $message; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-plus mr-1"></i> Thêm Danh Mục Mới</h6>
            </div>
            <div class="card-body">
                <form id="categoryForm" method="POST">
                    <input type="hidden" name="cid" id="cid">
                    <div class="form-group">
                        <label for="name">Tên Danh Mục:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="parent_id">Danh Mục Cha (Nếu là Danh mục con):</label>
                        <select class="form-control" id="parent_id" name="parent_id">
                            <option value="">-- Chọn Danh mục Cha --</option>
                            <?php
                            if ($parent_categories) {
                                while ($p_row = $parent_categories->fetch_assoc()):
                            ?>
                                    <option value="<?php echo $p_row['cid']; ?>"><?php echo htmlspecialchars($p_row['name']); ?></option>
                            <?php
                                endwhile;
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary" id="submitButton"><i class="fas fa-save"></i> Thêm Danh Mục</button>
                    <button type="button" class="btn btn-secondary d-none" id="cancelEditBtn"><i class="fas fa-times"></i> Hủy Sửa</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách Danh mục (<?php echo count($all_categories); ?>)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên Danh Mục</th>
                                <th>Danh Mục Cha</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_categories as $cat): ?>
                                <tr>
                                    <td><?php echo $cat['cid']; ?></td>
                                    <td>
                                        <?php if ($cat['parent_id']): ?>
                                            &nbsp;&nbsp;&nbsp;&nbsp; &raquo; <?php endif; ?>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </td>
                                    <td>
                                        <?php echo $cat['parent_name'] ? htmlspecialchars($cat['parent_name']) : '<span class="text-success">*** (Chính) ***</span>'; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning edit-cat-btn"
                                            data-cid="<?php echo $cat['cid']; ?>"
                                            data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                            data-parent-id="<?php echo $cat['parent_id'] ?? ''; ?>">
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>
                                        <a href="categories_list.php?action=delete&cid=<?php echo $cat['cid']; ?>"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục ID <?php echo $cat['cid']; ?>?')"
                                            class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript để chuyển đổi form từ Thêm sang Sửa
    $('.edit-cat-btn').on('click', function() {
        var cid = $(this).data('cid');
        var name = $(this).data('name'); // Lấy data-name
        var parentId = $(this).data('parent-id');

        // Đổ dữ liệu vào form (id='name')
        $('#cid').val(cid);
        $('#name').val(name);
        $('#parent_id').val(parentId);

        // Thay đổi nút Submit
        $('#submitButton').text('Lưu Thay Đổi').attr('name', 'edit_category').removeClass('btn-primary').addClass('btn-warning');

        // Hiển thị nút Hủy Sửa
        $('#cancelEditBtn').removeClass('d-none');
    });

    // Xử lý Hủy Sửa
    $('#cancelEditBtn').on('click', function() {
        // Reset form và nút
        $('#categoryForm')[0].reset();
        $('#cid').val('');
        $('#submitButton').text('Thêm Danh Mục').attr('name', 'add_category').removeClass('btn-warning').addClass('btn-primary');
        $(this).addClass('d-none');
    });
</script>

<?php
include '../Layout/footer.php';
?>