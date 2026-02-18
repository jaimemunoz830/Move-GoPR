document.addEventListener("DOMContentLoaded", function () {

  // ================= MAP =================

  if (document.getElementById("map")) {

    const map = L.map("map").setView([18.2208, -66.5901], 9);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    const propertyList = document.getElementById("propertyList");
    const propertyDetails = document.getElementById("propertyDetails");

    const properties = [
      {
        title: "Cozy Suburban House",
        price: "$2,200/mo",
        beds: 3,
        baths: 2,
        sqft: 1800,
        coords: [18.4655, -66.1057],
        description: "Beautiful family home with backyard and garage."
      },
      {
        title: "Luxury Villa",
        price: "$850,000",
        beds: 5,
        baths: 4,
        sqft: 4200,
        coords: [18.3358, -65.6520],
        description: "Oceanfront luxury villa with private pool."
      }
    ];

    properties.forEach(property => {

      const marker = L.marker(property.coords).addTo(map);

      marker.on("click", () => showDetails(property));

      // Bottom cards
      const card = document.createElement("div");
      card.className = "property-card";
      card.innerHTML = `
        <h4>${property.title}</h4>
        <p>${property.price}</p>
        <small>${property.beds} Beds • ${property.baths} Baths</small>
      `;

      card.onclick = () => {
        map.setView(property.coords, 13);
        showDetails(property);
      };

      propertyList.appendChild(card);
    });

    function showDetails(property) {
      propertyDetails.innerHTML = `
        <h2>${property.title}</h2>
        <h3>${property.price}</h3>
        <p>${property.beds} Beds | ${property.baths} Baths | ${property.sqft} sqft</p>
        <p>${property.description}</p>
        <button class="confirm-btn">Request Viewing</button>
      `;
    }
  }

  // ================= CALENDAR =================

  if (document.getElementById("calendarGrid")) {

    const confirmBtn = document.getElementById("confirmBooking");
    const confirmationText = document.getElementById("confirmationText");

    let selectedDate = null;
    let selectedTime = null;

    document.querySelectorAll(".calendar-day").forEach(day => {
      day.onclick = () => {
        document.querySelectorAll(".calendar-day")
          .forEach(d => d.classList.remove("selected"));

        day.classList.add("selected");
        selectedDate = day.innerText;
      };
    });

    document.querySelectorAll(".time-option").forEach(time => {
      time.onclick = () => {
        document.querySelectorAll(".time-option")
          .forEach(t => t.classList.remove("selected"));

        time.classList.add("selected");
        selectedTime = time.innerText;
      };
    });

    confirmBtn.addEventListener("click", function () {

      if (selectedDate && selectedTime) {
        confirmationText.innerHTML =
          `✅ Appointment confirmed for <strong>${selectedDate}</strong> at <strong>${selectedTime}</strong>`;
      } else {
        confirmationText.innerHTML =
          `⚠ Please select a date and time first.`;
      }

    });
  }

});
