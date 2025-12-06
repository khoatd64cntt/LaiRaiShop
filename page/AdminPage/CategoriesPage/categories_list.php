<?php
// File: page/AdminPage/CategoriesPage/categories_list.php
require_once '../Layout/header.php';

$message = '';

// --- HÀM XỬ LÝ UPLOAD ẢNH ---
function uploadCatImage($file)
{
    if (empty($file['name'])) return null;

    // Định nghĩa thư mục lưu: /images/categories/
    $target_dir = ROOT_PATH . '/images/categories/';

    // Tạo thư mục nếu chưa có
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Trả về đường dẫn tương đối để lưu vào DB
        return '/images/categories/' . $filename;
    }
    return null;
}

// --- XỬ LÝ: THÊM MỚI ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $parent_id = !empty($_POST['parent_id']) ? "'" . $conn->real_escape_string($_POST['parent_id']) . "'" : 'NULL';

    // 1. Ưu tiên file upload trước
    $image_path = uploadCatImage($_FILES['cat_image']);

    // 2. Nếu không có file upload, kiểm tra URL nhập vào
    if (!$image_path && !empty($_POST['cat_image_url'])) {
        $image_path = $conn->real_escape_string(trim($_POST['cat_image_url']));
    }

    $img_sql = $image_path ? "'$image_path'" : 'NULL';

    $sql = "INSERT INTO categories (name, parent_id, image) VALUES ('$name', $parent_id, $img_sql)";
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

    if ($parent_id !== 'NULL' && str_replace("'", "", $parent_id) == $cid) {
        $message = "<div class='alert alert-danger'>Lỗi: Danh mục không thể là cha của chính nó.</div>";
    } else {
        // Xử lý ảnh mới
        $upload_sql = "";

        // 1. Check file upload
        $new_image = uploadCatImage($_FILES['cat_image']);

        // 2. Nếu không upload file, check URL input
        // Lưu ý: Nếu người dùng muốn đổi từ ảnh file sang link, họ chỉ cần nhập link mới
        if (!$new_image && !empty($_POST['cat_image_url'])) {
            $new_image = $conn->real_escape_string(trim($_POST['cat_image_url']));
        }

        // Nếu có ảnh mới (từ 1 trong 2 nguồn), cập nhật SQL
        if ($new_image) {
            $upload_sql = ", image = '$new_image'";
        }

        $sql = "UPDATE categories SET name = '$name', parent_id = $parent_id $upload_sql WHERE cid = '$cid'";
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
$sql = "SELECT c1.*, c2.name AS parent_name 
        FROM categories c1 
        LEFT JOIN categories c2 ON c1.parent_id = c2.cid 
        ORDER BY c1.cid DESC";
$result = $conn->query($sql);

$parents = $conn->query("SELECT cid, name FROM categories WHERE parent_id IS NULL");
?>

<h1 class="h3 mb-4 text-gray-800">Quản Lý Danh Mục</h1>
<?= $message ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6>
            </div>
            <div class="card-body">
                <form method="POST" id="catForm" enctype="multipart/form-data">
                    <input type="hidden" name="cid" id="cid">

                    <div class="form-group">
                        <label>Tên Danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Danh mục cha</label>
                        <select name="parent_id" id="parent_id" class="form-control">
                            <option value="">-- Là danh mục gốc --</option>
                            <?php if ($parents) while ($p = $parents->fetch_assoc()): ?>
                                <option value="<?= $p['cid'] ?>"><?= $p['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Hình ảnh / Icon</label>
                        <div class="mb-2 text-center">
                            <img id="imgPreview" src="" style="width: 100px; height: 100px; object-fit: contain; display: none; border: 1px solid #ddd; border-radius: 4px; margin: 0 auto;">
                        </div>

                        <div class="custom-file mb-2">
                            <input type="file" name="cat_image" id="cat_image" class="custom-file-input" accept="image/*" onchange="previewImage(this)">
                            <label class="custom-file-label" for="cat_image">Chọn file ảnh...</label>
                        </div>

                        <div class="text-center text-muted mb-2">- HOẶC -</div>

                        <input type="text" name="cat_image_url" id="cat_image_url" class="form-control" placeholder="Dán đường dẫn ảnh (URL) vào đây..." oninput="previewUrl(this.value)">
                        <small class="form-text text-muted">Ưu tiên File upload nếu chọn cả hai.</small>
                    </div>

                    <button type="submit" name="add_category" id="btnSubmit" class="btn btn-primary btn-block">Thêm Mới</button>
                    <button type="button" id="btnCancel" class="btn btn-secondary btn-block d-none" onclick="resetForm()">Hủy</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách hiện có</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Hình</th>
                                <th>Tên Danh Mục</th>
                                <th>Danh Mục Cha</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result) while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['cid'] ?></td>
                                    <td class="text-center">
                                        <?php
                                        // Xử lý hiển thị ảnh (Local hoặc URL online)
                                        $imgSrc = '';
                                        if (!empty($row['image'])) {
                                            if (strpos($row['image'], 'http') === 0) {
                                                $imgSrc = $row['image']; // Link online
                                            } else {
                                                $imgSrc = BASE_URL . $row['image']; // Link local
                                            }
                                        }
                                        ?>
                                        <?php if ($imgSrc): ?>
                                            <img src="<?= $imgSrc ?>" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px; border: 1px solid #eee;">
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="fas fa-image"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= $row['parent_name'] ?? '<span class="badge badge-light">Gốc</span>' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"
                                            onclick="editCat(<?= $row['cid'] ?>, '<?= htmlspecialchars($row['name']) ?>', '<?= $row['parent_id'] ?>', '<?= htmlspecialchars($row['image']) ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?action=delete&cid=<?= $row['cid'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa danh mục này?')">
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
    </div>
</div>

<script>
    // Preview khi chọn FILE
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imgPreview').src = e.target.result;
                document.getElementById('imgPreview').style.display = 'block';
                // Xóa giá trị ô URL để tránh nhầm lẫn
                document.getElementById('cat_image_url').value = '';
            }
            reader.readAsDataURL(input.files[0]);

            // Cập nhật text cho input file custom của Bootstrap
            var fileName = input.files[0].name;
            var label = input.nextElementSibling;
            label.innerText = fileName;
        }
    }

    // Preview khi nhập URL
    function previewUrl(url) {
        var imgPre = document.getElementById('imgPreview');
        if (url.trim() !== '') {
            imgPre.src = url;
            imgPre.style.display = 'block';
            // Reset input file nếu đang nhập URL
            document.getElementById('cat_image').value = '';
            document.querySelector('.custom-file-label').innerText = 'Chọn file ảnh...';
        } else {
            imgPre.style.display = 'none';
        }
    }

    function editCat(cid, name, parent_id, image) {
        document.getElementById('cid').value = cid;
        document.getElementById('name').value = name;
        document.getElementById('parent_id').value = parent_id;

        // Xử lý hiển thị ảnh cũ
        var imgPre = document.getElementById('imgPreview');
        var urlInput = document.getElementById('cat_image_url');

        if (image) {
            var displaySrc = image;
            // Nếu không phải http (là file local), thêm BASE_URL để hiển thị
            if (image.indexOf('http') !== 0) {
                displaySrc = '<?php echo BASE_URL; ?>' + (image.startsWith('/') ? image : '/' + image);
            }

            imgPre.src = displaySrc;
            imgPre.style.display = 'block';

            // Điền giá trị vào ô URL để admin biết đường dẫn hiện tại (hoặc sửa đổi)
            urlInput.value = image;
        } else {
            imgPre.style.display = 'none';
            imgPre.src = '';
            urlInput.value = '';
        }

        document.getElementById('btnSubmit').innerText = "Cập nhật";
        document.getElementById('btnSubmit').name = "edit_category";
        document.getElementById('btnSubmit').classList.replace('btn-primary', 'btn-warning');
        document.getElementById('btnCancel').classList.remove('d-none');
    }

    function resetForm() {
        document.getElementById('catForm').reset();
        document.getElementById('cid').value = '';
        document.getElementById('imgPreview').style.display = 'none';
        document.querySelector('.custom-file-label').innerText = 'Chọn file ảnh...';

        document.getElementById('btnSubmit').innerText = "Thêm Mới";
        document.getElementById('btnSubmit').name = "add_category";
        document.getElementById('btnSubmit').classList.replace('btn-warning', 'btn-primary');
        document.getElementById('btnCancel').classList.add('d-none');
    }
</script>

<?php require_once '../Layout/footer.php'; ?>