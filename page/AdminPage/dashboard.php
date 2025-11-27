<?php
// 1. Gọi Header
include 'include/header.php';

// Giả lập số liệu
$revenue = 5600000;
$orders = 15;

// HÀM HỖ TRỢ: Nếu file header.php chưa có hàm này, ta định nghĩa tạm ở đây để tránh lỗi
if (!function_exists('formatCurrency')) {
    function formatCurrency($n)
    {
        return number_format($n, 0, ',', '.') . ' VNĐ';
    }
}
?>

<h1 class="h3 mb-4 text-gray-800">Tổng Quan Hệ Thống</h1>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Doanh thu Hôm nay</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo formatCurrency($revenue); ?>
                        </div>
                    </div>
                    <div class="col-auto"><i class="fas fa-dong-sign fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Đơn hàng mới</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $orders; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-receipt fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// 3. Gọi Footer
include 'include/footer.php';
?>