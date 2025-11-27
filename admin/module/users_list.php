<?php
// BƯỚC 1: NHÚNG HEADER
require_once '../include/header.php';

// Lấy ID admin hiện tại từ Session để ngăn tự đổi quyền chính mình
// (Session đã được set trong login.php: $_SESSION['aid'])
$current_admin_aid = $_SESSION['aid'] ?? 0;

// BƯỚC 2: XỬ LÝ CẬP NHẬT VAI TRÒ
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $user_id = $conn->real_escape_string($_POST['user_id']);
    $new_role = $conn->real_escape_string($_POST['new_role']);

    if ($user_id == $current_admin_aid) {
        $message = "<div class='alert alert-danger'>Bạn không thể tự thay đổi vai trò của chính mình.</div>";
    } else {
        // Chỉ cho phép cập nhật nếu vai trò mới là hợp lệ
        $valid_roles = ['admin', 'seller', 'user'];
        if (in_array($new_role, $valid_roles)) {
            $sql_update = "UPDATE acc SET role = '$new_role' WHERE aid = '$user_id'";
            if ($conn->query($sql_update) === TRUE) {
                $message = "<div class='alert alert-success'>Cập nhật vai trò tài khoản ID **$user_id** thành **$new_role** thành công.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Lỗi cập nhật: " . $conn->error . "</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Vai trò không hợp lệ.</div>";
        }
    }
}

// BƯỚC 3: XỬ LÝ LỌC VÀ TRUY VẤN
$filter_role = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : '';

$where_clause = "WHERE 1=1";
if ($filter_role && $filter_role !== 'all') {
    $where_clause .= " AND role = '$filter_role'";
}

$sql = "SELECT aid, afname, alname, email, phone, username, role FROM acc $where_clause ORDER BY aid ASC";
$result = $conn->query($sql);
?>

<h1 class="mt-4">Quản Lý Tài Khoản Người Dùng</h1>
<?php echo $message; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Bộ Lọc Theo Vai Trò</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <label class="mr-2">Vai trò:</label>
            <select name="role" class="form-control mr-3">
                <option value="all" <?php if ($filter_role === 'all' || $filter_role === '') echo 'selected'; ?>>Tất cả</option>
                <option value="admin" <?php if ($filter_role === 'admin') echo 'selected'; ?>>Quản trị viên</option>
                <option value="seller" <?php if ($filter_role === 'seller') echo 'selected'; ?>>Người bán</option>
                <option value="user" <?php if ($filter_role === 'user') echo 'selected'; ?>>Người mua</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách Tài khoản (<?php echo ($result) ? $result->num_rows : 0; ?>)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Vai trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['aid']; ?></td>
                                <td><?php echo htmlspecialchars($row['afname'] . ' ' . $row['alname']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <?php
                                    $role_class = [
                                        'admin' => 'badge-danger',
                                        'seller' => 'badge-success',
                                        'user' => 'badge-primary'
                                    ];
                                    echo '<span class="badge ' . ($role_class[$row['role']] ?? 'badge-secondary') . '">' . ucfirst($row['role']) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($row['aid'] != $current_admin_aid): ?>
                                        <button type="button" class="btn btn-sm btn-warning update-role-btn"
                                            data-toggle="modal" data-target="#updateRoleModal"
                                            data-id="<?php echo $row['aid']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['afname'] . ' ' . $row['alname']); ?>"
                                            data-current-role="<?php echo $row['role']; ?>">
                                            <i class="fas fa-user-tag"></i> Đổi Vai trò
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Chức năng demo"><i class="fas fa-ban"></i> Khóa</button>
                                    <?php else: ?>
                                        <span class="text-muted font-italic">Tài khoản của bạn</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Không tìm thấy tài khoản nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="updateRoleModal" tabindex="-1" role="dialog" aria-labelledby="updateRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateRoleModalLabel">Cập nhật Vai trò cho: <span id="userNameDisplay" class="text-primary font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="modalUserId">
                    <div class="form-group">
                        <label for="new_role">Vai trò Mới:</label>
                        <select name="new_role" id="new_role" class="form-control">
                            <option value="admin">Quản trị viên (Admin)</option>
                            <option value="seller">Người bán (Seller)</option>
                            <option value="user">Người mua (User)</option>
                        </select>
                    </div>
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle"></i> Lưu ý: Thay đổi vai trò sẽ ảnh hưởng đến quyền truy cập của người dùng này ngay lập tức.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" name="update_role" class="btn btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript để truyền dữ liệu User vào Modal
    $('.update-role-btn').on('click', function() {
        var userId = $(this).data('id');
        var userName = $(this).data('name');
        var currentRole = $(this).data('current-role');

        $('#userNameDisplay').text(userName);
        $('#modalUserId').val(userId);
        $('#new_role').val(currentRole); // Đặt vai trò hiện tại làm mặc định
    });
</script>

<?php
include '../include/footer.php';
?>