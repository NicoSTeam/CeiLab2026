<?php
// Aseguramos que la sesión esté iniciada para persistir los horarios y verificar acceso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 

// Inicializar el arreglo en sesión para los horarios del DOT si no existe
if (!isset($_SESSION['horarios_dot'])) {
    $_SESSION['horarios_dot'] = [];
}

// Procesar el formulario cuando el DOT agrega un nuevo horario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_horario') {
    $anioPost = (int)$_POST['anio'];
    $mesPost = (int)$_POST['mes'];
    $diaPost = (int)$_POST['dia'];
    $horaInicio = $_POST['hora_inicio'] ?? '';
    $horaFin = $_POST['hora_fin'] ?? '';
    $descripcion = trim($_POST['descripcion'] ?? 'Presencial en Sala');

    // Llave única para la fecha (Ej: "2026-10-25")
    $fechaKey = sprintf("%04d-%02d-%02d", $anioPost, $mesPost, $diaPost);

    if (!empty($horaInicio) && !empty($horaFin)) {
        $_SESSION['horarios_dot'][$fechaKey][] = [
            'inicio' => $horaInicio,
            'fin' => $horaFin,
            'descripcion' => !empty($descripcion) ? $descripcion : 'Presencial en Sala'
        ];
    }

    // Redirigir para evitar reenvío de formulario y mantener el día seleccionado
    header("Location: calendarioDOT.php?anio=$anioPost&mes=$mesPost&dia=$diaPost");
    exit();
}

// Datos de fecha actual del sistema
$anioActual = date('Y');
$mesActual = date('m');
$diaActual = date('j');

// Capturar año, mes y día desde la URL
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)$anioActual;
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)$mesActual;
$diaSeleccionado = isset($_GET['dia']) ? (int)$_GET['dia'] : null;

// Validar límites de meses y años
if ($mes < 1) { $mes = 12; $anio--; } 
elseif ($mes > 12) { $mes = 1; $anio++; }

$mesAnterior = $mes - 1; $anioAnterior = $anio;
if ($mesAnterior < 1) { $mesAnterior = 12; $anioAnterior--; }

$mesSiguiente = $mes + 1; $anioSiguiente = $anio;
if ($mesSiguiente > 12) { $mesSiguiente = 1; $anioSiguiente++; }

$mesesEspañol = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$nombreMes = $mesesEspañol[$mes] ?? '';
$fechaSeleccionadaKey = ($diaSeleccionado) ? sprintf("%04d-%02d-%02d", $anio, $mes, $diaSeleccionado) : '';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Calendario del DOT - CeiLab</title>
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
                                    <h2 class="fw-bold text-dark mb-1">Gestión de Horarios - DOT</h2>
                                    <p class="text-muted mb-0">Seleccione un día en el calendario para registrar o consultar los bloques de presencia en el salón.</p>
                                </div>
                                <a href="../index.php" class="btn btn-outline-secondary fw-bold rounded-pill px-4 shadow-sm">
                                    ← Volver al Inicio
                                </a>
                            </div>

                            <div class="row g-4">
                                <!-- Columna del Calendario Principal -->
                                <div class="<?php echo $diaSeleccionado ? 'col-lg-7' : 'col-lg-12'; ?>">
                                    <div class="border rounded-4 p-4 shadow-sm h-100" style="background-color: #adbfdc !important;">
                                        <div class="bg-white rounded-4 p-3 shadow-sm h-100 d-flex flex-column justify-content-between">
                                            
                                            <!-- Cabecera del Mes con Navegación -->
                                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                                <h4 class="fw-bold text-dark mb-0 fs-5">
                                                    <?php echo "$nombreMes $anio"; ?>
                                                </h4>
                                                <div class="btn-group shadow-sm">
                                                    <a href="calendarioDOT.php?anio=<?php echo $anioAnterior; ?>&mes=<?php echo $mesAnterior; ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes Anterior">
                                                        ◀
                                                    </a>
                                                    <a href="calendarioDOT.php" class="btn btn-outline-dark btn-sm fw-bold" title="Mes actual">
                                                        Hoy
                                                    </a>
                                                    <a href="calendarioDOT.php?anio=<?php echo $anioSiguiente; ?>&mes=<?php echo $mesSiguiente; ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes Siguiente">
                                                        ▶
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Tabla de Días -->
                                            <div class="table-responsive">
                                                <table class="table table-bordered text-center align-middle mb-0 small">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>L</th><th>M</th><th>X</th><th>J</th><th>V</th><th>S</th><th>D</th>
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
                                                            $tieneHorariosGuardados = !empty($_SESSION['horarios_dot'][$fechaIteracion]);
                                                            
                                                            if ($esSeleccionado) {
                                                                $claseDia = 'bg-warning text-dark fw-bold rounded shadow-sm';
                                                            } elseif ($esHoy) {
                                                                $claseDia = 'bg-primary text-white fw-bold rounded shadow-sm';
                                                            } elseif ($tieneHorariosGuardados) {
                                                                $claseDia = 'bg-success bg-opacity-25 text-dark fw-bold border border-success rounded';
                                                            } else {
                                                                $claseDia = 'text-dark fw-semibold bg-light';
                                                            }
                                                            
                                                            echo "<td class='p-1 position-relative'>";
                                                            echo "<a href='calendarioDOT.php?anio=$anio&mes=$mes&dia=$dia' class='text-decoration-none d-block p-2 $claseDia' style='border-radius: 8px;'>" . $dia . "</a>";
                                                            
                                                            if ($tieneHorariosGuardados && !$esHoy && !$esSeleccionado) {
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
                                                * Haga clic en cualquier día para registrar o ver los horarios del DOT.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna de Gestión y Horarios para el Día Seleccionado -->
                                <?php if ($diaSeleccionado): ?>
                                <div class="col-lg-5">
                                    <div class="border rounded-4 p-4 shadow-sm h-100 bg-white d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="fw-bold text-dark mb-0 fs-5">
                                                    Día: <?php echo "$diaSeleccionado de $nombreMes $anio"; ?>
                                                </h4>
                                                <a href="calendarioDOT.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>" class="btn-close" aria-label="Cerrar"></a>
                                            </div>
                                            
                                            <!-- Listado de Horarios Registrados para este día -->
                                            <h6 class="text-muted fw-bold small mb-2">Horarios ya registrados:</h6>
                                            <div class="list-group mb-4 overflow-auto" style="max-height: 180px;">
                                                <?php 
                                                if (!empty($_SESSION['horarios_dot'][$fechaSeleccionadaKey])):
                                                    foreach ($_SESSION['horarios_dot'][$fechaSeleccionadaKey] as $h):
                                                ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 mb-2 rounded-3 border shadow-sm">
                                                        <div>
                                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($h['inicio'] . ' - ' . $h['fin']); ?></span>
                                                            <div class="text-muted small"><?php echo htmlspecialchars($h['descripcion']); ?></div>
                                                        </div>
                                                        <span class="badge bg-success">Activo</span>
                                                    </div>
                                                <?php 
                                                    endforeach;
                                                else: 
                                                ?>
                                                    <div class="text-muted small fst-italic p-2 bg-light rounded-3 text-center">
                                                        No hay horarios registrados para este día.
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Formulario para Agregar Nuevo Horario -->
                                            <form action="calendarioDOT.php" method="POST" class="border-top pt-3">
                                                <input type="hidden" name="accion" value="agregar_horario">
                                                <input type="hidden" name="anio" value="<?php echo $anio; ?>">
                                                <input type="hidden" name="mes" value="<?php echo $mes; ?>">
                                                <input type="hidden" name="dia" value="<?php echo $diaSeleccionado; ?>">

                                                <h6 class="fw-bold text-dark small mb-3">Agregar nuevo horario:</h6>
                                                
                                                <div class="row g-2 mb-2">
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted mb-1">Hora Inicio</label>
                                                        <input type="time" name="hora_inicio" class="form-control form-control-sm rounded-3 shadow-sm" required value="09:00">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted mb-1">Hora Fin</label>
                                                        <input type="time" name="hora_fin" class="form-control form-control-sm rounded-3 shadow-sm" required value="12:00">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small text-muted mb-1">Descripción / Turno</label>
                                                    <input type="text" name="descripcion" class="form-control form-control-sm rounded-3 shadow-sm" placeholder="Ej. Turno Matutino / Presencial" value="Presencial en Sala">
                                                </div>

                                                <button type="submit" class="btn btn-primary btn-sm fw-bold w-100 rounded-pill py-2 shadow-sm">
                                                    + Guardar Horario
                                                </button>
                                            </form>
                                        </div>

                                        <div class="mt-3 text-center">
                                            <a href="calendarioDOT.php?anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>" class="text-muted small text-decoration-none">Cerrar panel de edición</a>
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
    </body>
</html>