<?php
// File: page/AdminPage/dashboard.php

// 1. Gọi Header
require_once 'Layout/header.php';

// --- HÀM FORMAT TIỀN TỆ ---
function fullCurrency($number)
{
    return number_format($number, 0, ',', '.') . ' đ';
}

// --- PHẦN 1: TRUY VẤN SỐ LIỆU THỐNG KÊ (KPIs) ---

// 1.1. TỔNG DOANH THU (GMV)
$sql_revenue = "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'";
$res_revenue = $conn->query($sql_revenue);
$revenue = $res_revenue->fetch_assoc()['total'] ?? 0;

// 1.1b. TỔNG HOA HỒNG (COMMISSION) - Lấy từ cột commission_fee trong DB
// Nếu chưa có cột này, nó sẽ tạm tính 5% doanh thu
$sql_commission = "SELECT SUM(commission_fee) as total FROM orders WHERE status = 'completed'";
// Fallback: Nếu chưa chạy lệnh SQL thêm cột, dùng tạm 5% doanh thu
if (!$conn->query("SHOW COLUMNS FROM `orders` LIKE 'commission_fee'")->num_rows) {
    $commission = $revenue * 0.05;
} else {
    $res_commission = $conn->query($sql_commission);
    $commission = $res_commission->fetch_assoc()['total'] ?? 0;
}

// 1.2. Đơn hàng Chờ Xử Lý
$sql_pending_orders = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
$pending_orders = $conn->query($sql_pending_orders)->fetch_assoc()['count'] ?? 0;

// 1.3. Sản phẩm Chờ Duyệt
$sql_pending_products = "SELECT COUNT(*) as count FROM products WHERE status = 'pending'";
$pending_products = $conn->query($sql_pending_products)->fetch_assoc()['count'] ?? 0;

// 1.4. Tổng số User
$sql_users = "SELECT COUNT(*) as count FROM acc WHERE role = 'user'";
$total_users = $conn->query($sql_users)->fetch_assoc()['count'] ?? 0;


// --- PHẦN 2: DỮ LIỆU BIỂU ĐỒ & BẢNG (6 THÁNG) ---
$chart_labels = [];
$chart_data = [];
$table_data = []; // Mảng chứa dữ liệu cho bảng

for ($i = 5; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));

    // Nếu muốn test dữ liệu tương lai (2025), hãy mở comment dòng dưới:
    // $year = 2025; 

    $sql_chart = "SELECT SUM(total_amount) as total, SUM(commission_fee) as comm
                  FROM orders 
                  WHERE status = 'completed' 
                  AND MONTH(order_date) = '$month' 
                  AND YEAR(order_date) = '$year'";

    $query_chart = $conn->query($sql_chart);
    $row = $query_chart->fetch_assoc();
    $total_month = $row['total'] ?? 0;
    $comm_month = $row['comm'] ?? ($total_month * 0.05); // Fallback 5%

    $chart_labels[] = "Tháng $month/$year";
    $chart_data[] = $total_month;

    // Lưu vào mảng để hiển thị bảng
    $table_data[] = [
        'time' => "Tháng $month/$year",
        'revenue' => $total_month,
        'commission' => $comm_month
    ];
}

$json_labels = json_encode($chart_labels);
$json_data = json_encode($chart_data);
?>

<h1 class="h3 mb-4 text-gray-800">Tổng Quan Hệ Thống</h1>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng Doanh Thu (GMV)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo fullCurrency($revenue); ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Doanh Thu Sàn (5%)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo fullCurrency($commission); ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đơn Chờ Xử Lý</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_orders; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">SP Chờ Duyệt</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_products; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-box-open fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu (6 tháng gần nhất)</h6>
            </div>
            <div class="card-body">
                <div style="height: 320px;">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Chi tiết theo tháng</h6>
            </div>
            <div class="card-body" style="max-height: 360px; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>Tháng</th>
                                <th>Doanh Thu</th>
                                <th>Hoa Hồng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($table_data) as $row): // Đảo ngược để tháng mới nhất lên đầu 
                            ?>
                                <tr>
                                    <td><?= $row['time'] ?></td>
                                    <td class="text-right"><?= number_format($row['revenue'], 0, ',', '.') ?></td>
                                    <td class="text-right text-success font-weight-bold"><?= number_format($row['commission'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
                        label: 'Tổng Doanh Thu',
                        data: data,
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderColor: '#4e73df',
                        pointBackgroundColor: '#4e73df',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4e73df',
                        borderWidth: 2,
                        fill: true
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