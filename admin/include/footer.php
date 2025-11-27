</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

<script>
    // 1. Script ẩn/hiện menu
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    // 2. Script vẽ biểu đồ (Chỉ chạy nếu có thẻ canvas id="monthlyRevenueChart")
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('monthlyRevenueChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11'],
                    datasets: [{
                        label: 'Doanh thu (VND)',
                        data: [200000000, 350000000, 410000000, 380000000, 450000000, 345000000],
                        backgroundColor: 'rgba(19, 94, 75, 0.1)',
                        borderColor: '#135E4B',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + '₫';
                                }
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function(item) {
                                return item.yLabel.toLocaleString('vi-VN') + '₫';
                            }
                        }
                    }
                }
            });
        }
    });
</script>
</body>

</html>