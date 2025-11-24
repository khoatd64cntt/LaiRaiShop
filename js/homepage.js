document.addEventListener("DOMContentLoaded", function () {
  if (typeof $ !== "undefined" && $(".carousel").length > 0) {
    $(".carousel").carousel({
      interval: 3000,
      cycle: true,
    });
  }

  // Xử lý nút Next/Prev cho danh mục
  const catContainer = document.getElementById("categoryList");
  const nextBtn = document.getElementById("catNextBtn");
  const prevBtn = document.getElementById("catPrevBtn");

  if (catContainer && nextBtn && prevBtn) {
    function checkButtons() {
      const scrollLeft = catContainer.scrollLeft;
      // Tổng chiều rộng có thể trượt
      const maxScroll = catContainer.scrollWidth - catContainer.clientWidth;

      // --- Xử lý Nút Prev (Lùi) ---
      if (scrollLeft > 10) {
        prevBtn.classList.add("show");
      } else {
        prevBtn.classList.remove("show");
      }

      // --- Xử lý Nút Next (Tiến) ---
      if (scrollLeft >= maxScroll - 5) {
        nextBtn.classList.add("hide");
      } else {
        nextBtn.classList.remove("hide");
      }
    }

    // Sự kiện bấm nút Next
    nextBtn.addEventListener("click", function () {
      const scrollAmount = catContainer.clientWidth / 2; // Cuộn nửa chiều rộng
      catContainer.scrollBy({ left: scrollAmount, behavior: "smooth" });
    });

    // Sự kiện bấm nút Prev
    prevBtn.addEventListener("click", function () {
      const scrollAmount = catContainer.clientWidth / 2;
      catContainer.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    });

    // Cập nhật trạng thái nút khi cuộn
    catContainer.addEventListener("scroll", checkButtons);
    checkButtons();
  }
});
