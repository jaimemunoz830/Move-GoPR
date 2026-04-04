<?php
/**
 * properties_panel.php
 * 
 * Panel de administración para gestionar propiedades: listar, crear, editar y eliminar.
 * Cada propiedad tiene campos como título, tipo, precio, ubicación, descripción e imagen.
 * El código maneja la lógica de carga de imágenes, validación básica y operaciones CRUD.
 * 
 * TODO:
 * - Implementar precio diferenciado para venta vs alquiler (ej. mostrar precio mensual para rentas).
 * - Implementar filtros y búsqueda para listar propiedades.
 * - Implementar paginación al listado de propiedades.
 * - Mejorar la gestión de imágenes, permitiendo múltiples fotos por propiedad.
 * - Crear la interfear de para gestionar la galería de fotos (añadir/eliminar fotos adicionales).
 * 
 * @author     Jaime A. Muñoz Rodriguez
 * @version    1.0
 */


$upload_dir        = dirname(__DIR__) . '/uploads/properties/';
$upload_url_prefix = 'uploads/properties/';

/**
 * Maneja la carga de una imagen desde el formulario.
 * Valida el archivo, lo mueve al directorio de uploads y devuelve la URL relativa. 
 * 
 * @param string $upload_dir El directorio absoluto donde se guardarán las imágenes.
 * @param string $upload_url_prefix El prefijo de URL para acceder a las imágenes desde el navegador.
 * @return string|null|false La URL de la imagen cargada, null si no se envió ningún archivo, o false si hubo un error en la carga o formato no permitido
 */
function handle_image_upload($upload_dir, $upload_url_prefix) {
    error_log('FILES: ' . print_r($_FILES['image'], true));
    error_log('DIR: ' . $upload_dir);
    error_log('DIR EXISTS: ' . (is_dir($upload_dir) ? 'yes' : 'no'));
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; 
    }
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        return false; 
    }
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return false;
    }
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = 'prop_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
        return $upload_url_prefix . $filename;
    }
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['form_action'] ?? '';

    /**
     * CREATE: Inserta una nueva propiedad en la base de datos. 
     * Maneja la carga de imagen y redirige al listado con un mensaje de éxito.
     */
    if ($form_action === 'create') {
        $image_path = handle_image_upload($upload_dir, $upload_url_prefix);
        if ($image_path === false) $image_path = '';
        if ($image_path === null) $image_path = '';

        $stmt = $pdo->prepare("
            INSERT INTO properties
                (title, property_type, listing_type, price, address, municipio_id,
                 sqft, beds, baths, parking, furnished, featured,
                 status, description, image, lat, lng)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($_POST['title']),
            $_POST['property_type'],
            $_POST['listing_type'],
            (float)$_POST['price'],
            trim($_POST['address']),
            (int)$_POST['municipio_id'],
            !empty($_POST['sqft'])    ? (int)$_POST['sqft']    : null,
            !empty($_POST['beds'])    ? (int)$_POST['beds']     : null,
            !empty($_POST['baths'])   ? (int)$_POST['baths']    : null,
            !empty($_POST['parking']) ? (int)$_POST['parking']  : null,
            isset($_POST['furnished']) ? 1 : 0,
            isset($_POST['featured'])  ? 1 : 0,
            $_POST['status'],
            trim($_POST['description']),
            $image_path,
            !empty($_POST['lat']) ? (float)$_POST['lat'] : null,
            !empty($_POST['lng']) ? (float)$_POST['lng'] : null,
        ]);
        header("Location: dashboard.php?section=properties&msg=created");
        exit();
    }

    /**
     * UPDATE: Actualiza una propiedad existente en la base de datos. 
     * Maneja la carga de imagen y redirige al listado con un mensaje de éxito.
     */
    if ($form_action === 'update') {
        $prop_id   = (int)$_POST['id'];
        $old_image = $_POST['existing_image'] ?? '';

        $new_image = handle_image_upload($upload_dir, $upload_url_prefix);

        if ($new_image === null || $new_image === false) {
            $image_path = $old_image;
        } else {
            $image_path = $new_image;
            if ($old_image && file_exists(dirname(__DIR__) . '/' . $old_image)) {
                unlink(dirname(__DIR__) . '/' . $old_image);
            }
        }

        $stmt = $pdo->prepare("
            UPDATE properties SET
                title         = ?,
                property_type = ?,
                listing_type  = ?,
                price         = ?,
                address       = ?,
                municipio_id  = ?,
                sqft          = ?,
                beds          = ?,
                baths         = ?,
                parking       = ?,
                furnished     = ?,
                featured      = ?,
                status        = ?,
                description   = ?,
                image         = ?,
                lat           = ?,
                lng           = ?
            WHERE id = ?
        ");
        $stmt->execute([
            trim($_POST['title']),
            $_POST['property_type'],
            $_POST['listing_type'],
            (float)$_POST['price'],
            trim($_POST['address']),
            (int)$_POST['municipio_id'],
            !empty($_POST['sqft'])    ? (int)$_POST['sqft']    : null,
            !empty($_POST['beds'])    ? (int)$_POST['beds']     : null,
            !empty($_POST['baths'])   ? (int)$_POST['baths']    : null,
            !empty($_POST['parking']) ? (int)$_POST['parking']  : null,
            isset($_POST['furnished']) ? 1 : 0,
            isset($_POST['featured'])  ? 1 : 0,
            $_POST['status'],
            trim($_POST['description']),
            $image_path,
            !empty($_POST['lat']) ? (float)$_POST['lat'] : null,
            !empty($_POST['lng']) ? (float)$_POST['lng'] : null,
            $prop_id,
        ]);
        header("Location: dashboard.php?section=properties&msg=updated");
        exit();
    }

    /**
     * DELETE: Elimina una propiedad de la base de datos. 
     * Antes de eliminar, borra la imagen asociada del servidor si existe.
     */
    if ($form_action === 'delete') {
        $prop_id = (int)$_POST['id'];

       
        $stmt = $pdo->prepare("SELECT image FROM properties WHERE id = ?");
        $stmt->execute([$prop_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['image'] && file_exists(dirname(__DIR__) . '/' . $row['image'])) {
            unlink(dirname(__DIR__) . '/' . $row['image']);
        }

        $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$prop_id]);
        header("Location: dashboard.php?section=properties&msg=deleted");
        exit();
    }
}

// Logica para mostrar el listado de propiedades o el formulario de creación/edición

$action  = $_GET['action'] ?? 'list';
$prop_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$msg     = $_GET['msg'] ?? '';

$municipios = $pdo->query(
    "SELECT id, municipality, region FROM municipios ORDER BY municipality ASC"
)->fetchAll(PDO::FETCH_ASSOC);


$prop = [
    'id' => null, 'title' => '', 'property_type' => 'house',
    'listing_type' => 'sale', 'price' => '', 'address' => '',
    'municipio_id' => '', 'sqft' => '', 'beds' => '', 'baths' => '',
    'parking' => '', 'furnished' => 0, 'featured' => 0,
    'status' => 'available', 'description' => '', 'image' => '',
    'lat' => '', 'lng' => '',
];

// Si estamos editando, cargamos los datos de la propiedad para prellenar el formulario
if ($action === 'edit' && $prop_id) {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$prop_id]);
    $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
    // Si no se encuentra la propiedad, redirigimos al listado con un mensaje de error
    if (!$fetched) {
        header("Location: dashboard.php?section=properties&msg=notfound"); 
        exit();
    }
    $prop = $fetched;
}

function status_badge($status) {
    $colors = [
        'available' => '#16a34a',
        'sold'      => '#1e3a8a',
        'rented'    => '#d97706',
        'inactive'  => '#94a3b8',
    ];
    $labels = [
        'available' => 'Disponible',
        'sold'      => 'Vendido',
        'rented'    => 'Alquilado',
        'inactive'  => 'Inactivo',
    ];
    $color = $colors[$status] ?? '#94a3b8';
    $label = $labels[$status] ?? $status;
    return "<span style='background:{$color}; color:white; padding:3px 10px; border-radius:12px; font-size:12px;'>{$label}</span>";
}
?>

<style>
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .btn-primary {
        background: #1e3a8a;
        color: white;
        padding: 9px 18px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        cursor: pointer;
        display: inline-block;
    }
    .btn-primary:hover { background: #1e40af; }

    .btn-secondary {
        background: #475569;
        color: white;
        padding: 9px 18px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        cursor: pointer;
        display: inline-block;
    }
    .btn-secondary:hover { background: #334155; }

    .btn-danger {
        background: #dc2626;
        color: white;
        padding: 6px 12px;
        border: none;
        border-radius: 5px;
        font-size: 13px;
        cursor: pointer;
    }
    .btn-danger:hover { background: #b91c1c; }

    .btn-edit {
        background: #d97706;
        color: white;
        padding: 6px 12px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 13px;
        display: inline-block;
    }
    .btn-edit:hover { background: #b45309; }

    .alert {
        padding: 12px 18px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    .prop-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .prop-table th {
        background: #f4f6f9;
        padding: 11px 14px;
        text-align: left;
        border-bottom: 2px solid #ddd;
        white-space: nowrap;
    }
    .prop-table td { padding: 11px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .prop-table tr:hover td { background: #f9fafb; }

    .prop-thumb {
        width: 60px;
        height: 45px;
        object-fit: cover;
        border-radius: 5px;
        display: block;
    }
    .no-thumb {
        width: 60px;
        height: 45px;
        background: #e2e8f0;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #94a3b8;
    }

    .form-card {
        background: white;
        padding: 28px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }
    .form-card h3 {
        margin: 0 0 20px;
        color: #1e3a8a;
        font-size: 16px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 12px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-grid.three-col { grid-template-columns: 1fr 1fr 1fr 1fr; }
    .form-grid.full      { grid-template-columns: 1fr; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-group label { font-size: 13px; font-weight: 600; color: #374151; }
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 8px 11px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.2s;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #1e3a8a;
    }
    .form-group textarea { resize: vertical; }
    .checkbox-row {
        display: flex;
        gap: 30px;
        align-items: center;
        padding: 10px 0;
    }
    .checkbox-row label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
    }
    .checkbox-row input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
    .form-actions { display: flex; gap: 12px; margin-top: 24px; }
    .current-image-wrap { margin-top: 8px; }
    .current-image-wrap img { width: 120px; height: 90px; object-fit: cover; border-radius: 6px; border: 2px solid #e2e8f0; }
    .current-image-wrap small { display: block; color: #888; font-size: 12px; margin-top: 4px; }
</style>

<?php 
/**
 * LIST: Muestra un listado de todas las propiedades registradas en la base de datos.
 * Cada propiedad se muestra con su foto (si tiene), título, tipo, precio, municipio, estado y acciones para editar o eliminar.
 * 
 * También muestra mensajes de exito o error basados en la acción realizada (creación, actualización, eliminación o propiedad no encontrada)
 * Si no hay propiedades registradas, muestra un mensaje indicando que el listado está vacío e invita a crear la primera propiedad.
 */
if ($action === 'list'): ?>
    <div class="panel-header">
        <h1 style="margin:0;">Propiedades</h1>
        <a href="dashboard.php?section=properties&action=add" class="btn-primary">+ Nueva Propiedad</a>
    </div>

    <?php if ($msg === 'created'): ?>
        <div class="alert alert-success">✅ Propiedad añadida exitosamente.</div>
    <?php elseif ($msg === 'updated'): ?>
        <div class="alert alert-success">✅ Propiedad actualizada exitosamente.</div>
    <?php elseif ($msg === 'deleted'): ?>
        <div class="alert alert-success">🗑 Propiedad eliminada.</div>
    <?php elseif ($msg === 'notfound'): ?>
        <div class="alert alert-danger">❌ Propiedad no encontrada.</div>
    <?php endif; ?>

    <?php
    $properties = $pdo->query("
        SELECT p.*, m.municipality
        FROM properties p
        LEFT JOIN municipios m ON p.municipio_id = m.id
        ORDER BY p.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="card" style="padding:0; overflow:hidden; cursor:default;">
        <?php if (empty($properties)): ?>
            <p style="padding:30px; color:#888; text-align:center;">
                No hay propiedades registradas. <a href="dashboard.php?section=properties&action=add">Añadir la primera.</a>
            </p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="prop-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Listado</th>
                            <th>Precio</th>
                            <th>Municipio</th>
                            <th>Estado</th>
                            <th>Destacado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $p): ?>
                        <tr>
                            <td style="color:#94a3b8;"><?= $p['id'] ?></td>
                            <td>
                                <?php if ($p['image']): ?>
                                    <img src="<?= htmlspecialchars($p['image']) ?>"
                                         alt="foto"
                                         class="prop-thumb">
                                <?php else: ?>
                                    <div class="no-thumb">Sin foto!</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                            <td style="text-transform:capitalize;"><?= htmlspecialchars($p['property_type']) ?></td>
                            <td><?= $p['listing_type'] === 'sale' ? 'Venta' : 'Alquiler' ?></td>
                            <td>$<?= number_format($p['price'], 2) ?></td> <!-- TODO: Añadir logica /mes a precio de renta -->
                            <td><?= htmlspecialchars($p['municipality'] ?? '—') ?></td>
                            <td><?= status_badge($p['status']) ?></td>
                            <td style="text-align:center;">
                                <?= $p['featured'] ? '⭐' : '—' ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <a href="dashboard.php?section=properties&action=edit&id=<?= $p['id'] ?>"
                                       class="btn-edit">Editar</a> 

                                    <form method="POST"
                                          onsubmit="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($p['title'])) ?>»? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<?php
/**
 * ADD/EDIT: Muestra un formulario para crear una nueva propiedad o editar una existente.

 */
elseif ($action === 'add' || $action === 'edit'): ?>

    <div class="panel-header">
        <h1 style="margin:0;">
            <?= $action === 'add' ? 'Nueva Propiedad' : 'Editar Propiedad' ?>
        </h1>
        <a href="dashboard.php?section=properties" class="btn-secondary">← Volver al listado</a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="form_action" value="<?= $action === 'add' ? 'create' : 'update' ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?= (int)$prop['id'] ?>">
            <input type="hidden" name="existing_image"  value="<?= htmlspecialchars($prop['image']) ?>">
        <?php endif; ?>

        <div class="form-card">
            <h3>Información básica</h3>

            <div class="form-grid" style="margin-bottom:16px;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Título *</label>
                    <input type="text" name="title"
                           value="<?= htmlspecialchars($prop['title']) ?>"
                           placeholder="Ej. Apartamento para estudiantes en Ponce!" required>
                </div>
            </div>

            <div class="form-grid" style="margin-bottom:16px;">
                <div class="form-group">
                    <label>Tipo de propiedad *</label>
                    <select name="property_type" required>
                        <?php foreach (['house'=>'Casa','apartment'=>'Apartamento','condo'=>'Condo','land'=>'Terreno','commercial'=>'Comercial'] as $val=>$lbl): ?>
                            <option value="<?= $val ?>" <?= $prop['property_type']===$val?'selected':'' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipo de listado *</label>
                    <select name="listing_type" required>
                        <option value="sale"  <?= $prop['listing_type']==='sale' ?'selected':'' ?>>Venta</option>
                        <option value="rent"  <?= $prop['listing_type']==='rent' ?'selected':'' ?>>Alquiler</option>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Precio (USD) *</label>
                    <input type="number" name="price" step="0.01" min="0"
                           value="<?= htmlspecialchars($prop['price']) ?>"
                           placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Estado *</label>
                    <select name="status" required>
                        <?php foreach (['available'=>'Disponible','sold'=>'Vendido','rented'=>'Alquilado','inactive'=>'Inactivo'] as $val=>$lbl): ?>
                            <option value="<?= $val ?>" <?= $prop['status']===$val?'selected':'' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-card">
            <h3>Ubicación</h3>

            <div class="form-grid" style="margin-bottom:16px;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Dirección completa *</label>
                    <input type="text" name="address"
                           value="<?= htmlspecialchars($prop['address']) ?>"
                           placeholder="Ej. Calle Robles 45, Urb. Las Lomas" required>
                </div>
            </div>

           <div class="form-grid" style="margin-bottom:16px;">
                <div class="form-group">
                    <label>Municipio *</label>
                    <select name="municipio_id" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($municipios as $m): ?>
                            <option value="<?= $m['id'] ?>"
                                    <?= (string)$prop['municipio_id'] === (string)$m['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['municipality']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Latitud <small style="color:#888;">(opcional)</small></label>
                    <input type="number" name="lat" step="0.0000001"
                           value="<?= htmlspecialchars($prop['lat']) ?>"
                           placeholder="18.2208">
                </div>
                <div class="form-group">
                    <label>Longitud <small style="color:#888;">(opcional)</small></label>
                    <input type="number" name="lng" step="0.0000001"
                           value="<?= htmlspecialchars($prop['lng']) ?>"
                           placeholder="-66.5901">
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3>Detalles</h3>

            <div class="form-grid three-col" style="margin-bottom:16px;">
                <div class="form-group">
                    <label>Pies cuadrados</label>
                    <input type="number" name="sqft" min="0"
                           value="<?= htmlspecialchars($prop['sqft']) ?>"
                           placeholder="1200">
                </div>
                <div class="form-group">
                    <label>Cuartos</label>
                    <input type="number" name="beds" min="0" max="99"
                           value="<?= htmlspecialchars($prop['beds']) ?>"
                           placeholder="3">
                </div>
                <div class="form-group">
                    <label>Baños</label>
                    <input type="number" name="baths" min="0" max="99"
                           value="<?= htmlspecialchars($prop['baths']) ?>"
                           placeholder="2">
                </div>
                <div class="form-group">
                    <label>Estacionamientos</label>
                    <input type="number" name="parking" min="0" max="99"
                           value="<?= htmlspecialchars($prop['parking']) ?>"
                           placeholder="1">
                </div>
            </div>

            <div class="checkbox-row">
                <label>
                    <input type="checkbox" name="furnished"
                           <?= $prop['furnished'] ? 'checked' : '' ?>>
                    Amueblado
                </label>
                <label>
                    <input type="checkbox" name="featured"
                           <?= $prop['featured'] ? 'checked' : '' ?>>
                    ⭐ Destacar en el sitio
                </label>
            </div>
        </div>

        <!-- 
        TODO: Añadir capacidad de incluir mas de una imagen y poder gestionar una galería de fotos por propiedad. 
        Esto implica crear una tabla adicional para las imágenes y crear un interface para gestionar la galeria.
        Por ahora, se mantiene una sola imagen principal.
        -->
        <div class="form-card">
            <h3>Descripción e imagen</h3>

            <div class="form-grid full" style="margin-bottom:16px;">
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" rows="4"
                              placeholder="Describe la propiedad..."><?= htmlspecialchars($prop['description']) ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label>
                    Imagen
                    <?php if ($action === 'edit' && $prop['image']): ?>
                        <small style="color:#888; font-weight:normal;">(subir una nueva reemplaza la actual)</small>
                    <?php endif; ?>
                </label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <small style="color:#94a3b8;">Formatos: JPG, PNG, WebP — Máximo recomendado: 2MB</small>

                <?php if ($action === 'edit' && $prop['image']): ?>
                    <div class="current-image-wrap">
                        <img src="<?= htmlspecialchars($prop['image']) ?>" alt="Imagen actual">
                        <small>Imagen actual</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <?= $action === 'add' ? '✅ Guardar Propiedad' : '💾 Actualizar Propiedad' ?>
            </button>
            <a href="dashboard.php?section=properties" class="btn-secondary">Cancelar</a>
        </div>

    </form>

<?php endif; ?>