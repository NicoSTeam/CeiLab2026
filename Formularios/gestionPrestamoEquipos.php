<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
require_once '../Clases/ClsOperaciones.php';
require_once '../Clases/ClsEquipo.php';

$objOperaciones = new ClsOperaciones();
$objEquipo = new ClsEquipo();

//Recuperar ID y Roles de la sesión actual
$idUsuarioSesion = $_SESSION['IDUsuario'] ?? $_SESSION['id_usuario'] ?? null;
$rolesSesion = $_SESSION['Roles'] ?? $_SESSION['roles'] ?? [];

$esAdminOResponsable = false;
if (is_array($rolesSesion)) {
    foreach ($rolesSesion as $r) {
        $rolId = is_array($r) ? ($r['IDRol'] ?? $r['id'] ?? $r['Rol'] ?? '') : $r;
        $rolNombre = is_array($r) ? ($r['nombre'] ?? $r['Rol'] ?? '') : (string)$r;
        
        if (in_array($rolId, [1, 2, '1', '2'], true) || 
            strcasecmp(trim($rolNombre), 'Administrador') === 0 || 
            strcasecmp(trim($rolNombre), 'DOT') === 0 || 
            strcasecmp(trim($rolNombre), 'Responsable-CeiLab') === 0) {
            $esAdminOResponsable = true;
            break;
        }
    }
} elseif (is_numeric($rolesSesion) || is_string($rolesSesion)) {
    if (in_array($rolesSesion, [1, 2, '1', '2'], true) || 
        strcasecmp(trim($rolesSesion), 'Administrador') === 0 || 
        strcasecmp(trim($rolesSesion), 'DOT') === 0 || 
        strcasecmp(trim($rolesSesion), 'Responsable-CeiLab') === 0) {
        $esAdminOResponsable = true;
    }
}

$fechaHoy = date('Y-m-d');

//Parámetros para préstamo directo
$horaInicioDir = $_GET['hora_inicio'] ?? $_POST['hora_inicio'] ?? '';
$horaFinDir = $_GET['hora_fin'] ?? $_POST['hora_fin'] ?? '';
$tipoFiltroDir = $_GET['tipo_equipo'] ?? '';
$listaTiposUnicos = $objEquipo->obtenerTiposEquipos();
$listaUsuarios = $objOperaciones->obtenerUsuariosActivos();

//Procesamiento de peticiones POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'convertir_reserva') {
        $idReserva = $_POST['idReserva'] ?? 0;
        echo $objOperaciones->convertirReservaEnPrestamo($idReserva, $idUsuarioSesion);
        exit;
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'cancelar_reserva') {
        $idReserva = $_POST['idReserva'] ?? 0;
        echo $objOperaciones->cancelarReservaPorDot($idReserva);
        exit;
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'registrar_prestamo_directo') {
        $idUsuarioDestino = $_POST['id_usuario_destino'] ?? 0;
        $equiposSeleccionados = $_POST['equipos'] ?? [];
        echo $objOperaciones->registrarPrestamoDirecto($idUsuarioDestino, $horaInicioDir, $horaFinDir, $equiposSeleccionados);
        exit;
    }
}

$reservasHoy = $objOperaciones->obtenerReservasDelDia($fechaHoy);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestión de Préstamos de Equipos - CeiLab</title>
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
                <div class="col-lg-11">
                    
                    <div class="card shadow-lg border-0 rounded-4 p-4 mb-5 bg-white bg-opacity-95">
                        
                        <!-- Contenedor para inyectar modales dinámicos -->
                        <div id="contenedorModal"></div>

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div>
                                <h2 class="fw-bold text-dark mb-1">Gestión de Préstamos Diarios</h2>
                                <h5 class="text-muted mb-0">Fecha actual: <b><?php echo date('d/m/Y'); ?></b></h5>
                            </div>
                            <a href="/CeiLab2026/index.php" class="btn btn-outline-secondary fw-bold rounded-pill px-4 shadow-sm">
                                ← Volver al inicio
                            </a>
                        </div>

                        <div class="row g-4">
                            <!-- Columna Izquierda: Reservas Programadas para Hoy -->
                            <div class="col-lg-6">
                                <div class="border-0 rounded-4 p-3 shadow-sm h-100 bg-light">
                                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 ">
                                        <span>📅</span> Reservas Programadas Hoy
                                    </h5>
                                    
                                    <?php if (!empty($reservasHoy)): ?>
                                        <div class="overflow-auto pe-1" style="max-height: 480px;">
                                            <div class="row g-3">
                                                <?php foreach ($reservasHoy as $res): 
                                                    $faltasRes = (int)($res['Faltas'] ?? 0);
                                                    $esRiesgoso = $faltasRes > 5;
                                                ?>
                                                    <div class="col-12">
                                                        <div class="card border-0 shadow-sm rounded-3 p-3  border-start <?php echo $esRiesgoso ? 'border-danger bg-danger bg-opacity-10' : 'border-primary'; ?> border-4 position-relative overflow-hidden transition-all">
                                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                                                <div>
                                                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill mb-1 fw-bold" style="font-size: 0.75rem;">
                                                                        🕒 <?php echo substr($res['HoraInicio'], 0, 5) . ' - ' . substr($res['HoraFin'], 0, 5); ?>
                                                                    </span>
                                                                    <h6 class="fw-bold text-dark mb-0 mt-1">
                                                                        👤 <?php echo htmlspecialchars($res['nombre_usuario']); ?>
                                                                        <?php if ($esRiesgoso): ?>
                                                                            <span class="badge bg-danger ms-2 shadow-sm" style="font-size: 0.7rem;">⚠️ <?php echo $faltasRes; ?> Faltas (Riesgo)</span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-secondary bg-opacity-25 text-dark ms-2" style="font-size: 0.7rem;">Faltas: <?php echo $faltasRes; ?></span>
                                                                        <?php endif; ?>
                                                                    </h6>
                                                                </div>
                                                                
                                                                <?php if ($esAdminOResponsable): ?>
                                                                    <div class="d-flex gap-2">
                                                                        <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3 py-1 rounded-pill shadow-sm btn-cancelar-reserva" data-id="<?php echo $res['IDReservas']; ?>" data-usuario="<?php echo htmlspecialchars($res['nombre_usuario']); ?>">
                                                                            ❌ Cancelar
                                                                        </button>
                                                                        <button type="button" class="btn btn-success btn-sm fw-bold px-3 py-1 rounded-pill shadow-sm btn-convertir" data-id="<?php echo $res['IDReservas']; ?>">
                                                                            ✔ Entregar
                                                                        </button>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <hr class="my-2 opacity-10">
                                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                                <?php foreach ($res['equipos'] as $eq): ?>
                                                                    <span class="badge bg-white text-dark border shadow-sm d-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.75rem;">
                                                                        <span class="fw-bold text-primary"><?php echo htmlspecialchars($eq['Tipo']); ?></span> 
                                                                        <span class="text-muted">(ID: <?php echo htmlspecialchars($eq['IDEquipo']); ?>)</span>
                                                                    </span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-light border text-center py-5 rounded-3 text-muted small shadow-sm">
                                            No hay reservas programadas para el día de hoy.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Columna derecha: préstamo directo sin reserva previa -->
                            <div class="col-lg-6">
                                <div class="border rounded-4 p-3 shadow-sm h-100 bg-white">
                                    <h5 class="fw-bold text-dark mb-3"> Préstamo Directo (Sin Reserva)</h5>
                                    
                                    <!-- Paso 1: selecciono horario -->
                                    <form method="GET" action="gestionPrestamoEquipos.php" class="mb-3">
                                        <label class="form-label small fw-bold text-muted mb-1">1. Definir Franja Horaria:</label>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <select class="form-select form-select-sm rounded-3 shadow-sm" name="hora_inicio" required>
                                                    <option value="">Inicio...</option>
                                                    <?php for ($h = 7; $h <= 22; $h++): $val = sprintf("%02d:00:00", $h); ?>
                                                        <option value="<?php echo $val; ?>" <?php echo ($horaInicioDir === $val) ? 'selected' : ''; ?>><?php echo sprintf("%02d:00", $h); ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select class="form-select form-select-sm rounded-3 shadow-sm" name="hora_fin" required>
                                                    <option value="">Fin...</option>
                                                    <?php for ($h = 8; $h <= 23; $h++): $val = sprintf("%02d:00:00", $h); ?>
                                                        <option value="<?php echo $val; ?>" <?php echo ($horaFinDir === $val) ? 'selected' : ''; ?>><?php echo sprintf("%02d:00", $h); ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-outline-primary btn-sm fw-bold w-100 rounded-pill shadow-sm">
                                            Consultar Disponibilidad para Préstamo
                                        </button>
                                    </form>

                                    <!-- Paso 2: selecciono usuario y equipos -->
                                    <?php if (!empty($horaInicioDir) && !empty($horaFinDir)): 
                                        $listaDisponibles = $objEquipo->listarEquiposDisponibles($fechaHoy, $horaInicioDir, $horaFinDir, $tipoFiltroDir);
                                    ?>
                                        <hr class="my-2">
                                        <form id="formPrestamoDirecto">
                                            <input type="hidden" name="accion" value="registrar_prestamo_directo">
                                            <input type="hidden" name="hora_inicio" value="<?php echo $horaInicioDir; ?>">
                                            <input type="hidden" name="hora_fin" value="<?php echo $horaFinDir; ?>">

                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-secondary mb-1">2. Seleccionar Solicitante:</label>
                                                <select class="form-select form-select-sm rounded-3 shadow-sm" name="id_usuario_destino" id="selectUsuarioDestino" required>
                                                    <option value="">Seleccione usuario...</option>
                                                    <?php foreach ($listaUsuarios as $usr): 
                                                        $faltasUsuario = (int)($usr['Faltas'] ?? 0);
                                                        $etiquetaFaltas = $faltasUsuario > 0 ? " — (⚠️ Faltas: {$faltasUsuario})" : " — (Sin faltas)";
                                                    ?>
                                                        <option value="<?php echo $usr['IDUsuario']; ?>" data-faltas="<?php echo $faltasUsuario; ?>">
                                                            <?php echo htmlspecialchars($usr['Nombre']) . ' (' . htmlspecialchars($usr['email']) . ')' . $etiquetaFaltas; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                
                                                <!-- Alerta dinámica si supera las 5 faltas -->
                                                <div id="alertaUsuarioRiesgo" class="alert alert-danger py-2 px-3 mt-2 small d-none shadow-sm mb-0">
                                                    ⚠️ <b>¡Atención!</b> Este usuario supera las 5 faltas acumuladas. Queda a criterio del DOT autorizar o denegar el préstamo.
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-muted mb-1">3. Seleccionar Equipos:</label>
                                                <select class="form-select form-select-sm rounded-3 shadow-sm mb-2" name="tipo_equipo" onchange="this.form.action='gestionPrestamoEquipos.php'; this.form.method='GET'; this.form.submit();">
                                                    <option value="">Todos los tipos...</option>
                                                    <?php foreach ($listaTiposUnicos as $t): ?>
                                                        <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($tipoFiltroDir === $t) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($t); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <?php if (!empty($listaDisponibles)): ?>
                                                    <div class="overflow-auto mb-2 pe-1 border rounded-3 p-2 bg-light" style="max-height: 180px;">
                                                        <div class="row g-2">
                                                            <?php foreach ($listaDisponibles as $eq): 
                                                                $estaPrestado = $objOperaciones->estaEquipoPrestado($eq['IDEquipo']);
                                                            ?>
                                                                <div class="col-12">
                                                                    <div class="card shadow-sm border rounded-2 p-2 <?php echo $estaPrestado ? 'bg-warning bg-opacity-10 border-warning' : 'bg-white'; ?>">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <span class="badge bg-secondary" style="font-size: 0.65rem;"><?php echo htmlspecialchars($eq['Tipo']); ?></span>
                                                                                <span class="fw-bold text-dark small ms-1">ID: <?php echo htmlspecialchars($eq['IDEquipo']); ?></span>
                                                                                
                                                                                <?php if ($estaPrestado): ?>
                                                                                    <span class="badge bg-danger ms-2 shadow-sm" style="font-size: 0.65rem;">⚠️ Ya Prestado</span>
                                                                                <?php endif; ?>
                                                                                
                                                                                <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($eq['Observaciones'] ?? 'Sin observaciones'); ?></div>
                                                                            </div>
                                                                            <div class="form-check">
                                                                                <?php if (!$estaPrestado): ?>
                                                                                    <input class="form-check-input" type="checkbox" name="equipos[]" value="<?php echo htmlspecialchars($eq['IDEquipo']); ?>">
                                                                                <?php else: ?>
                                                                                    <span class="badge bg-secondary text-white small px-2 py-1" style="font-size: 0.7rem;">En uso</span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100 rounded-pill py-2 shadow-sm">
                                                        Confirmar Préstamo Directo
                                                    </button>
                                                <?php else: ?>
                                                    <div class="alert alert-warning text-center small py-2 mb-0">
                                                        No hay equipos disponibles en este horario.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../footer.php'; ?>
    <script src="../assets/js/popper.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. CONVERTIR RESERVA EXISTENTE
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-convertir');
            if (!btn) return;

            const idReserva = btn.getAttribute('data-id');

            document.getElementById('contenedorModal').innerHTML = `
                <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold">Confirmar Entrega de Equipos</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">¿Desea entregar los equipos y registrar el préstamo de esta reserva?</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" id="btnEjecutarPrestamo" class="btn btn-success px-4 fw-bold" data-id="${idReserva}">Sí, Entregar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            var modalConfirmarEl = document.getElementById('modalRespuesta');
            var modalConfirmar = new bootstrap.Modal(modalConfirmarEl);
            modalConfirmar.show();

            const btnEjecutar = document.getElementById('btnEjecutarPrestamo');
            if (btnEjecutar) {
                btnEjecutar.addEventListener('click', function () {
                    const idRes = this.getAttribute('data-id');
                    modalConfirmar.hide();

                    const formData = new FormData();
                    formData.append('accion', 'convertir_reserva');
                    formData.append('idReserva', idRes);

                    fetch('gestionPrestamoEquipos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('contenedorModal').innerHTML = html;
                        var modalEl = document.getElementById('modalRespuesta');
                        if (modalEl) {
                            var myModal = new bootstrap.Modal(modalEl);
                            myModal.show();
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            }
        });

        // 2. CANCELAR RESERVA EXISTENTE (CONTROL DE FALTAS)
        document.addEventListener('click', function (e) {
            const btnCancel = e.target.closest('.btn-cancelar-reserva');
            if (!btnCancel) return;

            const idReserva = btnCancel.getAttribute('data-id');
            const nombreUsuario = btnCancel.getAttribute('data-usuario');

            document.getElementById('contenedorModal').innerHTML = `
                <div class="modal fade" id="modalCancelarReserva" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold">Cancelar Reserva</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-1">¿Está seguro de que desea cancelar la reserva del usuario <b>${nombreUsuario}</b>?</p>
                                <p class="text-muted small mb-0">Esta acción liberará los equipos y desactivará la reserva.</p>
                            </div>
                            <div class="modal-footer justify-content-center bg-light">
                                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Volver</button>
                                <button type="button" id="btnEjecutarCancelacion" class="btn btn-danger px-4 fw-bold" data-id="${idReserva}">Sí, Cancelar Reserva</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            var modalCancelEl = document.getElementById('modalCancelarReserva');
            var modalCancel = new bootstrap.Modal(modalCancelEl);
            modalCancel.show();

            const btnEjecutarCancel = document.getElementById('btnEjecutarCancelacion');
            if (btnEjecutarCancel) {
                btnEjecutarCancel.addEventListener('click', function () {
                    const idRes = this.getAttribute('data-id');
                    modalCancel.hide();

                    const formData = new FormData();
                    formData.append('accion', 'cancelar_reserva');
                    formData.append('idReserva', idRes);

                    fetch('gestionPrestamoEquipos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('contenedorModal').innerHTML = html;
                        var modalEl = document.getElementById('modalRespuesta');
                        if (modalEl) {
                            var myModal = new bootstrap.Modal(modalEl);
                            myModal.show();
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            }
        });

        // 3. ALERTA DINÁMICA DE FALTAS (> 5) EN PRÉSTAMO DIRECTO
        document.addEventListener('change', function (e) {
            if (e.target && e.target.id === 'selectUsuarioDestino') {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const faltas = parseInt(selectedOption.getAttribute('data-faltas') || 0);
                const alertaBox = document.getElementById('alertaUsuarioRiesgo');

                if (alertaBox) {
                    if (faltas > 5) {
                        alertaBox.classList.remove('d-none');
                    } else {
                        alertaBox.classList.add('d-none');
                    }
                }
            }
        });

        // 4. REGISTRAR PRÉSTAMO DIRECTO POR AJAX
        document.addEventListener('submit', function (e) {
            const formDir = e.target.closest('#formPrestamoDirecto');
            if (!formDir) return;
            e.preventDefault();

            const checkboxes = formDir.querySelectorAll('input[name="equipos[]"]:checked');
            if (checkboxes.length === 0) {
                document.getElementById('contenedorModal').innerHTML = `
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title fw-bold">Atención</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">Debe seleccionar al menos un equipo para realizar el préstamo.</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn bg-warning text-dark px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                var modalEl = document.getElementById('modalRespuesta');
                var myModal = new bootstrap.Modal(modalEl);
                myModal.show();
                return;
            }

            const formData = new FormData(formDir);

            fetch('gestionPrestamoEquipos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('contenedorModal').innerHTML = html;
                var modalEl = document.getElementById('modalRespuesta');
                if (modalEl) {
                    var myModal = new bootstrap.Modal(modalEl);
                    myModal.show();
                }
            })
            .catch(error => console.error('Error:', error));
        });

    });
    </script>
</body>
</html>