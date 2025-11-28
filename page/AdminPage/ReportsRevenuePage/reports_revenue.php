<?php
// BƯỚC 1: NHÚNG HEADER
require_once '../Layout/header.php';

// BƯỚC 2: XỬ LÝ LỌC THỜI GIAN
$current_year = date('Y');
$current_month = date('m');

$filter_year = isset($_GET['year']) ? intval($_GET['year']) : $current_year;
$filter_month = isset($_GET['month']) ? intval($_GET['month']) : 'all';

$time_range_title = "Năm $filter_year";
$time_filter_sql = "YEAR(order_date) = $filter_year";

if ($filter_month !== 'all' && $filter_month > 0) {
    $time_range_title = "Tháng $filter_month/$filter_year";
    $time_filter_sql .= " AND MONTH(order_date) = $filter_month";
}

// BƯỚC 3: TRUY VẤN DỮ LIỆU BÁO CÁO (Dùng trạng thái 'completed')

// 1. Tổng quan KPI
$sql_kpi = "SELECT 
                SUM(total_amount) AS total_revenue, 
                COUNT(oid) AS total_orders
            FROM orders 
            WHERE status = 'completed' AND $time_filter_sql";
$kpi_query = $conn->query($sql_kpi);
$kpi_result = ($kpi_query) ? $kpi_query->fetch_assoc() : ['total_revenue' => 0, 'total_orders' => 0];

$total_revenue = $kpi_result['total_revenue'] ?? 0;
$total_orders = $kpi_result['total_orders'] ?? 0;
$aov = ($total_orders > 0) ? ($total_revenue / $total_orders) : 0;

// 2. Top 5 Sản phẩm Bán chạy (Theo số lượng)
// SỬA: p.name thay vì p.pname
$sql_top_products = "SELECT 
                        p.name, 
                        SUM(oi.quantity) AS total_sold
                    FROM order_items oi
                    JOIN orders o ON oi.oid = o.oid
                    JOIN products p ON oi.pid = p.pid
                    WHERE o.status = 'completed' AND $time_filter_sql
                    GROUP BY p.name
                    ORDER BY total_sold DESC
                    LIMIT 5";
$top_products_result = $conn->query($sql_top_products);

// 3. Top 5 Người bán (Theo Doanh thu)
// SỬA: s.shop_name thay vì s.sname
$sql_top_sellers = "SELECT 
                        s.shop_name, 
                        SUM(oi.quantity * oi.price) AS seller_revenue
                    FROM order_items oi
                    JOIN orders o ON oi.oid = o.oid
                    JOIN products p ON oi.pid = p.pid
                    JOIN shops s ON p.sid = s.sid
                    WHERE o.status = 'completed' AND $time_filter_sql
                    GROUP BY s.shop_name
                    ORDER BY seller_revenue DESC
                    LIMIT 5";
$top_sellers_result = $conn->query($sql_top_sellers);

?>

<h1 class="mt-4">Thống Kê & Báo Cáo Doanh Thu</h1>
<p class="lead text-muted">Báo cáo cho giai đoạn: **<?php echo $time_range_title; ?>**</p>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i> Lựa Chọn Thời Gian</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <label class="mr-2">Năm:</label>
            <select name="year" class="form-control mr-3">
                <?php for ($y = $current_year; $y >= $current_year - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php if ($filter_year == $y) echo 'selected'; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <label class="mr-2">Tháng:</label>
            <select name="month" class="form-control mr-3">
                <option value="all" <?php if ($filter_month === 'all') echo 'selected'; ?>>Cả Năm</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php if ($filter_month == $m) echo 'selected'; ?>>Tháng <?php echo $m; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-chart-bar"></i> Xem Báo Cáo</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng Doanh thu Đã hoàn thành</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatCurrency($total_revenue); ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng Số Đơn hàng Đã hoàn thành</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_orders); ?> đơn</div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Giá trị Đơn hàng Trung bình (AOV)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatCurrency($aov); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-gift mr-1"></i> Top 5 Sản phẩm Bán chạy nhất</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php if ($top_products_result && $top_products_result->num_rows > 0): ?>
                        <?php $rank = 1;
                        while ($row = $top_products_result->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>#<?php echo $rank++; ?>. <?php echo htmlspecialchars($row['name']); ?></span>
                                <span class="badge badge-info badge-pill"><?php echo number_format($row['total_sold']); ?> lượt bán</span>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted">Không có dữ liệu sản phẩm đã bán trong giai đoạn này.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-store mr-1"></i> Top 5 Người Bán theo Doanh thu</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php if ($top_sellers_result && $top_sellers_result->num_rows > 0): ?>
                        <?php $rank = 1;
                        while ($row = $top_sellers_result->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>#<?php echo $rank++; ?>. <?php echo htmlspecialchars($row['shop_name']); ?></span>
                                <span class="badge badge-success badge-pill"><?php echo formatCurrency($row['seller_revenue']); ?></span>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted">Không có dữ liệu người bán trong giai đoạn này.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
include '../Layout/footer.php';
?>