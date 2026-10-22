<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
require_once '../Clases/ClsSala.php';

$objSala = new ClsSala();
$idSala = $_GET['idsala'] ?? '';

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
$diaSeleccionado = $_GET['dia'] ?? null;

// Límites de meses y años
if ($mes < 1) {
    $mes = 12;
    $anio--;
} elseif ($mes > 12) {
    $mes = 1;
    $anio++;
}

// Calcular mes anterior
$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anioAnterior = $anio - 1;
}

// Calcular mes siguiente
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
                    <div class="col-lg-10">
                        
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
                                <!-- Columna del Calendario Mensual -->
                                <div class="<?php echo $diaSeleccionado ? 'col-lg-6' : 'col-lg-12'; ?>">
                                    <div class="border rounded-4 p-4 shadow-sm h-100" style="background-color: #adbfdc !important;">
                                        <div class="bg-white rounded-4 p-3 shadow-sm h-100 d-flex flex-column justify-content-between">
                                            
                                            <!-- Cabecera del Mes con Botones de Navegación -->
                                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                                <h4 class="fw-bold text-dark mb-0 fs-5">
                                                    <?php echo "$nombreMes $anio"; ?>
                                                </h4>
                                                <div class="btn-group shadow-sm">
                                                    <!-- Botón Mes Anterior -->
                                                    <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anioAnterior; ?>&mes=<?php echo $mesAnterior; ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes Anterior">
                                                        ◀
                                                    </a>
                                                    <!-- Botón Mes Actual -->
                                                    <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Ir al mes actual">
                                                        Hoy
                                                    </a>
                                                    <!-- Botón Mes Siguiente -->
                                                    <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anioSiguiente; ?>&mes=<?php echo $mesSiguiente; ?>" class="btn btn-outline-dark btn-sm fw-bold" title="Mes Siguiente">
                                                        ▶
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Tabla de Días del Mes -->
                                            <div class="table-responsive">
                                                <table class="table table-bordered text-center align-middle mb-0 small">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>LU</th>
                                                            <th>MA</th>
                                                            <th>MI</th>
                                                            <th>JU</th>
                                                            <th>VI</th>
                                                            <th>SA</th>
                                                            <th>DO</th>
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
                                                            
                                                            if ($esSeleccionado) {
                                                                $claseDia = 'bg-warning text-dark fw-bold rounded shadow-sm';
                                                            } elseif ($esHoy) {
                                                                $claseDia = 'bg-primary text-white fw-bold rounded shadow-sm';
                                                            } else {
                                                                $claseDia = 'text-dark fw-semibold bg-light';
                                                            }
                                                            
                                                            echo "<td class='p-1'>";
                                                            echo "<a href='calendarioSala.php?idsala=" . urlencode($idSala) . "&anio=$anio&mes=$mes&dia=$dia' class='text-decoration-none d-block p-2 $claseDia' style='border-radius: 8px;'>" . $dia . "</a>";
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
                                                * Haga clic en un día para ver su disponibilidad horaria.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna de Detalle de Horas del Día Seleccionado -->
                                <?php if ($diaSeleccionado): ?>
                                <div class="col-lg-6">
                                    <div class="border rounded-4 p-4 shadow-sm h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="fw-bold text-dark mb-0 fs-5">
                                                Horarios: <?php echo "$diaSeleccionado de $nombreMes $anio"; ?>
                                            </h4>
                                            <a href="calendarioSala.php?idsala=<?php echo urlencode($idSala); ?>&anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>" class="btn-close" aria-label="Cerrar"></a>
                                        </div>
                                        <p class="text-muted small mb-3">Seleccione las horas de inicio y fin para la reserva:</p>

                                        <!-- Listado de Franjas Horarias (Ejemplo de 08:00 a 20:00) -->
                                        <div class="list-group overflow-auto" style="max-height: 320px;">
                                            <?php 
                                            for ($hora = 7; $hora <= 22; $hora++): 
                                                $horaInicio = sprintf("%02d:00", $hora);
                                                $horaFin = sprintf("%02d:00", $hora + 1);
                                                $rangoHorario = "$horaInicio - $horaFin";
                                            ?>
                                                <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 mb-2 rounded-3 border shadow-sm">
                                                    <div>
                                                        <input class="form-check-input me-2" type="checkbox" value="<?php echo $rangoHorario; ?>">
                                                        <span class="fw-semibold text-dark"><?php echo $rangoHorario; ?></span>
                                                    </div>
                                                    <span class="badge bg-success bg-opacity-75 text-white">Disponible</span>
                                                </label>
                                            <?php endfor; ?>
                                        </div>

                                        <div class="mt-3 text-end">
                                            <button type="button" class="btn btn-primary fw-bold rounded-pill px-4 shadow-sm" onclick="alert('Funcionalidad de selección de rango horario en desarrollo.');">
                                                Reservar Franja Seleccionada
                                            </button>
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