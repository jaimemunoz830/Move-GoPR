<?php
/*--CONFIGURACIÓN INICIAL---------*/
define('MOVE_GO_APP', true);   // requerido por db_queries.php para verificación de seguridad
require 'config.php';          // establece $pdo (conexión PDO)
require_once 'db_queries.php'; // todas las funciones de BD están aquí

if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? 'viewer';

/*--CARGA DE DATOS DESDE LA BASE DE DATOS---------*/
$properties        = getProperties($pdo);
$pinnedLocations   = getPinnedLocations($pdo);
$allLocations      = getLocations($pdo);
$unpinnedLocations = getLocations($pdo, 'no');
$pinnedList        = getLocations($pdo, 'yes');

// Codifica los datos para ser usados en JavaScript
$propertiesJson   = json_encode($properties,         JSON_HEX_TAG | JSON_HEX_APOS);
$locationsJson    = json_encode($allLocations,        JSON_HEX_TAG | JSON_HEX_APOS);
$pinnedJson       = json_encode($pinnedLocations,     JSON_HEX_TAG | JSON_HEX_APOS);
$unpinnedJson     = json_encode($unpinnedLocations,   JSON_HEX_TAG | JSON_HEX_APOS);
$pinnedListJson   = json_encode($pinnedList,          JSON_HEX_TAG | JSON_HEX_APOS);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mapa de Propiedades — Move & Go PR</title>

<!-- Fuentes e íconos -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<!-- Estilos del sitio (incluye todos los estilos del mapa) -->
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/map_styling.css">

<style>
  /* Controles de Leaflet por debajo del header sticky */
  .leaflet-top.leaflet-left {
    top: 12px !important;
  }
</style>
</head>

<!-- Lightbox del carrusel -->
<div id="carouselLightbox" onclick="closeLightbox(event)">
  <button class="lb-close" onclick="closeLightbox()">✕</button>
  <img id="lightboxImg" src="" alt="">
</div>

<!-- La clase page-map activa los estilos específicos del mapa en styles.css -->
<body class="page-map">

<?php include 'header.php'; ?>

<!-- =====================================================
     ESTRUCTURA PRINCIPAL DE LA PÁGINA
     Contiene: barra de filtros, fila del mapa + panel
     derecho, tira horizontal de propiedades y el panel
     de detalles expandible. El JS rellena todos los
     elementos dinámicamente.
     ===================================================== -->
<div id="mapPage">

  <!-- Barra de filtros -->
  <div id="filterBar">
    <span>Filtrar:</span>
    <button class="filterBtn active" onclick="filterProperties('all', this)">Todas</button>
    <button class="filterBtn" onclick="filterProperties('rent', this)">Alquiler</button>
    <button class="filterBtn" onclick="filterProperties('sale', this)">Venta</button>
  </div>

  <!-- Fila principal: mapa + panel derecho -->
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
      <div id="propertyDetails">
        <h2>Selecciona una propiedad</h2>
        <p>Haz clic en un marcador o en una tarjeta abajo.</p>
      </div>
      <div id="sidebar">
        <h2>Dentro del Radio</h2>
        <p style="color:#bbb;font-size:0.8rem;margin:0">Mueve el círculo para ver propiedades.</p>
      </div>
    </div>
  </div>

  <!-- Tira horizontal de tarjetas de propiedades -->
  <div id="propertyList"></div>

  <!-- Panel de detalles expandido (se abre al presionar "Más Info") -->
  <div id="detailPanel">
    <button class="dp-close" onclick="closeDetailPanel()">✕ Cerrar</button>
    <div id="detailPanelContent"></div>
  </div>

</div>

<!-- Fondo oscuro del popup de administrador -->
<div id="overlay" onclick="closePopup()"></div>

<!-- Modal para crear pinpoint (solo admins/owners) -->
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

<!-- Scripts -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

<script>

  /*--LIGHTBOX DEL CARRUSEL---------*/
  /* Al hacer clic en una imagen del carrusel se abre un
     popup de pantalla completa con la imagen ampliada.
     Clic en la X o fuera de la imagen lo cierra.       */
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('carouselLightbox').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeLightbox(e) {
  /* Si el clic fue sobre la imagen no cierra */
  if (e && e.target.id === 'lightboxImg') return;
  document.getElementById('carouselLightbox').classList.remove('active');
  document.getElementById('lightboxImg').src = '';
  document.body.style.overflow = '';
}

/* Cierra con la tecla Escape */
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeLightbox();
});

/*--DATOS INYECTADOS DESDE PHP / BASE DE DATOS---------*/
/* Las variables PHP se serializan como JSON y se asignan
   directamente a constantes de JavaScript. Las imágenes
   se extraen del subarreglo anidado que devuelve la BD. */
const properties          = <?= $propertiesJson ?>;
const pinnedLocations     = <?= $pinnedJson ?>;
const unpinnedLocations   = <?= $unpinnedJson ?>;
const pinnedListLocations = <?= $pinnedListJson ?>;

properties.forEach(p => {
  p.desc   = p.description ?? '';
  p.images = (p.images || []).map(img => img.image_url);
});

/*--ROL DE USUARIO---------*/
/* El rol se lee de la sesión PHP y determina qué acciones
   están disponibles (crear/eliminar pinpoints).          */
const role = <?= json_encode($role) ?>;

/*--ESTADO GLOBAL---------*/
/* Filtro activo y arreglos de marcadores del mapa.      */
let currentFilter = 'all';
let propMarkers   = [];
let pinMarkers    = [];

/*--INICIALIZACIÓN DEL MAPA LEAFLET---------*/
/* Crea el mapa centrado en Puerto Rico y añade la capa
   de teselas de OpenStreetMap.                          */
const map = L.map('map', { dragging: true }).setView([18.22, -66.5], 9);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

/*--CÍRCULO DE RADIO ARRASTRABLE---------*/
/* Crea un círculo verde arrastrable sobre el mapa.
   Los eventos de ratón permiten reposicionarlo con drag.
   Queda limitado al bounding box de Puerto Rico.
   El slider de radio actualiza el tamaño en tiempo real. */
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

/*--DRAG CON MOUSE---------*/
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

/*--DRAG TÁCTIL (MÓVIL)---------*/
/* Permite arrastrar el círculo con el dedo en dispositivos
   táctiles. Solo activa si el toque inicia dentro del radio. */
map.getContainer().addEventListener('touchstart', function(e) {
  if (e.touches.length !== 1) return;
  const touch = e.touches[0];
  const rect  = map.getContainer().getBoundingClientRect();
  const latlng = map.containerPointToLatLng(
    L.point(touch.clientX - rect.left, touch.clientY - rect.top)
  );
  const dist = map.distance(latlng, circle.getLatLng());
  if (dist > circle.getRadius()) return;
  draggingCircle = true;
  dragOffset = {
    lat: latlng.lat - circle.getLatLng().lat,
    lng: latlng.lng - circle.getLatLng().lng
  };
  map.dragging.disable();
  e.preventDefault();
}, { passive: false });

map.getContainer().addEventListener('touchmove', function(e) {
  if (!draggingCircle || !dragOffset) return;
  e.preventDefault();
  const touch  = e.touches[0];
  const rect   = map.getContainer().getBoundingClientRect();
  const latlng = map.containerPointToLatLng(
    L.point(touch.clientX - rect.left, touch.clientY - rect.top)
  );
  circle.setLatLng(clampToBounds(L.latLng(
    latlng.lat - dragOffset.lat,
    latlng.lng - dragOffset.lng
  )));
  updateRadiusSidebar();
}, { passive: false });

map.getContainer().addEventListener('touchend', function() {
  if (draggingCircle) {
    draggingCircle = false;
    dragOffset = null;
    map.dragging.enable();
    map.panTo(circle.getLatLng(), { animate: true, duration: 0.4 });
  }
});

/*--SLIDER DE RADIO---------*/
document.getElementById('radiusSlider').oninput = function() {
  radiusMiles = +this.value;
  document.getElementById('radiusValue').textContent = radiusMiles;
  circle.setRadius(radiusMiles * 1609.34);
  updateRadiusSidebar();
};

/*--MARCADORES Y TIRA DE TARJETAS DE PROPIEDADES---------*/
/* Itera el arreglo de propiedades filtrado, coloca un
   marcador en el mapa por cada una y construye la tira
   horizontal de tarjetas en la parte inferior.
   Incluye el botón "Más Info" que abre el panel
   expandido de detalles al presionarlo.                 */
function loadProperties() {
  propMarkers.forEach(m => map.removeLayer(m));
  propMarkers = [];

  const list = document.getElementById('propertyList');
  list.innerHTML = '';

  const filtered = properties.filter(p => currentFilter === 'all' || p.type === currentFilter);

  filtered.forEach(property => {
    /* Color del marcador según tipo de propiedad */
    if (property.lat && property.lng) {
      const iconColor = property.type === 'sale' ? '#4E8F22' : '#1e40af';
      const icon = L.divIcon({
        html: `<div style="background:${iconColor};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4)"></div>`,
        className: '',
        iconSize: [14, 14],
        iconAnchor: [7, 7]
      });

      /* Marcador en el mapa */
      const marker = L.marker([parseFloat(property.lat), parseFloat(property.lng)], { icon }).addTo(map);
      marker.on('click', () => {
        showDetails(property);
        map.setView([parseFloat(property.lat), parseFloat(property.lng)], 13);
      });
      marker.propData = property;
      propMarkers.push(marker);
    }

    /* Tarjeta en la tira horizontal */
    const card = document.createElement('div');
    card.className = 'propertyCard';
    card.innerHTML = `
      <img src="${property.image}" alt="${property.title}">
      <span class="badge badge-${property.type}">${property.type === 'sale' ? 'Venta' : 'Alquiler'}</span>
      <h4>${property.title}</h4>
      <p>${property.price}</p>
      <button class="btn-card-info">Más Info</button>
    `;

    /* Clic en la tarjeta: centra mapa y muestra detalle rápido */
    card.addEventListener('click', (e) => {
      if (e.target.classList.contains('btn-card-info')) return; /* evita doble disparo */
      if (property.lat && property.lng) map.setView([parseFloat(property.lat), parseFloat(property.lng)], 13);
      showDetails(property);
    });

    /* Clic en "Más Info": abre el panel expandido */
    card.querySelector('.btn-card-info').addEventListener('click', (e) => {
      e.stopPropagation();
      if (property.lat && property.lng) map.setView([parseFloat(property.lat), parseFloat(property.lng)], 13);
      showDetails(property);
      openDetailPanel(property);
    });

    list.appendChild(card);
  });

  updateRadiusSidebar();
}

/*--PANEL DERECHO DE DETALLES---------*/
/* Rellena el panel derecho con los datos básicos
   de la propiedad seleccionada.                        */
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

/*--FILTRO DE PROPIEDADES---------*/
function filterProperties(type, btn) {
  currentFilter = type;
  document.querySelectorAll('.filterBtn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  loadProperties();
}

/*--SIDEBAR DE RADIO---------*/
/* Recalcula qué marcadores (propiedades y pinpoints)
   quedan dentro del círculo cada vez que éste se mueve
   o cambia de tamaño. Renderiza una radiusCard por cada
   uno y conecta el botón "Más Info" al panel expandido. */
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
      c.querySelector('.rc-top').onclick       = () => { map.setView(m.getLatLng(), 13); showDetails(p); };
      c.querySelector('.btn-more-info').onclick = (e) => { e.stopPropagation(); openDetailPanel(p); };
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

/*--PANEL DE DETALLE EXPANDIDO---------*/
/* Despliega un panel de ancho completo bajo la tira de
   propiedades con un carrusel de fotos (cíclico en
   ambas direcciones), cuadrícula de especificaciones y
   descripción. dpMove() maneja la navegación del
   carrusel con wrap-around en los extremos.            */
let dpSlide       = 0;
let dpTotalSlides = 0;

function openDetailPanel(property) {
  dpSlide = 0;

  const images = (property.images && property.images.length > 0)
    ? property.images
    : [property.image];

  dpTotalSlides = images.length;

  const imgSlides = images.map((src, i) =>
    `<img src="${src}" alt="${property.title} — foto ${i + 1}" loading="lazy"
          style="cursor:zoom-in"
          onclick="openLightbox('${src}')">`
  ).join('');

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
      <span class="spec-value"><i class="fa-solid ${s.icon}"></i> ${s.value}</span>
    </div>
  `).join('');

  /* Los botones del carrusel solo aparecen si hay más de una imagen */
  const carouselNav = dpTotalSlides > 1 ? `
    <button class="dp-btn prev" onclick="dpMove(-1)" title="Foto anterior">&#8249;</button>
    <button class="dp-btn next" onclick="dpMove(1)"  title="Foto siguiente">&#8250;</button>
  ` : '';

  /* Indicador de posición (puntos) */
  const dotsHtml = dpTotalSlides > 1
    ? `<div class="dp-dots" id="dpDots">
        ${images.map((_, i) => `<span class="dp-dot${i === 0 ? ' active' : ''}" onclick="dpGoTo(${i})"></span>`).join('')}
       </div>`
    : '';

  document.getElementById('detailPanelContent').innerHTML = `
    <div class="dp-carousel">
      ${carouselNav}
      <div class="dp-track" id="dpTrack">${imgSlides}</div>
      ${dotsHtml}
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

  /* Inyecta estilos de los puntos indicadores (solo primera vez) */
  if (!document.getElementById('dpDotStyles')) {
    const st = document.createElement('style');
    st.id = 'dpDotStyles';
    st.textContent = `
      .dp-dots {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
        z-index: 10;
      }
      .dp-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        cursor: pointer;
        transition: background 0.2s, transform 0.2s;
      }
      .dp-dot.active {
        background: #fff;
        transform: scale(1.3);
      }
    `;
    document.head.appendChild(st);
  }

  const panel = document.getElementById('detailPanel');
  panel.style.display = 'block';
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeDetailPanel() {
  document.getElementById('detailPanel').style.display = 'none';
  document.getElementById('detailPanelContent').innerHTML = '';
  dpSlide = 0;
  dpTotalSlides = 0;
}

/*--NAVEGACIÓN DEL CARRUSEL---------*/
/* Navega el carrusel de forma cíclica en ambas
   direcciones y actualiza los puntos indicadores.      */
function dpMove(dir) {
  if (dpTotalSlides === 0) return;
  dpGoTo((dpSlide + dir + dpTotalSlides) % dpTotalSlides);
}

function dpGoTo(index) {
  dpSlide = index;
  const track = document.getElementById('dpTrack');
  if (track) track.style.transform = `translateX(-${dpSlide * 100}%)`;

  document.querySelectorAll('.dp-dot').forEach((dot, i) => {
    dot.classList.toggle('active', i === dpSlide);
  });
}

/*--MARCADORES DE PINPOINTS (BD VÍA PHP)---------*/
/* Lee las ubicaciones pineadas desde los datos inyectados
   por PHP y coloca un marcador Leaflet por cada una.
   Los admins pueden hacer clic para eliminar el pin;
   la operación se envía al servidor mediante AJAX.     */
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
          fetch('ajax/remove_pin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: loc.id })
          })
          .then(r => r.json())
          .then(res => {
            if (res.success) {
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

/*--POPUP DE ADMINISTRADOR Y CREACIÓN DE PIN---------*/
/* Clic derecho en el mapa abre un modal para que los
   admins elijan un registro de ubicación y lo pinen en
   las coordenadas clickeadas. La operación se persiste
   en la BD mediante una llamada AJAX.                  */
let selectedLatLng = null;

document.getElementById('map').addEventListener('contextmenu', function(e) {
  e.preventDefault();
  if (role !== 'admin' && role !== 'owner') return;
  selectedLatLng = map.mouseEventToLatLng(e);
  openPopup();
});

function openPopup() {
  filterPinItems();
  document.getElementById('popup').style.display   = 'block';
  document.getElementById('overlay').style.display = 'block';
}

function closePopup() {
  document.getElementById('popup').style.display   = 'none';
  document.getElementById('overlay').style.display = 'none';
  selectedLatLng = null;
}

/*--FILTRO DEL DROPDOWN DE ITEMS---------*/
/* El dropdown se filtra según el estado de pinpoint
   del registro usando los arreglos inyectados por PHP. */
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
    o.value = loc.id; o.text = loc.name;
    sel.appendChild(o);
  });
}

/*--CREAR PIN EN LA BASE DE DATOS---------*/
/* Envía las coordenadas y el id del item seleccionado
   al endpoint AJAX. Si tiene éxito, actualiza los
   arreglos locales y recarga los marcadores del mapa.  */
function createPin() {
  const id   = parseInt(document.getElementById('pinItems').value);
  const name = document.getElementById('pinTitle').value.trim();
  if (!id || !selectedLatLng) return;

  fetch('ajax/set_pin.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, lat: selectedLatLng.lat, lng: selectedLatLng.lng, name: name || null })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      pinnedLocations.push({ id, name: name || res.name, lat: selectedLatLng.lat, lng: selectedLatLng.lng, pinpoint: 'yes' });
      const idx = unpinnedLocations.findIndex(l => l.id === id);
      if (idx > -1) unpinnedLocations.splice(idx, 1);
      closePopup();
      loadPinMarkers();
    } else {
      alert('Error al crear el pin: ' + (res.error || 'desconocido'));
    }
  });
}

/*--INICIALIZACIÓN---------*/
/* Cierra el popup y carga los marcadores de propiedades
   y de pinpoints al cargar la página.                  */
closePopup();
loadProperties();
loadPinMarkers();
</script>

<?php include 'footer.php'; ?>
</body>
</html>
