<?php
// seller/products.php
require_once 'seller_session.php';

$sid = $_SESSION['shop_id'];
$sql = "SELECT * FROM products WHERE sid = $sid ORDER BY pid DESC";
$result = $conn->query($sql);
?>
<div class="col-md-10 p-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>Sản phẩm của tôi</h3>
        <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm mới</a>
    </div>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Hình ảnh</th>
                <th>Tên</th>
                <th>Giá</th>
                <th>Kho</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['pid']; ?></td>
                <td><img src="<?php echo $row['main_image']; ?>" width="50"></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($row['price']); ?>đ</td>
                <td><?php echo $row['stock']; ?></td>
                <td>
                    <a href="edit_product.php?id=<?php echo $row['pid']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                    <a href="delete_product.php?id=<?php echo $row['pid']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa thật không?');">Xóa</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>