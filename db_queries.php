<?php
/**
 * DB tables needed:
 *   properties      — one row per listing
 *   property_images — one row per image, linked by property_id
 *   locations       — admin map pinpoints
 *
 * db_queries.php — Move & Go PR
 * ─────────────────────────────────────────────────────────────
 * Central file for all database reads used across the project.
 * Include wherever DB data is needed:
 *   require_once 'db_queries.php';
 *
 * Requires $pdo to already be defined (set in config.php).
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

// ── Prevent direct access ────────────────────────────────────
if (!defined('MOVE_GO_APP')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

// ─────────────────────────────────────────────────────────────
//  PROPERTIES
// ─────────────────────────────────────────────────────────────

/**
 * Format numeric property fields into display strings for the map UI.
 * Mutates the row in place so getProperties / getPropertyById stay clean.
 */
function formatPropertyFields(array &$prop): void
{
    // Price: always a decimal in DB; format with /mes suffix for rentals
    $price = (float)$prop['price'];
    $prop['price'] = '$' . number_format($price, 0, '.', ',')
        . ($prop['type'] === 'rent' ? '/mes' : '');

    // Numeric specs → friendly strings (skip if null/zero)
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

/**
 * Fetch all available properties, optionally filtered by type.
 *
 * @param  PDO         $pdo
 * @param  string|null $type  'sale' | 'rent' | null = all
 * @return array  Each row includes formatted display fields and an 'images' sub-array.
 */
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

        // Alias for JS compatibility (map uses 'desc')
        $prop['desc'] = $prop['description'] ?? '';

        formatPropertyFields($prop);
    }
    unset($prop);

    return $properties;
}

/**
 * Fetch all images for a single property, ordered by sort_order.
 *
 * @param  PDO $pdo
 * @param  int $propertyId
 * @return array
 */
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

/**
 * Fetch a single available property by ID (with images and formatted fields).
 *
 * @param  PDO $pdo
 * @param  int $id
 * @return array|null
 */
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

// ─────────────────────────────────────────────────────────────
//  PINPOINT LOCATIONS
// ─────────────────────────────────────────────────────────────

/**
 * Fetch all locations, optionally filtered by pinpoint status.
 *
 * @param  PDO         $pdo
 * @param  string|null $pinpoint  'yes' | 'no' | null = all
 * @return array
 */
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

/**
 * Fetch only locations that have been pinned (pinpoint='yes' with valid coords).
 *
 * @param  PDO $pdo
 * @return array
 */
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

/**
 * Fetch a single location by ID.
 *
 * @param  PDO $pdo
 * @param  int $id
 * @return array|null
 */
function getLocationById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM locations WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

// ─────────────────────────────────────────────────────────────
//  ADMIN WRITES  (used by AJAX endpoints only)
// ─────────────────────────────────────────────────────────────

/**
 * Set or update a pin on an existing location record.
 *
 * @param  PDO    $pdo
 * @param  int    $id
 * @param  float  $lat
 * @param  float  $lng
 * @param  string $name  Optional new display name (empty = keep existing)
 * @return bool
 */
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

/**
 * Remove a pin from a location (keeps the record, clears coordinates).
 *
 * @param  PDO $pdo
 * @param  int $id
 * @return bool
 */
function removePinLocation(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare(
        "UPDATE locations
            SET pinpoint='no', lat=NULL, lng=NULL
          WHERE id=:id"
    );
    return $stmt->execute([':id' => $id]);
}
