<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

require_once '../Clases/ClsEquipo.php';
require_once '../Clases/ClsReservas.php';

$objEquipo = new ClsEquipo();
$objReserva = new ClsReservas();

//Recupera ID y Roles de la sesión actual
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

//Parámetros de fecha, horario y filtro de equipo
$anioActual = date('Y');
$mesActual = date('m');
$diaActual = date('j');

$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)$anioActual;
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)$mesActual;
$diaSeleccionado = isset($_GET['dia']) ? (int)$_GET['dia'] : null;

$horaInicio = $_GET['hora_inicio'] ?? $_POST['hora_inicio'] ?? '';
$horaFin = $_GET['hora_fin'] ?? $_POST['hora_fin'] ?? '';
$tipoFiltro = $_GET['tipo_equipo'] ?? '';

if ($mes < 1) { $mes = 12; $anio--; } 
elseif ($mes > 12) { $mes = 1; $anio++; }

$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) { $mesAnterior = 12; $anioAnterior--; }

$mesSiguiente = $mes + 1;
$anioSiguiente = $anio;
if ($mesSiguiente > 12) { $mesSiguiente = 1; $anioSiguiente++; }

$mesesEspañol = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$nombreMes = $mesesEspañol[$mes] ?? '';
$fechaSeleccionadaKey = ($diaSeleccionado) ? sprintf("%04d-%02d-%02d", $anio, $mes, $diaSeleccionado) : '';

//Procesamiento de peticiones POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_reserva') {
        $idReservaDel = $_POST['idReserva'] ?? 0;
        echo $objReserva->eliminarReserva($idReservaDel, $idUsuarioSesion, $rolesSesion);
        exit;
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_equipo_reserva') {
        $idReservaDel = $_POST['idReserva'] ?? 0;
        $idEquipoDel = $_POST['idEquipo'] ?? '';
        echo $objReserva->eliminarEquipoDeReserva($idReservaDel, $idEquipoDel, $idUsuarioSesion, $rolesSesion);
        exit;
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'reservar_equipos') {
        $equiposSeleccionados = $_POST['equipos'] ?? [];
        echo $objReserva->registrarReservaEquipos($idUsuarioSesion, $fechaSeleccionadaKey, $horaInicio, $horaFin, $equiposSeleccionados);
        exit;
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'agregar_equipos_existente') {
        $idReserva = $_POST['idReserva'] ?? 0;
        $equiposNuevos = $_POST['equipos_nuevos'] ?? [];
        echo $objReserva->agregarEquiposAReserva($idReserva, $equiposNuevos);
        exit;
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'cargar_modal_agregar') {
        $idRes = $_POST['idReserva'] ?? 0;
        $disponiblesExtra = $objReserva->listarEquiposDisponiblesParaReservaExistente($idRes);
        
        $html = '
        <div class="modal fade" id="modalAgregarEquipos" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">Agregar Equipos a la Reserva</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formAgregarExtra">
                        <div class="modal-body py-3">
                            <input type="hidden" name="accion" value="agregar_equipos_existente">
                            <input type="hidden" name="idReserva" value="' . $idRes . '">';
                            
        if (!empty($disponiblesExtra)) {
            $html .= '<p class="text-muted small mb-2">Seleccione los equipos que desea incorporar:</p>
                      <div class="overflow-auto pe-1 border rounded-3 p-2 bg-light" style="max-height: 200px;">
                          <div class="row g-2">';
            foreach ($disponiblesExtra as $eq) {
                $html .= '
                    <div class="col-12">
                        <div class="card shadow-sm border rounded-3 p-2 bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-secondary mb-1" style="font-size: 0.65rem;">' . htmlspecialchars($eq['Tipo']) . '</span>
                                    <h6 class="fw-bold text-dark mb-0 small">ID: ' . htmlspecialchars($eq['IDEquipo']) . '</h6>
                                    <span class="text-muted" style="font-size: 0.75rem;">' . htmlspecialchars($eq['Observaciones'] ?? 'Sin observaciones') . '</span>
                                </div>
                                <div class="form-check fs-5">
                                    <input class="form-check-input" type="checkbox" name="equipos_nuevos[]" value="' . htmlspecialchars($eq['IDEquipo']) . '">
                                </div>
                            </div>
                        </div>
                    </div>';
            }
            $html .= '</div></div>';
        } else {
            $html .= '<div class="alert alert-warning text-center small mb-0">No hay equipos adicionales disponibles para este horario.</div>';
        }

        $html .= '  </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>';
        if (!empty($disponiblesExtra)) {
            $html .= '      <button type="submit" class="btn btn-primary px-4 fw-bold">Guardar Cambios</button>';
        }
        $html .= '      </div>
                    </form>
                </div>
            </div>
        </div>';
        echo $html;
        exit;
    }
}

//Obtengo reservas de equipos registradas en el mes
$reservasMesBD = $objReserva->obtenerReservasEquiposMes($anio, $mes);
$listaTiposUnicos = $objEquipo->obtenerTiposEquipos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Calendario de Reserva de Equipos - CeiLab</title>
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
                                <h2 class="fw-bold text-dark mb-1">Calendario de Reserva de Equipos</h2>
                                <h5 class="text-muted mb-0">Seleccione un día para consultar disponibilidad y reservar múltiples equipos</h5>
                            </div>
                            <a href="/CeiLab2026/Formularios/listadoEquipos.php" class="btn btn-outline-secondary fw-bold rounded-pill px-4 shadow-sm">
                                ← Ver Estado de Equipos
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
                                                <a href="calendarioEquipos.php?anio=<?php echo $anioAnterior; ?>&mes=<?php echo $mesAnterior; ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes Anterior">
                                                    ◀
                                                </a>
                                                <a href="calendarioEquipos.php" class="btn btn-outline-dark btn-sm fw-bold" title="Mes actual">
                                                    Hoy
                                                </a>
                                                <a href="calendarioEquipos.php?anio=<?php echo $anioSiguiente; ?>&mes=<?php echo $mesSiguiente; ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes Siguiente">
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
                                                        
                                                        $esPasado = ($fechaIteracion < date('Y-m-d'));
                                                        
                                                        if ($esSeleccionado) {
                                                            $claseDia = 'bg-warning text-dark fw-bold rounded shadow-sm';
                                                        } elseif ($esHoy) {
                                                            $claseDia = 'bg-primary text-white fw-bold rounded shadow-sm';
                                                        } elseif ($esPasado) {
                                                            $claseDia = 'bg-light text-muted opacity-50 fw-semibold text-decoration-none';
                                                        } elseif ($tieneReservasGuardadas) {
                                                            $claseDia = 'bg-success bg-opacity-25 text-dark fw-bold border border-success rounded';
                                                        } else {
                                                            $claseDia = 'text-dark fw-semibold bg-light';
                                                        }
                                                        
                                                        echo "<td class='p-1 position-relative'>";
                                                        echo "<a href='calendarioEquipos.php?anio=$anio&mes=$mes&dia=$dia' class='text-decoration-none d-block p-2 $claseDia' style='border-radius: 8px;'>" . $dia . "</a>";
                                                        
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
                                            Haga clic en cualquier día para consultar reservas de equipos o realizar una nueva solicitud.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel Lateral para el día Seleccionado -->
                            <?php if ($diaSeleccionado): ?>
                            <div class="col-lg-5">
                                <div class="border rounded-4 p-4 shadow-sm h-100 bg-white d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="fw-bold text-dark mb-0 fs-5">
                                                Día: <?php echo "$diaSeleccionado de $nombreMes $anio"; ?>
                                            </h4>
                                            <a href="calendarioEquipos.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>" class="btn-close" aria-label="Cerrar"></a>
                                        </div>
                                        
                                        <!-- Contenedor para inyectar modales dinámicos -->
                                        <div id="contenedorModal"></div>

                                        <!-- Lista de Reservas de Equipos Agrupadas -->
                                        <h6 class="text-muted fw-bold small mb-2">Reservas de Equipos Registradas:</h6>
                                        <div class="list-group mb-4 overflow-auto" style="max-height: 190px;">
                                            <?php 
                                            if (!empty($reservasMesBD[$fechaSeleccionadaKey])):
                                                $reservasAgrupadas = [];
                                                foreach ($reservasMesBD[$fechaSeleccionadaKey] as $res) {
                                                    $idRes = $res['IDReservas'];
                                                    if (!isset($reservasAgrupadas[$idRes])) {
                                                        $reservasAgrupadas[$idRes] = [
                                                            'IDReservas' => $res['IDReservas'],
                                                            'HoraInicio' => $res['HoraInicio'],
                                                            'HoraFin' => $res['HoraFin'],
                                                            'IDUsuario' => $res['IDUsuario'],
                                                            'nombre_usuario' => $res['nombre_usuario'],
                                                            'equipos' => []
                                                        ];
                                                    }
                                                    $reservasAgrupadas[$idRes]['equipos'][] = [
                                                        'IDEquipo' => $res['IDEquipo'],
                                                        'Tipo' => $res['Tipo']
                                                    ];
                                                }

                                                foreach ($reservasAgrupadas as $reservaItem):
                                                    $esDuenio = ($reservaItem['IDUsuario'] == $idUsuarioSesion);
                                            ?>
                                                <div class="list-group-item py-2 px-3 mb-2 rounded-3 border shadow-sm bg-white">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="fw-bold text-dark small">
                                                            🕒 <?php echo substr($reservaItem['HoraInicio'], 0, 5) . ' - ' . substr($reservaItem['HoraFin'], 0, 5); ?>
                                                        </span>
                                                        <div class="d-flex gap-1">
                                                            <?php if ($esAdminOResponsable || $esDuenio): ?>
                                                                <button type="button" class="btn btn-outline-primary btn-sm border-0 py-0 px-2 btn-abrir-agregar" data-id="<?php echo $reservaItem['IDReservas']; ?>" title="Agregar más equipos">
                                                                    + Agregar
                                                                </button>
                                                                <button type="button" class="btn btn-outline-danger btn-sm border-0 fw-bold py-0 px-2 btn-eliminar-reserva" data-id="<?php echo $reservaItem['IDReservas']; ?>" title="Cancelar toda la reserva">
                                                                    Cancelar Toda ✕
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-muted small mb-1">Por: <?php echo htmlspecialchars($reservaItem['nombre_usuario']); ?></div>
                                                    
                                                    <!-- Listado de equipos individuales -->
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <?php foreach ($reservaItem['equipos'] as $eq): ?>
                                                            <span class="badge bg-light text-dark border d-flex align-items-center gap-1 py-1 px-2">
                                                                <span class="fw-bold text-primary"><?php echo htmlspecialchars($eq['Tipo']); ?></span> 
                                                                <span class="text-muted">(ID: <?php echo htmlspecialchars($eq['IDEquipo']); ?>)</span>
                                                                <?php if ($esAdminOResponsable || $esDuenio): ?>
                                                                    <button type="button" class="btn btn-link text-danger p-0 text-decoration-none fw-bold btn-eliminar-equipo ms-1" data-id-reserva="<?php echo $reservaItem['IDReservas']; ?>" data-id-equipo="<?php echo htmlspecialchars($eq['IDEquipo']); ?>" style="font-size: 0.75rem;" title="Quitar este equipo">
                                                                        ✕
                                                                    </button>
                                                                <?php endif; ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php 
                                                endforeach;
                                            else: 
                                            ?>
                                                <div class="text-muted small fst-italic p-2 bg-light rounded-3 text-center">
                                                    No hay reservas de equipos registradas para este día.
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php 
                                        $esDiaPasado = ($fechaSeleccionadaKey < date('Y-m-d'));
                                        if ($esDiaPasado): 
                                        ?>
                                            <div class="alert alert-secondary py-3 text-center small mb-0 rounded-3 shadow-sm">
                                                Esta fecha ya ha pasado. No es posible realizar nuevas reservas.
                                            </div>
                                        <?php else: ?>
                                            <!-- Definir horario y filtrar equipos -->
                                            <form method="GET" action="calendarioEquipos.php" class="border-top pt-3 mb-3">
                                                <input type="hidden" name="anio" value="<?php echo $anio; ?>">
                                                <input type="hidden" name="mes" value="<?php echo $mes; ?>">
                                                <input type="hidden" name="dia" value="<?php echo $diaSeleccionado; ?>">

                                                <h6 class="fw-bold text-dark small mb-2">1. Seleccione el horario:</h6>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted mb-1">Inicio</label>
                                                        <select class="form-select form-select-sm rounded-3 shadow-sm" name="hora_inicio" required>
                                                            <option value="">Seleccione...</option>
                                                            <?php for ($h = 7; $h <= 22; $h++): $val = sprintf("%02d:00:00", $h); ?>
                                                                <option value="<?php echo $val; ?>" <?php echo ($horaInicio === $val) ? 'selected' : ''; ?>><?php echo sprintf("%02d:00", $h); ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted mb-1">Fin</label>
                                                        <select class="form-select form-select-sm rounded-3 shadow-sm" name="hora_fin" required>
                                                            <option value="">Seleccione...</option>
                                                            <?php for ($h = 8; $h <= 23; $h++): $val = sprintf("%02d:00:00", $h); ?>
                                                                <option value="<?php echo $val; ?>" <?php echo ($horaFin === $val) ? 'selected' : ''; ?>><?php echo sprintf("%02d:00", $h); ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-outline-primary btn-sm fw-bold w-100 rounded-pill py-2 shadow-sm">
                                                    Consultar Equipos Disponibles
                                                </button>
                                            </form>

                                            <!-- Mostrar listado de equipos con tarjetas y filtro por tipo -->
                                            <?php if (!empty($horaInicio) && !empty($horaFin)): 
                                                $listaDisponibles = $objEquipo->listarEquiposDisponibles($fechaSeleccionadaKey, $horaInicio, $horaFin, $tipoFiltro);
                                            ?>
                                                <hr class="my-2">
                                                <h6 class="fw-bold text-dark small mb-2">2. Filtrar y Seleccionar Equipos:</h6>
                                                
                                                <!-- Selector de Tipo de Equipo -->
                                                <form method="GET" action="calendarioEquipos.php" class="mb-2">
                                                    <input type="hidden" name="anio" value="<?php echo $anio; ?>">
                                                    <input type="hidden" name="mes" value="<?php echo $mes; ?>">
                                                    <input type="hidden" name="dia" value="<?php echo $diaSeleccionado; ?>">
                                                    <input type="hidden" name="hora_inicio" value="<?php echo $horaInicio; ?>">
                                                    <input type="hidden" name="hora_fin" value="<?php echo $horaFin; ?>">

                                                    <select class="form-select form-select-sm rounded-3 shadow-sm" name="tipo_equipo" onchange="this.form.submit()">
                                                        <option value="">Todos los tipos de equipos...</option>
                                                        <?php foreach ($listaTiposUnicos as $t): ?>
                                                            <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($tipoFiltro === $t) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($t); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>

                                                <?php if (!empty($listaDisponibles)): ?>
                                                    <form id="formReservaEquipos">
                                                        <input type="hidden" name="accion" value="reservar_equipos">
                                                        <input type="hidden" name="hora_inicio" value="<?php echo $horaInicio; ?>">
                                                        <input type="hidden" name="hora_fin" value="<?php echo $horaFin; ?>">

                                                        <div class="overflow-auto mb-3 pe-1 border rounded-3 p-2 bg-light" style="max-height: 160px;">
                                                            <div class="row g-2">
                                                                <?php foreach ($listaDisponibles as $eq): ?>
                                                                    <div class="col-12">
                                                                        <div class="card shadow-sm border rounded-3 p-2 bg-white">
                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                <div>
                                                                                    <span class="badge bg-secondary mb-1" style="font-size: 0.65rem;"><?php echo htmlspecialchars($eq['Tipo']); ?></span>
                                                                                    <h6 class="fw-bold text-dark mb-0 small">ID: <?php echo htmlspecialchars($eq['IDEquipo']); ?></h6>
                                                                                    <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($eq['Observaciones'] ?? 'Sin observaciones'); ?></span>
                                                                                </div>
                                                                                <div class="form-check fs-5">
                                                                                    <input class="form-check-input" type="checkbox" name="equipos[]" value="<?php echo htmlspecialchars($eq['IDEquipo']); ?>" id="eq_<?php echo $eq['IDEquipo']; ?>">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>

                                                        <button type="submit" class="btn btn-primary btn-sm fw-bold w-100 rounded-pill py-2 shadow-sm">
                                                            Confirmar Reserva de Equipos
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <div class="alert alert-warning text-center small py-2 mb-0">
                                                        No hay equipos disponibles de este tipo en el horario seleccionado.
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-3 text-center">
                                        <a href="calendarioEquipos.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>" class="btn btn-light btn-sm text-secondary fw-bold rounded-pill w-100 border shadow-sm py-2">
                                            ✕ Cerrar panel de reservas
                                        </a>
                                    </div>
                                </div>
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
        //Envío por AJAX del formulario de reserva múltiple de equipos
        const formEquipos = document.getElementById('formReservaEquipos');
        if (formEquipos) {
            formEquipos.addEventListener('submit', function (e) {
                e.preventDefault();
                
                const checkboxes = this.querySelectorAll('input[name="equipos[]"]:checked');
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
                                        <p class="fs-5 text-dark mb-0">Debe seleccionar al menos un equipo para realizar la reserva.</p>
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
                formData.append('accion', 'reservar_equipos');

                fetch('calendarioEquipos.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&dia=<?php echo $diaSeleccionado; ?>&hora_inicio=<?php echo urlencode($horaInicio); ?>&hora_fin=<?php echo urlencode($horaFin); ?>&tipo_equipo=<?php echo urlencode($tipoFiltro); ?>', {
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

        //Eventos para el botón de eliminar reserva completa
        document.addEventListener('click', function (e) {
            const boton = e.target.closest('.btn-eliminar-reserva');
            if (!boton) return;

            const idReserva = boton.getAttribute('data-id');

            document.getElementById('contenedorModal').innerHTML = `
                <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold">Confirmar Cancelación</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">¿Está seguro de que desea cancelar toda esta reserva de equipos?</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">No, volver</button>
                                <button type="button" id="btnConfirmarBorrar" class="btn btn-danger px-4 fw-bold" data-id="${idReserva}">Sí, cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            var modalConfirmarEl = document.getElementById('modalConfirmarEliminar');
            var modalConfirmar = new bootstrap.Modal(modalConfirmarEl);
            modalConfirmar.show();

            const btnConfirmar = document.getElementById('btnConfirmarBorrar');
            if (btnConfirmar) {
                btnConfirmar.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    modalConfirmar.hide();

                    const formData = new FormData();
                    formData.append('accion', 'eliminar_reserva');
                    formData.append('idReserva', id);

                    fetch('calendarioEquipos.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&dia=<?php echo $diaSeleccionado; ?>', {
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

        //Evento para quitar un equipo individual de una reserva
        document.addEventListener('click', function (e) {
            const botonEq = e.target.closest('.btn-eliminar-equipo');
            if (!botonEq) return;

            const idReserva = botonEq.getAttribute('data-id-reserva');
            const idEquipo = botonEq.getAttribute('data-id-equipo');

            document.getElementById('contenedorModal').innerHTML = `
                <div class="modal fade" id="modalConfirmarEliminarEq" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title fw-bold">Quitar Equipo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">¿Desea quitar el equipo <b>${idEquipo}</b> de esta reserva?</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">No, volver</button>
                                <button type="button" id="btnConfirmarBorrarEq" class="btn btn-warning px-4 fw-bold text-dark" data-id-reserva="${idReserva}" data-id-equipo="${idEquipo}">Sí, quitar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            var modalConfirmarEl = document.getElementById('modalConfirmarEliminarEq');
            var modalConfirmar = new bootstrap.Modal(modalConfirmarEl);
            modalConfirmar.show();

            const btnConfirmarEq = document.getElementById('btnConfirmarBorrarEq');
            if (btnConfirmarEq) {
                btnConfirmarEq.addEventListener('click', function () {
                    const rId = this.getAttribute('data-id-reserva');
                    const eId = this.getAttribute('data-id-equipo');
                    modalConfirmar.hide();

                    const formData = new FormData();
                    formData.append('accion', 'eliminar_equipo_reserva');
                    formData.append('idReserva', rId);
                    formData.append('idEquipo', eId);

                    fetch('calendarioEquipos.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&dia=<?php echo $diaSeleccionado; ?>', {
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

        //Evento para abrir el modal de agregar equipos a una reserva existente
        document.addEventListener('click', function (e) {
            const btnAbrir = e.target.closest('.btn-abrir-agregar');
            if (!btnAbrir) return;

            const idReserva = btnAbrir.getAttribute('data-id');
            const formData = new FormData();
            formData.append('accion', 'cargar_modal_agregar');
            formData.append('idReserva', idReserva);

            fetch('calendarioEquipos.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&dia=<?php echo $diaSeleccionado; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('contenedorModal').innerHTML = html;
                var modalEl = document.getElementById('modalAgregarEquipos');
                if (modalEl) {
                    var myModal = new bootstrap.Modal(modalEl);
                    myModal.show();
                }
            })
            .catch(error => console.error('Error:', error));
        });

        //Evento para enviar el formulario de agregar equipos extra mediante AJAX
        document.addEventListener('submit', function (e) {
            const formExtra = e.target.closest('#formAgregarExtra');
            if (!formExtra) return;
            e.preventDefault();

            const checkboxes = formExtra.querySelectorAll('input[name="equipos_nuevos[]"]:checked');
            if (checkboxes.length === 0) {
                alert('Debe seleccionar al menos un equipo.');
                return;
            }

            const formData = new FormData(formExtra);

            fetch('calendarioEquipos.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&dia=<?php echo $diaSeleccionado; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                var modalEl = document.getElementById('modalAgregarEquipos');
                var modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                document.getElementById('contenedorModal').innerHTML = html;
                var modalRespEl = document.getElementById('modalRespuesta');
                if (modalRespEl) {
                    var myModalResp = new bootstrap.Modal(modalRespEl);
                    myModalResp.show();
                }
            })
            .catch(error => console.error('Error:', error));
        });
        //Validación de rango de horarios 
        document.addEventListener('submit', function (e) {
            //Identificamos el formulario de selección de horario 
            const formHorario = e.target.closest('form[action="calendarioEquipos.php"]');
            if (!formHorario) return;

            const selectInicio = formHorario.querySelector('select[name="hora_inicio"]');
            const selectFin = formHorario.querySelector('select[name="hora_fin"]');

            //Si el formulario contiene los selectores de hora de inicio y fin
            if (selectInicio && selectFin) {
                const horaInicio = selectInicio.value;
                const horaFin = selectFin.value;

                //Verifico si ambos campos están llenos
                if (horaInicio !== "" && horaFin !== "") {
                    if (horaInicio >= horaFin) {
                        e.preventDefault(); //Evito que se envíe el formulario o la recarga

                        //Inyecto y muestra el modal de error solicitado
                        document.getElementById('contenedorModal').innerHTML = `
                            <div class="modal fade" id="modalRangoIncorrecto" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow border-0">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold">Error en el Horario</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center py-4">
                                            <p class="fs-5 text-dark mb-0">Rango de horarios incorrecto</p>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        var modalEl = document.getElementById('modalRangoIncorrecto');
                        var myModal = new bootstrap.Modal(modalEl);
                        myModal.show();
                    }
                }
            }
        });
    });
    </script>
</body>
</html>