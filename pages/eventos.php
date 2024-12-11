<?php
include "../procesar.php";

$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : null;
unset($_SESSION['errors']);

$isEdit = isset($_GET['id']);
$eventData = null;

if ($isEdit) {
    $eventId = $_GET['id'];
    $events = get("eventos");
    foreach ($events as $event) {
        if ($event['id'] == $eventId) {
            $eventData = $event;
            break;
        }
    }
}

$events = get("eventos");

if (isset($_GET['nombre_evento']) && !empty($_GET['nombre_evento'])) {
    $nombreEvento = $_GET['nombre_evento'];
    $events = array_filter($events, function($event) use ($nombreEvento) {
        return stripos($event['nombre_evento'], $nombreEvento) !== false; 
    });
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id'; 
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc'; 

usort($events, function($a, $b) use ($sort, $order) {
    if ($order === 'asc') {
        return $a[$sort] <=> $b[$sort];
    } else {
        return $b[$sort] <=> $a[$sort];
    }
});

define('EVENTS_PER_PAGE', 5); 
$totalEvents = count($events);
$totalPages = ceil($totalEvents / EVENTS_PER_PAGE);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); 
$startIndex = ($currentPage - 1) * EVENTS_PER_PAGE;
$eventsToShow = array_slice($events, $startIndex, EVENTS_PER_PAGE);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos</title>
    <link rel="stylesheet" href="../styles/crudStyle.css">
</head>

<body>
    <header class="header">
        <h1>Eventos</h1>
    </header>

    <main class="main-container">
        <div>
            <a href="../index.html" class="btn btn-back">Volver al Inicio</a>

            <form method="GET" action="" class="form">
                <input type="text" name="nombre_evento" class="input-field" placeholder="Buscar evento" value="<?php echo isset($_GET['nombre_evento']) ? htmlspecialchars($_GET['nombre_evento']) : ''; ?>">
                <button type="submit" class="btn">Buscar</button>
            </form>

            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th><a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=id&order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>">Id <?php echo $sort === 'id' ? ($order === 'asc' ? '↑' : '↓') : ''; ?></a></th>
                            <th><a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=nombre_evento&order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>">Nombre Evento <?php echo $sort === 'nombre_evento' ? ($order === 'asc' ? '↑' : '↓') : ''; ?></a></th>
                            <th><a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=tipo_deporte&order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>">Tipo Deporte <?php echo $sort === 'tipo_deporte' ? ($order === 'asc' ? '↑' : '↓') : ''; ?></a></th>
                            <th><a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=fecha&order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>">Fecha <?php echo $sort === 'fecha' ? ($order === 'asc' ? '↑' : '↓') : ''; ?></a></th>
                            <th><a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=hora&order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>">Hora <?php echo $sort === 'hora' ? ($order === 'asc' ? '↑' : '↓') : ''; ?></a></th>
                            <th><a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=ubicacion&order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>">Ubicacion <?php echo $sort === 'ubicacion' ? ($order === 'asc' ? '↑' : '↓') : ''; ?></a></th>
                            <th><a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=nombre_organizador&order=<?php echo $order === 'asc' ? 'desc' : 'asc'; ?>">Organizador <?php echo $sort === 'nombre_organizador' ? ($order === 'asc' ? '↑' : '↓') : ''; ?></a></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php 
                        if (empty($events)) {
                            echo "<tr><td colspan='7'>No se ha encontrado ningún evento</td></tr>";
                        } else {
                            foreach ($events as $event):
                        ?>
                            <tr>
                                <td><?php echo $event['id']; ?></td>
                                <td><?php echo $event['nombre_evento']; ?></td>
                                <td><?php echo $event['tipo_deporte']; ?></td>
                                <td><?php echo $event['fecha']; ?></td>
                                <td><?php echo $event['hora']; ?></td>
                                <td><?php echo $event['ubicacion']; ?></td>
								<td><?php echo $event['nombre_organizador']; ?></td>
                                <td>
                                    <a href='eventos.php?id=<?php echo $event['id']; ?>' class="btn">Editar</a>
                                    <form action='../procesar.php' method='POST' style="display:inline;">
                                        <input type='hidden' name='accion' value='DELTeventos'>
                                        <input type='hidden' name='id' value='<?php echo $event['id']; ?>'>
                                        <button type='submit' class="btn" onclick='return confirm("¿Estás seguro de que deseas eliminar este evento?")'>Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php 
                            endforeach; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        
            <div class="pagination-container">
                <?php if ($currentPage > 1): ?>
                    <a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $currentPage - 1; ?>">Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $i; ?>" <?php echo $i === $currentPage ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?nombre_evento=<?php echo htmlspecialchars($_GET['nombre_evento'] ?? ''); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $currentPage + 1; ?>">Siguiente</a>
                <?php endif; ?>
            </div>
        </div>

        <form action="../procesar.php" method="post" class="form-container">
            <?php if ($errors == true): ?>
                <ul class="error-list">
                    <?php 
                    foreach ($errors as $error) {
                        echo "<li>$error</li>";
                    }
                    ?>
                </ul>
            <?php endif; ?>

            <div>
                <h2><span id="action"><?php echo $isEdit ? "Editar" : "Crear"; ?></span> Evento</h2>

                <input type="hidden" name="accion" value="<?php echo $isEdit ? 'UPDAeventos' : 'POSTeventos'; ?>">

                <?php echo $isEdit ? '<input type="hidden" name="id" value="' . $eventData['id'] . '">' : ''; ?>

                <input type="text" name="nombre_evento" id="nombre_evento" class="input-field" placeholder="Nombre del Evento" value="<?php echo $isEdit ? $eventData['nombre_evento'] : ''; ?>">
                <input type="text" name="deporte" id="deporte" class="input-field" placeholder="Deporte" value="<?php echo $isEdit ? $eventData['tipo_deporte'] : ''; ?>">
                
                <div>
                    <input type="datetime-local" name="fecha" id="fecha" value="<?php echo $isEdit ? $eventData['fecha'] . 'T' . $eventData['hora'] : ''; ?>">
                    <select name="idOrganizador" id="idOrganizador" class="input-field">
                        <option selected disabled>Selecciona un Organizador</option>
                        <?php
                        $managers = get("organizadores");
                        foreach ($managers as $manager) {
                            $selected = ($isEdit && $eventData['id_organizador'] == $manager['id']) ? 'selected' : '';
                            echo "<option value='{$manager['id']}' $selected>{$manager['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <input type="text" name="ubicacion" id="ubicacion" class="input-field" placeholder="Ubicacion" value="<?php echo $isEdit ? $eventData['ubicacion'] : ''; ?>">
            </div>

            <button type="submit" class="btn"><?php echo $isEdit ? "Actualizar" : "Crear"; ?></button>
        </form>
    </main>
</body>

</html>