<?php
require_once '../../../config.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Về LaiRaiShop - Câu Chuyện Của Chúng Tôi</title>
    <link rel="stylesheet" href="../style/about.css?v=<?php echo time(); ?>">
    <?php include ROOT_PATH . '/includes/head_meta.php'; ?>
</head>

<body>

    <?php // include 'header.php'; 
    ?>

    <div class="about-container">
        <img src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?q=80&w=1000&auto=format&fit=crop" class="hero-image" alt="Văn phòng LaiRaiShop">

        <div class="content-box">
            <h1>Xin chào, đây là LaiRaiShop!</h1>
            <div class="slogan">"Mua sắm lai rai - Niềm vui dài dài"</div>
            <p>
                Được thành lập bởi một nhóm sinh viên đam mê công nghệ,
                <b>LaiRaiShop</b> ra đời với mong muốn mang lại một không gian mua sắm trực tuyến
                đơn giản, nhẹ nhàng và tin cậy.
            </p>
            <p>
                Tại đây, hãy mua sắm thoải thích. Cứ từ từ chọn lựa, "lai rai" ngắm nghía.
            </p>
            <div class="signature">
                Thân ái,<br>
                Đội ngũ Admin LaiRaiShop
            </div>
            <a href="../homepage.php" class="btn-home">Bắt Đầu Mua Sắm Ngay</a>
        </div>
    </div>

    <?php // include 'footer.php'; 
    ?>

</body>

</html>