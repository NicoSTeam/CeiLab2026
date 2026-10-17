<?php
require_once '../Clases/ClsLogin.php';
ClsLogin::verificarSesion(); 
require_once '../Clases/ClsUsuarios.php';

// Si la petición viene por POST vía fetch, procesamos y devolvemos solo el modal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $objUsuario = new ClsUsuarios();
    echo $objUsuario->registrarUsuario();
    exit; 
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Registrar Usuario - CeiLab</title>
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

        <section class="hero-section flex-fill pb-5 pt-5">
            <div class="container pb-4 pt-4">
                <div class="row justify-content-center mb-4"> 
                    <div class="col-md-9 col-xl-8">
                        <div class="bg-white p-4 shadow rounded text-center">
                            <h3 class="fw-bold h2 mb-0 text-primary">Registro de usuarios</h3>
                        </div>
                    </div> 
                </div>

                <div class="row justify-content-center"> 
                    <div class="col-md-9 col-xl-8">
                        <div class="bg-white p-4 p-lg-5 shadow rounded">
                            <h2 class="mb-1 text-dark">Registro</h2>
                            <p class="mb-4">Todos los campos son obligatorios</p>
                            
                            <form id="formRegistro" action="" method="POST">
                                
                                <!-- Nombre -->
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="bg-light input-group-text" id="addon-nombre">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                                <path fill="none" d="M0 0h24v24H0z"/>
                                                <path d="M 4 22 C 4 15.842 10.667 11.993 16 15.072 C 18.475 16.501 20 19.142 20 22 L 4 22 Z M 12 13 C 8.685 13 6 10.315 6 7 C 6 3.685 8.685 1 12 1 C 15.315 1 18 3.685 18 7 C 18 10.315 15.315 13 12 13 Z M 12 11 C 14.21 11 16 9.21 16 7 C 16 4.79 14.21 3 12 3 C 9.79 3 8 4.79 8 7 C 8 9.21 9.79 11 12 11 Z M 12 21 C 8.774 21 18.655 17.125 18.534 20.349 L 6 20 L 18 20 L 18.004 19.775 C 15.929 19.054 13.626 19.523 12 21 Z"/>
                                            </svg>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Nombre" aria-label="Nombre" aria-describedby="addon-nombre" name="fnombre" required>
                                    </div>
                                </div>

                                <!-- Cédula -->
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="bg-light input-group-text" id="addon-wrapping1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                                        <g>
                                                            < <path fill="none" d="M0 0h24v24H0z"/>
                                                            <path d="M 1.862 3.608 C 1.862 3.056 2.31 2.608 2.862 2.608 L 20.862 2.608 C 21.414 2.608 21.862 3.056 21.862 3.608 L 21.862 16.867 C 21.862 17.42 21.414 18.051 20.862 18.051 L 2.862 18.028 C 2.31 18.028 1.862 17.351 1.862 16.798 L 1.862 3.608 Z M 19.862 4.608 L 3.862 4.608 L 3.862 15.453 L 19.862 15.476 L 19.862 4.608 Z M 11.862 7.261 L 17.862 7.307 L 17.839 9.399 L 11.908 9.468 L 11.862 7.261 Z M 11.862 10.608 L 17.862 10.608 L 17.862 12.608 L 11.862 12.608 L 11.862 10.608 Z M 7.576 13.733 L 10.991 13.733 C 10.991 12.629 9.956 11.249 8.852 11.249 L 6.668 11.226 C 5.563 11.226 4.138 12.675 4.138 13.779 L 7.576 13.733 Z M 7.862 9.608 C 9.401 9.608 10.364 7.942 9.594 6.608 C 9.237 5.99 8.576 5.608 7.862 5.608 C 6.322 5.608 5.36 7.275 6.13 8.608 C 6.487 9.227 7.147 9.608 7.862 9.608 Z"/>
                                                        </g>
                                        </svg></span>
                                        <input type="number" class="form-control" placeholder="Cédula (sin puntos ni guiones)" aria-label="Cedula" aria-describedby="addon-wrapping1" name="fcedula" required>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="bg-light input-group-text" id="addon-email">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                                <path fill="none" d="M0 0h24v24H0z"></path>
                                                <path d="M3 3h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm17 4.238l-7.928 7.1L4 7.216V19h16V7.238z"></path>
                                            </svg>
                                        </span>
                                        <input type="email" class="form-control" placeholder="Email" aria-label="Email" aria-describedby="addon-email" name="femail" required>
                                    </div>
                                </div>
                                <!-- Dirección -->
                                 <div class="mb-3">
                                 <div class="input-group">
                                        <span class="bg-light input-group-text" id="addon-direccion">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                                <path fill="none" d="M0 0h24v24H0z"/>
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                            </svg>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Dirección" aria-label="Direccion" aria-describedby="addon-direccion" name="fdireccion">
                                </div>
                                </div>
                                <!-- Telefonos -->
                                <div class="mb-3">
                                    <div class="input-group mb-3">
                                    <span class="bg-light input-group-text" id="addon-telefono1">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                            <path fill="none" d="M0 0h24v24H0z"/>
                                            <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 0 0-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" placeholder="Teléfono principal (ej: 42223344)" aria-label="Telefono1" aria-describedby="addon-telefono1" name="ftelefonos[]">
                                </div>                          
                                <div class="input-group">
                                    <span class="bg-light input-group-text" id="addon-telefono2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                            <path fill="none" d="M0 0h24v24H0z"/>
                                            <path d="M17 2H7c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 18H7V4h10v16zM8 6h8v2H8V6zm0 12h8v2H8v-2z"/>
                                        </svg>
                                    </span>
                                    <input type="text" class="form-control" placeholder="Celular secundario (opcional)" aria-label="Telefono2" aria-describedby="addon-telefono2" name="ftelefonos[]">
                                </div>
                                </div>
                                <!-- Observaciones -->
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="bg-light input-group-text" id="addon-obs">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                                <path fill="none" d="M0 0h24v24H0z"/>
                                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                            </svg>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Observaciones" aria-label="Observaciones" aria-describedby="addon-obs" name="fobservaciones">
                                    </div>
                                </div>
                                <!-- Rol de Usuario -->
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="bg-light input-group-text" id="addon-rol">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                                <path fill="none" d="M0 0h24v24H0z"/>
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                            </svg>
                                        </span>
                                        <select class="form-select" aria-label="Rol de Usuario" aria-describedby="addon-rol" name="frol" required>
                                            <option value="" selected disabled>Seleccione un rol...</option>
                                            <option value="1">1 - Administrador</option>
                                            <option value="2">2 - Responsable-CeiLab</option>
                                            <option value="3">3 - Docente</option>
                                            <option value="4">4 - Estudiante</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Contraseña -->
                                <div class="mb-4">
                                    <div class="input-group">
                                        <span class="bg-light input-group-text" id="addon-password">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                                                <path fill="none" d="M0 0h24v24H0z"></path>
                                                <path d="M10.758 11.828l7.849-7.849 1.414 1.414-1.414 1.415 2.474 2.474-1.414 1.415-2.475-2.475-1.414 1.414 2.121 2.121-1.414 1.415-2.121-2.122-2.192 2.192a5.002 5.002 0 0 1-7.708 6.294 5 5 0 0 1 6.294-7.708z"></path>
                                            </svg>
                                        </span>
                                        <input type="password" class="form-control" placeholder="Nueva Contraseña" aria-label="Password" aria-describedby="addon-password" name="fcontrasenia" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn d-block w-100 btn-primary fw-bold pb-2 ps-3 pe-3 pt-2">Registrar</button>
                            </form>
                            <div id="contenedorModal"></div>
                        </div>
                    </div> 
                </div> 

            </div> 
        </section>

        <!-- Footer -->
        <?php include '../footer.php'; ?>
        
        <script src="../assets/js/popper.min.js"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>
        <script>
            const formRegistro = document.getElementById('formRegistro');
            if (formRegistro) {
                formRegistro.addEventListener('submit', function(e) {
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