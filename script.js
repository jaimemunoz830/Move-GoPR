// ===========================================
//  Aquí han trabajado:
//    Esteban G. Echevarria, Gustavo Pagan, Juan D Torres
// ===========================================

document.addEventListener("DOMContentLoaded", function () {

  // ===========================================
  //  SECCIÓN: MAPA DE PROPIEDADES
  //  Solo se ejecuta si existe el elemento #map
  //  en la página actual.
  // ===========================================

  if (document.getElementById("map")) {

    // ===========================================
    //  Inicialización del mapa Leaflet
    //  Se centra en Puerto Rico con zoom 9
    //  usando teselas de OpenStreetMap.
    // ===========================================
    const map = L.map("map").setView([18.2208, -66.5901], 9);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    const propertyList    = document.getElementById("propertyList");
    const propertyDetails = document.getElementById("propertyDetails");

    // ===========================================
    //  Datos de propiedades (estáticos)
    //  Arreglo de ejemplo con coordenadas,
    //  precio y descripción de cada propiedad.
    // ===========================================
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

    // ===========================================
    //  Marcadores y tarjetas de propiedades
    //  Por cada propiedad se coloca un marcador
    //  en el mapa y se crea una tarjeta en la
    //  tira inferior. Ambos muestran el detalle
    //  al hacer clic.
    // ===========================================
    properties.forEach(property => {

      const marker = L.marker(property.coords).addTo(map);

      marker.on("click", () => showDetails(property));

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

    // ===========================================
    //  showDetails()
    //  Rellena el panel derecho con los datos
    //  completos de la propiedad seleccionada.
    // ===========================================
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

  // ===========================================
  //  SECCIÓN: CALENDARIO DE CITAS
  //  Solo se ejecuta si existe #calendarGrid
  //  en la página actual.
  // ===========================================

  if (document.getElementById("calendarGrid")) {

    const confirmBtn       = document.getElementById("confirmBooking");
    const confirmationText = document.getElementById("confirmationText");

    let selectedDate = null;
    let selectedTime = null;

    // ===========================================
    //  Selección de día
    //  Al hacer clic en un día del calendario se
    //  marca como activo y se guarda la selección.
    // ===========================================
    document.querySelectorAll(".calendar-day").forEach(day => {
      day.onclick = () => {
        document.querySelectorAll(".calendar-day")
          .forEach(d => d.classList.remove("selected"));

        day.classList.add("selected");
        selectedDate = day.innerText;
      };
    });

    // ===========================================
    //  Selección de hora
    //  Al hacer clic en una franja horaria se
    //  marca como activa y se guarda la selección.
    // ===========================================
    document.querySelectorAll(".time-option").forEach(time => {
      time.onclick = () => {
        document.querySelectorAll(".time-option")
          .forEach(t => t.classList.remove("selected"));

        time.classList.add("selected");
        selectedTime = time.innerText;
      };
    });

    // ===========================================
    //  Confirmación de cita
    //  Al pulsar el botón se valida que el usuario
    //  haya elegido fecha y hora. Si falta alguna,
    //  muestra una advertencia en lugar de confirmar.
    // ===========================================
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
