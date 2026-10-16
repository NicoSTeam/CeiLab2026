<?php
require_once '../Clases/ClsUsuarios.php';
$objUsuario = new ClsUsuarios();

// Si la petición viene por POST vía AJAX para eliminar, procesamos y devolvemos solo el modal de respuesta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fidusuario'])) {
    echo $objUsuario->eliminarUsuario($_POST['fidusuario']);
    exit; 
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Eliminar Usuario - CeiLab</title>
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
                            <h3 class="fw-bold h2 mb-1 text-danger">Gestión de Eliminación</h3>
                            <p class="fw-light text-secondary mb-0">Seleccione un usuario para eliminar del sistema.</p> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- LISTADO Y FILTRADO -->
            <div class="container pb-5"> 
                <div class="row">
                    <div class="col-md-9 col-xl-8 ms-auto me-auto">
                        <div class="bg-white p-4 p-lg-5 shadow-sm rounded">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="mb-0 text-dark h4">Buscar usuario a eliminar</h2>
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
                        <h3 class="mb-3 text-dark fw-bold">Seleccione un usuario para eliminar</h3> 
                        <?php
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
                                            <th>Rol</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($resultadoListado)) { 
                                            $rolArr = $objUsuario->obtenerRolesPorUsuario($row['IDUsuario']);
                                            $textorol = !empty($rolArr) ? implode(', ', $rolArr) : 'Sin rol asignado';
                                            
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['IDUsuario']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($textorol); ?></span></td>
                                                <td class="text-center">
                                                    <!-- Botón que activa el modal de confirmación -->
                                                    <button type="button" class="btn btn-sm btn-danger fw-bold px-3" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalConfirmar" 
                                                            data-id="<?php echo $row['IDUsuario']; ?>"
                                                            data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>">
                                                        Eliminar
                                                    </button>
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
        </section>

        <!-- Modal de Confirmación de Borrado -->
        <div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">Confirmar Eliminación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="fs-5 text-dark mb-1">¿Estás seguro de que deseas eliminar al usuario?</p>
                        <p class="fw-bold text-danger fs-5" id="nombreUsuarioEliminar"></p>
                        <small class="text-muted">Esta acción eliminará al usuario <strong>definitivamente.</strong></small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger px-4 fw-bold" id="btnConfirmarEliminacion">Sí, eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor donde se inyectará el modal de respuesta final vía Fetch -->
        <div id="contenedorModal"></div>

        <?php include '../footer.php'; ?>
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        <script>
        let idUsuarioAEliminar = null;
        const modalConfirmar = document.getElementById('modalConfirmar');

        // Al hacer clic en Eliminar, capturamos el ID y nombre para mostrarlos en el modal de confirmación
        modalConfirmar.addEventListener('show.bs.modal', function (event) {
            let button = event.relatedTarget;
            idUsuarioAEliminar = button.getAttribute('data-id');
            let nombreUsuario = button.getAttribute('data-nombre');
            document.getElementById('nombreUsuarioEliminar').textContent = nombreUsuario;
        });

        // Al confirmar en el modal, enviamos la petición fetch de forma asíncrona
        document.getElementById('btnConfirmarEliminacion').addEventListener('click', function() {
            let modalConfirmarInstance = bootstrap.Modal.getInstance(modalConfirmar);
            modalConfirmarInstance.hide();

            let formData = new FormData();
            formData.append('fidusuario', idUsuarioAEliminar);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('contenedorModal').innerHTML = html;
                let elementoModalRespuesta = document.getElementById("modalRespuesta");
                if (elementoModalRespuesta) {
                    let miModalRespuesta = new bootstrap.Modal(elementoModalRespuesta);
                    miModalRespuesta.show();
                }
            })
            .catch(error => console.error('Error:', error));
        });
        </script>
    </body>
</html>