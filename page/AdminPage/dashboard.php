<?php
// File: page/AdminPage/dashboard.php
require_once 'Layout/header.php';

// --- HÀM FORMAT TIỀN TỆ ---
function fullCurrency($number)
{
    return number_format($number, 0, ',', '.') . ' đ';
}

// --- 1. TRUY VẤN KPI ---
$revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;

if (!$conn->query("SHOW COLUMNS FROM `orders` LIKE 'commission_fee'")->num_rows) {
    $commission = $revenue * 0.05;
} else {
    $commission = $conn->query("SELECT SUM(commission_fee) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
}

$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;
$pending_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as count FROM acc WHERE role = 'user'")->fetch_assoc()['count'] ?? 0;

// --- 2. DỮ LIỆU BIỂU ĐỒ & BẢNG (12 THÁNG) ---
$chart_labels = [];
$chart_data = [];
$table_data = [];

for ($i = 11; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));

    $sql_chart = "SELECT SUM(total_amount) as total, SUM(commission_fee) as comm
                  FROM orders WHERE status = 'completed' 
                  AND MONTH(order_date) = '$month' AND YEAR(order_date) = '$year'";
    $row = $conn->query($sql_chart)->fetch_assoc();

    $total_month = $row['total'] ?? 0;
    $comm_month = $row['comm'] ?? ($total_month * 0.05);

    // [LABEL BIỂU ĐỒ]
    if ($i == 0 || $month == '01') {
        $chart_labels[] = "$month/$year";
    } else {
        $chart_labels[] = "$month";
    }

    $chart_data[] = $total_month;

    $table_data[] = [
        'time' => "Tháng $month/$year",
        'revenue' => $total_month,
        'commission' => $comm_month
    ];
}

$json_labels = json_encode($chart_labels);
$json_data = json_encode($chart_data);
?>

<style>
    /* [ĐIỀU CHỈNH QUAN TRỌNG] 
       Giảm chiều cao xuống 350px (thay vì 400px cũ) 
       để giảm khoảng trống thừa bên dưới bảng */
    .sync-height {
        height: 350px;
    }

    /* Thanh cuộn siêu mỏng (chỉ hiện nếu màn hình quá bé) */
    .custom-scroll {
        overflow-y: auto;
    }

    .custom-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scroll::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 4px;
    }

    .custom-scroll::-webkit-scrollbar-track {
        background-color: #f8f9fa;
    }

    /* Sticky Header */
    .sticky-top th {
        position: sticky;
        top: 0;
        background-color: #eaecf4;
        z-index: 2;
        border-top: none;
    }
</style>

<h1 class="h3 mb-4 text-gray-800">Tổng Quan Hệ Thống</h1>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng Doanh Thu (GMV)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo fullCurrency($revenue); ?></div>
                        <div class="mt-2 small text-muted"><i class="fas fa-check-circle text-success"></i> Đã hoàn thành</div>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Doanh Thu Sàn (Hoa Hồng)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo fullCurrency($commission); ?></div>
                        <div class="mt-2 small text-muted">Phí thu được (5%)</div>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đơn Hàng Chờ Xử Lý</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_orders; ?></div>
                        <div class="mt-2 small text-muted">Cần xử lý ngay</div>
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
                        <div class="mt-2 small text-muted">Sản phẩm mới đăng</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-box-open fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4 h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu (12 tháng gần nhất)</h6>
            </div>
            <div class="card-body">
                <div class="sync-height">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4 h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Chi tiết theo tháng</h6>
            </div>
            <div class="card-body p-0">
                <div class="sync-height custom-scroll">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th class="pl-3 border-top-0">Tháng</th>
                                <th class="text-right border-top-0">Doanh Thu</th>
                                <th class="text-right pr-3 border-top-0">Hoa Hồng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($table_data) as $row): ?>
                                <tr>
                                    <td class="pl-3 text-dark font-weight-bold"><?= $row['time'] ?></td>
                                    <td class="text-right <?= $row['revenue'] > 0 ? 'text-primary font-weight-bold' : 'text-muted' ?>">
                                        <?= number_format($row['revenue'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-right pr-3 <?= $row['commission'] > 0 ? 'text-success font-weight-bold' : 'text-muted' ?>">
                                        <?= number_format($row['commission'], 0, ',', '.') ?>
                                    </td>
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
                                suggestedMax: Math.max(...data) * 1.1,
                                callback: function(value) {
                                    if (value >= 1000000000) return (value / 1000000000) + ' tỷ';
                                    if (value >= 1000000) return (value / 1000000) + ' tr';
                                    return value;
                                }
                            }
                        }]
                    }
                }
            });
        }
    });
</script>