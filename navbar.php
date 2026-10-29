<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificamos si el usuario actual tiene rol
$esAdministrador = isset($_SESSION['roles']) && in_array('1', $_SESSION['roles']);
$esDot = isset($_SESSION['roles']) && in_array('2', $_SESSION['roles']);
$esDocente = isset($_SESSION['roles']) && in_array('3', $_SESSION['roles']);
$esEstudiante = isset($_SESSION['roles']) && in_array('4', $_SESSION['roles']);
$estaLogueado = isset($_SESSION['IDUsuario']);
?>
<style>
    
</style>
<nav class="navbar navbar-expand-lg navbar-light py-lg-2 text-uppercase shadow" style="background-color: #adbfdc; border-bottom: 3px solid rgba(0, 0, 0, 0.15);"> 
    <div class="container">    
        <picture>
            <source type="image/webp">
            <a href="/CeiLab2026/index.php" class="d-inline-block text-decoration-none p-0 m-0">
                <img src="/CeiLab2026/assets/img/Ceilab-logo.jpg" class="img-thumbnail shadow-sm rounded-3" style="max-width: 180px; height: auto;" alt="Logo CeiLab">
            </a>
        </picture>                
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown-3" aria-controls="navbarNavDropdown-3" aria-expanded="false" aria-label="Toggle navigation"> 
            <span class="navbar-toggler-icon"></span> 
        </button>                
        
        <div class="collapse navbar-collapse" id="navbarNavDropdown-3"> 
            <ul class="mb-2 mb-lg-0 ms-auto navbar-nav align-items-lg-center gap-1"> 
                <!-- MÓDULO RESTRINGIDO: Solo visible para Administradores -->
                <?php if ($esAdministrador || $esDot): ?>
                    <li class="nav-item dropdown"> 
                    <a class="nav-link px-lg-3 py-lg-2 dropdown-toggle fw-bold text-dark rounded-3" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false" >Usuarios</a> 
                    <div class="dropdown-menu shadow border border-3 border-white rounded-3 p-2" aria-labelledby="navbarDropdownMenuLink"> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/registroUsuario.php">Registrar</a>   
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/listadoUsuario.php">Listar</a>
                        <?php if ($esAdministrador): ?> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/modUsuario.php">Modificar</a> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/borrarUsuario.php">Eliminar</a>
                        <?php endif; ?>  
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/calendarioDOT.php">Modificar horarios del DOT</a> 
                    </div>                        
                    </li>
                <?php endif; ?>
                <?php if ($esAdministrador || $esDot || $esDocente): ?>
                <li class="nav-item dropdown"> 
                    <a class="nav-link px-lg-3 py-lg-2 dropdown-toggle fw-bold text-dark rounded-3" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">Salas</a> 
                    <div class="dropdown-menu shadow border border-3 border-white rounded-3 p-2" aria-labelledby="navbarDropdownMenuLink"> 
                       <?php if ($esAdministrador): ?>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/gestionarSala.php">Gestionar Salas</a> 
                    <?php endif; ?>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/disponibilidadSala.php">Ver Disponibilidad</a> 
                    </div> 
                </li>
                <?php endif; ?>
                <?php if ($esAdministrador || $esDot || $esDocente || $esEstudiante ): ?>
                <li class="nav-item dropdown"> 
                    <a class="nav-link px-lg-3 py-lg-2 dropdown-toggle fw-bold text-dark rounded-3" href="#" id="navbarDropdownMenuLink2" role="button" data-bs-toggle="dropdown" aria-expanded="false">Equipos</a> 
                    <div class="dropdown-menu shadow border border-3 border-white rounded-3 p-2" aria-labelledby="navbarDropdownMenuLink2"> 
                        <?php if ($esAdministrador || $esDot ): ?>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/registrarEquipo.php">Registrar equipos</a> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/modEquipo.php">Modificar equipos</a>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/borrarEquipo.php">Eliminar equipos</a>  
                        <?php endif; ?>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/listadoEquipos.php">Consultar equipos</a>
                        <?php if ($esAdministrador || $esDot || $esDocente ): ?>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/calendarioEquipos.php">Reservar equipos</a>
                        <?php endif; ?>
                        <?php if ($esAdministrador || $esDot ): ?>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/gestionPrestamoEquipos.php">Prestamo equipos</a>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/gestionDevolucionEquipos.php">Devolución equipos</a>
                        <?php endif; ?>  
                    </div>                        
                </li>
                <?php endif; ?>
                <?php if ($esAdministrador || $esDot ): ?>
                <li class="nav-item dropdown"> 
                    <a class="nav-link px-lg-3 py-lg-2 dropdown-toggle fw-bold text-dark rounded-3" href="#" id="navbarDropdownMenuLink2" role="button" data-bs-toggle="dropdown" aria-expanded="false">Solicitar material</a> 
                    <div class="dropdown-menu shadow border border-3 border-white rounded-3 p-2" aria-labelledby="navbarDropdownMenuLink2"> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/solicitudEquipos.php">Solicitar equipos</a> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/consultarEstadoSolicitudEquipos.php">Consultar estado de las solicitudes</a>
                        <?php if ($esAdministrador): ?>
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/actualizarEstadoSolicitudEquipo.php">Actualizar estado de las solicitudes</a>  
                        <?php endif; ?>
                    </div>                        
                </li>
                <?php endif; ?>
                <li class="nav-item dropdown"> 
                    <a class="nav-link px-lg-3 py-lg-2 dropdown-toggle fw-bold text-dark rounded-3" href="#" id="navbarDropdownMenuLink3" role="button" data-bs-toggle="dropdown" aria-expanded="false">Registros</a> 
                    <div class="dropdown-menu shadow border border-3 border-white rounded-3 p-2" aria-labelledby="navbarDropdownMenuLink3"> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/EnProgreso.php">Reservas</a> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/EnProgreso.php">Prestamos</a> 
                        <a class="dropdown-item fw-semibold rounded-2 py-2" href="/CeiLab2026/Formularios/EnProgreso.php">Devoluciones</a> 
                    </div>                        
                </li> 
                <!-- SECCIÓN DE AUTENTICACIÓN -->
                <?php if ($estaLogueado): ?>
                    <li class="nav-item px-lg-2 py-lg-2">
                        <span class="nav-link fw-bold text-dark ms-2 rounded-3 bg-white bg-opacity-50 px-3 py-2 shadow-sm">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                    </li>
                </ul>    
                    <div class="ms-lg-2">
                        <a class="btn btn-primary fw-bold shadow-sm rounded-pill px-4 py-2" href="/CeiLab2026/Clases/ClsLogin.php?action=logout">Cerrar Sesión</a>
                    </div>
                <?php else: ?>
                </ul> 
                    <div class="ms-lg-2">   
                        <a class="btn btn-primary fw-bold shadow-sm rounded-pill px-4 py-2" href="/CeiLab2026/Formularios/acceso.php">Acceder</a>
                    </div>
                <?php endif; ?>                                 
        </div>            
    </div>        
</nav>