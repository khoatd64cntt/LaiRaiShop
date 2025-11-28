<?php
session_start();
require_once 'help_data.php';

// Lấy ID và tìm bài viết trong mảng
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$article = null;

foreach ($help_data as $item) {
    if ($item['id'] === $id) {
        $article = $item;
        break;
    }
}

if (!$article) {
    die("Bài viết không tồn tại!");
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article['title']; ?> - Trợ Giúp</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../style/help_center.css">
    <link rel="icon" href="../../../images/icon.png" />
</head>

<body style="background-color: #fff;">

    <header class="help-header">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="logo">
                    <a href="../homepage.php">
                        <img src="../../../images/logo2.png" height="42">
                    </a>
                </div>

                <div class="divider"></div>

                <div class="page-title">
                    <i class="fas fa-life-ring"></i> Trung Tâm Trợ Giúp
                </div>
            </div>

            <div class="header-right">
                <a href="help_center.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </header>

    <div class="container mt-5" style="max-width: 800px;">

        <h2 style="color: #333; font-weight: 600; margin-bottom: 15px;">
            <?php echo $article['title']; ?>
        </h2>

        <hr style="width: 50px; border-top: 3px solid #135E4B; margin-left: 0; margin-bottom: 30px;">

        <div class="article-content" style="font-size: 16px; line-height: 1.8; color: #444;">
            <?php echo $article['content']; ?>
        </div>

    </div>

</body>

</html>