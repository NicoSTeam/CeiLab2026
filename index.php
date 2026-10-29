<?php
// Inicia la sesión sin forzar redirección si es invitado.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Clases/ClsLogin.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>CeiLab</title>
        <!-- Bootstrap core CSS -->
        <link href="bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <!-- Custom styles for this template -->
        <link rel="stylesheet" href="Estilos/blocks.css">
        <link href="Estilos/style.css" rel="stylesheet">
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    </head>
    <body>
        
        <div class="container">
</div>
<?php include 'navbar.php'; ?> 
    
        <section class="h-90 hero-section pb-5 pt-5 min-vh-100">
            <div class="container pb-5 pt-5">
                <div class="align-items-center mb-3 row"> 
                    <div class="col-lg-7"> 
                        <h2 class="h6 mb-1 text-light">Bienvenidos</h2> 
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex">
                                <img src="/CeiLab2026/assets/img/cerp logo.jpeg" class="img-fluid rounded shadow-sm bg-white p-1" style="max-height: 100px; width: auto" alt="icono cerp del este">
                            </div>
                            <div>
                                <h3 class="fw-bold h2 mb-1 text-white">Sala Ceilab</h3>
                                <p class="fw-light text-white mb-0">Plataforma para la gestión de equipos y salas CeiLab para <strong>Cerp del Este</strong>.</p> 
                            </div>
                        </div>
                    </div>                     
                    <div class="col-lg-auto ms-auto"> 
                    </div>                     
                </div>                 
                <div class="row"> 
                    <div class="col-sm-6 col-xl-6 pb-3 pt-3"> 
                        <div class="d-block pb-5 ps-4 pe-4 pt-5 shadow rounded-4" style="background-color: #adbfdc;">  
                            <?php include 'Formularios/calendarioIndex.php'; ?>
                            
                        </div>                         
                    </div>
                    <div class="col-sm-6 col-xl-6 pb-3 pt-3"> 
                        <div class="d-block pb-5 ps-4 pe-4 pt-5 shadow rounded-4" style="background-color: #adbfdc;"> 
                            <div class="bg-white rounded-4 p-4">
                            <h4 class="fw-bold h5">Novedades</h4> 
                            <img src="/CeiLab2026/assets/img/novedades.jpeg" class="img-thumbnail" style="with: auto; height: auto;" alt="workInProgress">
                            </div>  
                        </div>                         
                    </div> 
                    <div class="col-12 pt-3">
                        <div class="overflow-hidden shadow rounded-4 p-2" style="background-color: #adbfdc !important;">
                            <img src="/CeiLab2026/assets/img/Cerp-mural.jpeg" class="img-fluid w-100 rounded-3 shadow-sm" alt="Mural Cerp">
                        </div>
                    </div>                    
                </div>                 
            </div>             
        </section>
        
        <?php include 'footer.php'; ?> 
        <script src="assets/js/popper.min.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>

        <?php 
        // Capturar mensajes flash de la sesión
        $mensajeIndex = $_SESSION['mensaje'] ?? '';
        $tipoAlertaIndex = $_SESSION['tipoAlerta'] ?? 'danger';

        if (!empty($mensajeIndex)): 
            // Limpiar las variables para que el modal no vuelva a aparecer al recargar
            unset($_SESSION['mensaje'], $_SESSION['tipoAlerta']);
            
            $esWarning = ($tipoAlertaIndex === 'warning');
            $claseTextoHeader = $esWarning ? 'text-dark' : 'text-white';
            $claseBotonCierre = $esWarning ? '' : 'btn-close-white';
        ?>
        <!-- MODAL DE ACCESO DENEGADO / NOTIFICACIÓN EN INDEX -->
        <div class="modal fade" id="modalNotificacionGlobal" tabindex="-1" aria-labelledby="modalNotificacionGlobalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-<?php echo $tipoAlertaIndex; ?> <?php echo $claseTextoHeader; ?> border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold" id="modalNotificacionGlobalLabel">Atención</h5>
                        <button type="button" class="btn-close <?php echo $claseBotonCierre; ?>" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="fs-5 mb-0"><?php echo htmlspecialchars($mensajeIndex); ?></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-<?php echo $tipoAlertaIndex; ?> fw-bold rounded-pill px-4 <?php echo $esWarning ? 'text-dark' : 'text-white'; ?>" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Mostrar el modal automáticamente apenas cargue el index
            document.addEventListener("DOMContentLoaded", function() {
                var modalNoti = new bootstrap.Modal(document.getElementById('modalNotificacionGlobal'));
                modalNoti.show();
            });
        </script>
        <?php endif; ?>
        
    </body>
</html>
