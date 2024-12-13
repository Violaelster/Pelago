// Function to fetch and update booked dates
function updateBookedDates() {
  const roomType = document.getElementById("room_type").value;

  // Fetch booked dates from the API endpoint
  fetch(`room_calendar.php?room_id=${roomType}`)
    .then((response) => response.json())
    .then((data) => {
      // Debugging: Log fetched data
      console.log("Booked dates:", data);

      // Clear existing unavailable dates in the calendar
      document.querySelectorAll(".unavailable").forEach((cell) => {
        cell.classList.remove("unavailable");
      });

      // Highlight unavailable dates in the calendar
      data.forEach((date) => {
        const cell = document.querySelector(`[data-date="${date}"]`);
        if (cell) {
          cell.classList.add("unavailable");
        }
      });
    })
    .catch((error) => console.error("Error fetching booked dates:", error));
}

// Function to update the calendar dynamically
function updateCalendar() {
  const roomType = document.getElementById("room_type").value;
  const calendarDiv = document.getElementById("calendar");

  // Fetch calendar HTML for the selected room
  fetch(`room_calendar.php?room_id=${roomType}`)
    .then((response) => response.text())
    .then((html) => {
      calendarDiv.innerHTML = html; // Update calendar HTML
      updateBookedDates(); // Call function to highlight booked dates
    })
    .catch((error) => {
      calendarDiv.innerHTML = "Failed to load calendar.";
    });
}

// Event listener for room type dropdown
document.getElementById("room_type").addEventListener("change", updateCalendar);

// Automatically load the calendar for the default room type on page load
window.onload = updateCalendar;
