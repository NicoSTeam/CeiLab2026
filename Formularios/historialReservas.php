<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 

// Validar roles permitidos (Admin '1' y DOT '2')
$esAdministrador = isset($_SESSION['roles']) && in_array('1', $_SESSION['roles']);
$esDot = isset($_SESSION['roles']) && in_array('2', $_SESSION['roles']);

if (!$esAdministrador && !$esDot) {
    $_SESSION['mensaje'] = "Acceso denegado. No tienes permisos para ver el historial.";
    $_SESSION['tipoAlerta'] = "danger";
    header("Location: ../index.php");
    exit();
}

require_once '../Clases/ClsOperaciones.php';
$objOperaciones = new ClsOperaciones();
$historialReservas = $objOperaciones->obtenerHistorialReservas();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Reservas - CeiLab</title>
    <!-- Bootstrap core CSS -->
    <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="../Estilos/blocks.css">
    <link href="../Estilos/style.css" rel="stylesheet">
    <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include '../navbar.php'; ?>
    <section class="hero-section flex-fill pb-5 pt-5">
    <div class="container my-4">
        <div class="row mb-4 align-items-center">
            <div class="bg-white p-3 mb-4 shadow-sm rounded text-center">
                <h2 class="fw-bold text-dark">📋 Historial General de Reservas</h2>
                
                <p class="text-muted mb-0">Registro completo de todas las reservas realizadas en el sistema.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <?php if (!empty($historialReservas)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted">
                            <tr>
                                <th>#ID</th>
                                <th>Fecha & Horario</th>
                                <th>Salón</th>
                                <th>Duración</th>
                                <th>Usuario Responsable</th>
                                <th>Equipos / Recursos</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historialReservas as $res): ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?php echo $res['IDReservas']; ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo date("d/m/Y", strtotime($res['Fecha'])); ?></div>
                                        <span class="badge bg-light text-dark border px-2 py-1 mt-1">
                                            🕒 <?php echo substr($res['HoraInicio'], 0, 5) . ' - ' . substr($res['HoraFin'], 0, 5); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold px-2 py-1">
                                            🚪 Salón: <?php echo htmlspecialchars($res['IDSalon']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info fw-bold px-2 py-1">
                                            ⏱️ <?php echo htmlspecialchars($res['cantHoras']); ?> hs
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($res['nombre_usuario']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($res['email']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if (!empty($res['equipos'])): ?>
                                                <?php foreach ($res['equipos'] as $eq): ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 py-1 px-2" style="font-size: 0.75rem;">
                                                        <?php echo htmlspecialchars($eq['Tipo']); ?> (ID: <?php echo $eq['IDEquipo']; ?>)
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted small">Reserva de Sala</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($res['activo'] == 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-bold">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill fw-bold">Cancelada</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="fs-5 text-muted mb-0">No se encontraron registros en el historial de reservas.</p>
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