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
  const rooms = document.querySelectorAll(".room");
  const basePath =
    document.querySelector('meta[name="base-path"]')?.content || "";

  // Function to update room display
  function updateRoomDisplay() {
    const selectedRoomText =
      roomTypeSelect.options[roomTypeSelect.selectedIndex].text;
    const selectedRoomType = selectedRoomText.split(" (")[0]; // Get room type without price

    rooms.forEach((room) => {
      if (room.querySelector("h2").textContent === selectedRoomType) {
        room.style.display = "block";
      } else {
        room.style.display = "none";
      }
    });
  }

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
    return totalcost;
  }

  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    try {
      const response = await fetch("process_booking.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

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

        if (result.status === "success") {
          // Ta bort h2
          document.querySelector("#form-section h2").remove();
        }

        // Show success message
        const successDiv = document.createElement("div");
        successDiv.className = "success-message";
        successDiv.innerHTML = `
    <h2>Booking Confirmed!</h2>
    <p>Your receipt has been downloaded.</p>
    <img src="${basePath}/assets/images/success.png" alt="Booking Success">
    <button onclick="location.reload()">Book Again</button>
  `;
        form.innerHTML = "";
        form.appendChild(successDiv);
      } else {
        alert(
          result.message ||
            result.errors?.join("\n") ||
            "Booking failed. Please try again."
        );
        submitButton.disabled = false;
      }
    } catch (error) {
      alert("An error occurred while processing your booking.");
      submitButton.disabled = false;
    }
  });

  // Add event listeners
  roomTypeSelect.addEventListener("change", () => {
    calculateTotalcost();
    updateRoomDisplay();
  });

  arrivalDateInput.addEventListener("change", calculateTotalcost);
  departureDateInput.addEventListener("change", calculateTotalcost);
  featureCheckboxes.forEach((checkbox) =>
    checkbox.addEventListener("change", calculateTotalcost)
  );

  // Initialize room display on page load
  updateRoomDisplay();
});

document.addEventListener("DOMContentLoaded", function () {
  const popup = document.querySelector(".welcome-popup");
  const overlay = document.querySelector(".popup-overlay");
  const closeButton = document.querySelector(".welcome-popup-close");

  // Visa popup när sidan laddas
  setTimeout(() => {
    popup.classList.add("show");
    overlay.classList.add("show");
  }, 500);

  // Stäng popup när man klickar på krysset eller utanför
  closeButton.addEventListener("click", closePopup);
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      closePopup();
    }
  });

  function closePopup() {
    popup.classList.remove("show");
    overlay.classList.remove("show");
  }
});
