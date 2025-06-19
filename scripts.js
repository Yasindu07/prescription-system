document.addEventListener("DOMContentLoaded", function () {
  // Add item functionality in quotation form
  const itemsContainer = document.getElementById("items-container");
  const addItemBtn = document.querySelector(".add-item");

  if (addItemBtn) {
    addItemBtn.addEventListener("click", function () {
      const itemRow = document.querySelector(".item-row").cloneNode(true);
      const inputs = itemRow.querySelectorAll("input");
      inputs.forEach((input) => (input.value = ""));

      // Change button to remove button
      const button = itemRow.querySelector("button");
      button.classList.remove("add-item");
      button.classList.add("remove-item");
      button.textContent = "-";

      button.addEventListener("click", function () {
        itemsContainer.removeChild(itemRow);
        calculateTotal();
      });

      itemsContainer.appendChild(itemRow);
    });
  }

  // Remove item functionality
  itemsContainer.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-item")) {
      if (document.querySelectorAll(".item-row").length > 1) {
        e.target.closest(".item-row").remove();
        calculateTotal();
      }
    }
  });

  // Calculate total amount
  function calculateTotal() {
    let total = 0;
    document.querySelectorAll('input[name="amount[]"]').forEach((input) => {
      const value = parseFloat(input.value) || 0;
      total += value;
    });
    document.getElementById("total-amount").textContent =
      "$" + total.toFixed(2);
  }

  // Listen for amount changes
  if (itemsContainer) {
    itemsContainer.addEventListener("input", function (e) {
      if (e.target.name === "amount[]") {
        calculateTotal();
      }
    });
  }

  // Initialize total
  calculateTotal();
});
