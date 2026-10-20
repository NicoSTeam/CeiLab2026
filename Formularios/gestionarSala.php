<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
require_once '../Clases/ClsSala.php';

$objSala = new ClsSala();
$mensaje = "";
$tipoAlerta = "";
$salaEditando = null;

// Procesar el formulario (Registrar o Modificar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $idSalon = $_POST['idSalon'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $disponibilidad = $_POST['disponibilidad'] ?? '1';

    if ($accion === 'registrar') {
        if (!empty($idSalon)) {
            if ($objSala->registrarSala($idSalon, $observaciones, $disponibilidad)) {
                $mensaje = "¡Sala registrada exitosamente!";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Error al registrar la sala (es posible que el ID ya exista).";
                $tipoAlerta = "danger";
            }
        } else {
            $mensaje = "El identificador de la sala es obligatorio.";
            $tipoAlerta = "warning";
        }
    } elseif ($accion === 'modificar') {
        $idOriginal = $_POST['idOriginal'] ?? '';
        if (!empty($idOriginal) && !empty($idSalon)) {
            if ($objSala->modificarSala($idOriginal, $idSalon, $observaciones, $disponibilidad)) {
                $mensaje = "¡Sala modificada exitosamente!";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Error al modificar la sala (es posible que el nuevo ID ya exista).";
                $tipoAlerta = "danger";
            }
        } else {
            $mensaje = "Los campos de identificador son obligatorios.";
            $tipoAlerta = "warning";
        }
    }
}

// Si se solicita editar, cargamos los datos de la sala
if (isset($_GET['editar'])) {
    $salaEditando = $objSala->obtenerSalaPorId($_GET['editar']);
}

// Procesar eliminación si se solicita
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    if ($objSala->eliminarSala($idEliminar)) {
        $mensaje = "Sala eliminada correctamente.";
        $tipoAlerta = "success";
    } else {
        $mensaje = "No se pudo eliminar la sala.";
        $tipoAlerta = "danger";
    }
}

$listaSalas = $objSala->listarSalas();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Gestion de Salas - CeiLab</title>
        <!-- Bootstrap core CSS -->
        <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <!-- Custom styles -->
        <link rel="stylesheet" href="../Estilos/blocks.css">
        <link href="../Estilos/style.css" rel="stylesheet">
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    </head>
    <body class="d-flex flex-column min-vh-100 bg-light">
        
        <!-- Barra de navegación -->
        <?php include '../navbar.php'; ?> 
        
        <section class="hero-section flex-fill pb-5 pt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        
                        <!-- Tarjeta del Formulario -->
                        <div class="card shadow-lg border-0 rounded-4 p-4 mb-5 bg-white bg-opacity-95">
                            <h2 class="text-center fw-bold text-dark mb-4">
                                <?php echo $salaEditando ? 'Modificar Sala: ' . htmlspecialchars($salaEditando['IDSalon']) : 'Gestión de Salas CeiLab'; ?>
                            </h2>

                            <!-- Formulario para Registrar / Modificar Sala -->
                            <form action="gestionarSala.php" method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="accion" value="<?php echo $salaEditando ? 'modificar' : 'registrar'; ?>">
                                <?php if ($salaEditando): ?>
                                    <input type="hidden" name="idOriginal" value="<?php echo htmlspecialchars($salaEditando['IDSalon']); ?>">
                                <?php endif; ?>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="idSalon" class="form-label fw-bold text-dark">ID / Nombre del Salón</label>
                                        <input type="text" class="form-control rounded-3 shadow-sm" id="idSalon" name="idSalon" placeholder="Ej: Sala-01" value="<?php echo $salaEditando ? htmlspecialchars($salaEditando['IDSalon']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="disponibilidad" class="form-label fw-bold text-dark">Disponibilidad</label>
                                        <select class="form-select rounded-3 shadow-sm" id="disponibilidad" name="disponibilidad">
                                            <option value="1" <?php echo ($salaEditando && (string)$salaEditando['Disponibilidad'] === '1') ? 'selected' : ''; ?>>1 - Disponible</option>
                                            <option value="0" <?php echo ($salaEditando && (string)$salaEditando['Disponibilidad'] === '0') ? 'selected' : ''; ?>>0 - No disponible</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="observaciones" class="form-label fw-bold text-dark">Observaciones</label>
                                        <input type="text" class="form-control rounded-3 shadow-sm" id="observaciones" name="observaciones" placeholder="Detalles de la sala..." value="<?php echo $salaEditando ? htmlspecialchars($salaEditando['Observaciones']) : ''; ?>">
                                    </div>
                                    <div class="col-12 text-end mt-4">
                                        <?php if ($salaEditando): ?>
                                            <a href="gestionarSala.php" class="btn btn-secondary fw-bold rounded-pill px-4 shadow-sm me-2">Cancelar</a>
                                            <button type="submit" class="btn btn-warning fw-bold rounded-pill px-5 shadow text-dark">Actualizar Sala</button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-primary fw-bold rounded-pill px-5 shadow">Registrar Sala</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Listado -->
            <div class="container pb-5 pt-3 rounded shadow-sm col-md-10 col-xl-9" style="background-color: #adbfdc;">
                <div class="row">
                    <div>
                        <h3 class="mb-3 text-dark fw-bold">Salas Registradas</h3> 
                        
                        <?php if (count($listaSalas) > 0): ?>
                            <div class="table-responsive bg-white p-3 shadow-sm rounded">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Salón</th>
                                            <th>Disponibilidad</th>
                                            <th>Observaciones</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($listaSalas as $sala): 
                                            $disp = (string)$sala['Disponibilidad'];
                                            $esDisponible = ($disp === '1');
                                            $textoDisp = $esDisponible ? '1 - Disponible' : '0 - No disponible';
                                            $claseBadge = $esDisponible ? 'success' : 'danger';
                                        ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($sala['IDSalon']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $claseBadge; ?>">
                                                        <?php echo $textoDisp; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($sala['Observaciones']); ?></td>
                                                <td class="text-center">
                                                    <a href="gestionarSala.php?editar=<?php echo urlencode($sala['IDSalon']); ?>" class="btn btn-sm btn-outline-warning text-dark fw-bold px-3 me-1">Modificar</a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalEliminar" data-idsalon="<?php echo htmlspecialchars($sala['IDSalon']); ?>">
                                                        Eliminar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class='alert alert-info text-center bg-white mb-0'>No hay salas registradas actualmente.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN -->
        <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-danger text-white border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold" id="modalEliminarLabel">Confirmar Eliminación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="fs-5 mb-1">¿Estás seguro de que deseas eliminar la sala?</p>
                        <p class="fw-bold fs-4 text-danger mb-0" id="nombreSalaEliminar"></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-secondary fw-bold rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <a id="btnConfirmarEliminar" href="#" class="btn btn-danger fw-bold rounded-pill px-4">Eliminar</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE NOTIFICACIÓN DE RESPUESTA -->
        <?php if (!empty($mensaje)): ?>
        <div class="modal fade" id="modalNotificacion" tabindex="-1" aria-labelledby="modalNotificacionLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-<?php echo $tipoAlerta; ?> text-white border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold" id="modalNotificacionLabel">
                            <?php echo ($tipoAlerta === 'success') ? '¡Operación Exitosa!' : 'Atención'; ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="fs-5 mb-0"><?php echo htmlspecialchars($mensaje); ?></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-<?php echo $tipoAlerta; ?> fw-bold rounded-pill px-4 text-white" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <?php include '../footer.php'; ?>
        
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        
        <script>
            // Configuración del modal de confirmación de eliminación
            var modalEliminar = document.getElementById('modalEliminar');
            if (modalEliminar) {
                modalEliminar.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var idSalon = button.getAttribute('data-idsalon');
                    
                    var nombreSalaElem = document.getElementById('nombreSalaEliminar');
                    var btnConfirmar = document.getElementById('btnConfirmarEliminar');
                    
                    nombreSalaElem.textContent = idSalon;
                    btnConfirmar.href = 'gestionarSala.php?eliminar=' + encodeURIComponent(idSalon);
                });
            }

            // Desplegar automáticamente el modal de notificación cuando haya un mensaje de respuesta
            <?php if (!empty($mensaje)): ?>
                var modalNoti = new bootstrap.Modal(document.getElementById('modalNotificacion'));
                modalNoti.show();
            <?php endif; ?>
        </script>
    </body>
</html>