document.addEventListener("DOMContentLoaded", function () {
  const itemsContainer = document.getElementById("items-container");
  const addItemBtn = document.querySelector(".add-item");

  if (addItemBtn) {
    addItemBtn.addEventListener("click", function () {
      const itemRow = document.querySelector(".item-row").cloneNode(true);
      const inputs = itemRow.querySelectorAll("input");
      inputs.forEach((input) => (input.value = ""));

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

  itemsContainer.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-item")) {
      if (document.querySelectorAll(".item-row").length > 1) {
        e.target.closest(".item-row").remove();
        calculateTotal();
      }
    }
  });

  function calculateTotal() {
    let total = 0;

    const rows = document.querySelectorAll(".item-row");
    rows.forEach((row) => {
      const amountInput = row.querySelector('input[name="amount[]"]');
      const quantityDescInput = row.querySelector(
        'input[name="quantity_description[]"]'
      );

      const amount = parseFloat(amountInput.value) || 0;

      let quantity = 1;
      if (quantityDescInput && quantityDescInput.value) {
        const matches = quantityDescInput.value.match(/[\d.]+/g); // Extract numbers
        if (matches) {
          quantity = matches.reduce((acc, val) => acc * parseFloat(val), 1);
        }
      }

      total += amount * quantity;
    });

    document.getElementById("total-amount").textContent =
      "$" + total.toFixed(2);
  }

  itemsContainer.addEventListener("input", function (e) {
    if (
      e.target.name === "amount[]" ||
      e.target.name === "quantity_description[]"
    ) {
      calculateTotal();
    }
  });

  calculateTotal();
});
