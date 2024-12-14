// Function to update the calendar
function updateCalendar() {
  const roomType = document.getElementById("room_type").value;
  const calendarDiv = document.getElementById("calendar");

  // Fetch booked dates from the API
  fetch(`room_calendar.php?room_id=${roomType}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((bookedDates) => {
      console.log("Booked dates:", bookedDates); // Debugging log

      // Clear the calendar
      calendarDiv.innerHTML = ""; // Reset calendar content

      // Generate a simple calendar
      // Generate a calendar for January
      const daysInJanuary = 31;
      for (let day = 1; day <= daysInJanuary; day++) {
        const date = `2025-01-${String(day).padStart(2, "0")}`;
        const dayDiv = document.createElement("div");
        dayDiv.textContent = date;
        dayDiv.className = "calendar-day";

        // Highlight booked dates
        if (bookedDates.includes(date)) {
          dayDiv.classList.add("unavailable");
        }

        calendarDiv.appendChild(dayDiv);
      }
    })
    .catch((error) => {
      console.error("Error fetching calendar:", error);
      calendarDiv.innerHTML = "Failed to load calendar.";
    });
}

// Load calendar on page load and when room type changes
document.getElementById("room_type").addEventListener("change", updateCalendar);
window.onload = updateCalendar;
