<?php
require 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Property Map</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>
body{
  margin:0;
  font-family:Arial, sans-serif;
  background:#f3f4f6;
}

/* BACK BUTTON */
.backBtn{
  position:absolute;
  top:20px;
  left:20px;
  background:#dc2626;
  color:white;
  border:none;
  padding:10px 15px;
  border-radius:8px;
  cursor:pointer;
  z-index:1000;
}

/* FILTER BUTTONS */
.filterBtns{
  margin:70px 20px 10px 20px;
}

.filterBtns button{
  margin-right:10px;
  padding:8px 15px;
  border:none;
  border-radius:8px;
  background:#1e3a8a;
  color:white;
  cursor:pointer;
}

/* MAP LAYOUT */
.mapContainer{
  display:flex;
  padding:20px;
  gap:20px;
}

#map{
  flex:2;
  height:500px;
  border-radius:15px;
  box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

#propertyDetails{
  flex:1;
  background:white;
  padding:20px;
  border-radius:15px;
  box-shadow:0 5px 15px rgba(0,0,0,0.2);
  overflow-y:auto;
  max-height:500px;
}

#propertyDetails img{
  width:100%;
  border-radius:12px;
  margin-bottom:15px;
}

#propertyList{
  display:flex;
  overflow-x:auto;
  gap:15px;
  margin:20px;
}

.propertyCard{
  min-width:220px;
  background:white;
  padding:10px;
  border-radius:10px;
  box-shadow:0 3px 10px rgba(0,0,0,0.15);
  cursor:pointer;
}

.propertyCard img{
  width:100%;
  height:140px;
  object-fit:cover;
  border-radius:8px;
}
</style>
</head>

<body>

<button class="backBtn" onclick="goBack()">Back</button>

<div class="filterBtns">
  <button onclick="filterProperties('all')">All</button>
  <button onclick="filterProperties('rent')">Rent</button>
  <button onclick="filterProperties('sale')">Sale</button>
</div>

<div class="mapContainer">
  <div id="map"></div>
  <div id="propertyDetails">
    <h2>Select a property</h2>
    <p>Click a marker or house below</p>
  </div>
</div>

<div id="propertyList"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
// BACK BUTTON FUNCTION
function goBack(){
  window.history.back();
}

// PROPERTY DATA
const properties = [
  {title:"Luxury Villa", type:"sale", lat:18.3358, lng:-65.6520, price:"$850,000", sqft:"4200 sqft", beds:"5 Beds", bath:"4 Baths", image:"https://images.unsplash.com/photo-1600585154340-be6161a56a0c", desc:"Oceanfront villa with private pool."},
  {title:"Modern Family Home", type:"sale", lat:18.4655, lng:-66.1057, price:"$450,000", sqft:"2800 sqft", beds:"4 Beds", bath:"3 Baths", image:"https://images.unsplash.com/photo-1570129477492-45c003edd2be", desc:"Spacious modern home near the city."},
  {title:"Cozy Apartment", type:"rent", lat:18.2011, lng:-67.1396, price:"$1,800/mo", sqft:"1200 sqft", beds:"2 Beds", bath:"2 Baths", image:"https://images.unsplash.com/photo-1507089947368-19c1da9775ae", desc:"Comfortable apartment close to amenities."},
  {title:"Suburban House", type:"rent", lat:18.3800, lng:-66.0600, price:"$2,200/mo", sqft:"1800 sqft", beds:"3 Beds", bath:"2 Baths", image:"https://images.unsplash.com/photo-1568605114967-8130f3a36994", desc:"Perfect family house in quiet neighborhood."},
  {title:"Cozy Studio", type:"rent", lat:18.4350, lng:-66.0550, price:"$900/mo", sqft:"550 sqft", beds:"1 Bed", bath:"1 Bath", image:"https://images.unsplash.com/photo-1522708323590-d24dbb6b0267", desc:"Affordable studio perfect for students."},
  {title:"Oceanview Mansion", type:"sale", lat:18.4800, lng:-66.0000, price:"$1,200,000", sqft:"5000 sqft", beds:"6 Beds", bath:"5 Baths", image:"https://images.unsplash.com/photo-1605276374104-dee2a0ed3cd6", desc:"Luxury estate with infinity pool and ocean views."}
];

let currentFilter = 'all';
let markers = [];

// INITIALIZE MAP
const map = L.map('map').setView([18.2208, -66.5901], 9);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
  attribution:'© OpenStreetMap'
}).addTo(map);

// LOAD PROPERTIES
function loadProperties(){
  markers.forEach(m=>map.removeLayer(m));
  markers=[];

  const list=document.getElementById('propertyList');
  list.innerHTML='';

  properties.filter(p=>currentFilter==='all'||p.type===currentFilter)
    .forEach(property=>{
      // MARKER
      const marker = L.marker([property.lat, property.lng]).addTo(map);
      marker.on('click',()=>showDetails(property));
      markers.push(marker);

      // CARD
      const card=document.createElement('div');
      card.className='propertyCard';
      card.innerHTML=`
        <img src="${property.image}">
        <h4>${property.title}</h4>
        <p>${property.price}</p>
      `;
      card.onclick=()=>{
        map.setView([property.lat,property.lng],13);
        showDetails(property);
      };
      list.appendChild(card);
    });
}

// SHOW PROPERTY DETAILS
function showDetails(property){
  const details=document.getElementById('propertyDetails');
  details.innerHTML=`
    <img src="${property.image}">
    <h2>${property.title}</h2>
    <p><strong>${property.price}</strong></p>
    <p>${property.sqft} • ${property.beds} • ${property.bath}</p>
    <p>${property.desc}</p>
  `;
}

// FILTER PROPERTIES
function filterProperties(type){
  currentFilter = type;
  loadProperties();
}

// INITIAL LOAD
loadProperties();

</script>
</body>
</html>
