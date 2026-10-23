<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
require_once '../Clases/ClsSala.php';
require_once '../Clases/ClsReservas.php';

$objSala = new ClsSala();
$objReserva = new ClsReservas();
$idSala = $_GET['idsala'] ?? '';

// Recupera ID y Roles de la sesión actual
$idUsuarioSesion = $_SESSION['IDUsuario'] ?? $_SESSION['id_usuario'] ?? null;
$rolesSesion = $_SESSION['Roles'] ?? $_SESSION['roles'] ?? [];

// Comprobación de roles
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

// Procesamiento de peticiones POST (Crear o Eliminar reserva)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_reserva') {
        $idReservaDel = $_POST['idReserva'] ?? 0;
        echo $objReserva->eliminarReserva($idReservaDel, $idUsuarioSesion, $rolesSesion);
        exit;
    } else {
        echo $objReserva->registrarReservaSala();
        exit;
    }
}

if (empty($idSala)) {
    header("Location: disponibilidadSala.php");
    exit();
}

// Datos de fecha actual del sistema
$anioActual = date('Y');
$mesActual = date('m');
$diaActual = date('j');

// Obtengo año y mes desde la URL, o uso los actuales por defecto
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)$anioActual;
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)$mesActual;
$diaSeleccionado = isset($_GET['dia']) ? (int)$_GET['dia'] : null;

// Límites de meses y años
if ($mes < 1) {
    $mes = 12;
    $anio--;
} elseif ($mes > 12) {
    $mes = 1;
    $anio++;
}

// Calculo mes anterior
$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anioAnterior = $anio - 1;
}

// Calculo mes siguiente
$mesSiguiente = $mes + 1;
$anioSiguiente = $anio;
if ($mesSiguiente > 12) {
    $mesSiguiente = 1;
    $anioSiguiente = $anio + 1;
}

$mesesEspañol = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$nombreMes = $mesesEspañol[$mes] ?? '';
$fechaSeleccionadaKey = ($diaSeleccionado) ? sprintf("%04d-%02d-%02d", $anio, $mes, $diaSeleccionado) : '';

// Consulto las reservas registradas en la Base de Datos para todo el mes
$reservasMesBD = $objReserva->obtenerReservasMes($idSala, $anio, $mes);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Calendario - Sala <?php echo htmlspecialchars($idSala); ?> - CeiLab</title>
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
                            
                            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                                <div>
                                    <h2 class="fw-bold text-dark mb-1">Calendario de Sala</h2>
                                    <h5 class="text-muted mb-0">Sala seleccionada: <span class="badge bg-secondary"><?php echo htmlspecialchars($idSala); ?></span></h5>
                                </div>
                                <a href="disponibilidadSala.php" class="btn btn-outline-secondary fw-bold rounded-pill px-4 shadow-sm">
                                    ← Volver a Salas
                                </a>
                            </div>

                            <div class="row g-4">
                                <!-- Columna del Calendario Principal -->
                                <div class="<?php echo $diaSeleccionado ? 'col-lg-7' : 'col-lg-12'; ?>">
                                    <div class="border rounded-4 p-4 shadow-sm h-100" style="background-color: #adbfdc !important;">
                                        <div class="bg-white rounded-4 p-3 shadow-sm h-100 d-flex flex-column justify-content-between">
                                            
                                            <!-- Cabecera del Mes con la Nav -->
                                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                                <h4 class="fw-bold text-dark mb-0 fs-5">
                                                    <?php echo "$nombreMes $anio"; ?>
                                                </h4>
                                                <div class="btn-group shadow-sm">
                                                    <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anioAnterior; ?>&mes=<?php echo $mesAnterior; ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes Anterior">
                                                        ◀
                                                    </a>
                                                    <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes actual">
                                                        Hoy
                                                    </a>
                                                    <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anioSiguiente; ?>&mes=<?php echo $mesSiguiente; ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes Siguiente">
                                                        ▶
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Días del Mes -->
                                            <div class="table-responsive">
                                                <table class="table table-bordered text-center align-middle mb-0 small">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>LU</th><th>MA</th><th>MI</th><th>JU</th><th>VI</th><th>SA</th><th>DO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $primerDiaMes = mktime(0, 0, 0, $mes, 1, $anio);
                                                        $totalDiasMes = date('t', $primerDiaMes);
                                                        $diaSemanaInicio = date('N', $primerDiaMes);
                                                        
                                                        $esMesActual = ($anio == (int)$anioActual && $mes == (int)$mesActual);

                                                        echo "<tr>";
                                                        for ($i = 1; $i < $diaSemanaInicio; $i++) {
                                                            echo "<td class='bg-light text-muted'></td>";
                                                        }

                                                        for ($dia = 1; $dia <= $totalDiasMes; $dia++) {
                                                            $esHoy = ($esMesActual && $dia == (int)$diaActual);
                                                            $esSeleccionado = ($diaSeleccionado == $dia);
                                                            $fechaIteracion = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
                                                            $tieneReservasGuardadas = !empty($reservasMesBD[$fechaIteracion]);
                                                            
                                                            // Valida si el día ya pasó
                                                            $esPasado = ($fechaIteracion < date('Y-m-d'));
                                                            
                                                            if ($esSeleccionado) {
                                                                $claseDia = 'bg-warning text-dark fw-bold rounded shadow-sm';
                                                            } elseif ($esHoy) {
                                                                $claseDia = 'bg-primary text-white fw-bold rounded shadow-sm';
                                                            } elseif ($esPasado) {
                                                                // Cambio visual para días pasados tipo desactivado
                                                                $claseDia = 'bg-light text-muted opacity-50 fw-semibold text-decoration-none';
                                                            } elseif ($tieneReservasGuardadas) {
                                                                $claseDia = 'bg-success bg-opacity-25 text-dark fw-bold border border-success rounded';
                                                            } else {
                                                                $claseDia = 'text-dark fw-semibold bg-light';
                                                            }
                                                            
                                                            echo "<td class='p-1 position-relative'>";
                                                            echo "<a href='calendarioSala.php?idsala=" . urlencode($idSala) . "&anio=$anio&mes=$mes&dia=$dia' class='text-decoration-none d-block p-2 $claseDia' style='border-radius: 8px;'>" . $dia . "</a>";
                                                            
                                                            if ($tieneReservasGuardadas && !$esHoy && !$esSeleccionado) {
                                                                echo '<span class="position-absolute top-0 start-75 translate-middle p-1 bg-success border border-light rounded-circle" style="width: 8px; height: 8px;"></span>';
                                                            }
                                                            
                                                            echo "</td>";

                                                            if (($dia + $diaSemanaInicio - 1) % 7 == 0 && $dia < $totalDiasMes) {
                                                                echo "</tr><tr>";
                                                            }
                                                        }

                                                        $celdasRestantes = (7 - (($totalDiasMes + $diaSemanaInicio - 1) % 7)) % 7;
                                                        for ($i = 0; $i < $celdasRestantes; $i++) {
                                                            echo "<td class='bg-light text-muted'></td>";
                                                        }
                                                        echo "</tr>";
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="text-muted small mt-3 text-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-bar-up" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M8 10a.5.5 0 0 0 .5-.5V3.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 3.707V9.5a.5.5 0 0 0 .5.5m-7 2.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5"/>
                                                </svg> 
                                                Haga clic en cualquier día para consultar reservas o registrar un nuevo horario.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel Lateral para el Día Seleccionado -->
                                <?php if ($diaSeleccionado): ?>
                                <div class="col-lg-5">
                                    <div class="border rounded-4 p-4 shadow-sm h-100 bg-white d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="fw-bold text-dark mb-0 fs-5">
                                                    Día: <?php echo "$diaSeleccionado de $nombreMes $anio"; ?>
                                                </h4>
                                                <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>" class="btn-close" aria-label="Cerrar"></a>
                                            </div>
                                            
                                            <!-- Contenedor para inyectar modales dinámicos -->
                                            <div id="contenedorModal"></div>

                                            <!-- Lista de Reservas Existentes para este día -->
                                            <h6 class="text-muted fw-bold small mb-2">Reservas Registradas:</h6>
                                            <div class="list-group mb-4 overflow-auto" style="max-height: 180px;">
                                                <?php 
                                                if (!empty($reservasMesBD[$fechaSeleccionadaKey])):
                                                    foreach ($reservasMesBD[$fechaSeleccionadaKey] as $res):
                                                        $esDuenio = ($res['IDUsuario'] == $idUsuarioSesion);
                                                ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 mb-2 rounded-3 border shadow-sm">
                                                        <div>
                                                            <span class="fw-bold text-dark"><?php echo substr($res['HoraInicio'], 0, 5) . ' - ' . substr($res['HoraFin'], 0, 5); ?></span>
                                                            <div class="text-muted small">Reservado por: <?php echo htmlspecialchars($res['nombre_usuario']); ?></div>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-danger bg-opacity-75 text-white">Ocupado</span>
                                                            
                                                            <!-- El botón se muestra si es Administrador, si es DOT o si fue quien hizo la res -->
                                                            <?php if ($esAdminOResponsable || $esDuenio): ?>
                                                                <button type="button" class="btn btn-outline-danger btn-sm border-0 fw-bold px-2 btn-eliminar-reserva" data-id="<?php echo $res['IDReservas']; ?>" title="Cancelar reserva">
                                                                    ✕
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php 
                                                    endforeach;
                                                else: 
                                                ?>
                                                    <div class="text-muted small fst-italic p-2 bg-light rounded-3 text-center">
                                                        No hay reservas registradas para este día. ¡Todo disponible!
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php 
                                            $esDiaPasado = ($fechaSeleccionadaKey < date('Y-m-d'));
                                            if ($esDiaPasado): 
                                            ?>
                                                <!-- Aviso si la fecha ya pasó -->
                                                <div class="alert alert-secondary py-3 text-center small mb-0 rounded-3 shadow-sm">
                                                    <i class="bi bi-calendar-x-fill text-muted fs-5 d-block mb-1"></i>
                                                    Esta fecha ya ha pasado. No es posible realizar nuevas reservas.
                                                </div>
                                            <?php else: ?>
                                                <!-- Formulario para Registrar Nueva Reserva (Solo para hoy y fechas futuras) -->
                                                <form id="formReserva" class="border-top pt-3">
                                                    <input type="hidden" name="idsala" value="<?php echo htmlspecialchars($idSala); ?>">
                                                    <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionadaKey; ?>">

                                                    <h6 class="fw-bold text-dark small mb-3">Crear nueva reserva:</h6>
                                                    
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small text-muted mb-1">Hora Inicio</label>
                                                            <select class="form-select form-select-sm rounded-3 shadow-sm" id="hora_inicio" name="hora_inicio" required>
                                                                <option value="">Seleccione...</option>
                                                                <?php for ($h = 7; $h <= 22; $h++): ?>
                                                                    <option value="<?php echo sprintf("%02d:00:00", $h); ?>"><?php echo sprintf("%02d:00", $h); ?></option>
                                                                <?php endfor; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small text-muted mb-1">Hora Fin</label>
                                                            <select class="form-select form-select-sm rounded-3 shadow-sm" id="hora_fin" name="hora_fin" required>
                                                                <option value="">Seleccione...</option>
                                                                <?php for ($h = 8; $h <= 23; $h++): ?>
                                                                    <option value="<?php echo sprintf("%02d:00:00", $h); ?>"><?php echo sprintf("%02d:00", $h); ?></option>
                                                                <?php endfor; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100 rounded-pill py-2 shadow-sm">
                                                        Confirmar Reserva
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-3 text-center">
                                            <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>" class="btn btn-light btn-sm text-secondary fw-bold rounded-pill w-100 border shadow-sm py-2">
                                                ✕ Cerrar panel de reservas
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    //Envío form de reserva por rango de horario
                                    const formReserva = document.getElementById('formReserva');
                                    const horaInicioSelect = document.getElementById('hora_inicio');
                                    const horaFinSelect = document.getElementById('hora_fin');

                                    if (formReserva) {
                                        formReserva.addEventListener('submit', function (e) {
                                            e.preventDefault();
                                            
                                            const inicio = horaInicioSelect.value;
                                            const fin = horaFinSelect.value;

                                            if (inicio >= fin) {
                                                document.getElementById('contenedorModal').innerHTML = `
                                                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content shadow border-0">
                                                                <div class="modal-header bg-warning text-dark">
                                                                    <h5 class="modal-title fw-bold">Atención</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-center py-4">
                                                                    <p class="fs-5 text-dark mb-0">La hora de fin debe ser posterior a la hora de inicio.</p>
                                                                </div>
                                                                <div class="modal-footer justify-content-center">
                                                                    <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                                                var modalEl = document.getElementById('modalRespuesta');
                                                if (modalEl) {
                                                    var myModal = new bootstrap.Modal(modalEl);
                                                    myModal.show();
                                                }
                                                return;
                                            }

                                            const formData = new FormData(this);

                                            fetch('calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&dia=<?php echo $diaSeleccionado; ?>', {
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

                                    //Eventos para el botón de eliminar reserva (la cruz)
                                    document.addEventListener('click', function (e) {
                                        const boton = e.target.closest('.btn-eliminar-reserva');
                                        if (!boton) return;

                                        const idReserva = boton.getAttribute('data-id');

                                        //Inyectamos el modal de confirmación en el contenedor
                                        document.getElementById('contenedorModal').innerHTML = `
                                            <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content shadow border-0">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title fw-bold">Confirmar Cancelación</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-center py-4">
                                                            <p class="fs-5 text-dark mb-0">¿Está seguro de que desea cancelar esta reserva?</p>
                                                        </div>
                                                        <div class="modal-footer justify-content-center">
                                                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">No, volver</button>
                                                            <button type="button" id="btnConfirmarBorrar" class="btn btn-danger px-4 fw-bold" data-id="${idReserva}">Sí, cancelar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;

                                        //Muestro el modal de confirmación
                                        var modalConfirmarEl = document.getElementById('modalConfirmarEliminar');
                                        var modalConfirmar = new bootstrap.Modal(modalConfirmarEl);
                                        modalConfirmar.show();

                                        //Al hacer clic en "Sí, cancelar" dentro del modal
                                        const btnConfirmar = document.getElementById('btnConfirmarBorrar');
                                        if (btnConfirmar) {
                                            btnConfirmar.addEventListener('click', function () {
                                                const id = this.getAttribute('data-id');
                                                
                                                // Oculta el modal de confirmación
                                                modalConfirmar.hide();

                                                const formData = new FormData();
                                                formData.append('accion', 'eliminar_reserva');
                                                formData.append('idReserva', id);

                                                fetch('calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&dia=<?php echo $diaSeleccionado; ?>', {
                                                    method: 'POST',
                                                    body: formData
                                                })
                                                .then(response => response.text())
                                                .then(html => {
                                                    document.getElementById('contenedorModal').innerHTML = html;
                                                    var modalRespuestaEl = document.getElementById('modalRespuesta');
                                                    if (modalRespuestaEl) {
                                                        var myModalRespuesta = new bootstrap.Modal(modalRespuestaEl);
                                                        myModalRespuesta.show();
                                                    }
                                                })
                                                .catch(error => console.error('Error:', error));
                                            });
                                        }
                                    });
                                });
                                </script>
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
    </body>
</html>