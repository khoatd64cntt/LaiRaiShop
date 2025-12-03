<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ưu Đãi HDBank Shopee</title>
    <?php include ROOT_PATH . '/includes/head_meta.php'; ?>
    <link rel="stylesheet" href="../style/ad_banner2.css">
</head>

<body>

    <div class="hero">
        <div class="cloud c1"></div>
        <div class="cloud c2"></div>

        <div class="content-wrapper">
            <div class="text-side">
                <div class="logo">HDBank <span>✈</span></div>
                <div class="sub-text">MUA SẮM SHOPEE</div>
                <h1>GIẢM ĐẾN</h1>
                <div class="highlight">250.000Đ</div>
                <div style="margin-top: 15px; margin-left: 12px; color: #555;">Với thẻ tín dụng HDBank</div>

                <button class="cta-btn" onclick="moTheNgay()">MỞ THẺ NGAY</button>
            </div>

            <div class="card-side">
                <div class="atm-card">
                    <div class="card-logo">HDBank / Vietjet</div>
                    <div class="chip"></div>
                    <div class="card-number">**** **** **** 8888</div>
                    <div style="font-size: 12px; margin-top: 10px;">VISA PLATINUM</div>
                </div>
            </div>
        </div>

        <div style="text-align: center; padding: 20px; color: #666; margin-top: 20px; font-style: italic;">
            <p>*Áp dụng cho đơn hàng từ 1 triệu đồng. Số lượng có hạn.</p>
        </div>
    </div>

    <div class="modal-overlay" id="thongBaoModal">
        <div class="modal-box">
            <div class="success-icon">&#10003;</div>
            <div class="modal-title">Đăng Ký Thành Công!</div>
            <div class="modal-desc">
                Yêu cầu mở thẻ của bạn đã được ghi nhận.<br>
                Nhân viên sẽ liên hệ trong <b>24h</b> tới.<br>
                Quay lại mua sắm ngay thôi!
            </div>
            <button class="modal-btn" onclick="veTrangChu()">OK, QUAY VỀ TRANG CHỦ</button>
        </div>
    </div>

    <script src="../../../js/ad_banner2.js"></script>

</body>

</html>