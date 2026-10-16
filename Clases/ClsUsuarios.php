<?php
    include ("../Persistencia/connect.php");

   class ClsUsuarios {
    
    public function registrarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            // Capturamos los datos enviados desde el formulario con sus prefijos 'f'
            $nombre = $_POST['fnombre'] ?? '';
            $cedula = $_POST['fcedula'] ?? '';
            $email = $_POST['femail'] ?? '';
            $direccion = $_POST['fdireccion'] ?? '';
            $observaciones = $_POST['fobservaciones'] ?? '';
            $rol = $_POST['frol'] ?? '';
            $contrasenia = password_hash($_POST['fcontrasenia'] ?? '', PASSWORD_DEFAULT);
            $telefonos = $_POST['ftelefonos'] ?? []; 

            // Instanciamos la clase Conexion para obtener el link
            $objConexion = new Conexion();
            $link = $objConexion->conectar();

            // Limpiamos los datos para evitar inyecciones SQL básicas en MySQLi
            $nombre = mysqli_real_escape_string($link, $nombre);
            $cedula = mysqli_real_escape_string($link, $cedula);
            $email = mysqli_real_escape_string($link, $email);
            $direccion = mysqli_real_escape_string($link, $direccion);
            $observaciones = mysqli_real_escape_string($link, $observaciones);
            $rol = mysqli_real_escape_string($link, $rol);

            //SQL de inserción
            $sql = "INSERT INTO usuario (nombre, cedula, email, direccion, observaciones, contraseña) 
                    VALUES ('$nombre', '$cedula', '$email', '$direccion', '$observaciones', '$contrasenia')";

            // Ejecutamos la consulta
            $resultadoUsuario = $objConexion->ejecutarSQL($sql);

            if ($resultadoUsuario) {
                //Buscar el ID del usuario usando su cédula (que acabamos de insertar)
                $sqlBuscarId = "SELECT IDUsuario FROM usuario WHERE cedula = '$cedula' ORDER BY IDUsuario DESC LIMIT 1";
                $resultadoId = mysqli_query($link, $sqlBuscarId);

                if ($row = mysqli_fetch_assoc($resultadoId)) {
                    $idUsuario = $row['IDUsuario'];

                    //Insertar el rol en la tabla vinculada usando ese ID
                    $sqlRol = "INSERT INTO rol (IDUsuario, ROL) VALUES ('$idUsuario', '$rol')";
                    $resultadoRol = mysqli_query($link, $sqlRol);

                    //Inserta los telefonos
                    if (!empty($telefonos) && is_array($telefonos)) {
                        // Limpiamos espacios y eliminamos duplicados enviados en el formulario
                        $telefonosUnicos = array_unique(array_map('trim', $telefonos));
                        
                        foreach ($telefonosUnicos as $tel) {
                            if (!empty($tel)) {
                                $telEscape = mysqli_real_escape_string($link, $tel);
                                // INSERT IGNORE evita que la aplicación falle si el número se repite
                                $sqlTelefono = "INSERT IGNORE INTO Telefonos (IDUsuario, Telefono) VALUES ('$idUsuario', '$telEscape')";
                                mysqli_query($link, $sqlTelefono);
                            }
                        }
                    }
                    
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title fw-bold">¡Registro Exitoso!</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">¡Usuario, rol y teléfonos registrados con éxito!</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <a href="/CeiLab2026/Formularios/acceso.html" class="btn btn-success fw-bold px-4">Ir a Iniciar Sesión</a>
                                    <button type="button" class="btn btn-primary px-4 fw-bold" data-bs-dismiss="modal">volver</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                } else {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title fw-bold">Atención</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">El usuario se creó, pero no se pudo recuperar su ID.</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                $errorMsg = mysqli_error($link);
                return '
                <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold">Error de Registro</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">No se pudo registrar el usuario en el sistema.</p>
                                <small class="text-muted">' . htmlspecialchars($errorMsg) . '</small>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Intentar de nuevo</button>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        } catch (\Throwable $e) {
                // Capturamos cualquier error crítico (como la base de datos apagada o fallos de red)
                $errorConexion = $e->getMessage();
                return '
                <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold">Error de Conexión</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">No se pudo establecer comunicación con la base de datos.</p>
                                <small class="text-muted">Asegúrate de que el servidor MySQL esté encendido.</small>
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
    public function listarUsuarios($filtroNombre = '', $filtroCedula = '', $filtroEmail = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        // Consulta uniendo usuario y rol
        $sql = "SELECT u.IDUsuario, u.nombre, u.cedula, u.email, u.direccion, u.observaciones, r.ROL 
                FROM usuario u 
                INNER JOIN rol r ON u.IDUsuario = r.IDUsuario WHERE 1=1";

        // Aplicamos los filtros 
        if (!empty($filtroNombre)) {
            $nombreEscaped = mysqli_real_escape_string($link, $filtroNombre);
            $sql .= " AND u.nombre LIKE '%$nombreEscaped%'";
        }
        if (!empty($filtroCedula)) {
            $cedulaEscaped = mysqli_real_escape_string($link, $filtroCedula);
            $sql .= " AND u.cedula = '$cedulaEscaped'";
        }
        if (!empty($filtroEmail)) {
            $emailEscaped = mysqli_real_escape_string($link, $filtroEmail);
            $sql .= " AND u.email LIKE '%$emailEscaped%'";
        }

        $sql .= " ORDER BY u.IDUsuario DESC";
        
        return mysqli_query($link, $sql);
    }
    public function obtenerTelefonosPorUsuario($idUsuario) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $idEscape = mysqli_real_escape_string($link, $idUsuario);
        $sql = "SELECT Telefono FROM Telefonos WHERE IDUsuario = '$idEscape'";
        $resultado = mysqli_query($link, $sql);
        
        $telefonos = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $telefonos[] = $row['Telefono'];
            }
        }
        return $telefonos;
    }
}

// Instanciamos la clase y ejecutamos el método de registro automáticamente al recibir el POST
$objUsuario = new ClsUsuarios();
$objUsuario->registrarUsuario();
?>