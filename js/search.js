document.addEventListener("DOMContentLoaded", function () {
  const priceForm = document.getElementById("priceFilterForm");

  if (priceForm) {
    priceForm.addEventListener("submit", function (e) {
      const min = priceForm.querySelector('input[name="price_min"]').value;
      const max = priceForm.querySelector('input[name="price_max"]').value;

      // Kiểm tra chỉ khi nhập cả 2
      if (min && max && parseInt(min) > parseInt(max)) {
        e.preventDefault();
        alert("Giá tối thiểu không được lớn hơn giá tối đa!");
      }
    });
  }
});
