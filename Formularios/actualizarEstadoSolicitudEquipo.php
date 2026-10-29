<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 

// Verificación del usuario para que no ingrese por ruta del navegador
$esAdministrador = isset($_SESSION['roles']) && in_array('1', $_SESSION['roles']);

if (!$esAdministrador) {
    $_SESSION['mensaje'] = "Acceso denegado. Acción no permitida para el rol detectado.";
    $_SESSION['tipoAlerta'] = "danger";
    header("Location: ../index.php");
    exit();
}

require_once '../Clases/ClsSolicitud.php';
$objSolicitud = new ClsSolicitud();

// Proceso la actualización cuando se envía el formulario del modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $idSolicitud = $_POST['idsolicitud'] ?? 0;
    $tipoEquipo  = $_POST['tipoEquipo'] ?? '';
    $estado      = $_POST['estado'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $cantidad    = $_POST['cantidad'] ?? '';
    $fecha       = $_POST['fecha'] ?? '';

    if ($idSolicitud > 0 && !empty($tipoEquipo) && !empty($estado) && !empty($descripcion) && $cantidad > 0 && !empty($fecha)) {
        if ($objSolicitud->actualizarSolicitud($idSolicitud, $tipoEquipo, $estado, $descripcion, $cantidad, $fecha)) {
            $_SESSION['mensaje'] = "¡Solicitud #{$idSolicitud} actualizada exitosamente!";
            $_SESSION['tipoAlerta'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar la solicitud en la base de datos.";
            $_SESSION['tipoAlerta'] = "danger";
        }
    } else {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios y la cantidad debe ser mayor a 0.";
        $_SESSION['tipoAlerta'] = "warning";
    }
    
    header("Location: actualizarEstadoSolicitudEquipo.php");
    exit();
}

//Filtros
$filtroUsuario = $_GET['filtro_usuario'] ?? '';
$filtroEstado = $_GET['filtro_estado'] ?? '';
$filtroTipo = $_GET['filtro_tipo'] ?? '';

//Obtengo datos para filtros y listado
$listaUsuarios = $objSolicitud->listarUsuariosConSolicitudes();
$listaTipos = $objSolicitud->listarTiposEquiposSolicitados();
$listaSolicitudes = $objSolicitud->listarSolicitudesFiltradas($filtroUsuario, $filtroEstado, $filtroTipo);

$mensaje = $_SESSION['mensaje'] ?? '';
$tipoAlerta = $_SESSION['tipoAlerta'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipoAlerta']);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Actualizar Estado de Solicitudes - CeiLab</title>
        <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="../Estilos/blocks.css">
        <link href="../Estilos/style.css" rel="stylesheet">
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    </head>
    <body class="d-flex flex-column min-vh-100 bg-light">
        
        <?php include '../navbar.php'; ?> 
        
        <section class="hero-section flex-fill pb-5 pt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        
                        <!-- LISTADO Y FILTROS -->
                        <div class="card shadow-lg border-0 rounded-4 p-4 mb-5 bg-white bg-opacity-95">
                            <h2 class="text-center fw-bold text-dark mb-4">Gestión y Actualización de Solicitudes</h2>

                            <!-- Formulario de Filtros -->
                            <form method="GET" action="actualizarEstadoSolicitudEquipo.php" class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                                <div class="col-md-4">
                                    <label for="filtro_usuario" class="form-label fw-bold text-dark small">Filtrar por Usuario</label>
                                    <select class="form-select form-select-sm rounded-3 shadow-sm" id="filtro_usuario" name="filtro_usuario">
                                        <option value="">Todos los usuarios</option>
                                        <?php foreach ($listaUsuarios as $usr): ?>
                                            <option value="<?php echo $usr['IDUsuario']; ?>" <?php echo ($filtroUsuario == $usr['IDUsuario']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($usr['Nombre']); ?> (ID: <?php echo $usr['IDUsuario']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="filtro_estado" class="form-label fw-bold text-dark small">Filtrar por Estado</label>
                                    <select class="form-select form-select-sm rounded-3 shadow-sm" id="filtro_estado" name="filtro_estado">
                                        <option value="">Todos los estados</option>
                                        <option value="pendiente" <?php echo ($filtroEstado === 'pendiente') ? 'selected' : ''; ?>>pendiente</option>
                                        <option value="EnProceso" <?php echo ($filtroEstado === 'EnProceso') ? 'selected' : ''; ?>>EnProceso</option>
                                        <option value="aprobada" <?php echo ($filtroEstado === 'aprobada') ? 'selected' : ''; ?>>aprobada</option>
                                        <option value="rechazada" <?php echo ($filtroEstado === 'rechazada') ? 'selected' : ''; ?>>rechazada</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="filtro_tipo" class="form-label fw-bold text-dark small">Filtrar por Tipo</label>
                                    <select class="form-select form-select-sm rounded-3 shadow-sm" id="filtro_tipo" name="filtro_tipo">
                                        <option value="">Todos los tipos</option>
                                        <?php foreach ($listaTipos as $tipo): ?>
                                            <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo ($filtroTipo === $tipo) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tipo); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-sm fw-bold rounded-pill w-100 shadow">Filtrar</button>
                                </div>
                            </form>

                            <!-- Tabla de Resultados -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tipo Equipo</th>
                                            <th>Usuario</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($listaSolicitudes) > 0): ?>
                                            <?php foreach ($listaSolicitudes as $sol): ?>
                                                <tr>
                                                    <td class="fw-bold">#<?php echo htmlspecialchars($sol['IDSolicitud']); ?></td>
                                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($sol['tipoEquipo']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($sol['NombreUsuario']); ?> (ID: <?php echo htmlspecialchars($sol['IDUsuario']); ?>)</td>
                                                    <td>
                                                        <?php 
                                                            $est = $sol['Estado'];
                                                            $badgeColor = 'warning text-dark';
                                                            if(strtolower($est) === 'aprobada') $badgeColor = 'success';
                                                            if(strtolower($est) === 'rechazada') $badgeColor = 'danger';
                                                            if(strtolower($est) === 'enproceso') $badgeColor = 'info text-dark';
                                                        ?>
                                                        <span class="badge bg-<?php echo $badgeColor; ?>"><?php echo htmlspecialchars($est); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($sol['Fecha']); ?></td>
                                                    <td class="text-center">
                                                        <!-- Botón que activa el modal -->
                                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold rounded-pill px-3"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalActualizar"
                                                                data-id="<?php echo htmlspecialchars($sol['IDSolicitud']); ?>"
                                                                data-tipo="<?php echo htmlspecialchars($sol['tipoEquipo']); ?>"
                                                                data-estado="<?php echo htmlspecialchars($sol['Estado']); ?>"
                                                                data-cantidad="<?php echo htmlspecialchars($sol['Cantidad']); ?>"
                                                                data-fecha="<?php echo htmlspecialchars($sol['Fecha']); ?>"
                                                                data-descripcion="<?php echo htmlspecialchars($sol['Descripcion']); ?>">
                                                            Actualizar
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No se encontraron solicitudes registradas.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- MODAL FLOTANTE PARA EDITAR / ACTUALIZAR SOLICITUD -->
        <div class="modal fade" id="modalActualizar" tabindex="-1" aria-labelledby="modalActualizarLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form action="actualizarEstadoSolicitudEquipo.php" method="POST">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" id="edit_idsolicitud" name="idsolicitud">

                        <div class="modal-header bg-dark text-white border-0 rounded-top-4">
                            <h5 class="modal-title fw-bold" id="modalActualizarLabel">Actualizar Solicitud <span id="edit_titulo_id"></span></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <!-- Tipo de Equipo -->
                                <div class="col-md-6">
                                    <label for="edit_tipoEquipo" class="form-label fw-bold text-dark">Tipo de Equipo</label>
                                    <input type="text" class="form-control rounded-3 shadow-sm" id="edit_tipoEquipo" name="tipoEquipo" required>
                                </div>

                                <!-- Estado -->
                                <div class="col-md-6">
                                    <label for="edit_estado" class="form-label fw-bold text-dark">Estado</label>
                                    <select class="form-select rounded-3 shadow-sm" id="edit_estado" name="estado" required>
                                        <option value="pendiente">pendiente</option>
                                        <option value="EnProceso">EnProceso</option>
                                        <option value="aprobada">aprobada</option>
                                        <option value="rechazada">rechazada</option>
                                    </select>
                                </div>

                                <!-- Cantidad -->
                                <div class="col-md-6">
                                    <label for="edit_cantidad" class="form-label fw-bold text-dark">Cantidad</label>
                                    <input type="number" class="form-control rounded-3 shadow-sm" id="edit_cantidad" name="cantidad" min="1" required>
                                </div>

                                <!-- Fecha -->
                                <div class="col-md-6">
                                    <label for="edit_fecha" class="form-label fw-bold text-dark">Fecha</label>
                                    <input type="date" class="form-control rounded-3 shadow-sm" id="edit_fecha" name="fecha" required>
                                </div>

                                <!-- Descripción -->
                                <div class="col-12">
                                    <label for="edit_descripcion" class="form-label fw-bold text-dark">Descripción / Motivo</label>
                                    <textarea class="form-control rounded-3 shadow-sm" id="edit_descripcion" name="descripcion" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-secondary fw-bold rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-5 shadow">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL DE NOTIFICACIÓN DE RESPUESTA -->
        <?php if (!empty($mensaje)): 
            $esWarning = ($tipoAlerta === 'warning');
            $claseTextoHeader = $esWarning ? 'text-dark' : 'text-white';
            $claseBotonCierre = $esWarning ? '' : 'btn-close-white';
        ?>
        <div class="modal fade" id="modalNotificacion" tabindex="-1" aria-labelledby="modalNotificacionLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-<?php echo $tipoAlerta; ?> <?php echo $claseTextoHeader; ?> border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold" id="modalNotificacionLabel">
                            <?php echo ($tipoAlerta === 'success') ? '¡Operación Exitosa!' : 'Atención'; ?>
                        </h5>
                        <button type="button" class="btn-close <?php echo $claseBotonCierre; ?>" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="fs-5 mb-0"><?php echo htmlspecialchars($mensaje); ?></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-<?php echo $tipoAlerta; ?> fw-bold rounded-pill px-4 <?php echo $esWarning ? 'text-dark' : 'text-white'; ?>" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php include '../footer.php'; ?>
        
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        
        <script>
            <?php if (!empty($mensaje)): ?>
                var modalNoti = new bootstrap.Modal(document.getElementById('modalNotificacion'));
                modalNoti.show();
            <?php endif; ?>

            // Inyectar datos de la fila seleccionada al modal de edición (incluyendo la fecha)
            var modalActualizar = document.getElementById('modalActualizar');
            modalActualizar.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                
                document.getElementById('edit_idsolicitud').value = button.getAttribute('data-id');
                document.getElementById('edit_titulo_id').textContent = '#' + button.getAttribute('data-id');
                document.getElementById('edit_tipoEquipo').value = button.getAttribute('data-tipo');
                document.getElementById('edit_estado').value = button.getAttribute('data-estado');
                document.getElementById('edit_cantidad').value = button.getAttribute('data-cantidad');
                document.getElementById('edit_fecha').value = button.getAttribute('data-fecha');
                document.getElementById('edit_descripcion').value = button.getAttribute('data-descripcion');
            });
        </script>
    </body>
</html>