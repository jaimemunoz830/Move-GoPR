<?php
/**
 * DB tables needed
 *Your DB needs these three tables 
 *(schemas are documented at the top of db_queries.php):
 *properties — one row per listing
 *property_images — one row per image, linked by property_id
 *locations — the admin pinpoints
 * 
 * 
 * 
 * db_queries.php — Move & Go PR
 * ─────────────────────────────────────────────────────────────
 * Central file for all database access used across the project.
 * Include this file wherever DB data is needed:
 *   require_once 'db_queries.php';
 *
 * Requires $pdo to already be defined (set in config.php).
 * ─────────────────────────────────────────────────────────────
 *
 * EXPECTED TABLE SCHEMAS
 * ──────────────────────
 *
 * TABLE: properties
 *   id          INT          PRIMARY KEY AUTO_INCREMENT
 *   title       VARCHAR(255)
 *   type        ENUM('sale','rent')
 *   price       VARCHAR(50)   e.g. "$850,000" or "$1,800/mes"
 *   sqft        VARCHAR(50)   e.g. "4,200 ft²"
 *   beds        VARCHAR(50)   e.g. "5 Cuartos"
 *   bath        VARCHAR(50)   e.g. "4 Baños"
 *   laundry     VARCHAR(100)
 *   parking     VARCHAR(100)
 *   pets        VARCHAR(100)
 *   mailbox     VARCHAR(100)
 *   description TEXT
 *   lat         DECIMAL(10,7)
 *   lng         DECIMAL(10,7)
 *   active      TINYINT(1)    DEFAULT 1
 *
 * TABLE: property_images
 *   id          INT          PRIMARY KEY AUTO_INCREMENT
 *   property_id INT          FOREIGN KEY → properties(id)
 *   image_url   VARCHAR(500)
 *   is_primary  TINYINT(1)   DEFAULT 0   (1 = main thumbnail)
 *   sort_order  INT          DEFAULT 0
 *
 * TABLE: locations  (pinpoints / admin markers)
 *   id          INT          PRIMARY KEY AUTO_INCREMENT
 *   name        VARCHAR(255)
 *   lat         DECIMAL(10,7)  NULL
 *   lng         DECIMAL(10,7)  NULL
 *   pinpoint    ENUM('yes','no')  DEFAULT 'no'
 *   direction   VARCHAR(100)
 *   size        VARCHAR(100)
 *   description TEXT
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
 * Fetch all active properties, optionally filtered by type.
 *
 * @param  PDO         $pdo
 * @param  string|null $type  'sale' | 'rent' | null = all
 * @return array  Associative array; each row includes 'images' sub-array.
 */
function getProperties(PDO $pdo, ?string $type = null): array
{
    if ($type && in_array($type, ['sale', 'rent'], true)) {
        $stmt = $pdo->prepare(
            "SELECT * FROM properties WHERE active = 1 AND type = :type ORDER BY id"
        );
        $stmt->execute([':type' => $type]);
    } else {
        $stmt = $pdo->query(
            "SELECT * FROM properties WHERE active = 1 ORDER BY id"
        );
    }

    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach images to each property
    foreach ($properties as &$prop) {
        $prop['images'] = getPropertyImages($pdo, $prop['id']);
        // Convenience: first primary image (or first image) as 'image'
        $primary = array_filter($prop['images'], fn($img) => $img['is_primary']);
        $prop['image'] = $primary
            ? reset($primary)['image_url']
            : ($prop['images'][0]['image_url'] ?? '');
    }
    unset($prop);

    return $properties;
}

/**
 * Fetch all images for a single property, ordered by sort_order.
 *
 * @param  PDO $pdo
 * @param  int $propertyId
 * @return array  Rows from property_images.
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
 * Fetch a single property by ID (with images).
 *
 * @param  PDO $pdo
 * @param  int $id
 * @return array|null
 */
function getPropertyById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        "SELECT * FROM properties WHERE id = :id AND active = 1"
    );
    $stmt->execute([':id' => $id]);
    $prop = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$prop) return null;

    $prop['images'] = getPropertyImages($pdo, $prop['id']);
    $primary = array_filter($prop['images'], fn($img) => $img['is_primary']);
    $prop['image'] = $primary
        ? reset($primary)['image_url']
        : ($prop['images'][0]['image_url'] ?? '');

    return $prop;
}

// ─────────────────────────────────────────────────────────────
//  PINPOINT LOCATIONS
// ─────────────────────────────────────────────────────────────

/**
 * Fetch all locations.
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
 * Fetch only locations that have been pinned on the map
 * (pinpoint = 'yes' AND lat/lng are not null).
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
//  ADMIN WRITES  (used by AJAX endpoints, not by map.php directly)
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
 * Remove a pin from a location (keeps the record, just clears coordinates).
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
