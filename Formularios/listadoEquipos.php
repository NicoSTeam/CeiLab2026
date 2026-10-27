<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
require_once '../Clases/ClsEquipo.php';

$objEquipo = new ClsEquipo();

// Capturar los dos filtros enviados por GET
$filtroTipo = $_GET['tipo'] ?? '';
$filtroDisponibilidad = $_GET['disponibilidad'] ?? '';

// Obtener la lista filtrada
$listaEquipos = $objEquipo->listarEquipos($filtroTipo, $filtroDisponibilidad);

$tiposPermitidos = ["Dron", "Ceibalita", "Micro:bit", "Placa Makey Makey", "Kit de robótica", "Sensores Fisicoquímicos", "Impresora 3D", "Lápiz 3D", "Microscopio USB"];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Listado de Equipos - CeiLab</title>
        <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="../Estilos/blocks.css">
        <link href="../Estilos/style.css" rel="stylesheet">
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    </head>
    <body class="d-flex flex-column min-vh-100 bg-light">
        
        <?php include '../navbar.php'; ?> 
        
        <section class="hero-section flex-fill pb-5 pt-5">
            <div class="container pb-5 pt-3 rounded shadow-sm col-md-11 col-xl-10" style="background-color: #adbfdc;">
                <div class="row justify-content-center">
                    <div>
                        <div class="bg-white p-3 mb-4 shadow-sm rounded text-center">
                            <h3 class="fw-bold h2 mb-1 text-primary">Listado General de Equipos</h3>
                        </div>

                        <!-- Formulario de Filtros Simplificado -->
                        <form method="GET" action="listadoEquipos.php" class="d-flex flex-wrap align-items-end gap-3 mb-4 bg-white p-3 shadow-sm rounded">
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
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class='alert alert-info text-center bg-white mb-0'>No se encontraron equipos activos con los filtros seleccionados.</div>
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