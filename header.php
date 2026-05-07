<!-- ===========================================
  Aquí han trabajado:
    Esteban G. Echevarria Perez, Jaime A. Muñoz
============================================== -->

<?php
//  ===========================================
//   Configuración de sesión
// ============================================== 
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- ===========================================
  Estructura del header
  Los enlaces de administrador solo se muestran si el rol
  de sesión es 'admin'. El botón de cerrar
  sesión aparece para cualquier usuario logueado.
============================================== -->
<header class="main-header">
    <div class="logo">
        <a href="index.php">
            <img src="img/moveandgopr-logo-solito.png" alt="Move & Go PR" class="header-logo">
        </a>
    </div>
    
    <nav class="nav-links">
        <a href="index.php">Inicio</a>
        <a href="moving.php">Mudanza</a>
        <a href="map.php">Propiedades</a>
        
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="dashboard.php" class="btn-admin-tool">Dashboard</a>
            <a href="db_test.php" class="btn-admin-tool">DB Test</a>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        <?php elseif(isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        <?php endif; ?>
    </nav>
</header>

<style>
    /* ===========================================
       Estilos del header
       Queda sobre todos los elementos de la 
       página (z-index: 2000).
    ============================================== */
    .main-header {
        background: linear-gradient(135deg, #4E8F22 0%, #78B833 50%, #BFEA8C 100%);
        padding: 0.6rem 5%;
        height: 70px;
        overflow: hidden;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        position: sticky;
        top: 0;
        z-index: 2000;
        }

    /* ===========================================
       Logo
    ============================================== */
    .header-logo {
        height: 100%;      
        max-height: 95px;
        width: auto;
        display: block;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        transition: opacity 0.2s ease;
    }

    .header-logo:hover {
        opacity: 0.85;
    }

    /* ===========================================
       Navegación principal
    ============================================== */
    .nav-links {
        display: flex;
        gap: 24px;
        align-items: center;
    }

    .nav-links a {
        color: white;
        text-decoration: none;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        transition: color 0.2s ease;
        text-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }

    .nav-links a:hover {
        color: #1a3a0a;
    }

    /* ===========================================
       Botones de herramientas de administrador
       Visibles únicamente para el rol admin
    ============================================== */
    .btn-admin-tool {
        border: 1px solid rgba(255,255,255,0.7);
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 0.78rem !important;
        background: rgba(255,255,255,0.15);
        transition: background 0.2s ease !important;
    }

    .btn-admin-tool:hover {
        background: rgba(255,255,255,0.3) !important;
        color: white !important;
    }

    /* ===========================================
       Botón de cerrar sesión
    ============================================== */
    .btn-logout {
        background: rgba(0,0,0,0.2);
        color: white !important;
        padding: 6px 16px;
        border-radius: 5px;
        font-size: 0.85rem !important;
        transition: background 0.2s ease !important;
    }

    .btn-logout:hover {
        background: rgba(0,0,0,0.35) !important;
        color: white !important;
    }

    /* ===========================================
       Responsive — pantallas pequeñas
       Reduce alturas y tamaños de fuente para
       móvil. El enlace al mapa conserva padding
       derecho para no quedar cortado.
    ============================================== */
    @media (max-width: 768px) {
        .main-header {
            height: 56px;
            padding: 0.4rem 3%;
            gap: 8px;
        }

        .header-logo {
            height: 65px;        
            max-height: 65px;
        }

        .nav-links {
            gap: 10px;
            flex-shrink: 1;
            overflow: hidden;
        }

        .nav-links a {
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .nav-links a[href="map.php"] {
            padding-right: 36px;
        }

        .btn-logout {
            font-size: 0.62rem !important;
            padding: 3px 8px;
        }

        .btn-admin-tool {
            font-size: 0.60rem !important;
            padding: 3px 6px;
        }
    }
</style>
