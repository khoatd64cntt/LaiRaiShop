<?php
// File: page/AdminPage/UserPage/users_list.php
require_once '../Layout/header.php';
$current_aid = $_SESSION['aid'];
$message = '';

// --- [MỚI] 1. XỬ LÝ DUYỆT ĐƠN ĐĂNG KÝ SELLER ---
if (isset($_POST['approve_seller'])) {
    $req_aid = $conn->real_escape_string($_POST['req_aid']);
    // Kích hoạt shop & Nâng quyền
    $conn->query("UPDATE shops SET status = 'active' WHERE aid = '$req_aid'");
    $conn->query("UPDATE acc SET role = 'seller' WHERE aid = '$req_aid'");
    $message = "<div class='alert alert-success'>Đã duyệt User #$req_aid lên Seller thành công!</div>";
}

if (isset($_POST['reject_seller'])) {
    $req_aid = $conn->real_escape_string($_POST['req_aid']);
    // Xóa đơn pending
    $conn->query("DELETE FROM shops WHERE aid = '$req_aid' AND status = 'pending'");
    $message = "<div class='alert alert-warning'>Đã từ chối yêu cầu của User #$req_aid.</div>";
}

// --- 2. XỬ LÝ ĐỔI QUYỀN (CÓ CẬP NHẬT LOGIC SHOP) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $aid = $conn->real_escape_string($_POST['aid']);
    $role = $conn->real_escape_string($_POST['role']);

    if ($aid == $current_aid) {
        $message = "<div class='alert alert-warning'>Bạn không thể tự đổi quyền của chính mình.</div>";
    } else {
        if ($conn->query("UPDATE acc SET role = '$role' WHERE aid = '$aid'")) {
            // [LOGIC MỚI] Đồng bộ trạng thái Shop khi đổi quyền
            if ($role == 'user') {
                $conn->query("UPDATE shops SET status = 'banned' WHERE aid = '$aid'"); // Hạ xuống user -> Khóa shop
            } elseif ($role == 'seller') {
                $conn->query("UPDATE shops SET status = 'active' WHERE aid = '$aid'"); // Lên seller -> Mở shop
            }
            $message = "<div class='alert alert-success'>Đã đổi quyền User #$aid thành $role.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
}

// --- 3. XỬ LÝ XÓA USER (GIỮ NGUYÊN) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['aid'])) {
    $aid = $conn->real_escape_string($_GET['aid']);
    if ($aid == $current_aid) {
        $message = "<div class='alert alert-warning'>Không thể tự xóa tài khoản đang đăng nhập.</div>";
    } else {
        if ($conn->query("DELETE FROM acc WHERE aid = '$aid'")) {
            $message = "<div class='alert alert-success'>Đã xóa tài khoản #$aid và dữ liệu liên quan.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi xóa: " . $conn->error . "</div>";
        }
    }
}

// --- 4. TRUY VẤN DỮ LIỆU ---
// Lấy danh sách chờ duyệt
$sql_pending = "SELECT s.sid, s.shop_name, s.description, a.aid, a.username, a.email, a.phone 
                FROM shops s JOIN acc a ON s.aid = a.aid 
                WHERE s.status = 'pending'";
$res_pending = $conn->query($sql_pending);

// Lấy danh sách tất cả user
$sql = "SELECT * FROM acc ORDER BY aid ASC";
$result = $conn->query($sql);
?>

<h1 class="h3 mb-4 text-gray-800">Quản Lý Người Dùng</h1>
<?= $message ?>

<?php if ($res_pending && $res_pending->num_rows > 0): ?>
    <div class="card shadow mb-4 border-left-warning">
        <div class="card-header py-3 bg-warning text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-bell mr-2"></i>Có <?= $res_pending->num_rows ?> yêu cầu đăng ký Seller mới</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Tài khoản</th>
                            <th>Shop Dự Kiến</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res_pending->fetch_assoc()): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($row['username']) ?></b><br><small><?= htmlspecialchars($row['email']) ?></small></td>
                                <td class="text-primary font-weight-bold"><?= htmlspecialchars($row['shop_name']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="req_aid" value="<?= $row['aid'] ?>">
                                        <button type="submit" name="approve_seller" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Duyệt</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="req_aid" value="<?= $row['aid'] ?>">
                                        <button type="submit" name="reject_seller" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Hủy</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách tài khoản hệ thống</h6>
    </div>
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
                    <?php if ($result) while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['aid'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['afname'] . ' ' . $row['alname']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <?php
                                $badges = ['admin' => 'danger', 'seller' => 'success', 'user' => 'info'];
                                $r = $row['role'];
                                ?>
                                <span class="badge badge-<?= $badges[$r] ?>"><?= ucfirst($r) ?></span>
                            </td>
                            <td>
                                <?php if ($row['aid'] != $current_aid): ?>
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