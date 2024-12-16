document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const roomTypeSelect = document.getElementById("room_type");
  const arrivalDateInput = document.getElementById("arrival_date");
  const departureDateInput = document.getElementById("departure_date");
  const featureCheckboxes = document.querySelectorAll(
    'input[name="features[]"]'
  );
  const totalcostDiv = document.getElementById("total_cost");
  const discountInput = document.getElementById("discount");

  function calculateTotalcost() {
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
    const totalcost = roomPrice * nights + featureCost - discountAmount;

    totalcostDiv.textContent = `Total Cost: $${totalcost.toFixed(2)}`;
    return totalcost; // Return the calculated total
  }

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Create debug div if it doesn't exist
    let debugDiv = document.getElementById("debug-info");
    if (!debugDiv) {
      debugDiv = document.createElement("div");
      debugDiv.id = "debug-info";
      debugDiv.style.margin = "20px";
      debugDiv.style.padding = "10px";
      debugDiv.style.border = "1px solid #ccc";
      debugDiv.style.backgroundColor = "#f5f5f5";
      form.parentNode.insertBefore(debugDiv, form.nextSibling);
    }

    const formData = new FormData(this);
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    // Log form data being sent
    debugDiv.innerHTML = "<h3>Debug Information:</h3>";
    debugDiv.innerHTML += "<p>Form Data being sent:</p>";
    for (let pair of formData.entries()) {
      debugDiv.innerHTML += `<p>${pair[0]}: ${pair[1]}</p>`;
    }
    debugDiv.innerHTML += `<p>Calculated Total Cost: $${calculateTotalcost()}</p>`;

    try {
      const response = await fetch("process_booking.php", {
        method: "POST",
        body: formData,
      });

      // Log raw response
      const rawResponse = await response.text();
      debugDiv.innerHTML += "<p>Raw Response:</p>";
      debugDiv.innerHTML += `<pre>${rawResponse}</pre>`;

      // Try to parse JSON
      let result;
      try {
        result = JSON.parse(rawResponse);
      } catch (e) {
        debugDiv.innerHTML += `<p>Error parsing JSON: ${e.message}</p>`;
        throw new Error("Invalid JSON response");
      }

      if (result.status === "success") {
        // Create downloadable receipt
        const blob = new Blob([JSON.stringify(result, null, 2)], {
          type: "application/json",
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `booking-receipt-${result.booking_id}.json`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        // Show success message
        const successDiv = document.createElement("div");
        successDiv.className = "success-message";
        successDiv.innerHTML = `
          <h2>Booking Confirmed!</h2>
          <p>Booking ID: ${result.booking_id}</p>
          <p>Total Cost: $${result.total_cost}</p>
          <p>Your receipt has been downloaded.</p>
          <button onclick="location.reload()">Make Another Booking</button>
        `;
        form.innerHTML = "";
        form.appendChild(successDiv);
      } else {
        debugDiv.innerHTML += "<p>Error Response:</p>";
        debugDiv.innerHTML += `<pre>${JSON.stringify(result, null, 2)}</pre>`;
        alert(
          result.message ||
            result.errors?.join("\n") ||
            "Booking failed. Please try again."
        );
        submitButton.disabled = false;
      }
    } catch (error) {
      debugDiv.innerHTML += `<p>Fetch Error: ${error.message}</p>`;
      alert(
        "An error occurred while processing your booking. Please check the debug information below the form."
      );
      submitButton.disabled = false;
    }
  });

  // Add existing event listeners
  roomTypeSelect.addEventListener("change", calculateTotalcost);
  arrivalDateInput.addEventListener("change", calculateTotalcost);
  departureDateInput.addEventListener("change", calculateTotalcost);
  featureCheckboxes.forEach((checkbox) =>
    checkbox.addEventListener("change", calculateTotalcost)
  );
});
