<?php
// BƯỚC 1: NHÚNG HEADER & KẾT NỐI CSDL (Dùng require_once để an toàn)
require_once '../Layout/header.php';

// BƯỚC 2: XỬ LÝ CẬP NHẬT TRẠNG THÁI KIỂM DUYỆT
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product_status'])) {
    $pid = $conn->real_escape_string($_POST['pid']);
    $new_status = $conn->real_escape_string($_POST['new_status']);

    $valid_statuses = ['pending', 'approved', 'rejected'];
    if (in_array($new_status, $valid_statuses)) {
        $sql_update = "UPDATE products SET status = '$new_status' WHERE pid = '$pid'";
        if ($conn->query($sql_update) === TRUE) {
            $message = "<div class='alert alert-success'>Cập nhật trạng thái sản phẩm ID **$pid** thành **$new_status** thành công.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Lỗi cập nhật: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Trạng thái kiểm duyệt không hợp lệ.</div>";
    }
}

// BƯỚC 3: XỬ LÝ LỌC VÀ TRUY VẤN
$filter_status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : 'all';

$where_clause = "WHERE 1=1";
if ($filter_status && $filter_status !== 'all') {
    $where_clause .= " AND p.status = '$filter_status'";
}

// QUERY CHUẨN: Sử dụng tên cột từ file SQL (name, shop_name)
// p.name (Tên SP), s.shop_name (Tên Shop), c.name (Tên Danh mục)
$sql = "SELECT 
            p.*, 
            s.shop_name, 
            c.name AS category_name
        FROM products p
        LEFT JOIN shops s ON p.sid = s.sid
        LEFT JOIN categories c ON p.cid = c.cid
        $where_clause
        ORDER BY p.pid DESC";

// Chạy query an toàn
$result = $conn->query($sql);
// Nếu query lỗi (do sai tên cột hay gì đó), gán result = false để không crash trang web
if (!$result) {
    $message .= "<div class='alert alert-warning'>Lưu ý: Không thể tải danh sách sản phẩm. Lỗi SQL: " . $conn->error . "</div>";
}
?>

<h1 class="mt-4">Quản Lý & Kiểm Duyệt Sản Phẩm</h1>
<?php echo $message; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Bộ Lọc Theo Trạng Thái Kiểm Duyệt</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <label class="mr-2">Trạng thái:</label>
            <select name="status" class="form-control mr-3">
                <option value="all" <?php if ($filter_status === 'all') echo 'selected'; ?>>Tất cả Sản phẩm</option>
                <option value="pending" <?php if ($filter_status === 'pending') echo 'selected'; ?>>Chờ Kiểm duyệt</option>
                <option value="approved" <?php if ($filter_status === 'approved') echo 'selected'; ?>>Đã Duyệt</option>
                <option value="rejected" <?php if ($filter_status === 'rejected') echo 'selected'; ?>>Bị Từ chối</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách Sản phẩm (<?php echo ($result) ? $result->num_rows : 0; ?>)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Danh mục</th>
                        <th>Shop</th>
                        <th>Giá (VND)</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="align-middle"><?php echo $row['pid']; ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($row['category_name'] ?? 'Chưa phân loại'); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($row['shop_name'] ?? 'N/A'); ?></td>
                                <td class="align-middle"><?php echo formatCurrency($row['price']); ?></td>
                                <td class="align-middle"><?php echo number_format($row['stock']); ?></td>
                                <td class="align-middle text-center">
                                    <?php
                                    $status_class = [
                                        'pending' => 'badge-warning',
                                        'approved' => 'badge-success',
                                        'rejected' => 'badge-danger'
                                    ];
                                    // Thêm py-2 để badge to hơn xíu cho đẹp
                                    echo '<span class="badge ' . ($status_class[$row['status']] ?? 'badge-secondary') . ' py-1 px-2">' . ucfirst($row['status']) . '</span>';
                                    ?>
                                </td>

                                <td class="align-middle" style="white-space: nowrap; width: 1%;">
                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-sm btn-info view-product-btn mr-1"
                                            title="Xem chi tiết"
                                            data-toggle="modal" data-target="#productDetailModal"
                                            data-id="<?php echo $row['pid']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                            data-shop="<?php echo htmlspecialchars($row['shop_name'] ?? 'N/A'); ?>"
                                            data-price="<?php echo $row['price']; ?>"
                                            data-stock="<?php echo $row['stock']; ?>"
                                            data-status="<?php echo $row['status']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <?php if ($row['status'] !== 'approved'): ?>
                                            <button type="button" class="btn btn-sm btn-success quick-approve-btn mr-1"
                                                title="Duyệt nhanh"
                                                data-id="<?php echo $row['pid']; ?>" data-status="approved">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($row['status'] !== 'rejected'): ?>
                                            <button type="button" class="btn btn-sm btn-danger quick-approve-btn"
                                                title="Từ chối"
                                                data-id="<?php echo $row['pid']; ?>" data-status="rejected">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">Không tìm thấy sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="productDetailModal" tabindex="-1" role="dialog" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailModalLabel">Chi tiết Sản phẩm #<span id="productIdDisplay"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="productModalContent">
                <div class="col-md-4">
                    <img src="../../../images/placeholder.png" class="img-fluid" alt="Ảnh sản phẩm">
                </div>
                <div class="col-md-8">
                    <h6>**Thông tin sản phẩm**</h6>
                    <p><strong>Tên SP:</strong> <span id="modalName"></span></p>
                    <p><strong>Giá:</strong> <span id="modalPrice"></span> | <strong>Tồn kho:</strong> <span id="modalStock"></span></p>
                    <p><strong>Bán bởi:</strong> <span id="modalShop"></span></p>
                    <hr>
                    <p><strong>Mô tả:</strong> <span class="text-muted">Đang cập nhật...</span></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <form method="POST" class="form-inline">
                <input type="hidden" name="pid" id="modalProductPid">
                <label class="mr-2">Cập nhật trạng thái:</label>
                <select name="new_status" id="modalProductStatus" class="form-control mr-2">
                    <option value="pending">Chờ Kiểm duyệt</option>
                    <option value="approved">Đã Duyệt</option>
                    <option value="rejected">Bị Từ chối</option>
                </select>
                <button type="submit" name="update_product_status" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
            </form>
        </div>
    </div>
</div>
</div>

<script>
    // 1. Cập nhật nhanh trạng thái (Duyệt/Từ chối)
    $('.quick-approve-btn').on('click', function(e) {
        if (!confirm('Bạn có chắc chắn muốn thay đổi trạng thái kiểm duyệt sản phẩm này?')) {
            e.preventDefault();
            return;
        }

        var pid = $(this).data('id');
        var status = $(this).data('status');

        // Gửi form POST ẩn để cập nhật trạng thái
        var form = $('<form action="products_list.php" method="post">' +
            '<input type="hidden" name="pid" value="' + pid + '" />' +
            '<input type="hidden" name="new_status" value="' + status + '" />' +
            '<input type="hidden" name="update_product_status" value="1" />' +
            '</form>');
        $('body').append(form);
        form.submit();
    });

    // 2. Đổ dữ liệu vào Modal khi bấm "Xem/Sửa"
    $('.view-product-btn').on('click', function() {
        var pid = $(this).data('id');
        var name = $(this).data('name');
        var shop = $(this).data('shop');
        var price = $(this).data('price');
        var stock = $(this).data('stock');
        var status = $(this).data('status');

        // Điền dữ liệu vào modal
        $('#productIdDisplay').text(pid);
        $('#modalProductPid').val(pid);
        $('#modalName').text(name);
        $('#modalShop').text(shop);
        $('#modalPrice').text(new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price));
        $('#modalStock').text(stock);
        $('#modalProductStatus').val(status);
    });
</script>

<?php
include '../Layout/footer.php';
?>