<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 

// Verificación del usuario para que no ingrese por ruta del navegador
$esAdministrador = isset($_SESSION['roles']) && in_array('1', $_SESSION['roles']);
$esDot = isset($_SESSION['roles']) && in_array('2', $_SESSION['roles']);
$esDocente = isset($_SESSION['roles']) && in_array('3', $_SESSION['roles']);

if (!$esAdministrador && !$esDot && !$esDocente) {
    $_SESSION['mensaje'] = "Acceso denegado. Acción no permitida para el rol detectado.";
    $_SESSION['tipoAlerta'] = "danger";
    header("Location: ../index.php");
    exit();
}

require_once '../Clases/ClsSolicitud.php';
$objSolicitud = new ClsSolicitud();

//Toma filtros si se envían por GET
$filtroUsuario = $_GET['filtro_usuario'] ?? '';
$filtroEstado = $_GET['filtro_estado'] ?? '';
$filtroTipo = $_GET['filtro_tipo'] ?? '';

//Obtengo datos para los selectores de filtro
$listaUsuarios = $objSolicitud->listarUsuariosConSolicitudes();
$listaTipos = $objSolicitud->listarTiposEquiposSolicitados();

//Obtengo el listado aplicando los filtros
$listaSolicitudes = $objSolicitud->listarSolicitudesFiltradas($filtroUsuario, $filtroEstado, $filtroTipo);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Consultar Estado de Solicitudes - CeiLab</title>
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
                        
                        <!-- LISTADO Y FILTROS DE SOLICITUDES -->
                        <div class="card shadow-lg border-0 rounded-4 p-4 mb-5 bg-white bg-opacity-95">
                            <h2 class="text-center fw-bold text-dark mb-4">Listado y Estado de Solicitudes</h2>

                            <!-- Formulario de Filtros -->
                            <form method="GET" action="consultarEstadoSolicitudEquipos.php" class="row g-3 mb-4 bg-light p-3 rounded-3 border">
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
                                                        <!-- Botón que activa el modal flotante con los datos de la fila -->
                                                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalDetalle"
                                                                data-id="<?php echo htmlspecialchars($sol['IDSolicitud']); ?>"
                                                                data-tipo="<?php echo htmlspecialchars($sol['tipoEquipo']); ?>"
                                                                data-estado="<?php echo htmlspecialchars($sol['Estado']); ?>"
                                                                data-cantidad="<?php echo htmlspecialchars($sol['Cantidad']); ?>"
                                                                data-usuario="<?php echo htmlspecialchars($sol['NombreUsuario']); ?>"
                                                                data-idusuario="<?php echo htmlspecialchars($sol['IDUsuario']); ?>"
                                                                data-fecha="<?php echo htmlspecialchars($sol['Fecha']); ?>"
                                                                data-descripcion="<?php echo htmlspecialchars($sol['Descripcion']); ?>">
                                                            Ver Detalle
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No se encontraron solicitudes registradas con los filtros seleccionados.</td>
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

        <!-- MODAL FLOTANTE PARA EL DETALLE DE LA SOLICITUD -->
        <div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-dark text-white border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold" id="modalDetalleLabel">Detalle de Solicitud <span id="detIdTitulo"></span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 fs-6">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>ID Solicitud:</strong> <span id="detIDSolicitud"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Tipo de Equipo:</strong> <span id="detTipoEquipo" class="badge bg-secondary"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Estado Actual:</strong> <span id="detEstado" class="badge"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Cantidad:</strong> <span id="detCantidad"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Usuario Solicitante:</strong> <span id="detUsuario"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Fecha:</strong> <span id="detFecha"></span></p>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="p-3 bg-light rounded-3 border">
                                    <strong>Descripción / Motivo:</strong>
                                    <p class="mb-0 mt-1 text-muted" id="detDescripcion"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-secondary fw-bold rounded-pill px-4" data-bs-dismiss="modal">Finalizar Consulta</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../footer.php'; ?>
        
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        
        <script>
            //Script para inyectar dinámicamente los datos de la fila seleccionada dentro del modal flotante
            var modalDetalle = document.getElementById('modalDetalle');
            modalDetalle.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                
                //Extrae información de los atributos data
                var id = button.getAttribute('data-id');
                var tipo = button.getAttribute('data-tipo');
                var estado = button.getAttribute('data-estado');
                var cantidad = button.getAttribute('data-cantidad');
                var usuario = button.getAttribute('data-usuario');
                var idusuario = button.getAttribute('data-idusuario');
                var fecha = button.getAttribute('data-fecha');
                var descripcion = button.getAttribute('data-descripcion');
                
                // Asigna los valores a los elementos del modal
                document.getElementById('detIdTitulo').textContent = '#' + id;
                document.getElementById('detIDSolicitud').textContent = id;
                document.getElementById('detTipoEquipo').textContent = tipo;
                document.getElementById('detCantidad').textContent = cantidad;
                document.getElementById('detUsuario').textContent = usuario + ' (ID: ' + idusuario + ')';
                document.getElementById('detFecha').textContent = fecha;
                document.getElementById('detDescripcion').textContent = descripcion;
                
                // Estiliza la etiqueta del estado dinámicamente
                var spanEstado = document.getElementById('detEstado');
                spanEstado.textContent = estado;
                spanEstado.className = 'badge ';
                var estLower = estado.toLowerCase();
                if (estLower === 'aprobada') {
                    spanEstado.classList.add('bg-success');
                } else if (estLower === 'rechazada') {
                    spanEstado.classList.add('bg-danger');
                } else if (estLower === 'enproceso') {
                    spanEstado.classList.add('bg-info', 'text-dark');
                } else {
                    spanEstado.classList.add('bg-warning', 'text-dark');
                }
            });
        </script>
    </body>
</html>