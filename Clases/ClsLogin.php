<?php
include(__DIR__ . '/../Persistencia/connect.php');

class ClsLogin {

    // Método para el inicio de sesión y devolver el modal de respuesta
    public function iniciarSesion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $email = $_POST['femail'] ?? '';
                $contrasenia = $_POST['fcontrasenia'] ?? '';

                $objConexion = new Conexion();
                $link = $objConexion->conectar();

                $email = mysqli_real_escape_string($link, $email);

                // Buscamos al usuario por su correo electrónico
                $sql = "SELECT IDUsuario, nombre, contraseña FROM usuario WHERE email = '$email'";
                $resultado = mysqli_query($link, $sql);

                if ($resultado && $row = mysqli_fetch_assoc($resultado)) {
                    // Verificamos la contraseña encriptada con password_verify
                    //exit("Hash en BD: " . $row['contraseña'] . " | ¿Coincide?: " . (password_verify($contrasenia, $row['contraseña']) ? 'SÍ' : 'NO'));
                    if (password_verify($contrasenia, $row['contraseña'])) {
                        
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }

                        $idUsuario = $row['IDUsuario'];
                        $_SESSION['IDUsuario'] = $idUsuario;
                        $_SESSION['nombre'] = $row['nombre'];

                        // Consultamos y guardamos todos los roles asignados al usuario en un array de sesión
                        $sqlRoles = "SELECT ROL FROM rol WHERE IDUsuario = '$idUsuario'";
                        $resRoles = mysqli_query($link, $sqlRoles);
                        $rolesUsuario = [];
                        while ($r = mysqli_fetch_assoc($resRoles)) {
                            $rolesUsuario[] = $r['ROL'];
                        }
                        $_SESSION['roles'] = $rolesUsuario;

                        return '
                        <div class="modal fade" id="modalRespuesta" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow border-0">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title fw-bold">¡Bienvenido!</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <p class="fs-5 text-dark mb-0">¡Inicio de sesión exitoso, ' . htmlspecialchars($row['nombre']) . '!</p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <a href="../index.php" class="btn btn-success fw-bold px-4">Ir al Inicio</a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                }

                // Respuesta si las credenciales son incorrectas
                return '
                <div class="modal fade" id="modalRespuesta" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold">Acceso Denegado</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">Correo o contraseña incorrectos.</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Intentar de nuevo</button>
                            </div>
                        </div>
                    </div>
                </div>';

            } catch (\Throwable $e) {
                // Respuesta ante errores de conexión
                return '
                <div class="modal fade" id="modalRespuesta" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold">Error de Sistema</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">Ocurrió un error inesperado al procesar la solicitud.</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }
    }

    //Métodos de tipo estáticos para controlar el acceso.
    //Verifica si hay una sesión activa; si no, redirige al formulario de acceso
    public static function verificarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['IDUsuario'])) {
            header("Location: ../Formularios/acceso.php");
            exit;
        }
    }

    //Comprueba si el usuario autenticado posee un rol en específico (ej: '1' para Administrador)
    public static function tieneRol($rolRequerido) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['roles']) && in_array($rolRequerido, $_SESSION['roles']);
    }

    //Destruye la sesión actual y redirige al acceso
    public static function cerrarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        
        // Subimos un nivel desde Clases/ hacia la raíz donde está index.php
        header("Location: /CeiLab2026/index.php");
        exit;
        }
}
// Disparador para cerrar sesión mediante GET
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    ClsLogin::cerrarSesion();
    exit();
}
// Disparador automático para peticiones AJAX enviadas por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['femail'])) {
    $objLogin = new ClsLogin();
    echo $objLogin->iniciarSesion();
    exit;
}
?>