<?php
require_once '../../../config.php';
require_once 'help_data.php';

// Lấy tham số tìm kiếm từ URL
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$filtered_articles = [];

foreach ($help_data as $item) {
    $match_keyword = true;
    $match_category = true;

    // Kiểm tra từ khóa
    if ($keyword) {
        // Hàm mb_stripos giúp tìm tiếng Việt có dấu tốt hơn
        if (mb_stripos($item['title'], $keyword) === false && mb_stripos($item['content'], $keyword) === false) {
            $match_keyword = false;
        }
    }

    // Kiểm tra danh mục
    if ($category) {
        if ($item['category'] !== $category) {
            $match_category = false;
        }
    }

    // Thỏa 2 điều kiện lọc
    if ($match_keyword && $match_category) {
        $filtered_articles[] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung Tâm Trợ Giúp LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../style/help_center.css">
    <?php include ROOT_PATH . '/includes/head_meta.php'; ?>
</head>

<body>

    <header class="help-header">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="logo"><a href="../homepage.php"><img src="../../../images/logo2.png" alt="LaiRaiShop Logo"></a></div>
                <div class="divider"></div>
                <div class="page-title">Trung Tâm Trợ Giúp</div>
            </div>
            <div class="header-right">
                <a href="../homepage.php" class="simple-back-link">
                    <i class="fas fa-arrow-left"></i> Quay lại LaiRaiShop
                </a>
            </div>
        </div>
    </header>

    <section class="search-banner">
        <div class="container">
            <h1>Xin chào, LaiRaiShop có thể giúp gì cho bạn?</h1>
            <form action="help_center.php" method="GET" class="search-wrapper">
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Nhập từ khóa hoặc nội dung cần tìm...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </section>

    <div class="container main-content">

        <div class="category-grid">
            <a href="help_center.php?category=tai-khoan" class="cat-item"><i class="fas fa-user-circle"></i><span>Tài Khoản & Mục Khác</span></a>
            <a href="help_center.php?category=don-hang" class="cat-item"><i class="fas fa-box-open"></i><span>Đơn Hàng & Vận Chuyển</span></a>
            <a href="help_center.php?category=tra-hang" class="cat-item"><i class="fas fa-undo-alt"></i><span>Trả Hàng & Hoàn Tiền</span></a>
            <a href="help_center.php?category=mua-sam" class="cat-item"><i class="fas fa-shopping-basket"></i><span>Mua Sắm & Nhận Hàng</span></a>
            <a href="help_center.php?category=nguoi-ban" class="cat-item"><i class="fas fa-store"></i><span>Người Bán & Đối Tác</span></a>
            <a href="help_center.php?category=an-toan" class="cat-item"><i class="fas fa-shield-alt"></i><span>An Toàn & Bảo Mật</span></a>
        </div>

        <div class="faq-section">
            <h2 class="section-title">
                <?php
                if ($keyword) echo 'Kết quả tìm kiếm cho: "' . htmlspecialchars($keyword) . '"';
                elseif ($category) echo 'Danh mục đã chọn';
                else echo 'Câu Hỏi Thường Gặp';
                ?>
            </h2>

            <div class="row">
                <div class="col-12">
                    <ul class="faq-list">
                        <?php if (count($filtered_articles) > 0): ?>
                            <?php foreach ($filtered_articles as $row): ?>
                                <li>
                                    <a href="help_detail.php?id=<?php echo $row['id']; ?>">
                                        <i class="far fa-question-circle mr-2"></i> <?php echo $row['title']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-center text-muted py-3">Không tìm thấy bài viết nào phù hợp.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <?php if ($keyword || $category): ?>
                <div class="text-center mt-3">
                    <a href="help_center.php" class="btn btn-sm btn-outline-secondary">Xem tất cả câu hỏi</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="contact-section">
            <h2 class="section-title">Vẫn cần hỗ trợ?</h2>
            <div class="contact-grid">
                <div class="contact-card"><i class="fas fa-phone-alt"></i>
                    <div class="contact-info">
                        <h3>Gọi Tổng Đài</h3>
                        <p>1900 1234 (1000đ/phút)</p>
                    </div>
                </div>
                <div class="contact-card"><i class="fas fa-envelope"></i>
                    <div class="contact-info">
                        <h3>Gửi Email</h3>
                        <p>support@lairai.vn</p>
                    </div>
                </div>
                <div class="contact-card"><i class="fas fa-comments"></i>
                    <div class="contact-info">
                        <h3>Chat Ngay</h3>
                        <p>Trò chuyện với nhân viên</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="simple-footer">
        <div class="container text-center">
            <p>© 2025 LaiRaiShop. Tất cả các quyền được bảo lưu.</p>
        </div>
    </footer>
</body>

</html>