<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Clases/ClsCalendarioDOT.php';

$anioIndex = isset($_GET['anio_dot']) ? (int)$_GET['anio_dot'] : (int)date('Y');
$mesIndex = isset($_GET['mes_dot']) ? (int)$_GET['mes_dot'] : (int)date('m');
$diaActualIndex = (int)date('j');

$mesesEspañolIndex = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$nombreMesIndex = $mesesEspañolIndex[$mesIndex] ?? '';

// Consulta los horarios registrados en la Base de Datos para el mes actual
$objCalendarioDOT = new ClsCalendarioDOT();
$horariosDOTRegistrados = $objCalendarioDOT->obtenerHorariosMes($anioIndex, $mesIndex);
?>

<div class="bg-white rounded-4 p-4 shadow-sm w-100">
    
    <!-- Cabecera del mes -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark h5 mb-0">Calendario del DOT</h4>
        <div class="btn-group shadow-sm">
            <?php 
            $mesAnt = $mesIndex - 1; $anioAnt = $anioIndex;
            if ($mesAnt < 1) { $mesAnt = 12; $anioAnt--; }
            $mesSig = $mesIndex + 1; $anioSig = $anioIndex;
            if ($mesSig > 12) { $mesSig = 1; $anioSig++; }
            ?>
            <a href="?anio_dot=<?php echo $anioAnt; ?>&mes_dot=<?php echo $mesAnt; ?>" class="btn btn-outline-dark btn-sm fw-bold px-2 py-1" title="Mes Anterior">◀</a>
            <a href="index.php" class="btn btn-outline-dark btn-sm fw-bold px-2 py-1" title="Mes actual">Hoy</a>
            <a href="?anio_dot=<?php echo $anioSig; ?>&mes_dot=<?php echo $mesSig; ?>" class="btn btn-outline-dark btn-sm fw-bold px-2 py-1" title="Mes Siguiente">▶</a>
        </div>
    </div>

    <div class="text-muted small mb-2 fw-semibold">
        <?php echo "$nombreMesIndex $anioIndex"; ?>
    </div>

    <!-- Tabla de días del mini calendario -->
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle mb-0 w-100">
            <thead class="table-dark">
                <tr>
                    <th>LU</th><th>MA</th><th>MI</th><th>JU</th><th>VI</th><th>SA</th><th>DO</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $primerDia = mktime(0, 0, 0, $mesIndex, 1, $anioIndex);
                $totalDias = date('t', $primerDia);
                $diaInicio = date('N', $primerDia);
                $esActual = ($anioIndex == (int)date('Y') && $mesIndex == (int)date('m'));

                echo "<tr>";
                for ($i = 1; $i < $diaInicio; $i++) {
                    echo "<td class='bg-light text-muted'></td>";
                }

                for ($d = 1; $d <= $totalDias; $d++) {
                    $esHoy = ($esActual && $d == $diaActualIndex);
                    $fechaCelda = sprintf("%04d-%02d-%02d", $anioIndex, $mesIndex, $d);
                    
                    $tieneHorario = !empty($horariosDOTRegistrados[$fechaCelda]);
                    $tooltipTexto = "";
                    if ($tieneHorario) {
                        $cadenasHoras = [];
                        foreach ($horariosDOTRegistrados[$fechaCelda] as $h) {
                            $cadenasHoras[] = $h['inicio'] . '-' . $h['fin'] . ' (' . $h['nombre_dot'] . ')';
                        }
                        $tooltipTexto = implode(', ', $cadenasHoras);
                    }
                    
                    if ($esHoy) {
                        $clase = 'bg-primary text-white fw-bold rounded shadow-sm';
                    } elseif ($tieneHorario) {
                        $clase = 'bg-success bg-opacity-25 text-dark fw-bold border border-success rounded';
                    } else {
                        $clase = 'text-dark';
                    }
                    
                    $attrTooltip = $tieneHorario 
                        ? "data-bs-toggle='tooltip' data-bs-placement='top' title='Disponible: $tooltipTexto' style='cursor: pointer; border-radius: 6px;'" 
                        : "style='border-radius: 6px;'";

                    echo "<td class='p-1 position-relative'>";
                    echo "<span class='d-block p-2 $clase' $attrTooltip>" . $d . "</span>";
                    
                    if ($tieneHorario && !$esHoy) {
                        echo '<span class="position-absolute top-0 start-75 translate-middle p-1 bg-success border border-light rounded-circle" style="width: 8px; height: 8px;"></span>';
                    }
                    
                    echo "</td>";

                    if (($d + $diaInicio - 1) % 7 == 0 && $d < $totalDias) {
                        echo "</tr><tr>";
                    }
                }

                $restantes = (7 - (($totalDias + $diaInicio - 1) % 7)) % 7;
                for ($i = 0; $i < $restantes; $i++) {
                    echo "<td class='bg-light text-muted'></td>";
                }
                echo "</tr>";
                ?>
            </tbody>
        </table>
    </div>
    <div class="mt-3 text-center">
        <span class="text-muted" style="font-size: 11px;">
            <span class="badge bg-success bg-opacity-75 p-1"> </span> Pase el cursor sobre los días marcados para ver el horario
        </span>
    </div>

</div>

<!-- Script para inicializar los Tooltips de Bootstrap -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>