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

//Procesamiento de peticiones POST (Devoluciones y No Devoluciones)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'registrar_devolucion') {
        $idEquipo = $_POST['id_equipo'] ?? '';
        $estadoDevolucion = $_POST['estado_devolucion'] ?? 'normal'; 
        $nuevoEstadoEquipo = $_POST['nuevo_estado_equipo'] ?? 'Disponible';
        $observacionesDevolucion = $_POST['observaciones_devolucion'] ?? '';
        $idUsuarioResponsable = $_POST['id_usuario_responsable'] ?? 0;
        $aplicarFalta = isset($_POST['aplicar_falta']) ? true : false;

        echo $objOperaciones->registrarDevolucion($idEquipo, $estadoDevolucion, $nuevoEstadoEquipo, $observacionesDevolucion, $idUsuarioResponsable, $aplicarFalta);
        exit;
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'marcar_no_devuelto') {
        $idEquipo = $_POST['id_equipo'] ?? '';
        $idUsuarioResponsable = $_POST['id_usuario_responsable'] ?? 0;
        $observaciones = $_POST['observaciones_no_devuelto'] ?? '';

        echo $objOperaciones->marcarEquipoNoDevuelto($idEquipo, $idUsuarioResponsable, $observaciones);
        exit;
    }
}

$equiposPrestados = $objOperaciones->obtenerEquiposPrestadosActualmente();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestión de Devoluciones - CeiLab</title>
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
                    
                    <div class="card shadow-lg border-0 rounded-4 p-4 mb-5 bg-white bg-opacity-95">
                        
                        <!-- Contenedor para inyectar modales dinámicos -->
                        <div id="contenedorModal"></div>

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div>
                                <h2 class="fw-bold text-dark mb-1">📦 Gestión de Devoluciones</h2>
                                <h5 class="text-muted mb-0">Equipos actualmente prestados en la calle</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="gestionPrestamoEquipos.php" class="btn btn-outline-primary fw-bold rounded-pill px-4 shadow-sm">
                                    ← Ir a Préstamos
                                </a>
                                <a href="calendarioEquipos.php" class="btn btn-outline-secondary fw-bold rounded-pill px-4 shadow-sm">
                                    Calendario
                                </a>
                            </div>
                        </div>

                        <!-- Listado de Equipos Prestados -->
                        <div class="border-0 rounded-4 p-3 shadow-sm bg-light">
                            <?php if (!empty($equiposPrestados)): ?>
                                <div class="overflow-auto pe-1" style="max-height: 550px;">
                                    <div class="row g-3">
                                        <?php foreach ($equiposPrestados as $prestamo): ?>
                                            <div class="col-12">
                                                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-warning border-4">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                        <div>
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <span class="badge bg-secondary px-2 py-1 rounded-pill small">ID: <?php echo htmlspecialchars($prestamo['IDEquipo']); ?></span>
                                                                <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($prestamo['Tipo']); ?></span>
                                                                <span class="badge bg-warning text-dark px-2 py-1 small">⚠️ En Préstamo</span>
                                                            </div>
                                                            <div class="text-muted small mt-1">
                                                                👤 <b>Responsable:</b> <?php echo htmlspecialchars($prestamo['NombreUsuario']); ?> (<?php echo htmlspecialchars($prestamo['email']); ?>)
                                                            </div>
                                                            <div class="text-muted small">
                                                                🕒 <b>Prestado el:</b> <?php echo date('d/m/Y', strtotime($prestamo['Fecha'])); ?> 
                                                                <?php if (!empty($prestamo['HoraInicio'])): ?>
                                                                    (<?php echo substr($prestamo['HoraInicio'], 0, 5) . ' - ' . substr($prestamo['HoraFin'], 0, 5); ?>)
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if ($esAdminOResponsable): ?>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm btn-no-devuelto" 
                                                                    data-idequipo="<?php echo htmlspecialchars($prestamo['IDEquipo']); ?>"
                                                                    data-tipo="<?php echo htmlspecialchars($prestamo['Tipo']); ?>"
                                                                    data-idusuario="<?php echo htmlspecialchars($prestamo['IDUsuario']); ?>"
                                                                    data-nombreusuario="<?php echo htmlspecialchars($prestamo['NombreUsuario']); ?>">
                                                                ⚠️ No Devuelto
                                                            </button>
                                                            <button type="button" class="btn btn-primary btn-sm fw-bold px-4 py-2 rounded-pill shadow-sm btn-devolucion" 
                                                                    data-idequipo="<?php echo htmlspecialchars($prestamo['IDEquipo']); ?>"
                                                                    data-tipo="<?php echo htmlspecialchars($prestamo['Tipo']); ?>"
                                                                    data-idusuario="<?php echo htmlspecialchars($prestamo['IDUsuario']); ?>"
                                                                    data-nombreusuario="<?php echo htmlspecialchars($prestamo['NombreUsuario']); ?>">
                                                                📥 Procesar Devolución
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary text-center py-5 rounded-3 text-muted small shadow-sm mb-0">
                                    No hay equipos prestados en este momento. Todos se encuentran en el laboratorio.
                                </div>
                            <?php endif; ?>
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
        //1. Abrir modal de Devolución Normal/Roto
        document.addEventListener('click', function (e) {
            const btnDev = e.target.closest('.btn-devolucion');
            if (btnDev) {
                const idEquipo = btnDev.getAttribute('data-idequipo');
                const tipoEquipo = btnDev.getAttribute('data-tipo');
                const idUsuario = btnDev.getAttribute('data-idusuario');
                const nombreUsuario = btnDev.getAttribute('data-nombreusuario');

                document.getElementById('contenedorModal').innerHTML = `
                    <div class="modal fade" id="modalDevolucion" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <form id="formDevolucion">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title fw-bold">Procesar Devolución de Equipo</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="accion" value="registrar_devolucion">
                                        <input type="hidden" name="id_equipo" value="${idEquipo}">
                                        <input type="hidden" name="id_usuario_responsable" value="${idUsuario}">

                                        <div class="alert alert-light border small mb-3">
                                            <div><b>Equipo:</b> ${tipoEquipo} (ID: ${idEquipo})</div>
                                            <div><b>Responsable:</b> ${nombreUsuario}</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-secondary">Condición de Devolución:</label>
                                            <select class="form-select rounded-3 shadow-sm" id="selectEstadoDevolucion" name="estado_devolucion" required>
                                                <option value="normal">✅ Normal (Buen estado)</option>
                                                <option value="roto">❌ Roto / Dañado</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-secondary">Nuevo Estado Físico del Equipo:</label>
                                            <select class="form-select rounded-3 shadow-sm" name="nuevo_estado_equipo" id="selectNuevoEstado" required>
                                                <option value="Disponible">Disponible (Listo para prestar)</option>
                                                <option value="EnMantenimiento">En Mantenimiento</option>
                                                <option value="NoDisponible">No Disponible / Baja</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 p-3 bg-warning bg-opacity-10 border border-warning rounded-3 d-none" id="bloqueFalta">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="aplicar_falta" id="checkAplicarFalta" value="1">
                                                <label class="form-check-label fw-bold text-dark small" for="checkAplicarFalta">
                                                    ⚠️ Aplicar sanción de falta (+1 al historial de ${nombreUsuario})
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label fw-bold small text-secondary">Observaciones de la Devolución:</label>
                                            <textarea class="form-control rounded-3 shadow-sm" name="observaciones_devolucion" rows="2" placeholder="Detalles de la devolución..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light justify-content-center">
                                        <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success px-4 fw-bold">Confirmar Devolución</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;

                var modalEl = document.getElementById('modalDevolucion');
                var myModal = new bootstrap.Modal(modalEl);
                myModal.show();

                const selectEstadoDev = document.getElementById('selectEstadoDevolucion');
                const selectNuevoEst = document.getElementById('selectNuevoEstado');
                const bloqueFalta = document.getElementById('bloqueFalta');

                selectEstadoDev.addEventListener('change', function () {
                    const optDisponible = selectNuevoEst.querySelector('option[value="Disponible"]');
                    if (this.value === 'roto') {
                        selectNuevoEst.value = 'EnMantenimiento'; // Fuerza el mantenimiento o no disponible
                        optDisponible.disabled = true;             // Bloquea Disponible
                        bloqueFalta.classList.remove('d-none');   // Muestra el bloque de faltas
                    } else {
                        optDisponible.disabled = false;            // Vuelve a habilitar Disponible
                        selectNuevoEst.value = 'Disponible';      // Vuelve por defecto a Disponible
                        bloqueFalta.classList.add('d-none');      // Oculta faltas
                        document.getElementById('checkAplicarFalta').checked = false;
                    }
                });
            }

            //2. Abrir modal para marcar equipo como "No Devuelto"
            const btnNoDev = e.target.closest('.btn-no-devuelto');
            if (btnNoDev) {
                const idEquipo = btnNoDev.getAttribute('data-idequipo');
                const tipoEquipo = btnNoDev.getAttribute('data-tipo');
                const idUsuario = btnNoDev.getAttribute('data-idusuario');
                const nombreUsuario = btnNoDev.getAttribute('data-nombreusuario');

                document.getElementById('contenedorModal').innerHTML = `
                    <div class="modal fade" id="modalNoDevuelto" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <form id="formNoDevuelto">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title fw-bold">⚠️ Marcar Equipo como No Devuelto</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="accion" value="marcar_no_devuelto">
                                        <input type="hidden" name="id_equipo" value="${idEquipo}">
                                        <input type="hidden" name="id_usuario_responsable" value="${idUsuario}">

                                        <div class="alert alert-danger border small mb-3">
                                            <div><b>Equipo:</b> ${tipoEquipo} (ID: ${idEquipo})</div>
                                            <div><b>Responsable:</b> ${nombreUsuario}</div>
                                            <hr class="my-1">
                                            <span class="fw-bold">Esta acción marcará el equipo como "No Disponible" y aplicará automáticamente una falta (+1) al usuario.</span>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label fw-bold small text-secondary">Motivo u Observaciones:</label>
                                            <textarea class="form-control rounded-3 shadow-sm" name="observaciones_no_devuelto" rows="2" placeholder="Detalle por qué no fue devuelto..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light justify-content-center">
                                        <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger px-4 fw-bold">Confirmar y Sancionar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;

                var modalElND = document.getElementById('modalNoDevuelto');
                var myModalND = new bootstrap.Modal(modalElND);
                myModalND.show();
            }
        });

        //3. Enviar formularios por AJAX
        document.addEventListener('submit', function (e) {
            const formDev = e.target.closest('#formDevolucion') || e.target.closest('#formNoDevuelto');
            if (!formDev) return;
            e.preventDefault();

            const formData = new FormData(formDev);

            fetch('gestionDevolucionEquipos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                //Cerrar modales activos
                ['modalDevolucion', 'modalNoDevuelto'].forEach(id => {
                    var el = document.getElementById(id);
                    if (el) {
                        var modalInst = bootstrap.Modal.getInstance(el);
                        if (modalInst) modalInst.hide();
                    }
                });

                //Inyectar y mostrar modal de respuesta
                document.getElementById('contenedorModal').innerHTML = html;
                var modalRespEl = document.getElementById('modalRespuesta');
                if (modalRespEl) {
                    var myModalResp = new bootstrap.Modal(modalRespEl);
                    myModalResp.show();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    </script>
</body>
</html>