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

require_once '../Clases/ClsOperaciones.php';
$objOperaciones = new ClsOperaciones();

//Filtros opcionales 
$cedulaFiltro = $_GET['cedula'] ?? '';
$accionFiltro = $_GET['accion'] ?? '';
$historialPrestamos = $objOperaciones->obtenerHistorialPrestamoDev($cedulaFiltro, $accionFiltro);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Préstamos y Devoluciones - CeiLab</title>
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
        <!-- Encabezado con botones de acción -->
        <div class="row mb-4 align-items-center bg-white p-3 shadow-sm rounded">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-0">📋 Historial de Préstamos y Devoluciones</h2>
                <p class="text-muted mb-0">Registro completo de operaciones, horarios, equipos y usuarios responsables.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2 flex-wrap">
                <!-- Botón Volver al Inicio -->
                <a href="../index.php" class="btn btn-outline-secondary fw-bold rounded-pill px-4 shadow-sm">
                    ← Volver al Inicio
                </a>
                <!-- Botón Imprimir / PDF -->
                <button type="button" onclick="window.print();" class="btn btn-danger fw-bold rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2">
                    📄 Imprimir Reporte (PDF)
                </button>
            </div>
        </div>

        <!-- Formulario de Búsqueda / Filtro Opcional -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 print-hidden">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-md-9">
                    <input type="text" class="form-control" name="cedula" placeholder="Filtrar por Cédula (8 dígitos sin puntos ni guiones)" maxlength="8" value="<?php echo htmlspecialchars($cedulaFiltro); ?>">
                </div>
                <div class="col-md-9">    
                    <select class="form-select" name="accion">
                        <option value="">Todas las acciones...</option>
                        <option value="1" <?php echo ($accionFiltro === '1') ? 'selected' : ''; ?>>Préstamo </option>
                        <option value="2" <?php echo ($accionFiltro === '2') ? 'selected' : ''; ?>>Devolución </option>
                    </select>    
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filtrar</button>
                    <a href="historialPrestaDev.php" class="btn btn-outline-secondary w-100 fw-bold">Limpiar</a>
                </div>
            </form>
        </div>

        <!-- Contenedor de la Tabla -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <?php if ($historialPrestamos && mysqli_num_rows($historialPrestamos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted">
                            <tr>
                                <th>#ID</th>
                                <th>Fecha & Horario</th>
                                <th>Usuario Responsable</th>
                                <th>ID Equipo</th>
                                <th>Estado / Acción</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($historialPrestamos)): 
                                $esNoDevuelto = false;

                                //1. Si el dot marca un equipo como al momento de la devolución: Incidencia registrada al cierre del día
                                $esIncidenciaDot = ($row['Accion'] == '2' && isset($row['Observaciones']) && stripos($row['Observaciones'], 'Equipo no devuelto al cierre del día') !== false);

                                //2. Prestamo activo y si es el registro más grande (MaxID) de ese equipo
                                $esUltimoMovimiento = (isset($row['MaxID']) && $row['IDOperaciones'] == $row['MaxID']);
                                $esPrestamoPendiente = ($row['Accion'] == '1' && $esUltimoMovimiento);

                                //Si se cumple cualquiera de las dos reglas, se marca como no devuelto
                                if ($esIncidenciaDot || $esPrestamoPendiente) {
                                    $esNoDevuelto = true;
                                }

                                //Defino estilos de la etiqueta
                                if ($esIncidenciaDot) {
                                    //1.rojo: Incidencia grave marcada por el DOT (No devuelto al cierre)
                                    $badgeClass = 'bg-danger text-white';
                                    $textoAccion = '🚨 No Devuelto';
                                } elseif ($esPrestamoPendiente) {
                                    //2. Naranja: El prestamo que esta activo actualmente en curso
                                    $badgeClass = 'bg-warning text-dark fw-bold';
                                    $textoAccion = '⏳ Préstamo Activo';
                                } elseif ($row['Accion'] == '1') {
                                    //3.Verde: prestamo ya finalizado correctamente
                                    $badgeClass = 'bg-success text-white';
                                    $textoAccion = 'Préstamo';
                                } else {
                                    //4.Azul: Devolucion registrada con exito
                                    $badgeClass = 'bg-primary text-white';
                                    $textoAccion = 'Devolución';
                                }
                            ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?php echo htmlspecialchars($row['IDOperaciones']); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo date("d/m/Y", strtotime($row['Fecha'])); ?></div>
                                        <span class="badge bg-light text-dark border px-2 py-1 mt-1">
                                            🕒 <?php echo htmlspecialchars(substr($row['HoraInicio'], 0, 5) . ' - ' . substr($row['HoraFin'], 0, 5)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['NombreUsuario']); ?></div>
                                        <small class="text-muted">C.I: <?php echo htmlspecialchars($row['CedulaUsuario']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold px-2 py-1">
                                            💻 Equipo ID: <?php echo htmlspecialchars($row['IDEquipo']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $badgeClass; ?> px-3 py-1 rounded-pill fw-bold">
                                            <?php echo $textoAccion; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><?php echo htmlspecialchars($row['Observaciones']); ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="fs-5 text-muted mb-0">No se encontraron registros en el historial de préstamos y devoluciones.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </section>
    
    <?php include '../footer.php'; ?>            
    <script src="../assets/js/popper.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
</body>
</html>