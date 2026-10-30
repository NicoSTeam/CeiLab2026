<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>In progress</title>
        <!-- Bootstrap core CSS -->
        <link href="../bootstrap_theme/bootstrap.css" rel="stylesheet" type="text/css">
        <!-- Custom styles for this template -->
        <link rel="stylesheet" href="../Estilos/blocks.css">
        <link href="../Estilos/style.css" rel="stylesheet">
        <link rel="icon" href="/CeiLab2026/assets/img/Ceibal_ico.png">
    </head>
    <body class="d-flex flex-column min-vh-100 bg-light">
        
        <!-- Barra de navegación -->
        <?php include '../navbar.php'; ?> 

        
            <section class="h-90 hero-section pb-5 pt-5 min-vh-100">
            <div class="container pb-5 pt-5">

                <div class="mb-4 d-block pb-5 ps-4 pe-4 pt-5 shadow rounded-4" style="background-color: #adbfdc;"> 
                    <div class="bg-white rounded-4 p-4">
                        <div class="text-center">
                            <h2 class="fw-bold h1 mb-1 text-primary">Creado por grupo</h2> 
                            <div class="pt-3">
                                <img src="/CeiLab2026/assets/img/syncDyn.png" class="img-thumbnail border-0" style="width: auto; height: auto; max-height: 120px;" alt="workInProgress">
                            </div>
                        </div>
                    </div>  
                </div>

                <!-- Sección de Integrantes -->
                <div class="d-block pb-5 ps-4 pe-4 pt-5 shadow rounded-4" style="background-color: #adbfdc;"> 
                    <div class="pt-3 pb-3">
                        <h2 class="fw-bold h1 mb-1 text-dark">Integrantes:</h2>
                        <p class="text-dark opacity-75">Conoce al equipo de desarrollo detrás de CeiLab2026.</p>
                    </div>

                    <!-- Contenedor de las tarjetas de los 5 integrantes -->
                    <div class="row g-4">
                        
                        <!-- Integrante 1 -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="row g-0 align-items-center">
                                    <div class="col-md-2 text-center mb-3 mb-md-0">
                                        <img src="/CeiLab2026/assets/img/pia.jpeg" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover;" alt="Foto de Integrante">
                                    </div>
                                    <div class="col-md-10 ps-md-3">
                                        <h4 class="fw-bold text-dark mb-1">Pia Cabrera</h4>
                                        <p class="text-muted small mb-2">Proyect Manager, encargada de gestionar el personal de la empresa.</p>
                                        <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                                            📧 pia.01.09.04@gmail.com
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Integrante 2 -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="row g-0 align-items-center">
                                    <div class="col-md-2 text-center mb-3 mb-md-0">
                                        <img src="/CeiLab2026/assets/img/nicolas.jpeg" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover;" alt="Foto de Integrante">
                                    </div>
                                    <div class="col-md-10 ps-md-3">
                                        <h4 class="fw-bold text-dark mb-1">Nicolás Silva</h4>
                                        <p class="text-muted small mb-2">Gerente de TI, Responsable de desarrollo full-stack.</p>
                                        <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                                            📧 nicolas.silvamedina@ceibal.edu.uy
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Integrante 3 -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="row g-0 align-items-center">
                                    <div class="col-md-2 text-center mb-3 mb-md-0">
                                        <img src="/CeiLab2026/assets/img/gonzalo.jpeg" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover;" alt="Foto de Integrante">
                                    </div>
                                    <div class="col-md-10 ps-md-3">
                                        <h4 class="fw-bold text-dark mb-1">Gonzalo Ruiz Diaz</h4>
                                        <p class="text-muted small mb-2">Database Team Lead, líder de desarrollo en bases de datos. </p>
                                        <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                                            📧 gonzaloruizdiazrodriguez@gmail.com
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Integrante 4 -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="row g-0 align-items-center">
                                    <div class="col-md-2 text-center mb-3 mb-md-0">
                                        <img src="/CeiLab2026/assets/img/pablo.png" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover;" alt="Foto de Integrante">
                                    </div>
                                    <div class="col-md-10 ps-md-3">
                                        <h4 class="fw-bold text-dark mb-1">Pablo Buydid</h4>
                                        <p class="text-muted small mb-2">QA Lead, responsable de las pruebas de calidad. 
</p>
                                        <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                                            📧 pbuydid@gmail.com
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Integrante 5 -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="row g-0 align-items-center">
                                    <div class="col-md-2 text-center mb-3 mb-md-0">
                                        <img src="/CeiLab2026/assets/img/daian.jpeg" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover;" alt="Foto de Integrante">
                                    </div>
                                    <div class="col-md-10 ps-md-3">
                                        <h4 class="fw-bold text-dark mb-1">Daian Zarauza</h4>
                                        <p class="text-muted small mb-2">Gerenta de Marketing, encargada de documentación.</p>
                                        <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                                            📧 prof.dzarauza@gmail.com
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>  
                </div>

            </div>
        </section>
       
        <!-- Footer -->
        <?php include '../footer.php'; ?>
        
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        
    </body>
</html>