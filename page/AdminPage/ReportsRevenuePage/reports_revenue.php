<?php
// File: page/AdminPage/ReportsRevenuePage/reports_revenue.php
require_once '../Layout/header.php';

// --- CẤU HÌNH BỘ LỌC ---
$current_year = date('Y');
$filter_year = isset($_GET['year']) ? intval($_GET['year']) : $current_year;
$filter_month = isset($_GET['month']) ? $_GET['month'] : 'all';
$filter_shop = isset($_GET['shop_id']) ? $_GET['shop_id'] : 'all';

// Tạo điều kiện lọc thời gian cho SQL
// Lưu ý: Dùng o.order_date để tránh lỗi ambiguous column khi join
$time_filter_sql = "YEAR(o.order_date) = $filter_year";
$title_time = "Năm $filter_year";

if ($filter_month !== 'all') {
    $time_filter_sql .= " AND MONTH(o.order_date) = $filter_month";
    $title_time = "Tháng $filter_month/$filter_year";
}

// 1. KPI TỔNG SÀN
$sql_kpi = "SELECT 
                SUM(o.total_amount) AS gmv, 
                SUM(o.commission_fee) AS commission, 
                COUNT(o.oid) AS total_orders
            FROM orders o
            WHERE o.status = 'completed' AND $time_filter_sql";

$kpi_result = $conn->query($sql_kpi)->fetch_assoc();
$gmv = $kpi_result['gmv'] ?? 0;
$commission = $kpi_result['commission'] ?? 0;
$total_orders = $kpi_result['total_orders'] ?? 0;
$aov = ($total_orders > 0) ? ($gmv / $total_orders) : 0;

// 2. BÁO CÁO SHOP (Bảng tổng quan)
$shops_list = $conn->query("SELECT sid, shop_name FROM shops ORDER BY shop_name ASC");

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


// --- [CẬP NHẬT] 3. LOGIC LẤY CHI TIẾT (LIỆT KÊ TỪNG ĐƠN HÀNG) ---
$detail_products = null;
$detail_shop_name = "";
$show_modal = false;

if (isset($_GET['view_detail_sid'])) {
    $view_sid = intval($_GET['view_detail_sid']);
    $show_modal = true;

    // Lấy tên shop
    $s_name_q = $conn->query("SELECT shop_name FROM shops WHERE sid = $view_sid");
    $detail_shop_name = ($s_name_q->num_rows > 0) ? $s_name_q->fetch_assoc()['shop_name'] : "Shop #$view_sid";

    // [THAY ĐỔI QUERY]: Không dùng GROUP BY nữa để hiện từng dòng chi tiết của từng đơn
    $sql_details = "SELECT 
                        o.oid,
                        o.order_date,
                        p.name AS product_name,
                        p.main_image,
                        oi.quantity,
                        (oi.quantity * oi.price) AS item_revenue
                    FROM order_items oi
                    JOIN orders o ON oi.oid = o.oid
                    JOIN products p ON oi.pid = p.pid
                    WHERE p.sid = $view_sid 
                      AND o.status = 'completed' 
                      AND $time_filter_sql
                    ORDER BY o.order_date DESC, o.oid DESC"; // Sắp xếp đơn mới nhất lên đầu

    $detail_products = $conn->query($sql_details);
}

// Hàm format tiền
if (!function_exists('formatCurrency')) {
    function formatCurrency($n)
    {
        return number_format($n, 0, ',', '.') . ' đ';
    }
}
?>

<h1 class="h3 mb-4 text-gray-800">Báo Cáo Doanh Thu</h1>

<div class="card shadow mb-4">
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
                <label>Chọn Shop:</label>
                <select name="shop_id" class="form-control">
                    <option value="all">-- Tất cả Shop --</option>
                    <?php if ($shops_list) while ($s = $shops_list->fetch_assoc()): ?>
                        <option value="<?= $s['sid'] ?>" <?= $filter_shop == $s['sid'] ? 'selected' : '' ?>><?= htmlspecialchars($s['shop_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Xem</button>
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
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Hiệu Quả Kinh Doanh Theo Shop</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%">
                <thead class="thead-light">
                    <tr>
                        <th>Tên Shop</th>
                        <th class="text-center">Số Đơn</th>
                        <th class="text-right">Doanh Thu (GMV)</th>
                        <th class="text-right">Phí Sàn (5%)</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_shop_report && $res_shop_report->num_rows > 0): ?>
                        <?php while ($row = $res_shop_report->fetch_assoc()):
                            $shop_gmv = $row['shop_gmv'];
                            $shop_fee = $shop_gmv * 0.05;
                        ?>
                            <tr>
                                <td style="font-weight: 500;"><?= htmlspecialchars($row['shop_name']) ?></td>
                                <td class="text-center"><?= $row['shop_orders'] ?></td>
                                <td class="text-right text-primary font-weight-bold"><?= formatCurrency($shop_gmv) ?></td>
                                <td class="text-right text-danger font-weight-bold"><?= formatCurrency($shop_fee) ?></td>
                                <td class="text-center">
                                    <a href="?year=<?= $filter_year ?>&month=<?= $filter_month ?>&shop_id=<?= $filter_shop ?>&view_detail_sid=<?= $row['sid'] ?>"
                                        class="btn btn-sm btn-info">
                                        <i class="fas fa-list"></i> Chi tiết đơn
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-3">Không có dữ liệu.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Lịch sử bán hàng: <b><?= htmlspecialchars($detail_shop_name) ?></b></h5>
                <a href="reports_revenue.php?year=<?= $filter_year ?>&month=<?= $filter_month ?>" class="close text-white">
                    <span aria-hidden="true">&times;</span>
                </a>
            </div>
            <div class="modal-body">
                <p>Thời gian thống kê: <b><?= $title_time ?></b></p>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Mã Đơn / Ngày</th>
                                <th>Sản phẩm</th>
                                <th class="text-center" width="80">SL</th>
                                <th class="text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($detail_products && $detail_products->num_rows > 0): ?>
                                <?php while ($d = $detail_products->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary">#<?= $d['oid'] ?></span><br>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($d['order_date'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php
                                                $img = !filter_var($d['main_image'], FILTER_VALIDATE_URL) ? BASE_URL . $d['main_image'] : $d['main_image'];
                                                ?>
                                                <img src="<?= $img ?>" style="width: 35px; height: 35px; margin-right:8px; object-fit:cover; border:1px solid #ddd;">
                                                <span><?= htmlspecialchars($d['product_name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold"><?= $d['quantity'] ?></td>
                                        <td class="text-right text-success font-weight-bold"><?= formatCurrency($d['item_revenue']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Chưa có đơn hàng nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="reports_revenue.php?year=<?= $filter_year ?>&month=<?= $filter_month ?>" class="btn btn-secondary">Đóng</a>
            </div>
        </div>
    </div>
</div>

<?php include '../Layout/footer.php'; ?>

<?php if ($show_modal): ?>
    <script>
        $(document).ready(function() {
            $('#detailModal').modal('show');
        });
    </script>
<?php endif; ?>