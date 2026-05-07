<!-- ===========================================
  Aquí han trabajado:
    Esteban G Echevarria, Jaime A Muñoz, Christian J Lespier
============================================== -->

<?php

/**
 * DB tables needed:
 *   properties      — one row per listing
 *   property_images — one row per image, linked by property_id
 *   locations       — admin map pinpoints
 *
 * db_queries.php — Move & Go PR
 * ─────────────────────────────────────────────────────────────
 * Archivo central para todas las lecturas de la base de datos
 * usadas en el proyecto. Inclúyelo donde se necesiten datos:
 *   require_once 'db_queries.php';
 *
 * Requiere que $pdo ya esté definido (configurado en config.php).
 * ─────────────────────────────────────────────────────────────
 *
 * EXPECTED TABLE SCHEMAS
 * ──────────────────────
 *
 * TABLE: properties
 *   id            BIGINT         PRIMARY KEY AUTO_INCREMENT
 *   title         VARCHAR(150)
 *   property_type ENUM(house,apartment,condo,land,commercial)
 *   type          ENUM('sale','rent')
 *   price         DECIMAL(12,2)
 *   address       VARCHAR(255)
 *   municipio_id  SMALLINT       FK → municipios(id)
 *   sqft          INT UNSIGNED   nullable
 *   beds          TINYINT        nullable
 *   bath          TINYINT        nullable
 *   laundry       VARCHAR(100)   nullable
 *   parking       VARCHAR(100)   nullable  (descriptive, e.g. "Garage Doble")
 *   pets          VARCHAR(100)   nullable
 *   mailbox       VARCHAR(100)   nullable
 *   furnished     TINYINT(1)     DEFAULT 0
 *   featured      TINYINT(1)     DEFAULT 0
 *   status        ENUM(available,sold,rented,inactive)  DEFAULT 'available'
 *   description   TEXT           nullable
 *   lat           DECIMAL(10,7)  nullable
 *   lng           DECIMAL(10,7)  nullable
 *   created_at    TIMESTAMP      DEFAULT current_timestamp()
 *
 * TABLE: property_images
 *   id          INT          PRIMARY KEY AUTO_INCREMENT
 *   property_id BIGINT       FK → properties(id) ON DELETE CASCADE
 *   image_url   VARCHAR(500)
 *   is_primary  TINYINT(1)   DEFAULT 0
 *   sort_order  INT          DEFAULT 0
 *
 * TABLE: locations  (map pinpoints)
 *   id          INT          PRIMARY KEY AUTO_INCREMENT
 *   name        VARCHAR(255)
 *   lat         DECIMAL(10,7)  nullable
 *   lng         DECIMAL(10,7)  nullable
 *   pinpoint    ENUM('yes','no')  DEFAULT 'no'
 *   direction   VARCHAR(100)   nullable
 *   size        VARCHAR(100)   nullable
 *   description TEXT           nullable
 */

/* ===========================================
  Bloque de seguridad — acceso directo
  Impide ejecutar este archivo directamente
  desde el navegador sin pasar por la app.
============================================== */
if (!defined('MOVE_GO_APP')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

/* ===========================================
  SECCIÓN: PROPIEDADES
  Funciones para leer y formatear propiedades
  desde la tabla `properties`.
============================================== */

/* ===========================================
  formatPropertyFields()
  Convierte los campos numéricos de una propiedad
  a cadenas de texto legibles para la interfaz
  del mapa (precio, área, cuartos, baños).
  Modifica el arreglo directamente por referencia.
============================================== */
function formatPropertyFields(array &$prop): void
{
    $price = (float)$prop['price'];
    $prop['price'] = '$' . number_format($price, 0, '.', ',')
        . ($prop['type'] === 'rent' ? '/mes' : '');

    if (!empty($prop['sqft'])) {
        $prop['sqft'] = number_format((int)$prop['sqft']) . ' ft²';
    }
    if (!empty($prop['beds'])) {
        $n = (int)$prop['beds'];
        $prop['beds'] = $n . ' ' . ($n === 1 ? 'Cuarto' : 'Cuartos');
    }
    if (!empty($prop['bath'])) {
        $n = (int)$prop['bath'];
        $prop['bath'] = $n . ' ' . ($n === 1 ? 'Baño' : 'Baños');
    }
}

/* ===========================================
  getProperties()
  Devuelve todas las propiedades disponibles,
  con filtro opcional por tipo (venta/alquiler).
============================================== */
function getProperties(PDO $pdo, ?string $type = null): array
{
    if ($type && in_array($type, ['sale', 'rent'], true)) {
        $stmt = $pdo->prepare(
            "SELECT * FROM properties WHERE status = 'available' AND type = :type ORDER BY id"
        );
        $stmt->execute([':type' => $type]);
    } else {
        $stmt = $pdo->query(
            "SELECT * FROM properties WHERE status = 'available' ORDER BY id"
        );
    }

    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($properties as &$prop) {
        $prop['images'] = getPropertyImages($pdo, $prop['id']);
        $primary = array_filter($prop['images'], fn($img) => $img['is_primary']);
        $prop['image'] = $primary
            ? reset($primary)['image_url']
            : ($prop['images'][0]['image_url'] ?? '');

        // Alias para compatibilidad con el JS del mapa (usa 'desc')
        $prop['desc'] = $prop['description'] ?? '';

        formatPropertyFields($prop);
    }
    unset($prop);

    return $properties;
}

/* ===========================================
  getPropertyImages()
  Devuelve todas las imágenes de una propiedad
  ordenadas por imagen principal primero y
  luego por sort_order ascendente.
============================================== */
function getPropertyImages(PDO $pdo, int $propertyId): array
{
    $stmt = $pdo->prepare(
        "SELECT * FROM property_images
          WHERE property_id = :pid
          ORDER BY is_primary DESC, sort_order ASC"
    );
    $stmt->execute([':pid' => $propertyId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ===========================================
  getPropertyById()
  Busca y devuelve una sola propiedad disponible
  por su ID, incluyendo imágenes y campos
  formateados. Retorna null si no existe.
============================================== */
function getPropertyById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        "SELECT * FROM properties WHERE id = :id AND status = 'available'"
    );
    $stmt->execute([':id' => $id]);
    $prop = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$prop) return null;

    $prop['images'] = getPropertyImages($pdo, $prop['id']);
    $primary = array_filter($prop['images'], fn($img) => $img['is_primary']);
    $prop['image'] = $primary
        ? reset($primary)['image_url']
        : ($prop['images'][0]['image_url'] ?? '');

    $prop['desc'] = $prop['description'] ?? '';
    formatPropertyFields($prop);

    return $prop;
}

/* ===========================================
  SECCIÓN: UBICACIONES / PINPOINTS
  Funciones para leer los marcadores del mapa
  desde la tabla `locations`.
============================================== */

/* ===========================================
  getLocations()
  Devuelve todas las ubicaciones, con filtro
  opcional por estado de pin ('yes' / 'no').
  Sin filtro retorna la tabla completa.
============================================== */
function getLocations(PDO $pdo, ?string $pinpoint = null): array
{
    if ($pinpoint && in_array($pinpoint, ['yes', 'no'], true)) {
        $stmt = $pdo->prepare(
            "SELECT * FROM locations WHERE pinpoint = :p ORDER BY name"
        );
        $stmt->execute([':p' => $pinpoint]);
    } else {
        $stmt = $pdo->query("SELECT * FROM locations ORDER BY name");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ===========================================
  getPinnedLocations()
  Devuelve solo las ubicaciones que tienen pin
  activo y coordenadas válidas asignadas.
  Usado para cargar marcadores en el mapa.
============================================== */
function getPinnedLocations(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT * FROM locations
          WHERE pinpoint = 'yes'
            AND lat IS NOT NULL
            AND lng IS NOT NULL
          ORDER BY name"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ===========================================
  getLocationById()
  Busca y devuelve una sola ubicación por ID.
  Retorna null si no existe el registro.
============================================== */
function getLocationById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM locations WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/* ===========================================
  SECCIÓN: ESCRITURAS DE ADMINISTRADOR
  Funciones usadas exclusivamente por los
  endpoints AJAX para modificar pinpoints.
============================================== */

/* ===========================================
  setPinLocation()
  Activa el pin de una ubicación existente y
  guarda sus coordenadas en la base de datos.
  Si se pasa un nombre, también lo actualiza.
============================================== */
function setPinLocation(PDO $pdo, int $id, float $lat, float $lng, string $name = ''): bool
{
    if ($name !== '') {
        $stmt = $pdo->prepare(
            "UPDATE locations
                SET pinpoint='yes', lat=:lat, lng=:lng, name=:name
              WHERE id=:id"
        );
        return $stmt->execute([':lat' => $lat, ':lng' => $lng, ':name' => $name, ':id' => $id]);
    }
    $stmt = $pdo->prepare(
        "UPDATE locations
            SET pinpoint='yes', lat=:lat, lng=:lng
          WHERE id=:id"
    );
    return $stmt->execute([':lat' => $lat, ':lng' => $lng, ':id' => $id]);
}

/* ===========================================
  removePinLocation()
  Desactiva el pin de una ubicación y borra
  sus coordenadas. El registro se conserva
  en la tabla, solo se limpian lat y lng.
============================================== */
function removePinLocation(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare(
        "UPDATE locations
            SET pinpoint='no', lat=NULL, lng=NULL
          WHERE id=:id"
    );
    return $stmt->execute([':id' => $id]);
}
