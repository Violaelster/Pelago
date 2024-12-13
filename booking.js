document.addEventListener("DOMContentLoaded", () => {
  const roomTypeSelect = document.getElementById("room_type");
  const arrivalDateInput = document.getElementById("arrival_date");
  const departureDateInput = document.getElementById("departure_date");
  const featureCheckboxes = document.querySelectorAll(
    'input[name="features[]"]'
  );
  const totalCostDiv = document.getElementById("total_cost");
  const discountInput = document.getElementById("discount");

  function calculateTotalCost() {
    const roomPrice = parseFloat(
      roomTypeSelect.options[roomTypeSelect.selectedIndex].dataset.price || 0
    );
    const discount = parseFloat(discountInput.value || 0);
    const arrivalDate = new Date(arrivalDateInput.value);
    const departureDate = new Date(departureDateInput.value);
    const nights = Math.max(
      (departureDate - arrivalDate) / (1000 * 60 * 60 * 24),
      0
    );

    let featureCost = 0;
    featureCheckboxes.forEach((checkbox) => {
      if (checkbox.checked) {
        featureCost += parseFloat(checkbox.dataset.price || 0);
      }
    });

    const discountAmount =
      nights > 1 ? roomPrice * (nights - 1) * (discount / 100) : 0;
    const totalCost = roomPrice * nights + featureCost - discountAmount;

    totalCostDiv.textContent = `Total Cost: $${totalCost.toFixed(2)}`;
  }

  roomTypeSelect.addEventListener("change", calculateTotalCost);
  arrivalDateInput.addEventListener("change", calculateTotalCost);
  departureDateInput.addEventListener("change", calculateTotalCost);
  featureCheckboxes.forEach((checkbox) =>
    checkbox.addEventListener("change", calculateTotalCost)
  );
});
