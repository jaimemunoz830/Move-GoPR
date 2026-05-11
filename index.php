<!-- ===========================================
  Aqui han trabajado:
    Alejandro Irizarry, Gustavo Pagan, Kenneth J. Gonzalez, Juan D. Torres
============================================== -->

<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move & Go PR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<!-- HEADER -->
<header class="hero-main text-center">
    <div class="container py-5">
        <img src="img/moveandgopr-logo-solito.png" alt="Logo Move & Go PR" class="hero-logo">
    </div>
</header>

<!-- SERVICIOS -->
<section id="servicios" class="services-section py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-5">Nuestros Servicios</h2>

        <div class="row g-4">

            <div class="col-md-4">
                <a href="map.php" class="card-link">
                    <div class="service-card p-4">
                        <i class="fa-solid fa-house service-icon"></i>
                        <h5 class="mt-3 fw-bold">Agente Inmobiliario</h5>
                        <p>Compra, venta y alquiler de propiedades.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="servicios-mudanzas.html" class="card-link">
                    <div class="service-card p-4">
                        <i class="fa-solid fa-truck-moving service-icon"></i>
                        <h5 class="mt-3 fw-bold">Servicio de Mudanza</h5>
                        <p>Mudanzas seguras y eficientes en todo PR.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="servicios-mejoras.html" class="card-link">
                    <div class="service-card p-4">
                        <i class="fa-solid fa-screwdriver-wrench service-icon"></i>
                        <h5 class="mt-3 fw-bold">Mantenimiento</h5>
                        <p>Reparaciones y mantenimiento general.</p>
                    </div>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- SOBRE NOSOTROS -->
<section class="sobre-nosotros">
    <h2 class="section-title">Sobre Nosotros</h2>

    <div class="about-row">
        <div class="about-text">
            <span class="tag">Nuestra Misión</span>
            <h3>Buscamos que tu mudanza <span class="highlight">sea accesible</span></h3>
            <p>Move &amp; Go fue nació con el objetivo de no solo facilitar el acceso a la compra y renta de bienes raices en nuestra isla, sino también conectarte con servicios de mudanzas y mantenimiento.</p>
            <p>Trabajamos en toda la isla utilizando nuestra experiencia y recursos para suplir las necesidades de nuestros clientes.</p>
            
        </div>
        <div class="about-img">
            <img src="img/GuaguaMove&Go.jpeg" alt="Nuestra Historia">
        </div>
    </div>

    <div class="about-row reverse bg-tinted">
        <div class="about-text">
            <span class="tag">Nuestra Valores</span>
            <h3>Responsabilidad, Profesionalismo y Compromiso <span class="highlight">en cada trabajo</span></h3>
            <p>Creemos que mudarse no debe ser un dolor de cabeza. Por eso, ofrecemos un servicio completo que va desde encontrar la propiedad ideal hasta trasladar tus pertenencias con el mayor cuidado.</p>
            <p>Nuestro equipo profesional está disponible para guiarte en cada paso del proceso, con transparencia, rapidez y <span class="highlight">confianza</span>.</p>
        <div class="stats-row">
                <div class="stat">
                    <span class="valor">Responsabilidad</span>
                </div>
                <div class="stat">
                    <span class="valor">Profesionalismo</span>
                </div>
                <div class="stat">
                    <span class="valor">Compromiso</span>
                </div>
            </div>
        </div>
        <div class="about-img">
            <img src="img/droneMove&Go.png" alt="Nuestra Misión">
        </div>
    </div>

    <div class="about-row">
        <div class="about-text">
            <span class="tag">Nuestro Agente Principal</span>
            <h3>Yo soy <span class="highlight">Randall Torres</span></h3>
            <p>Move &amp; Go nació con el objetivo de simplificar y volver a accesible todos los servicios necesarios en el mundo de las Bienes Raices.</p>
            <p>Mi equipo y yo estamos listos para darte el mejor servicio posible, siempre acatando los más altos estándares de calidad y profesionalismo.</p>
        </div>
        <div class="about-img">
            <div class="realtor-card">
                <div class="realtor-photo-container">
                    <img src="img/Randall-Move&GoPR.jpeg" alt="Foto del Realtor" class="realtor-photo">
                </div>
                <div class="realtor-info">
                    <h2 class="realtor-name">Randall Torres</h2>
                    <p class="realtor-title">Agente Inmobiliario</p>
                    <p class="realtor-contact"><i class="fas fa-phone-alt"></i> 787-543-8201</p>
                    <p class="realtor-contact"><i class="fas fa-building"></i> Move & Go PR</p>
                </div>
                <div class="realtor-buttons">
                    <a href="tel:7875438201" class="btn"><i class="fas fa-phone"></i> Llamar</a>
                    <a href="mailto:randall@moveandgo.com" class="btn"><i class="fas fa-envelope"></i> Email</a>
                </div>
            </div>
        </div>
    </div>

</section>

<?php include 'footer.php'; ?>
</body>
</html>
