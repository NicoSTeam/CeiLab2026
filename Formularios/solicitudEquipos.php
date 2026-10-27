<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
require_once '../Clases/ClsSolicitud.php';
$objSolicitud = new ClsSolicitud();

//Procesar el formulario de solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
    $tipoEquipo  = $_POST['tipoEquipo'] ?? '';
    $nuevoTipo   = $_POST['nuevoTipo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $cantidad    = $_POST['cantidad'] ?? '';
    $idUsuario   = $_SESSION['IDUsuario'] ?? $_SESSION['idUsuario'] ?? 0; 
    $fecha       = date('Y-m-d');

    $tipoValidado = ($tipoEquipo === 'OTRO') ? trim($nuevoTipo) : $tipoEquipo;

    if (!empty($tipoValidado) && !empty($descripcion) && !empty($cantidad) && $cantidad > 0 && !empty($idUsuario)) {
        if ($objSolicitud->registrarSolicitud($tipoEquipo, $nuevoTipo, $descripcion, $cantidad, $idUsuario, $fecha)) {
            $_SESSION['mensaje'] = "¡Solicitud registrada exitosamente con estado pendiente!";
            $_SESSION['tipoAlerta'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al registrar la solicitud en la base de datos.";
            $_SESSION['tipoAlerta'] = "danger";
        }
    } else {
        $_SESSION['mensaje'] = "Por favor, complete todos los campos obligatorios.";
        $_SESSION['tipoAlerta'] = "warning";
    }
    
    header("Location: solicitudEquipos.php");
    exit();
}

$mensaje = $_SESSION['mensaje'] ?? '';
$tipoAlerta = $_SESSION['tipoAlerta'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipoAlerta']);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Solicitud de Equipos - CeiLab</title>
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
                            <h2 class="text-center fw-bold text-dark mb-4">Solicitud de Equipos</h2>

                            <form action="solicitudEquipos.php" method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="accion" value="registrar">

                                <div class="row g-3">
                                    <!-- Selector de Tipo de Equipo -->
                                    <div class="col-md-6">
                                        <label for="tipoEquipo" class="form-label fw-bold text-dark">Tipo de Equipo</label>
                                        <select class="form-select rounded-3 shadow-sm" id="tipoEquipo" name="tipoEquipo" required onchange="verificarNuevoTipo(this)">
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
                                            <option value="OTRO">Otro (Nuevo material...)</option>
                                        </select>
                                    </div>

                                    <!-- Campo dinámico si selecciona OTRO -->
                                    <div class="col-md-6 d-none" id="divNuevoTipo">
                                        <label for="nuevoTipo" class="form-label fw-bold text-dark">Especifique el Nuevo Tipo</label>
                                        <input type="text" class="form-control rounded-3 shadow-sm" id="nuevoTipo" name="nuevoTipo" placeholder="Ej. Lentes RV">
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="col-md-6">
                                        <label for="cantidad" class="form-label fw-bold text-dark">Cantidad</label>
                                        <input type="number" class="form-control rounded-3 shadow-sm" id="cantidad" name="cantidad" min="1" placeholder="Ej. 3" required>
                                    </div>

                                    <!-- Fecha -->
                                    <div class="col-md-6">
                                        <label for="fecha" class="form-label fw-bold text-dark">Fecha</label>
                                        <input type="date" class="form-control rounded-3 shadow-sm" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" readonly>
                                    </div>

                                    <!-- Descripción -->
                                    <div class="col-12">
                                        <label for="descripcion" class="form-label fw-bold text-dark">Descripción o Motivo</label>
                                        <textarea class="form-control rounded-3 shadow-sm" id="descripcion" name="descripcion" rows="3" placeholder="Detalle el motivo de la solicitud..." required></textarea>
                                    </div>
                                    
                                    <div class="col-12 text-end mt-4">
                                        <button type="submit" class="btn btn-primary fw-bold rounded-pill px-5 shadow">Enviar Solicitud</button>
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

            function verificarNuevoTipo(select) {
                var divNuevo = document.getElementById('divNuevoTipo');
                var inputNuevo = document.getElementById('nuevoTipo');
                if (select.value === 'OTRO') {
                    divNuevo.classList.remove('d-none');
                    inputNuevo.setAttribute('required', 'true');
                } else {
                    divNuevo.classList.add('d-none');
                    inputNuevo.removeAttribute('required');
                    inputNuevo.value = '';
                }
            }
        </script>
    </body>
</html>