<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
require_once '../Clases/ClsEquipo.php';

$objEquipo = new ClsEquipo();
$mensaje = '';
$tipoAlerta = 'success';

//Procesar la baja lógica vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $idEquipo = $_POST['idEquipo'] ?? '';

    if (!empty($idEquipo)) {
        if ($objEquipo->eliminarEquipo($idEquipo)) {
            $_SESSION['mensaje'] = "¡El equipo se ha eliminado correctamente!";
            $_SESSION['tipoAlerta'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al intentar eliminar el equipo.";
            $_SESSION['tipoAlerta'] = "danger";
        }
    } else {
        $_SESSION['mensaje'] = "ID de equipo inválido.";
        $_SESSION['tipoAlerta'] = "warning";
    }

    header("Location: borrarEquipo.php");
    exit();
}

//Recuperar mensaje flash de la sesión
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoAlerta = $_SESSION['tipoAlerta'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipoAlerta']);

//Capturar los dos filtros enviados por GET
$filtroTipo = $_GET['tipo'] ?? '';
$filtroDisponibilidad = $_GET['disponibilidad'] ?? '';

//Obtener la lista filtrada
$listaEquipos = $objEquipo->listarEquipos($filtroTipo, $filtroDisponibilidad);

$tiposPermitidos = ["Dron", "Ceibalita", "Micro:bit", "Placa Makey Makey", "Kit de robótica", "Sensores Fisicoquímicos", "Impresora 3D", "Lápiz 3D", "Microscopio USB"];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Dar de Baja Equipos - CeiLab</title>
        <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="../Estilos/blocks.css">
        <link href="../Estilos/style.css" rel="stylesheet">
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    </head>
    <body class="d-flex flex-column min-vh-100 bg-light">
        
        <?php include '../navbar.php'; ?> 
        
        <section class="hero-section flex-fill pb-5 pt-5">
            <div class="container pb-5 pt-3 rounded shadow-sm col-md-11 col-xl-10" style="background-color: #adbfdc;">
                <div class="row">
                    <div>
                        <div class="bg-white p-3 mb-4 shadow-sm rounded text-center">
                        <h3 class="mb-3 text-danger fw-bold">Seleccione un equipo para eliminar</h3>
                        </div> 
                        
                        <!-- Formulario de Filtros Simplificado -->
                        <form method="GET" action="borrarEquipo.php" class="d-flex flex-wrap align-items-end gap-3 mb-4 bg-white p-3 shadow-sm rounded">
                            <!-- Filtrar por Tipo -->
                            <div class="flex-grow-1" style="min-width: 240px;">
                                <label for="tipo" class="form-label fw-bold text-dark">Tipo de Equipo</label>
                                <select class="form-select rounded-3 shadow-sm" id="tipo" name="tipo">
                                    <option value="">-- Todos los tipos --</option>
                                    <?php foreach ($tiposPermitidos as $t): ?>
                                        <option value="<?php echo $t; ?>" <?php echo ($filtroTipo === $t) ? 'selected' : ''; ?>>
                                            <?php echo $t; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Filtrar por Disponibilidad -->
                            <div class="flex-grow-1" style="min-width: 240px;">
                                <label for="disponibilidad" class="form-label fw-bold text-dark">Disponibilidad</label>
                                <select class="form-select rounded-3 shadow-sm" id="disponibilidad" name="disponibilidad">
                                    <option value="">-- Todas las disponibilidades --</option>
                                    <option value="1" <?php echo ($filtroDisponibilidad === '1') ? 'selected' : ''; ?>>1 - Disponible</option>
                                    <option value="0" <?php echo ($filtroDisponibilidad === '0') ? 'selected' : ''; ?>>0 - No disponible</option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex gap-2 flex-grow-1" style="min-width: 200px;">
                                <button type="submit" class="btn btn-primary fw-bold shadow-sm flex-fill">Filtrar</button>
                                <a href="borrarEquipo.php" class="btn btn-outline-secondary fw-bold shadow-sm">Limpiar</a>
                            </div>
                        </form>

                        <?php if (count($listaEquipos) > 0): ?>
                            <div class="table-responsive bg-white p-3 shadow-sm rounded">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Equipo</th>
                                            <th>Tipo</th>
                                            <th>Estado</th>
                                            <th>Sala</th>
                                            <th>Disponibilidad</th>
                                            <th>Observaciones</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($listaEquipos as $eq):
                                            $disp = (string)$eq['Disponibilidad'];
                                            $esDisponible = ($disp === '1');
                                            $textoDisp = $esDisponible ? '1 - Disponible' : '0 - No disponible';
                                            $claseBadge = $esDisponible ? 'success' : 'danger';    
                                        ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($eq['IDEquipo']); ?></td>
                                                <td><?php echo htmlspecialchars($eq['Tipo']); ?></td>
                                                <td><?php echo htmlspecialchars($eq['Estado']); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($eq['IDSala_ceilab']); ?></span></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $claseBadge; ?>">
                                                        <?php echo $textoDisp; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($eq['Observaciones']); ?></td>
                                                <td class="text-center">
                                                    <!-- Botón que activa el modal de confirmación pasando el ID -->
                                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3 rounded-pill shadow-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalConfirmarBaja" 
                                                            data-id="<?php echo htmlspecialchars($eq['IDEquipo']); ?>">
                                                        Eliminar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class='alert alert-info text-center bg-white mb-0'>No hay equipos activos registrados actualmente.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- MODAL DE CONFIRMACIÓN DE BAJA -->
        <div class="modal fade" id="modalConfirmarBaja" tabindex="-1" aria-labelledby="modalConfirmarBajaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form action="borrarEquipo.php" method="POST">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="idEquipo" id="modalIdEquipo" value="">
                        
                        <div class="modal-header bg-danger text-white border-0 rounded-top-4">
                            <h5 class="modal-title fw-bold" id="modalConfirmarBajaLabel">Confirmar Baja</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 text-center">
                            <p class="fs-5 text-dark mb-0">¿Estás seguro de que deseas dar de baja este equipo?</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-secondary fw-bold rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4 shadow">Sí, dar de baja</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
            // Script para inyectar dinámicamente el ID del equipo en el modal de confirmación
            var modalConfirmarBaja = document.getElementById('modalConfirmarBaja');
            if (modalConfirmarBaja) {
                modalConfirmarBaja.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var idEquipo = button.getAttribute('data-id');
                    var inputIdEquipo = document.getElementById('modalIdEquipo');
                    inputIdEquipo.value = idEquipo;
                });
            }

            // Script para mostrar el modal de notificación si hay un mensaje flash
            <?php if (!empty($mensaje)): ?>
                var modalNoti = new bootstrap.Modal(document.getElementById('modalNotificacion'));
                modalNoti.show();
            <?php endif; ?>
        </script>
    </body>
</html>