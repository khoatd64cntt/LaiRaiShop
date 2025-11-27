<?php
require_once '../../db/db.php';

$keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : '';

// Tìm kiếm sản phẩm theo từ khóa
$sql = "SELECT * FROM products WHERE name LIKE '%$keyword%' OR description LIKE '%$keyword%' LIMIT 20";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm: <?php echo htmlspecialchars($keyword); ?> | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/homepage.css?v=4">
</head>
<body>
    <div class="container mt-5">
        <h2>Kết quả tìm kiếm cho: <strong><?php echo htmlspecialchars($keyword); ?></strong></h2>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row mt-4">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text">Giá: <?php echo number_format($row['price'], 0, ',', '.'); ?> ₫</p>
                                <a href="#" class="btn btn-sm btn-primary">Chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                Không tìm thấy sản phẩm nào phù hợp với từ khóa "<strong><?php echo htmlspecialchars($keyword); ?></strong>"
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="homepage.php" class="btn btn-secondary">← Quay lại trang chủ</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
