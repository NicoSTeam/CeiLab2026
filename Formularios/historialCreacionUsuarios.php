<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion();

// Verificación de roles (Solo administradores)
$esAdministrador = isset($_SESSION['roles']) && in_array('1', $_SESSION['roles']);
if (!$esAdministrador) {
    $_SESSION['mensaje'] = "Acceso denegado. Sección exclusiva para administradores.";
    $_SESSION['tipoAlerta'] = "danger";
    header("Location: ../index.php");
    exit();
}

require_once '../Clases/ClsUsuarios.php';
$objUsuario = new ClsUsuarios();
$resultadoUsuarios = $objUsuario->listarUsuariosConDetallesCreacion();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Reporte de Altas de Usuarios - CeiLab</title>
        <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="../Estilos/blocks.css">
        <link href="../Estilos/style.css" rel="stylesheet">
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
        <link href="../Estilos/CSSImpresion.css" rel="stylesheet" type="text/css" media="print">
    </head>
    <body class="d-flex flex-column min-vh-100 bg-light">
        
        <?php include '../navbar.php'; ?> 
        
        <section class="hero-section flex-fill pb-5 pt-5">
            <div class="container pb-5 pt-3 rounded shadow-sm col-md-11 col-xl-10" style="background-color: #adbfdc;">
                <div class="row">
                    <div class="col-12">
                        <div class="bg-white p-3 mb-4 shadow-sm rounded">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                
                                <!-- Título y descripción -->
                                <div>
                                    <h3 class="mb-1 text-primary fw-bold">👥 Listado de Altas de Usuarios</h3>
                                    <p class="text-muted small mb-0">Detalle de cada usuario registrado, fecha de alta y responsable que realizó el registro.</p>
                                </div>
                                
                                <!-- Contenedor de los botones al costado -->
                                <div class="d-flex justify-content-md-end gap-2 flex-wrap">
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
                        </div>

                        <?php if ($resultadoUsuarios && mysqli_num_rows($resultadoUsuarios) > 0): ?>
                            <div class="table-responsive bg-white p-3 shadow-sm rounded">
                                <table class="table table-striped table-hover align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre del Usuario</th>
                                            <th>Cédula</th>
                                            <th>Email</th>
                                            <th>Fecha de Alta</th>
                                            <th>Dado de alta por</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($resultadoUsuarios)): ?>
                                            <tr>
                                                <td class="fw-bold">#<?php echo $row['IDUsuario']; ?></td>
                                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo !empty($row['Fecha_creacion']) ? date('d/m/Y', strtotime($row['Fecha_creacion'])) : 'No registrada'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark">
                                                        <?php echo htmlspecialchars($row['NombreCreador'] ?? 'Sistema'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class='alert alert-info text-center bg-white mb-0 shadow-sm'>No hay usuarios registrados en el sistema actualmente.</div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </section>

        <?php include '../footer.php'; ?>
        
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>