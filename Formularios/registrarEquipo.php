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
require_once '../Clases/ClsSala.php';

$objEquipo = new ClsEquipo();
$objSala = new ClsSala();

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
    $estado = $_POST['estado'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $idSala = $_POST['idSala'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    if (!empty($estado) && !empty($tipo) && !empty($idSala)) {
        // Ya no enviamos disponibilidad manualmente, se calcula en la clase
        if ($objEquipo->registrarEquipo($estado, $observaciones, $tipo, $idSala)) {
            $_SESSION['mensaje'] = "¡Equipo registrado exitosamente!";
            $_SESSION['tipoAlerta'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al registrar el equipo en la base de datos.";
            $_SESSION['tipoAlerta'] = "danger";
        }
    } else {
        $_SESSION['mensaje'] = "Los campos Estado, Tipo y Sala Asignada son obligatorios.";
        $_SESSION['tipoAlerta'] = "warning";
    }
    
    header("Location: registrarEquipo.php");
    exit();
}

$mensaje = $_SESSION['mensaje'] ?? '';
$tipoAlerta = $_SESSION['tipoAlerta'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipoAlerta']);

$listaSalas = $objSala->listarSalasFiltradas();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Registrar Equipo - CeiLab</title>
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
                    <div class="col-lg-8">
                        
                        <div class="card shadow-lg border-0 rounded-4 p-4 mb-5 bg-white bg-opacity-95">
                            <h2 class="text-center fw-bold text-dark mb-4">Registrar Nuevo Equipo</h2>

                            <form action="registrarEquipo.php" method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="accion" value="registrar">

                                <div class="row g-3">
                                    <!-- Selector de Tipo de Equipo -->
                                    <div class="col-md-6">
                                        <label for="tipo" class="form-label fw-bold text-dark">Tipo de Equipo</label>
                                        <select class="form-select rounded-3 shadow-sm" id="tipo" name="tipo" required>
                                            <option value="" disabled selected>Seleccione un tipo...</option>
                                            <option value="Dron">Dron</option>
                                            <option value="Ceibalita">Ceibalita</option>
                                            <option value="Micro:bit">Micro:bit</option>
                                            <option value="Placa Makey Makey">Placa Makey Makey</option>
                                            <option value="Kit de robótica">Kit de robótica</option>
                                            <option value="Sensores Fisicoquímicos">Sensores Fisicoquímicos</option>
                                            <option value="Impresora 3D">Impresora 3D</option>
                                            <option value="Lápiz 3D">Lápiz 3D</option>
                                            <option value="Microscopio USB">Microscopio USB</option>
                                        </select>
                                    </div>

                                    <!-- Selector de Estado del Equipo -->
                                    <div class="col-md-6">
                                        <label for="estado" class="form-label fw-bold text-dark">Estado</label>
                                        <select class="form-select rounded-3 shadow-sm" id="estado" name="estado" required>
                                            <option value="" disabled selected>Seleccione un estado...</option>
                                            <option value="Disponible">Disponible</option>
                                            <option value="EnMantenimiento">EnMantenimiento</option>
                                            <option value="NoDisponible">NoDisponible</option>
                                        </select>
                                    </div>

                                    <!-- Sala Asignada -->
                                    <div class="col-md-6">
                                        <label for="idSala" class="form-label fw-bold text-dark">Sala Asignada</label>
                                        <select class="form-select rounded-3 shadow-sm" id="idSala" name="idSala" required>
                                            <option value="" disabled selected>Seleccione una sala...</option>
                                            <?php foreach ($listaSalas as $sala): ?>
                                                <option value="<?php echo htmlspecialchars($sala['IDSalon']); ?>">
                                                    <?php echo htmlspecialchars($sala['IDSalon']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Observaciones -->
                                    <div class="col-md-6">
                                        <label for="observaciones" class="form-label fw-bold text-dark">Observaciones</label>
                                        <input type="text" class="form-control rounded-3 shadow-sm" id="observaciones" name="observaciones" placeholder="Detalles adicionales...">
                                    </div>
                                    
                                    <div class="col-12 text-end mt-4">
                                        <button type="submit" class="btn btn-primary fw-bold rounded-pill px-5 shadow">Registrar Equipo</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MODAL DE NOTIFICACIÓN DE RESPUESTA -->
        <?php if (!empty($mensaje)): 
            $esWarning = ($tipoAlerta === 'warning');
            $claseTextoHeader = $esWarning ? 'text-dark' : 'text-white';
            $claseBotonCierre = $esWarning ? '' : 'btn-close-white';
        ?>
        <div class="modal fade" id="modalNotificacion" tabindex="-1" aria-labelledby="modalNotificacionLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-<?php echo $tipoAlerta; ?> <?php echo $claseTextoHeader; ?> border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold" id="modalNotificacionLabel">
                            <?php echo ($tipoAlerta === 'success') ? '¡Operación Exitosa!' : 'Atención'; ?>
                        </h5>
                        <button type="button" class="btn-close <?php echo $claseBotonCierre; ?>" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="fs-5 mb-0"><?php echo htmlspecialchars($mensaje); ?></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-<?php echo $tipoAlerta; ?> fw-bold rounded-pill px-4 <?php echo $esWarning ? 'text-dark' : 'text-white'; ?>" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php include '../footer.php'; ?>
        
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        
        <script>
            <?php if (!empty($mensaje)): ?>
                var modalNoti = new bootstrap.Modal(document.getElementById('modalNotificacion'));
                modalNoti.show();
            <?php endif; ?>
        </script>
    </body>
</html>