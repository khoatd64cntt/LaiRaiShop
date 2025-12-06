</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

<script>
    $(document).ready(function() {
        // 1. KIỂM TRA TRẠNG THÁI SIDEBAR TỪ BỘ NHỚ
        // Nếu trước đó user đã đóng menu (toggled = true), thì giữ nguyên trạng thái đóng
        if (localStorage.getItem("sidebar_state") === "toggled") {
            $("#wrapper").addClass("toggled");
        } else {
            // Mặc định là mở
            $("#wrapper").removeClass("toggled");
        }

        // 2. XỬ LÝ CLICK NÚT MENU
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");

            // Lưu trạng thái mới vào localStorage để dùng cho trang sau
            if ($("#wrapper").hasClass("toggled")) {
                localStorage.setItem("sidebar_state", "toggled"); // Đã đóng
            } else {
                localStorage.setItem("sidebar_state", "open"); // Đã mở
            }
        });
    });
</script>

</body>

</html>