<?php
$session_path = $_SERVER['DOCUMENT_ROOT'] . '/LaiRaiShop/page/SellerPage/types/seller_session.php';
if (file_exists($session_path)) require_once $session_path;

if (isset($_GET['id'])) {
    $sid = $_SESSION['shop_id'];
    $pid = $_GET['id'];

    // Chỉ xóa nếu sản phẩm thuộc về shop này (tránh xóa bậy của shop khác)
    $stmt = $conn->prepare("DELETE FROM products WHERE pid = ? AND sid = ?");
    $stmt->bind_param("ii", $pid, $sid);

    if ($stmt->execute()) {
        header("Location: products.php");
    } else {
        echo "Lỗi xóa sản phẩm: " . $conn->error;
    }
} else {
    header("Location: products.php");
}
?>