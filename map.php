<?php
define('MOVE_GO_APP', true);   // required by db_queries.php security check
require 'config.php';          // sets up $pdo (PDO connection)
require_once 'db_queries.php'; // all DB functions live here

if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? 'viewer';

// ── Fetch data from DB ───────────────────────────────────────
$properties       = getProperties($pdo);          // all active properties + images
$pinnedLocations  = getPinnedLocations($pdo);      // admin pinpoints with coords
$allLocations     = getLocations($pdo);            // all locations (for popup dropdowns)
$unpinnedLocations = getLocations($pdo, 'no');     // for "items without a pin"
$pinnedList        = getLocations($pdo, 'yes');    // for "already pinned items"

// Encode for JavaScript
$propertiesJson  = json_encode($properties,      JSON_HEX_TAG | JSON_HEX_APOS);
$locationsJson   = json_encode($allLocations,    JSON_HEX_TAG | JSON_HEX_APOS);
$pinnedJson      = json_encode($pinnedLocations, JSON_HEX_TAG | JSON_HEX_APOS);
$unpinnedJson    = json_encode($unpinnedLocations, JSON_HEX_TAG | JSON_HEX_APOS);
$pinnedListJson  = json_encode($pinnedList,      JSON_HEX_TAG | JSON_HEX_APOS);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mapa de Propiedades — Move & Go PR</title>

<!-- Fonts & icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<link rel="stylesheet" href="css/styles.css">

<style>
/* ── Page shell ─────────────────────────────────────────────── */
body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: #f8f9fa;
}

/* ── Map page wrapper ───────────────────────────────────────── */
#mapPage {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 24px;
  gap: 18px;
  box-sizing: border-box;
}

/* ── Filter bar ─────────────────────────────────────────────── */
#filterBar {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

#filterBar span {
  font-size: 0.8rem;
  font-weight: 700;
  color: #3B6E1A;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-right: 4px;
}

.filterBtn {
  padding: 8px 20px;
  border: 2px solid #4E8F22;
  border-radius: 8px;
  background: transparent;
  color: #3B6E1A;
  font-family: 'Poppins', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}
.filterBtn:hover,
.filterBtn.active {
  background: #4E8F22;
  color: white;
}

/* ── Main map row ───────────────────────────────────────────── */
#mapRow {
  display: flex;
  gap: 20px;
  flex: 1;
  align-items: flex-start;
}

/* ── Map container ──────────────────────────────────────────── */
#mapContainer {
  position: relative;
  flex: 2;
  height: 540px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  border: 3px solid #78B833;
}

#map { height: 100%; width: 100%; }

/* ── Radius control panel ───────────────────────────────────── */
#rangeInfo {
  position: absolute;
  top: 12px;
  left: 70px;
  background: white;
  padding: 9px 14px;
  border-radius: 10px;
  z-index: 1000;
  box-shadow: 0 2px 10px rgba(0,0,0,0.15);
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #1a1a1a;
}
#rangeInfo input[type=range] {
  accent-color: #4E8F22;
  width: 110px;
}
#radiusValue {
  color: #4E8F22;
  font-weight: 700;
  min-width: 20px;
}

/* ── Right panel (property detail + radius sidebar) ─────────── */
#rightPanel {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 300px;
  flex-shrink: 0;
}

/* Property detail box */
#propertyDetails {
  background: white;
  padding: 18px;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  border-top: 4px solid #4E8F22;
}
#propertyDetails img {
  width: 100%;
  border-radius: 10px;
  margin-bottom: 12px;
  object-fit: cover;
  max-height: 160px;
}
#propertyDetails h2 {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 6px;
}
#propertyDetails p {
  font-size: 0.85rem;
  color: #555;
  margin: 3px 0;
}

/* Radius sidebar */
#sidebar {
  background: white;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  padding: 16px;
  max-height: 220px;
  overflow-y: auto;
}
#sidebar h2 {
  font-size: 0.8rem;
  font-weight: 700;
  color: #3B6E1A;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin: 0 0 12px;
  padding-bottom: 8px;
  border-bottom: 2px solid #BFEA8C;
}

/* Sidebar radius cards */
.radiusCard {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 10px 12px;
  margin-bottom: 8px;
  border-left: 4px solid #4E8F22;
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a1a1a;
}
.radiusCard small {
  display: block;
  font-size: 0.75rem;
  font-weight: 400;
  color: #4E8F22;
  margin-top: 2px;
}
.radiusCard .rc-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
  cursor: pointer;
  transition: opacity 0.15s;
}
.radiusCard .rc-top:hover { opacity: 0.75; }
.radiusCard .rc-name { flex: 1; }
.btn-more-info {
  flex-shrink: 0;
  margin-top: 8px;
  width: 100%;
  padding: 5px 0;
  background: #4E8F22;
  color: white;
  border: none;
  border-radius: 6px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}
.btn-more-info:hover { background: #3B6E1A; }

/* ── Expanded detail panel (below scroll strip) ─────────────── */
#detailPanel {
  display: none;
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
  border-top: 4px solid #4E8F22;
  padding: 24px;
  animation: slideDown 0.25s ease;
  position: relative;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to   { opacity: 1; transform: translateY(0); }
}

#detailPanel .dp-close {
  position: absolute;
  top: 16px;
  right: 18px;
  background: none;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 0.8rem;
  padding: 4px 10px;
  cursor: pointer;
  color: #666;
  font-family: 'Poppins', sans-serif;
  transition: all 0.2s;
}
#detailPanel .dp-close:hover { background: #f0f0f0; border-color: #999; }

/* Carousel inside detail panel */
.dp-carousel {
  position: relative;
  width: 100%;
  height: 280px;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 20px;
  background: #f0f0f0;
}
.dp-track {
  display: flex;
  height: 100%;
  transition: transform 0.4s ease-in-out;
}
.dp-track img {
  min-width: 100%;
  height: 100%;
  object-fit: cover;
}
.dp-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0,0,0,0.45);
  color: white;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  font-size: 1rem;
  cursor: pointer;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}
.dp-btn:hover { background: rgba(0,0,0,0.7); }
.dp-btn.prev { left: 12px; }
.dp-btn.next { right: 12px; }

/* Header row: title + price */
.dp-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 16px;
}
.dp-header h2 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0;
}
.dp-price {
  font-size: 1.1rem;
  font-weight: 800;
  color: #4E8F22;
}

/* Spec grid */
.dp-specs {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 10px;
  margin-bottom: 18px;
}
.dp-spec {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 12px 14px;
  border: 1px solid #e8f5e9;
}
.dp-spec .spec-label {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #888;
  display: block;
  margin-bottom: 4px;
}
.dp-spec .spec-value {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1a1a1a;
}
.dp-spec .spec-value i { color: #4E8F22; margin-right: 4px; }

/* Description box */
.dp-desc {
  background: #f0faf0;
  border-radius: 12px;
  padding: 16px 18px;
  border-left: 4px solid #4E8F22;
}
.dp-desc h4 {
  font-size: 0.85rem;
  font-weight: 700;
  color: #3B6E1A;
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 1px;
}
.dp-desc p {
  font-size: 0.88rem;
  color: #444;
  line-height: 1.7;
  margin: 0;
}

/* ── Property scroll strip ──────────────────────────────────── */
#propertyList {
  display: flex;
  overflow-x: auto;
  gap: 14px;
  padding: 4px 2px 10px;
  scrollbar-width: thin;
  scrollbar-color: #4E8F22 #f0f0f0;
}

.propertyCard {
  min-width: 200px;
  background: white;
  padding: 10px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  flex-shrink: 0;
}
.propertyCard:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.propertyCard img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 8px;
}
.propertyCard h4 {
  font-size: 0.85rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 4px;
}
.propertyCard p {
  font-size: 0.8rem;
  color: #4E8F22;
  font-weight: 600;
  margin: 0;
}
.propertyCard .badge {
  display: inline-block;
  font-size: 0.65rem;
  padding: 2px 8px;
  border-radius: 20px;
  margin-bottom: 4px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.badge-sale { background: #dcfce7; color: #166534; }
.badge-rent { background: #dbeafe; color: #1e40af; }

/* ── Overlay & popup ────────────────────────────────────────── */
#overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  backdrop-filter: blur(2px);
  z-index: 1999;
}

#popup {
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #fff;
  padding: 36px 40px;
  border-radius: 20px;
  z-index: 2000;
  width: 90%;
  max-width: 420px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.18);
}
#popup h3 {
  text-align: center;
  font-weight: 700;
  font-size: 1.2rem;
  color: #1a1a1a;
  margin: 0 0 20px;
}
#popup label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #888;
  letter-spacing: 1px;
  text-transform: uppercase;
  display: block;
  margin-bottom: 4px;
}
#popup input, #popup select {
  width: 100%;
  padding: 11px 14px;
  margin-bottom: 14px;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 0.95rem;
  font-family: 'Poppins', sans-serif;
  color: #495057;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s;
}
#popup input:focus, #popup select:focus { border-color: #4E8F22; }
#popup .btn-row { display: flex; gap: 10px; margin-top: 6px; }
#popup button {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 0.95rem;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  transition: all 0.25s ease;
}
#popup button:first-child { background: #4E8F22; color: white; }
#popup button:first-child:hover { background: #3B6E1A; transform: translateY(-2px); }
#popup button:last-child { background: transparent; color: #3B6E1A; border: 2px solid #4E8F22; }
#popup button:last-child:hover { background: #4E8F22; color: white; transform: translateY(-2px); }
</style>
</head>
<body>

<?php include 'header.php'; ?>

<div id="mapPage">

  <!-- Filter bar -->
  <div id="filterBar">
    <span>Filtrar:</span>
    <button class="filterBtn active" onclick="filterProperties('all', this)">Todas</button>
    <button class="filterBtn" onclick="filterProperties('rent', this)">Alquiler</button>
    <button class="filterBtn" onclick="filterProperties('sale', this)">Venta</button>
  </div>

  <!-- Map + right panel -->
  <div id="mapRow">

    <div id="mapContainer">
      <div id="map"></div>
      <div id="rangeInfo">
        <strong>Radio:</strong>
        <input type="range" min="1" max="100" value="20" id="radiusSlider">
        <span id="radiusValue">20</span> mi
      </div>
    </div>

    <div id="rightPanel">

      <!-- Selected property detail -->
      <div id="propertyDetails">
        <h2>Selecciona una propiedad</h2>
        <p>Haz clic en un marcador o en una tarjeta abajo.</p>
      </div>

      <!-- Properties inside radius -->
      <div id="sidebar">
        <h2>Dentro del Radio</h2>
        <p style="color:#bbb;font-size:0.8rem;margin:0">Mueve el círculo para ver propiedades.</p>
      </div>

    </div>
  </div>

  <!-- Horizontal property strip -->
  <div id="propertyList"></div>

  <!-- Expanded detail panel — opens below strip when More Info is clicked -->
  <div id="detailPanel">
    <button class="dp-close" onclick="closeDetailPanel()">✕ Cerrar</button>
    <div id="detailPanelContent"></div>
  </div>

</div>

<!-- Overlay -->
<div id="overlay" onclick="closePopup()"></div>

<!-- Create pinpoint popup (admin/owner only) -->
<div id="popup">
  <h3>Crear Pinpoint</h3>
  <label>Título</label>
  <input id="pinTitle" placeholder="Dejar en blanco para mantener el nombre">
  <label>Estado del item</label>
  <select id="pinFilter" onchange="filterPinItems()">
    <option value="no">Items sin pin</option>
    <option value="yes">Items ya pineados</option>
  </select>
  <label>Seleccionar item</label>
  <select id="pinItems"></select>
  <div class="btn-row">
    <button onclick="createPin()">Crear</button>
    <button onclick="closePopup()">Cancelar</button>
  </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

<script>
/* ══════════════════════════════════════════════════════════════
   DATA — injected from PHP / DB (via db_queries.php)
   Each property row has:
     id, title, type, price, sqft, beds, bath, laundry, parking,
     pets, mailbox, description, lat, lng, image, images[]
   Each location row has:
     id, name, lat, lng, pinpoint, direction, size, description
══════════════════════════════════════════════════════════════ */
const properties         = <?= $propertiesJson ?>;
const pinnedLocations    = <?= $pinnedJson ?>;
const unpinnedLocations  = <?= $unpinnedJson ?>;
const pinnedListLocations = <?= $pinnedListJson ?>;

// Normalise DB column names → JS-friendly aliases
properties.forEach(p => {
  p.desc   = p.description ?? '';   // map.php used 'desc'; DB column is 'description'
  p.images = (p.images || []).map(img => img.image_url);
});

/* ══════════════════════════════════════════════════════════════
   ROLE  (from PHP session)
══════════════════════════════════════════════════════════════ */
const role = <?= json_encode($role) ?>;

/* ══════════════════════════════════════════════════════════════
   STATE
══════════════════════════════════════════════════════════════ */
let currentFilter = 'all';
let propMarkers   = [];
let pinMarkers    = [];

/* ══════════════════════════════════════════════════════════════
   MAP INIT
══════════════════════════════════════════════════════════════ */
const map = L.map('map', { dragging: true }).setView([18.22, -66.5], 9);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

/* ══════════════════════════════════════════════════════════════
   RADIUS CIRCLE
══════════════════════════════════════════════════════════════ */
const PR_BOUNDS = { minLat:17.85, maxLat:18.55, minLng:-67.35, maxLng:-65.25 };

function clampToBounds(latlng) {
  return L.latLng(
    Math.max(PR_BOUNDS.minLat, Math.min(PR_BOUNDS.maxLat, latlng.lat)),
    Math.max(PR_BOUNDS.minLng, Math.min(PR_BOUNDS.maxLng, latlng.lng))
  );
}

let radiusMiles = 20;
let circle = L.circle([18.22, -66.5], {
  radius: radiusMiles * 1609.34,
  interactive: true,
  color: '#4E8F22',
  fillOpacity: 0.08
}).addTo(map);

let draggingCircle = false;
let dragOffset     = null;

circle.on('mousedown', function(e) {
  if (e.originalEvent.button !== 0) return;
  draggingCircle = true;
  dragOffset = {
    lat: e.latlng.lat - circle.getLatLng().lat,
    lng: e.latlng.lng - circle.getLatLng().lng
  };
  map.dragging.disable();
  L.DomEvent.stopPropagation(e);
});

map.on('mousemove', function(e) {
  if (!draggingCircle || !dragOffset) return;
  circle.setLatLng(clampToBounds(L.latLng(
    e.latlng.lat - dragOffset.lat,
    e.latlng.lng - dragOffset.lng
  )));
  updateRadiusSidebar();
});

map.on('mouseup', function() {
  if (draggingCircle) {
    draggingCircle = false;
    dragOffset = null;
    map.dragging.enable();
    map.panTo(circle.getLatLng(), { animate: true, duration: 0.4 });
  }
});

document.getElementById('map').addEventListener('mouseleave', function() {
  if (draggingCircle) {
    draggingCircle = false;
    dragOffset = null;
    map.dragging.enable();
  }
});

document.getElementById('radiusSlider').oninput = function() {
  radiusMiles = +this.value;
  document.getElementById('radiusValue').textContent = radiusMiles;
  circle.setRadius(radiusMiles * 1609.34);
  updateRadiusSidebar();
};

/* ══════════════════════════════════════════════════════════════
   PROPERTY MARKERS + CARDS
══════════════════════════════════════════════════════════════ */
function loadProperties() {
  propMarkers.forEach(m => map.removeLayer(m));
  propMarkers = [];

  const list = document.getElementById('propertyList');
  list.innerHTML = '';

  const filtered = properties.filter(p =>
    currentFilter === 'all' || p.type === currentFilter
  );

  filtered.forEach(property => {
    // Only place a map marker if the property has coordinates
    if (property.lat && property.lng) {
      const iconColor = property.type === 'sale' ? '#4E8F22' : '#1e40af';
      const icon = L.divIcon({
        html: `<div style="background:${iconColor};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4)"></div>`,
        className: '',
        iconSize: [14, 14],
        iconAnchor: [7, 7]
      });

      const marker = L.marker([parseFloat(property.lat), parseFloat(property.lng)], { icon }).addTo(map);
      marker.on('click', () => {
        showDetails(property);
        map.setView([parseFloat(property.lat), parseFloat(property.lng)], 13);
      });
      marker.propData = property;
      propMarkers.push(marker);
    }

    // Card always shows regardless of whether the property has coordinates
    const card = document.createElement('div');
    card.className = 'propertyCard';
    card.innerHTML = `
      <img src="${property.image}" alt="${property.title}">
      <span class="badge badge-${property.type}">${property.type === 'sale' ? 'Venta' : 'Alquiler'}</span>
      <h4>${property.title}</h4>
      <p>${property.price}</p>
    `;
    card.onclick = () => {
      if (property.lat && property.lng) {
        map.setView([parseFloat(property.lat), parseFloat(property.lng)], 13);
      }
      showDetails(property);
    };
    list.appendChild(card);
  });

  updateRadiusSidebar();
}

function showDetails(property) {
  const d = document.getElementById('propertyDetails');
  d.innerHTML = `
    <img src="${property.image}" alt="${property.title}">
    <span class="badge badge-${property.type}">${property.type === 'sale' ? 'Venta' : 'Alquiler'}</span>
    <h2>${property.title}</h2>
    <p><strong style="color:#4E8F22">${property.price}</strong></p>
    <p>
      <i class="fa-solid fa-ruler-combined" style="color:#4E8F22"></i> ${property.sqft} &nbsp;
      <i class="fa-solid fa-bed"            style="color:#4E8F22"></i> ${property.beds} &nbsp;
      <i class="fa-solid fa-bath"           style="color:#4E8F22"></i> ${property.bath}
    </p>
    <p style="font-size:0.82rem;color:#666;margin-top:6px">${property.desc}</p>
  `;
}

function filterProperties(type, btn) {
  currentFilter = type;
  document.querySelectorAll('.filterBtn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  loadProperties();
}

/* ══════════════════════════════════════════════════════════════
   RADIUS SIDEBAR
══════════════════════════════════════════════════════════════ */
function updateRadiusSidebar() {
  const s = document.getElementById('sidebar');
  s.innerHTML = '<h2>Dentro del Radio</h2>';

  const inside = propMarkers.filter(m =>
    map.distance(circle.getLatLng(), m.getLatLng()) <= circle.getRadius()
  );
  pinMarkers.forEach(m => {
    if (map.distance(circle.getLatLng(), m.getLatLng()) <= circle.getRadius()) {
      inside.push(m);
    }
  });

  if (inside.length === 0) {
    s.innerHTML += '<p style="color:#bbb;font-size:0.8rem;margin:0">Ninguna propiedad dentro del círculo.</p>';
    closeDetailPanel();
    return;
  }

  inside.forEach(m => {
    const c = document.createElement('div');
    c.className = 'radiusCard';

    if (m.propData) {
      const p = m.propData;
      c.innerHTML = `
        <div class="rc-top">
          <span class="rc-name">${p.title}<small>${p.price}</small></span>
        </div>
        <button class="btn-more-info">Más Info</button>
      `;
      c.querySelector('.rc-top').onclick = () => { map.setView(m.getLatLng(), 13); showDetails(p); };
      c.querySelector('.btn-more-info').onclick = e => { e.stopPropagation(); openDetailPanel(p); };
    } else if (m.locData) {
      c.innerHTML = `
        <div class="rc-top">
          <span class="rc-name">📍 ${m.locData.name}</span>
        </div>
      `;
      c.querySelector('.rc-top').onclick = () => map.setView(m.getLatLng(), 13);
    }

    s.appendChild(c);
  });
}

/* ══════════════════════════════════════════════════════════════
   DETAIL PANEL
══════════════════════════════════════════════════════════════ */
let dpSlide = 0;

function openDetailPanel(property) {
  dpSlide = 0;
  const images = property.images && property.images.length ? property.images : [property.image];

  const imgSlides = images.map(src => `<img src="${src}" alt="${property.title}">`).join('');

  const specs = [
    { label:'Tipo',     value: property.type === 'sale' ? 'Venta' : 'Alquiler', icon:'fa-tag' },
    { label:'Precio',   value: property.price,   icon:'fa-dollar-sign' },
    { label:'Tamaño',   value: property.sqft,    icon:'fa-ruler-combined' },
    { label:'Cuartos',  value: property.beds,    icon:'fa-bed' },
    { label:'Baños',    value: property.bath,    icon:'fa-bath' },
    { label:'Laundry',  value: property.laundry, icon:'fa-soap' },
    { label:'Parking',  value: property.parking, icon:'fa-car' },
    { label:'Mascotas', value: property.pets,    icon:'fa-paw' },
    { label:'Buzón',    value: property.mailbox, icon:'fa-mailbox' },
  ].filter(s => s.value);

  const specsHtml = specs.map(s => `
    <div class="dp-spec">
      <span class="spec-label">${s.label}</span>
      <span class="spec-value"><i class="fa-solid ${s.icon}"></i>${s.value}</span>
    </div>
  `).join('');

  const carouselNav = images.length > 1 ? `
    <button class="dp-btn prev" onclick="dpMove(-1)">&#8249;</button>
    <button class="dp-btn next" onclick="dpMove(1)">&#8250;</button>
  ` : '';

  document.getElementById('detailPanelContent').innerHTML = `
    <div class="dp-carousel">
      ${carouselNav}
      <div class="dp-track" id="dpTrack">${imgSlides}</div>
    </div>
    <div class="dp-header">
      <div>
        <span class="badge badge-${property.type}" style="margin-bottom:6px;display:inline-block">
          ${property.type === 'sale' ? 'Venta' : 'Alquiler'}
        </span>
        <h2>${property.title}</h2>
      </div>
      <div class="dp-price">${property.price}</div>
    </div>
    <div class="dp-specs">${specsHtml}</div>
    ${property.desc ? `
      <div class="dp-desc">
        <h4>Descripción</h4>
        <p>${property.desc}</p>
      </div>` : ''}
  `;

  const panel = document.getElementById('detailPanel');
  panel.style.display = 'block';
  panel.scrollIntoView({ behavior:'smooth', block:'nearest' });
}

function closeDetailPanel() {
  document.getElementById('detailPanel').style.display = 'none';
  document.getElementById('detailPanelContent').innerHTML = '';
  dpSlide = 0;
}

function dpMove(dir) {
  const track  = document.getElementById('dpTrack');
  if (!track) return;
  const slides = track.querySelectorAll('img');
  dpSlide = (dpSlide + dir + slides.length) % slides.length;
  track.style.transform = `translateX(-${dpSlide * 100}%)`;
}

/* ══════════════════════════════════════════════════════════════
   PINPOINT MARKERS  (loaded from DB via PHP, no localStorage)
══════════════════════════════════════════════════════════════ */
function loadPinMarkers() {
  pinMarkers.forEach(m => map.removeLayer(m));
  pinMarkers = [];

  pinnedLocations.forEach(loc => {
    if (!loc.lat || !loc.lng) return;

    const m = L.marker([parseFloat(loc.lat), parseFloat(loc.lng)]).addTo(map);
    m.locData = loc;

    if (role === 'admin' || role === 'owner') {
      m.on('click', function() {
        if (confirm(`¿Eliminar pinpoint "${loc.name}"?\n(El registro se conserva.)`)) {
          // Call server-side AJAX endpoint to remove pin from DB
          fetch('ajax/remove_pin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: loc.id })
          })
          .then(r => r.json())
          .then(res => {
            if (res.success) {
              // Remove from our local array and re-render
              const idx = pinnedLocations.findIndex(l => l.id === loc.id);
              if (idx > -1) pinnedLocations.splice(idx, 1);
              loadPinMarkers();
            } else {
              alert('Error al eliminar el pin: ' + (res.error || 'desconocido'));
            }
          });
        }
      });
    }

    pinMarkers.push(m);
  });

  updateRadiusSidebar();
}

/* ══════════════════════════════════════════════════════════════
   RIGHT-CLICK POPUP (admin/owner) — now writes to DB via AJAX
══════════════════════════════════════════════════════════════ */
let selectedLatLng = null;

document.getElementById('map').addEventListener('contextmenu', function(e) {
  e.preventDefault();
  if (role !== 'admin' && role !== 'owner') return;
  selectedLatLng = map.mouseEventToLatLng(e);
  openPopup();
});

function openPopup() {
  filterPinItems();   // populate dropdown based on current pinFilter value
  document.getElementById('popup').style.display   = 'block';
  document.getElementById('overlay').style.display = 'block';
}

function closePopup() {
  document.getElementById('popup').style.display   = 'none';
  document.getElementById('overlay').style.display = 'none';
  selectedLatLng = null;
}

/**
 * Populate the #pinItems <select> based on whether the user
 * wants to see unpinned or already-pinned locations.
 * Data comes from the PHP-injected arrays — no extra round-trip needed.
 */
function filterPinItems() {
  const f   = document.getElementById('pinFilter').value;
  const sel = document.getElementById('pinItems');
  sel.innerHTML = '';

  const source = f === 'no' ? unpinnedLocations : pinnedListLocations;

  if (source.length === 0) {
    const o = document.createElement('option');
    o.text = '— sin items —'; o.disabled = true;
    sel.appendChild(o);
    return;
  }
  source.forEach(loc => {
    const o = document.createElement('option');
    o.value = loc.id;
    o.text  = loc.name;
    sel.appendChild(o);
  });
}

function createPin() {
  const id   = parseInt(document.getElementById('pinItems').value);
  const name = document.getElementById('pinTitle').value.trim();
  if (!id || !selectedLatLng) return;

  fetch('ajax/set_pin.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id,
      lat:  selectedLatLng.lat,
      lng:  selectedLatLng.lng,
      name: name || null
    })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      // Add to pinnedLocations array so marker appears without full page reload
      const newLoc = {
        id,
        name:     name || res.name,
        lat:      selectedLatLng.lat,
        lng:      selectedLatLng.lng,
        pinpoint: 'yes'
      };
      pinnedLocations.push(newLoc);
      // Remove from unpinned array
      const idx = unpinnedLocations.findIndex(l => l.id === id);
      if (idx > -1) unpinnedLocations.splice(idx, 1);

      closePopup();
      loadPinMarkers();
    } else {
      alert('Error al crear el pin: ' + (res.error || 'desconocido'));
    }
  });
}

/* ══════════════════════════════════════════════════════════════
   INIT
══════════════════════════════════════════════════════════════ */
closePopup();
loadProperties();
loadPinMarkers();
</script>

<?php include 'footer.php'; ?>
</body>
</html>
