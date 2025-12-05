<?php
// File: page/AdminPage/ReportsRevenuePage/reports_revenue.php
require_once '../Layout/header.php';

// --- CẤU HÌNH BỘ LỌC ---
$current_year = date('Y');
$filter_year = isset($_GET['year']) ? intval($_GET['year']) : $current_year;
$filter_month = isset($_GET['month']) ? $_GET['month'] : 'all';
$filter_shop = isset($_GET['shop_id']) ? $_GET['shop_id'] : 'all';

$time_filter_sql = "YEAR(order_date) = $filter_year";
$title_time = "Năm $filter_year";

if ($filter_month !== 'all') {
    $time_filter_sql .= " AND MONTH(order_date) = $filter_month";
    $title_time = "Tháng $filter_month/$filter_year";
}

// 1. TRUY VẤN KPI TỔNG SÀN
// Tính cứng 5% trên tổng doanh thu để đảm bảo số liệu luôn có
$sql_kpi = "SELECT 
                SUM(total_amount) AS gmv, 
                SUM(total_amount * 0.05) AS commission, -- Luôn tính 5% doanh thu
                COUNT(oid) AS total_orders
            FROM orders 
            WHERE status = 'completed' AND $time_filter_sql";

$kpi_result = $conn->query($sql_kpi)->fetch_assoc();
$gmv = $kpi_result['gmv'] ?? 0;
$commission = $kpi_result['commission'] ?? 0;
$total_orders = $kpi_result['total_orders'] ?? 0;
$aov = ($total_orders > 0) ? ($gmv / $total_orders) : 0;

// 2. TRUY VẤN CHI TIẾT THEO SHOP (ĐÃ SỬA LOGIC TÍNH PHÍ)
$shops_list = $conn->query("SELECT sid, shop_name FROM shops ORDER BY shop_name ASC");

// Logic mới: Tính tổng tiền hàng của shop, sau đó nhân 5% ra phí sàn
$sql_shop_report = "SELECT 
                        s.sid,
                        s.shop_name, 
                        SUM(oi.quantity * oi.price) AS shop_gmv,
                        COUNT(DISTINCT o.oid) as shop_orders
                    FROM order_items oi
                    JOIN orders o ON oi.oid = o.oid
                    JOIN products p ON oi.pid = p.pid
                    JOIN shops s ON p.sid = s.sid
                    WHERE o.status = 'completed' AND $time_filter_sql";

if ($filter_shop !== 'all') {
    $sql_shop_report .= " AND s.sid = $filter_shop";
}

$sql_shop_report .= " GROUP BY s.sid ORDER BY shop_gmv DESC";
$res_shop_report = $conn->query($sql_shop_report);

// Hàm format tiền
if (!function_exists('formatCurrency')) {
    function formatCurrency($n)
    {
        return number_format($n, 0, ',', '.') . ' đ';
    }
}
?>

<h1 class="h3 mb-4 text-gray-800">Báo Cáo Doanh Thu & Hoa Hồng</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Bộ Lọc Dữ Liệu: <?php echo $title_time; ?></h6>
    </div>
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="col-md-3 mb-3">
                <label>Năm:</label>
                <select name="year" class="form-control">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label>Tháng:</label>
                <select name="month" class="form-control">
                    <option value="all">Cả năm</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $filter_month == $m ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>Chọn Shop (Người bán):</label>
                <select name="shop_id" class="form-control">
                    <option value="all">-- Tất cả Shop --</option>
                    <?php if ($shops_list) while ($s = $shops_list->fetch_assoc()): ?>
                        <option value="<?= $s['sid'] ?>" <?= $filter_shop == $s['sid'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['shop_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Xem Báo Cáo</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng Doanh Số (GMV)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= formatCurrency($gmv); ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Doanh Thu Sàn (Hoa Hồng)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= formatCurrency($commission); ?></div>
                <small class="text-muted">Phí thu được (5%)</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng Đơn Hàng</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_orders); ?> đơn</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Giá trị TB/Đơn (AOV)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= formatCurrency($aov); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Chi Tiết Doanh Thu & Hoa Hồng Theo Shop</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Tên Shop</th>
                        <th class="text-center">Số Đơn</th>
                        <th class="text-right">Tổng Doanh Thu (GMV)</th>
                        <th class="text-right">Phí Sàn Đóng Góp (5%)</th>
                        <th class="text-right">Thực Lĩnh (Dự kiến)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_shop_report && $res_shop_report->num_rows > 0): ?>
                        <?php while ($row = $res_shop_report->fetch_assoc()):
                            // TÍNH TOÁN TRỰC TIẾP TẠI ĐÂY ĐỂ ĐẢM BẢO KHÔNG BỊ 0
                            $shop_gmv = $row['shop_gmv'];
                            $shop_fee = $shop_gmv * 0.05; // Lấy đúng 5% doanh thu của shop
                            $shop_earn = $shop_gmv - $shop_fee;
                        ?>
                            <tr>
                                <td style="font-weight: 500;"><?= htmlspecialchars($row['shop_name']) ?></td>
                                <td class="text-center"><?= $row['shop_orders'] ?></td>
                                <td class="text-right text-primary font-weight-bold"><?= formatCurrency($shop_gmv) ?></td>
                                <td class="text-right text-danger font-weight-bold"><?= formatCurrency($shop_fee) ?></td>
                                <td class="text-right text-success font-weight-bold"><?= formatCurrency($shop_earn) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-3">Không có dữ liệu phù hợp.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../Layout/footer.php'; ?>