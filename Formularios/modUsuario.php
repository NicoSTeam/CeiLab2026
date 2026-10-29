<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion();

//Verificación del usuario para que no ingrese por ruta del navegador
$esAdministrador = isset($_SESSION['roles']) && in_array('1', $_SESSION['roles']);
$esDot = isset($_SESSION['roles']) && in_array('2', $_SESSION['roles']); //debo ver de que solo el admin pueda asignar rol admin

if (!$esAdministrador && !$esDot) {
    $_SESSION['mensaje'] = "Acceso denegado. Acción no permitida para el rol detectado.";
    $_SESSION['tipoAlerta'] = "danger";
    header("Location: ../index.php");
    exit();
}

//Obtengo el ID del usuario que se va a modificar (por POST o GET)
$idUsuarioAModificar = $_GET['id'] ?? $_POST['id_usuario'] ?? 0;
$idUsuarioAModificar = (int)$idUsuarioAModificar;

//Consulta si el usuario objetivo tiene el rol de Administrador ('1')
$objConexion = new Conexion();
$link = $objConexion->conectar();

$sqlRolesObjetivo = "SELECT ROL FROM rol WHERE IDUsuario = $idUsuarioAModificar";
$resRolesObjetivo = mysqli_query($link, $sqlRolesObjetivo);

$usuarioObjetivoEsAdmin = false;
if ($resRolesObjetivo) {
    while ($fila = mysqli_fetch_assoc($resRolesObjetivo)) {
        if ($fila['ROL'] === '1') {
            $usuarioObjetivoEsAdmin = true;
            break;
        }
    }
}

//Flag para bloquear si es DOT intentando modificar a un Admin
$bloquearModificacion = ($esDot && !$esAdministrador && $usuarioObjetivoEsAdmin);

require_once '../Clases/ClsUsuarios.php';
$objUsuario = new ClsUsuarios();

//Envia el formulario por POST vía AJAX, procesa la modificación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fidusuario'])) {
    echo $objUsuario->modificarUsuario();
    exit;
}

$idModificar = $_GET['id'] ?? null;
$usuarioData = null;
$telefonosData = [];
$rolesData = []; 

if ($idModificar) {
    $usuarioData = $objUsuario->obtenerUsuarioPorId($idModificar);
    $telefonosData = $objUsuario->obtenerTelefonosPorUsuario($idModificar);
    $rolesData = $objUsuario->obtenerRolesPorUsuario($idModificar); 
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Modificar Usuario - CeiLab</title>
        <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="../Estilos/blocks.css">
        <link href="../Estilos/style.css" rel="stylesheet">
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    </head>
    <body class="d-flex flex-column min-vh-100 bg-light">
        <?php include '../navbar.php'; ?> 

        <section class="hero-section flex-fill pb-5 pt-5">
            <div class="container pb-4">
                <div class="row">
                    <div class="col-md-9 col-xl-8 ms-auto me-auto">
                        <div class="bg-white p-4 shadow-sm rounded text-center">
                            <h3 class="fw-bold h2 mb-1 text-primary">Gestión de Modificación</h3>
                            
                            <p class="fw-light text-secondary mb-0">Seleccione un usuario para editar sus datos o busque en el sistema.</p> 
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$usuarioData): ?>
                <!--LISTADO Y FILTRADO -->
                <div class="container pb-5"> 
                    <div class="row">
                        <div class="col-md-9 col-xl-8 ms-auto me-auto">
                            <div class="bg-white p-4 p-lg-5 shadow-sm rounded">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="mb-1 text-dark h4">Buscar usuario a modificar</h2>
                                <a href="/CeiLab2026/index.php" class="btn btn-outline-primary fw-bold text-center" style="width: 100%; max-width: 210px;"> ← Volver al principio </a>
                                </div>
                                <p class="mb-4 text-muted">Filtre por Nombre, Cédula, Email o Rol para encontrar al usuario</p>
                                
                                <form method="GET" action="">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" name="nombre" placeholder="Filtrar por Nombre" value="<?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <input type="number" class="form-control" name="cedula" placeholder="Filtrar por Cédula" value="<?php echo htmlspecialchars($_GET['cedula'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <input type="email" class="form-control" name="email" placeholder="Filtrar por Email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <select class="form-select" name="rol">
                                                <option value="" selected>Todos los roles...</option>
                                                <option value="1" <?php echo (($_GET['rol'] ?? '') === '1') ? 'selected' : ''; ?>>1 - Administrador</option>
                                                <option value="2" <?php echo (($_GET['rol'] ?? '') === '2') ? 'selected' : ''; ?>>2 - Responsable-CeiLab</option>
                                                <option value="3" <?php echo (($_GET['rol'] ?? '') === '3') ? 'selected' : ''; ?>>3 - Docente</option>
                                                <option value="4" <?php echo (($_GET['rol'] ?? '') === '4') ? 'selected' : ''; ?>>4 - Estudiante</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn d-block w-100 btn-primary fw-bold pb-2 ps-3 pe-3 pt-2">Buscar / Filtrar</button>
                                </form>
                            </div>
                        </div>                
                    </div>
                </div> 

                <div class="container pb-5 pt-3 rounded shadow-sm col-md-10 col-xl-9" style="background-color: #adbfdc;">
                    <div class="row">
                        <div>
                            <h3 class="mb-3 text-dark fw-bold">Seleccione un usuario para modificar</h3> 
                            <?php
                            //Toma todos los filtros enviados por GET
                            $nombreFiltro = $_GET['nombre'] ?? '';
                            $cedulaFiltro = $_GET['cedula'] ?? '';
                            $emailFiltro  = $_GET['email'] ?? '';
                            $rolFiltro    = $_GET['rol'] ?? '';

                            $resultadoListado = $objUsuario->listarUsuarios($nombreFiltro, $cedulaFiltro, $emailFiltro, $rolFiltro);

                            if ($resultadoListado && mysqli_num_rows($resultadoListado) > 0) {
                            ?>
                                <div class="table-responsive bg-white p-3 shadow-sm rounded">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Cédula</th>
                                                <th>Email</th>
                                                <th>Dirección</th>
                                                <th>Teléfonos</th>
                                                <th>Rol</th>
                                                <th class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($resultadoListado)) { 
                                            $telefonosArr = $objUsuario->obtenerTelefonosPorUsuario($row['IDUsuario']);
                                            $textoTelefonos = !empty($telefonosArr) ? implode(', ', $telefonosArr) : 'Sin teléfonos';
 
                                            $rolArr = $objUsuario->obtenerRolesPorUsuario($row['IDUsuario']);
                                            $textorol = !empty($rolArr) ? implode(', ', $rolArr) : 'Sin rol asignado';
                                            
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['IDUsuario']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                                                    <td><?php echo htmlspecialchars($textoTelefonos); ?></td>
                                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($textorol); ?></span></td>
                                                    <td class="text-center">
                                                        <a href="modUsuario.php?id=<?php echo $row['IDUsuario']; ?>" class="btn btn-sm btn-warning fw-bold px-3">Modificar</a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else {
                                echo "<div class='alert alert-info text-center bg-white mb-0'>No se encontraron usuarios con los criterios especificados.</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- FORMULARIO DE MODIFICACIÓN PRE-LLENADO -->
                <div class="container pb-5">
                    <div class="row justify-content-center">
                        <div class="col-md-9 col-xl-8">
                            <div class="bg-white p-4 p-lg-5 shadow rounded">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="mb-0 text-dark h4">Modificar Datos</h2>
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <a href="modUsuario.php" class="btn btn-outline-secondary fw-bold text-center" style="width: 100%; max-width: 210px;">
                                        ← Volver al listado
                                    </a>
                                    <a href="/CeiLab2026/index.php" class="btn btn-outline-primary fw-bold text-center" style="width: 100%; max-width: 210px;">
                                        ← Volver al principio
                                    </a>
                                </div>
                            </div>
                                
                                <form id="formModificar" action="" method="POST">
                                    <!-- ID oculto -->
                                    <input type="hidden" name="fidusuario" value="<?php echo htmlspecialchars($usuarioData['IDUsuario']); ?>">

                                    <!-- Nombre -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Nombre completo</label>
                                        <input type="text" class="form-control" name="fnombre" value="<?php echo htmlspecialchars($usuarioData['nombre']); ?>" required>
                                    </div>

                                    <!-- Cédula -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Cédula</label>
                                        <input type="number" class="form-control" name="fcedula" value="<?php echo htmlspecialchars($usuarioData['cedula']); ?>" required>
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Email</label>
                                        <input type="email" class="form-control" name="femail" value="<?php echo htmlspecialchars($usuarioData['email']); ?>" required>
                                    </div>

                                    <!-- Dirección -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Dirección</label>
                                        <input type="text" class="form-control" name="fdireccion" value="<?php echo htmlspecialchars($usuarioData['direccion']); ?>">
                                    </div>

                                    <!-- Teléfonos (carga hasta 2 campos basados en los registros previos) -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Teléfonos</label>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="ftelefonos[]" placeholder="Teléfono principal" value="<?php echo htmlspecialchars($telefonosData[0] ?? ''); ?>">
                                        </div>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="ftelefonos[]" placeholder="Celular secundario" value="<?php echo htmlspecialchars($telefonosData[1] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <!-- Observaciones -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Observaciones</label>
                                        <input type="text" class="form-control" name="fobservaciones" value="<?php echo htmlspecialchars($usuarioData['observaciones']); ?>">
                                    </div>

                                    <?php if ($esAdministrador): ?> 
                                    <!-- Rol Principal -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Rol Principal</label>
                                        <select class="form-select" name="frol[]" required>
                                            <option value="" disabled>Seleccione un rol...</option>
                                            <option value="1" <?php echo (($rolesData[0] ?? '') == '1') ? 'selected' : ''; ?>>1 - Administrador</option>
                                            <option value="2" <?php echo (($rolesData[0] ?? '') == '2') ? 'selected' : ''; ?>>2 - Responsable-CeiLab</option>
                                            <option value="3" <?php echo (($rolesData[0] ?? '') == '3') ? 'selected' : ''; ?>>3 - Docente</option>
                                            <option value="4" <?php echo (($rolesData[0] ?? '') == '4') ? 'selected' : ''; ?>>4 - Estudiante</option>
                                        </select>
                                    </div>

                                    <!-- Rol Secundario -->
                                    <div class="mb-4">
                                        <label class="form-label text-muted small">Rol Secundario (Opcional)</label>
                                        <select class="form-select" name="frol[]">
                                            <option value="" <?php echo empty($rolesData[1]) ? 'selected' : ''; ?>>Ninguno / Opcional...</option>
                                            <option value="1" <?php echo (($rolesData[1] ?? '') == '1') ? 'selected' : ''; ?>>1 - Administrador</option>
                                            <option value="2" <?php echo (($rolesData[1] ?? '') == '2') ? 'selected' : ''; ?>>2 - Responsable-CeiLab</option>
                                            <option value="3" <?php echo (($rolesData[1] ?? '') == '3') ? 'selected' : ''; ?>>3 - Docente</option>
                                            <option value="4" <?php echo (($rolesData[1] ?? '') == '4') ? 'selected' : ''; ?>>4 - Estudiante</option>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    <div class="mb-4">
                                        <label for="fcontrasenia" class="form-label ">Nueva Contraseña <small class="text-muted">(Opcional)</small></label>
                                        <input type="password" class="form-control rounded-3 shadow-sm" id="fcontrasenia" name="fcontrasenia" placeholder="Dejar en blanco para mantener la actual">
                                    </div>

                                    <button type="submit" class="btn d-block w-100 btn-success fw-bold py-2">Guardar Cambios</button>
                                </form>
                                <div id="contenedorModal"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
        <?php if ($bloquearModificacion): ?>
            <!-- MODAL DE BLOQUEO DIRECTO -->
            <div class="modal fade show" id="modalRestriccion" tabindex="-1" aria-modal="true" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0 rounded-4">
                        <div class="modal-header bg-danger text-white border-0 rounded-top-4">
                            <h5 class="modal-title fw-bold">Acción No Permitida</h5>
                        </div>
                        <div class="modal-body text-center py-4">
                            <div class="mb-3">
                                <span class="fs-1">🚫</span>
                            </div>
                            <p class="fs-5 text-dark mb-0">Un usuario DOT no tiene permisos para modificar o alterar los datos de una cuenta de Administrador.</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <a href="/CeiLab2026/Formularios/modUsuario.php" class="btn btn-danger fw-bold rounded-pill px-4">Volver al listado</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>                                    
        <?php include '../footer.php'; ?>
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Mostrar el modal con sombreado automático de Bootstrap
            var modalRestriccion = new bootstrap.Modal(document.getElementById('modalRestriccion'));
            modalRestriccion.show();
            
            // 2. Deshabilitar clics y opacar el contenido de atrás por seguridad visual
            let contenedor = document.getElementById('contenidoPrincipal');
            if (contenedor) {
                contenedor.style.pointerEvents = 'none';
                contenedor.style.opacity = '0.3';
            }
        });
        // Script AJAX fetch para guardar sin recargar página y mostrar modal Bootstrap
        const formModificar = document.getElementById('formModificar');
        if (formModificar) {
            formModificar.addEventListener('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                fetch('', { 
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('contenedorModal').innerHTML = html;
                    var elementoModal = document.getElementById("modalRespuesta");
                    if (elementoModal) {
                        var miModal = new bootstrap.Modal(elementoModal);
                        miModal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        }
        </script>
    </body>
</html>