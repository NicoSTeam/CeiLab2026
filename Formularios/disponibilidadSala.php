<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion();

// Verificación del usuario para que no ingrese por ruta del navegador
$esAdministrador = isset($_SESSION['roles']) && in_array('1', $_SESSION['roles']);
$esDot = isset($_SESSION['roles']) && in_array('2', $_SESSION['roles']);
$esDocente = isset($_SESSION['roles']) && in_array('3', $_SESSION['roles']);

if (!$esAdministrador && !$esDot && !$esDocente) {
    $_SESSION['mensaje'] = "Acceso denegado. Acción no permitida para el rol detectado.";
    $_SESSION['tipoAlerta'] = "danger";
    header("Location: ../index.php");
    exit();
}

require_once '../Clases/ClsSala.php';

$objSala = new ClsSala();

//Toma los filtros de búsqueda
$idSalonFiltro = $_GET['idSalon'] ?? '';
$disponibilidadFiltro = $_GET['disponibilidad'] ?? '';

$listaSalas = $objSala->listarSalasFiltradas($idSalonFiltro, $disponibilidadFiltro);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Disponibilidad de Salas - CeiLab</title>
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
                            <h3 class="fw-bold h2 mb-1 text-primary">Disponibilidad de Salas</h3>
                            <p class="fw-light text-secondary mb-0">Consulte y filtre el estado de disponibilidad de las salas del sistema CeiLab.</p> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Búsqueda y Filtro -->
            <div class="container pb-5"> 
                <div class="row">
                    <div class="col-md-9 col-xl-8 ms-auto me-auto">
                        <div class="bg-white p-4 p-lg-5 shadow-sm rounded">
                            <h2 class="mb-1 text-dark h4">Filtrar salas</h2>
                            <p class="mb-4 text-muted">Busque por ID del salón o filtre según su estado actual</p>
                            
                            <form method="GET" action="">
                                <div class="mb-3">
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="bg-light input-group-text"><i class="bi bi-door-open"></i></span>
                                            <input type="text" class="form-control" name="idSalon" placeholder="ID / Nombre de la sala" value="<?php echo htmlspecialchars($idSalonFiltro); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="bg-light input-group-text" id="addon-disp"></span>
                                            <select class="form-select" aria-label="Filtrar por Disponibilidad" aria-describedby="addon-disp" name="disponibilidad">
                                                <option value="" selected>Todas las disponibilidades...</option>
                                                <option value="1" <?php echo ($disponibilidadFiltro === '1') ? 'selected' : ''; ?>>1 - Disponible</option>
                                                <option value="0" <?php echo ($disponibilidadFiltro === '0') ? 'selected' : ''; ?>>0 - No disponible</option>
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
        
            <!-- Contenedor de Resultados -->
            <div class="container pb-5 pt-3 rounded shadow-sm col-md-10 col-xl-9" style="background-color: #adbfdc;">
                <div class="row">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0 text-dark fw-bold">Resultados de Salas</h3> 
                            <span class="badge bg-secondary rounded-pill px-3 py-2">Total: <?php echo count($listaSalas); ?></span>
                        </div>
                        
                        <?php if (count($listaSalas) > 0): ?>
                            <div class="table-responsive bg-white p-3 shadow-sm rounded">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Salón</th>
                                            <th>Disponibilidad</th>
                                            <th>Observaciones</th>
                                            <th class="text-center">Acciones / Reservas</th>
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
                                                    <?php if ($esDisponible): ?>
                                                        <a href="calendarioSala.php?idsala=<?php echo urlencode($sala['IDSalon']); ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3 shadow-sm text-decoration-none">
                                                            Ver Calendario
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="#" class="btn btn-sm btn-outline-secondary fw-bold rounded-pill px-3 shadow-sm text-decoration-none disabled" tabindex="-1" aria-disabled="true" title="Sala no disponible para reservas">
                                                            Ver Calendario
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class='alert alert-info text-center bg-white mb-0'>No se encontraron salas con los criterios especificados.</div>
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