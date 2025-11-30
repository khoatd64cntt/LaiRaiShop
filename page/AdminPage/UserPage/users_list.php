<?php
// File: page/AdminPage/UserPage/users_list.php
require_once '../Layout/header.php';
$current_aid = $_SESSION['aid'];
$message = '';

// --- XỬ LÝ: ĐỔI QUYỀN (UPDATE ROLE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $aid = $conn->real_escape_string($_POST['aid']);
    $role = $conn->real_escape_string($_POST['role']);
    
    if ($aid == $current_aid) {
        $message = "<div class='alert alert-warning'>Bạn không thể tự đổi quyền của chính mình.</div>";
    } else {
        if($conn->query("UPDATE acc SET role = '$role' WHERE aid = '$aid'")){
            $message = "<div class='alert alert-success'>Đã đổi quyền User #$aid thành $role.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
}

// --- XỬ LÝ: XÓA USER (MỚI) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['aid'])) {
    $aid = $conn->real_escape_string($_GET['aid']);
    
    if ($aid == $current_aid) {
        $message = "<div class='alert alert-warning'>Không thể tự xóa tài khoản đang đăng nhập.</div>";
    } else {
        // Trong Database, bảng 'shops', 'orders', 'reviews' đều có ON DELETE CASCADE
        // Nên xóa user ở đây sẽ tự động xóa shop và đơn hàng của họ (hoặc theo cấu trúc DB bạn gửi)
        if ($conn->query("DELETE FROM acc WHERE aid = '$aid'")) {
            $message = "<div class='alert alert-success'>Đã xóa tài khoản #$aid và dữ liệu liên quan.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi xóa: " . $conn->error . "</div>";
        }
    }
}

// --- TRUY VẤN ---
$sql = "SELECT * FROM acc ORDER BY aid ASC";
$result = $conn->query($sql);
?>

<h1 class="h3 mb-4 text-gray-800">Quản Lý Người Dùng</h1>
<?= $message ?>

<div class="card shadow mb-4">
    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách tài khoản</h6></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Họ Tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result) while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['aid'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['afname'] . ' ' . $row['alname']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <?php 
                                $badges = ['admin'=>'danger', 'seller'=>'success', 'user'=>'info'];
                                $r = $row['role'];
                            ?>
                            <span class="badge badge-<?= $badges[$r] ?>"><?= ucfirst($r) ?></span>
                        </td>
                        <td>
                            <?php if($row['aid'] != $current_aid): ?>
                                <button class="btn btn-sm btn-primary" 
                                    data-toggle="modal" data-target="#modalUser"
                                    onclick="editUser('<?= $row['aid'] ?>', '<?= htmlspecialchars($row['username']) ?>', '<?= $row['role'] ?>')">
                                    <i class="fas fa-user-cog"></i>
                                </button>
                                
                                <a href="?action=delete&aid=<?= $row['aid'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('CẢNH BÁO: Xóa user này sẽ xóa cả Shop và Đơn hàng của họ (nếu có). Tiếp tục?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">(Tôi)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Phân quyền User: <span id="m_u_name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="aid" id="m_aid">
                    <div class="form-group">
                        <label>Chọn vai trò mới:</label>
                        <select name="role" id="m_role" class="form-control">
                            <option value="user">Người dùng (User)</option>
                            <option value="seller">Người bán (Seller)</option>
                            <option value="admin">Quản trị viên (Admin)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_role" class="btn btn-primary">Lưu thay đổi</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(aid, username, role) {
    document.getElementById('m_aid').value = aid;
    document.getElementById('m_u_name').innerText = username;
    document.getElementById('m_role').value = role;
}
</script>

<?php require_once '../Layout/footer.php'; ?>