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
    public function listarUsuarios($filtroNombre = '', $filtroCedula = '', $filtroEmail = '', $filtroRol = '') {
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
        if (!empty($filtroRol)) {
            $rolEscaped = mysqli_real_escape_string($link, $filtroRol);
            $sql .= " AND r.ROL = '$rolEscaped'";
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
    public function obtenerUsuarioPorId($idUsuario) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $idEscaped = mysqli_real_escape_string($link, $idUsuario);
        $sql = "SELECT u.IDUsuario, u.nombre, u.cedula, u.email, u.direccion, u.observaciones, r.ROL 
                FROM usuario u 
                LEFT JOIN rol r ON u.IDUsuario = r.IDUsuario 
                WHERE u.IDUsuario = '$idEscaped' LIMIT 1";
        
        $resultado = mysqli_query($link, $sql);
        return mysqli_fetch_assoc($resultado);
    }
    public function modificarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $idUsuario = $_POST['fidusuario'] ?? '';
                $nombre = $_POST['fnombre'] ?? '';
                $cedula = $_POST['fcedula'] ?? '';
                $email = $_POST['femail'] ?? '';
                $direccion = $_POST['fdireccion'] ?? '';
                $observaciones = $_POST['fobservaciones'] ?? '';
                $roles = $_POST['frol'] ?? []; //Arreglo de roles
                $telefonos = $_POST['ftelefonos'] ?? []; //Arreglo de teléfonos

                $objConexion = new Conexion();
                $link = $objConexion->conectar();

                $idUsuario = mysqli_real_escape_string($link, $idUsuario);
                $nombre = mysqli_real_escape_string($link, $nombre);
                $cedula = mysqli_real_escape_string($link, $cedula);
                $email = mysqli_real_escape_string($link, $email);
                $direccion = mysqli_real_escape_string($link, $direccion);
                $observaciones = mysqli_real_escape_string($link, $observaciones);

                //Actualiza datos de la tabla usuario
                $sqlUsuario = "UPDATE usuario SET nombre='$nombre', cedula='$cedula', email='$email', direccion='$direccion', observaciones='$observaciones' WHERE IDUsuario='$idUsuario'";
                $resUsuario = $objConexion->ejecutarSQL($sqlUsuario);

                if ($resUsuario) {
                    //Actualiza roles (borra los anteriores y reinserta los seleccionados)
                    $sqlDelRol = "DELETE FROM rol WHERE IDUsuario='$idUsuario'";
                    mysqli_query($link, $sqlDelRol);

                    if (!empty($roles) && is_array($roles)) {
                        $rolesUnicos = array_unique(array_map('trim', $roles));
                        foreach ($rolesUnicos as $r) {
                            if (!empty($r)) {
                                $rEscape = mysqli_real_escape_string($link, $r);
                                $sqlRol = "INSERT IGNORE INTO rol (IDUsuario, ROL) VALUES ('$idUsuario', '$rEscape')";
                                mysqli_query($link, $sqlRol);
                            }
                        }
                    }

                    //Actualiza teléfonos (borra los anteriores y reinserta los nuevos)
                    $sqlDelPhones = "DELETE FROM Telefonos WHERE IDUsuario='$idUsuario'";
                    mysqli_query($link, $sqlDelPhones);

                    if (!empty($telefonos) && is_array($telefonos)) {
                        $telefonosUnicos = array_unique(array_map('trim', $telefonos));
                        foreach ($telefonosUnicos as $tel) {
                            if (!empty($tel)) {
                                $telEscape = mysqli_real_escape_string($link, $tel);
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
                                    <h5 class="modal-title fw-bold">¡Modificación Exitosa!</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">¡Los datos del usuario se actualizaron correctamente!</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <a href="modUsuario.php" class="btn btn-success fw-bold px-4">Volver al Listado</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                } else {
                    $errorMsg = mysqli_error($link);
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title fw-bold">Error de Modificación</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">No se pudieron actualizar los datos.</p>
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
    public function obtenerRolesPorUsuario($idUsuario) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $idEscaped = mysqli_real_escape_string($link, $idUsuario);
        $sql = "SELECT ROL FROM rol WHERE IDUsuario = '$idEscaped'";
        $resultado = mysqli_query($link, $sql);
        
        $roles = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $roles[] = $row['ROL'];
            }
        }
        return $roles;
    }
    public function eliminarUsuario($idUsuario) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $objConexion = new Conexion();
                $link = $objConexion->conectar();
                $idUsuario = mysqli_real_escape_string($link, $idUsuario);

                //Borrar roles vinculados
                $sqlDelRol = "DELETE FROM rol WHERE IDUsuario = '$idUsuario'";
                mysqli_query($link, $sqlDelRol);

                //Borrar teléfonos vinculados
                $sqlDelPhones = "DELETE FROM Telefonos WHERE IDUsuario = '$idUsuario'";
                mysqli_query($link, $sqlDelPhones);

                //Borrar el usuario 
                $sqlUsuario = "DELETE FROM usuario WHERE IDUsuario = '$idUsuario'";
                $resultado = $objConexion->ejecutarSQL($sqlUsuario);

                if ($resultado) {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title fw-bold">¡Eliminación Exitosa!</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">¡El usuario ha sido eliminado del sistema correctamente!</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <a href="borrarUsuario.php" class="btn btn-success fw-bold px-4">Aceptar</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                } else {
                    $errorMsg = mysqli_error($link);
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title fw-bold">Error de Eliminación</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">No se pudo eliminar el usuario.</p>
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
}
?>