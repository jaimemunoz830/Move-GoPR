<?php
require_once 'config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoveAndGoPR | Inicio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<header class="hero">
  
  <div class="overlay"></div>
  <div class="hero-content">
    <h1>MoveAnd<span class="green">GoPR</span></h1>
    <p>Soluciones en bienes raíces, mudanza y mantenimiento</p>
    
    <div class="hero-buttons">
      <a href="#services" class="btn dark">Ver Servicios</a>
      <button class="btn pink" onclick="alert('Instagram message sent!')">Mensaje por Instagram</button>
      <button class="btn blue" onclick="alert('Facebook message sent!')">Mensaje por Facebook</button>
      <a href="db_test.php" class="btn dark" style="border: 1px solid #80ff00;">DB Demo Panel</a>
    </div>
  </div>
</header>

<section id="services" class="services">
  <h2>Nuestros Servicios</h2>

  <div class="service-cards">
    <div class="card" onclick="location.href='map.php'">
      <div class="icon">🏠</div>
      <h3>Agente Inmobiliario</h3>
      <p>Asesoría para compra, venta y alquiler de propiedades.</p>
    </div>

    <div class="card" onclick="location.href='moving.php'">
      <div class="icon">🚚</div>
      <h3>Servicio de Mudanza</h3>
      <p>Mudanzas seguras y confiables en todo Puerto Rico.</p>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>