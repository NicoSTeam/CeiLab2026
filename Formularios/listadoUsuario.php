<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
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
                            <h3 class="fw-bold h2 mb-1 text-primary">Listado de usuarios</h3>
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
        </section>
        
        <?php include '../footer.php'; ?>
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>