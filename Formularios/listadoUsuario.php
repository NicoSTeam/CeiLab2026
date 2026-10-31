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

require_once '../Clases/ClsUsuarios.php';
$objUsuario = new ClsUsuarios();

//Captura peticion AJAX para reactivar usuario
if (isset($_POST['accion']) && $_POST['accion'] === 'reactivar_usuario') {
    $idUsuario = $_POST['idUsuario'] ?? 0;
    $objUsuario->reactivarUsuario($idUsuario); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Listado de usuarios</title>
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
            <!-- Encabezado de la página -->
            <div class="container pb-4">
                <div class="row justify-content-center">
                    <div class="col-md-9 col-xl-8">
                        <div class="bg-white p-4 shadow-sm rounded text-center">
                            <h3 class="fw-bold h2 mb-1 text-primary">Listado de Usuarios</h3>
                            <p class="fw-light text-secondary mb-0">Consulte y filtre los usuarios registrados en el sistema CeiLab.</p> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de búsqueda -->
            <div class="container pb-5"> 
                <div class="row">
                    <div class="col-md-9 col-xl-8 ms-auto me-auto">
                        <div class="bg-white p-4 p-lg-5 shadow-sm rounded">
                            <h2 class="mb-1 text-dark h4">Buscar por usuario</h2>
                            <p class="mb-4 text-muted">Puede buscar / Filtrar por Nombre, Documento o Email</p>
                            
                            <form method="GET" action="">
                                <div class="mb-3">
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="bg-light input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="bg-light input-group-text"><i class="bi bi-card-text"></i></span>
                                            <input type="number" class="form-control" name="cedula" placeholder="Cédula (sin puntos ni guiones)" value="<?php echo htmlspecialchars($_GET['cedula'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                    <div class="input-group">
                                        <span class="bg-light input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                                    </div>
                                    </div>
                                    <div class="mb-3">
                                    <div class="input-group">
                                        <span class="bg-light input-group-text" id="addon-rol"></span>
                                        <select class="form-select" aria-label="Filtrar por Rol" aria-describedby="addon-rol" name="rol">
                                            <option value="" selected>Todos los roles...</option>
                                            <option value="1" <?php echo (($_GET['rol'] ?? '') === '1') ? 'selected' : ''; ?>>1 - Administrador</option>
                                            <option value="2" <?php echo (($_GET['rol'] ?? '') === '2') ? 'selected' : ''; ?>>2 - Responsable-CeiLab</option>
                                            <option value="3" <?php echo (($_GET['rol'] ?? '') === '3') ? 'selected' : ''; ?>>3 - Docente</option>
                                            <option value="4" <?php echo (($_GET['rol'] ?? '') === '4') ? 'selected' : ''; ?>>4 - Estudiante</option>
                                        </select>
                                    </div>
                                </div>
                                </div>
                                <button type="submit" class="btn d-block w-100 btn-primary fw-bold pb-2 ps-3 pe-3 pt-2">Buscar</button>
                            </form>
                        </div>
                    </div>                 
                </div>
            </div> 

            <!-- lista de usuarios activos -->
            <div class="container pb-5 pt-3 rounded shadow-sm col-md-10 col-xl-9" style="background-color: #adbfdc;">
                <div class="row">
                    <div>
                        <h3 class="mb-3 text-dark fw-bold">Resultados</h3> 
                        
                        <?php
                        require_once '../Clases/ClsUsuarios.php';
                        $objUsuario = new ClsUsuarios();

                        $nombre = $_GET['nombre'] ?? '';
                        $cedula = $_GET['cedula'] ?? '';
                        $email = $_GET['email'] ?? '';
                        $rol = $_GET['rol'] ?? '';

                        $resultado = $objUsuario->listarUsuarios($nombre, $cedula, $email, $rol);

                        if ($resultado && mysqli_num_rows($resultado) > 0) {
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
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($resultado)) { 
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
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($textorol); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['observaciones']); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php 
                        } else {
                            echo "<div class='alert alert-info text-center bg-white mb-0'>No se encontraron usuarios registrados con esos criterios.</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- lista de usuarios dados de baja -->
            <?php if ($esAdministrador): ?>
            <div class="container pb-4 pt-3 rounded shadow-sm col-md-10 col-xl-9 mt-4" style="background-color: #adbfdc;">
                <div class="bg-white border border-danger rounded p-3 shadow-sm">
                    <div class="row">
                        <div class="col-12">
                            
                            <!-- Encabezado con el boton de control por JS -->
                            <div class="d-flex justify-content-between align-items-center bg-danger bg-opacity-10 p-3 rounded mb-3">
                                <div>
                                    <h5 class="mb-0 text-danger fw-bold">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Usuarios Dados de Baja / Inactivos
                                    </h5>
                                    <small class="text-muted">Sección restringida para gestión de cuentas inactivas y reactivación.</small>
                                </div>
                                <button class="btn btn-danger btn-sm fw-bold px-3 shadow-sm" type="button" id="btnToggleInactivos">
                                    Mostrar / Ocultar Inactivos
                                </button>
                            </div>

                            <!-- Contenedor controlado por JavaScript (inicia oculto) -->
                            <div id="contenedorInactivosBody" style="display: none;">
                                <?php
                                $resultadoInactivos = $objUsuario->listarUsuariosInactivos(); 

                                if ($resultadoInactivos && mysqli_num_rows($resultadoInactivos) > 0) {
                                ?>
                                    <div class="table-responsive bg-light p-3 shadow-sm rounded border">
                                        <table class="table table-striped table-hover align-middle mb-0">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre</th>
                                                    <th>Cédula</th>
                                                    <th>Email</th>
                                                    <th>Dirección</th>
                                                    <th>Teléfonos</th>
                                                    <th>Rol</th>
                                                    <th>Observaciones</th>
                                                    <th class="text-center">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($rowInactivos = mysqli_fetch_assoc($resultadoInactivos)) { 
                                                    $telefonosArr = $objUsuario->obtenerTelefonosPorUsuario($rowInactivos['IDUsuario']);
                                                    $textoTelefonos = !empty($telefonosArr) ? implode(', ', $telefonosArr) : 'Sin teléfonos';
                                                    $rolArr = $objUsuario->obtenerRolesPorUsuario($rowInactivos['IDUsuario']);
                                                    $textorol = !empty($rolArr) ? implode(', ', $rolArr) : 'Sin rol asignado';
                                                ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($rowInactivos['IDUsuario']); ?></td>
                                                        <td><?php echo htmlspecialchars($rowInactivos['Nombre'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($rowInactivos['cedula'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($rowInactivos['Email'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($rowInactivos['Direccion'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($textoTelefonos); ?></td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo htmlspecialchars($textorol); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($rowInactivos['Observaciones'] ?? ''); ?></td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-success btn-sm fw-bold px-3 btn-reactivar shadow-sm" 
                                                                    data-id="<?php echo $rowInactivos['IDUsuario']; ?>" 
                                                                    data-nombre="<?php echo htmlspecialchars($rowInactivos['Nombre'] ?? ''); ?>">
                                                                🔄 Reactivar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php 
                                } else {
                                    echo "<div class='alert alert-info text-center bg-light border mb-0 shadow-sm'>No hay usuarios dados de baja actualmente.</div>";
                                }
                                ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>
      
        <?php include '../footer.php'; ?>
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        <script>
        //boton de reactivar
        document.addEventListener('DOMContentLoaded', function () {

            //1. Control del botón desplegable por JavaScript para evitar conflictos en el navbar
            const btnToggle = document.getElementById('btnToggleInactivos');
            const contenedorInactivos = document.getElementById('contenedorInactivosBody');

            if (btnToggle && contenedorInactivos) {
                btnToggle.addEventListener('click', function () {
                    if (contenedorInactivos.style.display === 'none' || contenedorInactivos.style.display === '') {
                        contenedorInactivos.style.display = 'block';
                    } else {
                        contenedorInactivos.style.display = 'none';
                    }
                });
            }

            //2. Lógica del boton de reactivar usuario
            document.addEventListener('click', function (e) {
                const btnReactivar = e.target.closest('.btn-reactivar');
                if (!btnReactivar) return;

                const idUsuario = btnReactivar.getAttribute('data-id');
                const nombreUsuario = btnReactivar.getAttribute('data-nombre');
                const contenedorModal = document.getElementById('contenedorModal');

                if (!contenedorModal) return;

                contenedorModal.innerHTML = `
                    <div class="modal fade" id="modalReactivar" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title fw-bold">🔄 Confirmar Reactivación</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-1">¿Desea reactivar al usuario <b>${nombreUsuario}</b>?</p>
                                    <p class="text-muted small mb-0">El usuario recuperará su acceso al sistema.</p>
                                </div>
                                <div class="modal-footer justify-content-center bg-light">
                                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" id="btnEjecutarReactivacion" class="btn btn-success px-4 fw-bold shadow-sm" data-id="${idUsuario}">Sí, Reactivar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                const modalEl = document.getElementById('modalReactivar');
                const myModal = new bootstrap.Modal(modalEl);
                myModal.show();

                const btnEjecutarReactivacion = document.getElementById('btnEjecutarReactivacion');
                if (btnEjecutarReactivacion) {
                    btnEjecutarReactivacion.addEventListener('click', function () {
                        const idUsr = this.getAttribute('data-id');
                        myModal.hide();

                        const formData = new FormData();
                        formData.append('accion', 'reactivar_usuario');
                        formData.append('idUsuario', idUsr);

                        fetch('/CeiLab2026/Formularios/listadoUsuario.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            location.reload();
                        })
                        .catch(error => console.error('Error:', error));
                    });
                }
            });

        });
        </script>
    </body>
</html>