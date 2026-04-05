<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
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
                <a href="servicios/bienes-raices.html" class="card-link">
                    <div class="service-card p-4">
                        <i class="fa-solid fa-house service-icon"></i>
                        <h5 class="mt-3 fw-bold">Agente Inmobiliario</h5>
                        <p>Compra, venta y alquiler de propiedades.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="servicios/logistica-transporte.html" class="card-link">
                    <div class="service-card p-4">
                        <i class="fa-solid fa-truck-moving service-icon"></i>
                        <h5 class="mt-3 fw-bold">Servicio de Mudanza</h5>
                        <p>Mudanzas seguras y eficientes en todo PR.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="servicios/servicios-mejoras.html" class="card-link">
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
            <span class="tag">Nuestra Historia</span>
            <h3>Nacimos para hacer <span class="highlight">tu mudanza más fácil</span></h3>
            <p>Move &amp; Go PR nació con una misión clara: brindar soluciones integrales de vivienda y logística a las familias de Puerto Rico. Desde nuestros inicios, nos hemos dedicado a conectar personas con el hogar que merecen.</p>
            <p>Con años de experiencia en el mercado inmobiliario y de mudanzas locales, entendemos que cada traslado es único y merece atención personalizada.</p>
            <div class="stats-row">
                <div class="stat">
                    <span class="num">500+</span>
                    <span class="label">Mudanzas</span>
                </div>
                <div class="stat">
                    <span class="num">10+</span>
                    <span class="label">Años de exp.</span>
                </div>
                <div class="stat">
                    <span class="num">78</span>
                    <span class="label">Municipios</span>
                </div>
            </div>
        </div>
        <div class="about-img">
            <img src="img/GuaguaMove&Go.png" alt="Nuestra Historia">
        </div>
    </div>

    <div class="about-row reverse bg-tinted">
        <div class="about-text">
            <span class="tag">Nuestra Misión</span>
            <h3>Comprometidos con <span class="highlight">cada familia</span> en PR</h3>
            <p>Creemos que mudarse no debe ser un dolor de cabeza. Por eso, ofrecemos un servicio completo que va desde encontrar la propiedad ideal hasta trasladar tus pertenencias con el mayor cuidado.</p>
            <p>Nuestro equipo profesional está disponible para guiarte en cada paso del proceso, con transparencia, rapidez y <span class="highlight">confianza</span>.</p>
        </div>
        <div class="about-img">
            <img src="img/droneMove&Go.png" alt="Nuestra Misión">
        </div>
    </div>

    <div class="about-row">
        <div class="about-text">
            <span class="tag">Nuestros Valores</span>
            <h3>Honestidad, <span class="highlight">excelencia</span> y servicio</h3>
            <p>En Move &amp; Go PR los valores no son solo palabras — son la base de cada interacción. Tratamos tu hogar y tus bienes como si fueran nuestros, con el respeto y la dedicación que se merecen.</p>
            <p>Somos un equipo local, orgulloso de servir a nuestra isla con los más altos estándares de calidad y profesionalismo.</p>
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
