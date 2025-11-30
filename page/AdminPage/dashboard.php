<?php
// File: page/AdminPage/dashboard.php

// 1. Gọi Header
require_once 'Layout/header.php';

// --- HÀM ĐỊNH DẠNG SỐ LIỆU (HIỂN THỊ ĐẦY ĐỦ) ---
function fullCurrency($number)
{
    // Chỉ thêm dấu chấm phân cách, không rút gọn thành chữ
    return number_format($number, 0, ',', '.') . ' đ';
}

// --- PHẦN 1: TRUY VẤN SỐ LIỆU THỐNG KÊ (KPIs) ---

// 1.1. TỔNG DOANH THU (Chỉ tính đơn 'completed')
$sql_revenue = "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'";
$res_revenue = $conn->query($sql_revenue);
$row_revenue = $res_revenue->fetch_assoc();
$revenue = $row_revenue['total'] ?? 0;

// 1.2. Đơn hàng Chờ Xử Lý
$sql_pending_orders = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
$res_pending_orders = $conn->query($sql_pending_orders);
$pending_orders = $res_pending_orders->fetch_assoc()['count'] ?? 0;

// 1.3. Sản phẩm Chờ Duyệt
$sql_pending_products = "SELECT COUNT(*) as count FROM products WHERE status = 'pending'";
$res_pending_products = $conn->query($sql_pending_products);
$pending_products = $res_pending_products->fetch_assoc()['count'] ?? 0;

// 1.4. Tổng số User
$sql_users = "SELECT COUNT(*) as count FROM acc WHERE role = 'user'";
$res_users = $conn->query($sql_users);
$total_users = $res_users->fetch_assoc()['count'] ?? 0;


// --- PHẦN 2: DỮ LIỆU BIỂU ĐỒ (6 THÁNG) ---
$chart_labels = [];
$chart_data = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));

    // Nếu muốn test dữ liệu tương lai (2025), hãy mở comment dòng dưới:
    // $year = 2025; 

    $sql_chart = "SELECT SUM(total_amount) as total 
                  FROM orders 
                  WHERE status = 'completed' 
                  AND MONTH(order_date) = '$month' 
                  AND YEAR(order_date) = '$year'";

    $query_chart = $conn->query($sql_chart);
    $data = $query_chart->fetch_assoc()['total'] ?? 0;

    $chart_labels[] = "Tháng $month/$year";
    $chart_data[] = $data;
}

$json_labels = json_encode($chart_labels);
$json_data = json_encode($chart_data);
?>

<h1 class="h3 mb-4 text-gray-800">Tổng Quan Hệ Thống</h1>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng Doanh thu (Đã xong)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo fullCurrency($revenue); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dong-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đơn hàng Chờ xử lý</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Sản phẩm Chờ duyệt</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_products; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Khách hàng đăng ký</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu (6 tháng gần nhất)</h6>
            </div>
            <div class="card-body">
                <div style="height: 400px;">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
                <p class="mt-3 text-center small text-muted font-italic">
                    * Dữ liệu dựa trên các đơn hàng có trạng thái "Completed".
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'Layout/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('monthlyRevenueChart');
        if (ctx) {
            var labels = <?php echo $json_labels; ?>;
            var data = <?php echo $json_data; ?>;

            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh thu',
                        data: data,
                        backgroundColor: 'rgba(19, 94, 75, 0.1)',
                        borderColor: '#135E4B',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return new Intl.NumberFormat('vi-VN', {
                                    style: 'currency',
                                    currency: 'VND'
                                }).format(tooltipItem.yLabel);
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                // Hiển thị số liệu đầy đủ trên trục Y
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                                }
                            }
                        }]
                    }
                }
            });
        }
    });
</script>