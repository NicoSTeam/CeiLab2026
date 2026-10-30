<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 

// Verificación del usuario para que no ingrese por ruta del navegador
$esAdministrador = isset($_SESSION['roles']) && in_array('1', $_SESSION['roles']);
$esDot = isset($_SESSION['roles']) && in_array('2', $_SESSION['roles']);

if (!$esAdministrador && !$esDot) {
    $_SESSION['mensaje'] = "Acceso denegado. Acción no permitida para el rol detectado.";
    $_SESSION['tipoAlerta'] = "danger";
    header("Location: ../index.php");
    exit();
}

require_once '../Clases/ClsEquipo.php';
$objEquipo = new ClsEquipo();
$equipos = $objEquipo->listarEquipos();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock y Estado de Equipos - CeiLab</title>
    <!-- Bootstrap core CSS -->
    <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="../Estilos/blocks.css">
    <link href="../Estilos/style.css" rel="stylesheet">
    <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    <link href="../Estilos/CSSImpresion.css" rel="stylesheet" type="text/css" media="print">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include '../navbar.php'; ?>
    
    <section class="hero-section flex-fill pb-5 pt-5">
    <div class="container my-4">
        <!-- Encabezado -->
        <div class="row mb-4 align-items-center bg-white p-3 shadow-sm rounded">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-0">💻 Stock y Estado de Equipos</h2>
                <p class="text-muted mb-0">Control general de disponibilidad, tipos e inventario en tiempo real.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2 flex-wrap">
                <a href="../index.php" class="btn btn-outline-secondary fw-bold rounded-pill px-4 shadow-sm">
                    ← Volver al Inicio
                </a>
                <button type="button" onclick="window.print();" class="btn btn-danger fw-bold rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2">
                    📄 Imprimir Reporte (PDF)
                </button>
            </div>
        </div>

        <!-- Contenedor de la Tabla -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <?php if (!empty($equipos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted">
                            <tr>
                                <th># ID Equipo</th>
                                <th>Tipo</th>
                                <th>Estado Físico</th>
                                <th>Disponibilidad</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipos as $row): 
                                $estaDisponible = ($row['Disponibilidad'] == 1);
                                
                                if ($estaDisponible) {
                                    $badgeClass = 'bg-success text-white';
                                    $textoDisponibilidad = '✅ Disponible';
                                } else {
                                    $badgeClass = 'bg-danger text-white';
                                    $textoDisponibilidad = '⚠️ No Disponible';
                                }
                            ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        💻 Equipo ID: <?php echo htmlspecialchars($row['IDEquipo']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-dark px-2 py-1">
                                            <?php echo htmlspecialchars($row['Tipo']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">
                                            <?php echo htmlspecialchars($row['Estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $badgeClass; ?> px-3 py-1 rounded-pill fw-bold">
                                            <?php echo $textoDisponibilidad; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            <?php echo !empty($row['Observaciones']) ? htmlspecialchars($row['Observaciones']) : 'Sin observaciones registradas.'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="fs-5 text-muted mb-0">No hay equipos activos registrados en el sistema actualmente.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </section>
    
    <?php include '../footer.php'; ?>            
    
    <!-- Scripts corregidos con la ruta correcta -->
    <script src="../assets/js/popper.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
</body>
</html>