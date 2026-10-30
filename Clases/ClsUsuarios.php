<?php
    
require_once __DIR__ . '/../Persistencia/connect.php';

class ClsUsuarios {
     
    public function registrarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Capturamos los datos enviados desde el formulario
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
                $nombre = mysqli_real_escape_string($link, trim($nombre));
                $cedula = mysqli_real_escape_string($link, trim($cedula));
                $email = mysqli_real_escape_string($link, trim($email));
                $direccion = mysqli_real_escape_string($link, trim($direccion));
                $observaciones = mysqli_real_escape_string($link, trim($observaciones));
                $rol = mysqli_real_escape_string($link, trim($rol));

                //Verifica que tenga 8 caracteres y sean solo numeros
                if (strlen($cedula) !== 8 || !ctype_digit($cedula)) {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title fw-bold">Atención</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">La cédula debe contener exactamente 8 dígitos numéricos (sin puntos ni guiones).</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }

                if ($this->verificarMail($email)) {
                        return '
                        <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow border-0">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title fw-bold">Atención</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <p class="fs-5 text-dark mb-0">El mail ingresado ya existe en el sistema, por favor, ingrese otro.</p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                    if ($this->verificarCedula($cedula)) {
                        return '
                        <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow border-0">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title fw-bold">Atención</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <p class="fs-5 text-dark mb-0">La cédula ingresada ya existe en el sistema, por favor, ingrese otra.</p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }

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
                                        <a href="/CeiLab2026/Formularios/acceso.php" class="btn btn-success fw-bold px-4">Ir a Iniciar Sesión</a>
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
                                    <small class="text-muted">Asegúrate de que la base de datos de MySQL esté encendida.</small>
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

        $sql = "SELECT u.IDUsuario, u.nombre, u.cedula, u.email, u.direccion, u.observaciones, MIN(r.ROL) AS ROL
                 FROM usuario u 
                LEFT JOIN rol r ON u.IDUsuario = r.IDUsuario 
                WHERE u.activo = 1";

        if (!empty($filtroNombre)) {
            $nombreEscaped = mysqli_real_escape_string($link, $filtroNombre);
            $sql .= " AND nombre LIKE '%$nombreEscaped%'";
        }
        if (!empty($filtroCedula)) {
            $cedulaEscaped = mysqli_real_escape_string($link, $filtroCedula);
            $sql .= " AND cedula = '$cedulaEscaped'";
        }
        if (!empty($filtroEmail)) {
            $emailEscaped = mysqli_real_escape_string($link, $filtroEmail);
            $sql .= " AND email LIKE '%$emailEscaped%'";
        }
        if (!empty($filtroRol)) {
            $rolEscaped = mysqli_real_escape_string($link, $filtroRol);
            $sql .= " AND r.ROL = '$rolEscaped'";
        }

        // Agrupamos estrictamente por el ID y los datos del usuario para que cada uno salga una sola vez
        $sql .= " GROUP BY u.IDUsuario, u.nombre, u.cedula, u.email, u.direccion, u.observaciones";
        $sql .= " ORDER BY u.IDUsuario ASC";

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
                $roles = $_POST['frol'] ?? []; 
                $telefonos = $_POST['ftelefonos'] ?? []; 
                $nuevaContrasenia = $_POST['fcontrasenia'] ?? ''; 

                $objConexion = new Conexion();
                $link = $objConexion->conectar();

                $idUsuario = mysqli_real_escape_string($link, trim($idUsuario));
                $nombre = mysqli_real_escape_string($link, trim($nombre));
                $cedula = mysqli_real_escape_string($link, trim($cedula));
                $email = mysqli_real_escape_string($link, trim($email));
                $direccion = mysqli_real_escape_string($link, trim($direccion));
                $observaciones = mysqli_real_escape_string($link, trim($observaciones));

                //Verifica que tenga 8 caracteres y sean solo numeros
                if (strlen($cedula) !== 8 || !ctype_digit($cedula)) {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title fw-bold">Atención</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">La cédula debe contener exactamente 8 dígitos numéricos (sin puntos ni guiones).</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }

                if ($this->verificarMail($email, $idUsuario)) {
                        return '
                        <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow border-0">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title fw-bold">Atención</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <p class="fs-5 text-dark mb-0">El mail ingresado ya existe en el sistema, por favor, ingrese otro.</p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                if ($this->verificarCedula($cedula, $idUsuario)) {
                        return '
                        <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow border-0">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title fw-bold">Atención</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <p class="fs-5 text-dark mb-0">La cédula ingresada ya existe en el sistema, por favor, ingrese otra.</p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }

                if (!empty($nuevaContrasenia)) {
                    $contraseniaHash = password_hash($nuevaContrasenia, PASSWORD_DEFAULT);
                    $sqlUsuario = "UPDATE usuario SET nombre='$nombre', cedula='$cedula', email='$email', direccion='$direccion', observaciones='$observaciones', contraseña='$contraseniaHash' WHERE IDUsuario='$idUsuario'";
                } else {
                    $sqlUsuario = "UPDATE usuario SET nombre='$nombre', cedula='$cedula', email='$email', direccion='$direccion', observaciones='$observaciones' WHERE IDUsuario='$idUsuario'";
                }

                $resUsuario = $objConexion->ejecutarSQL($sqlUsuario);

                if ($resUsuario) {
                    // Actualiza roles (borra los anteriores y reinserta los seleccionados)
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

                    // Actualiza teléfonos (borra los anteriores y reinserta los nuevos)
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

                // Baja lógica 
                $sqlUsuario = "UPDATE usuario SET activo = 0 WHERE IDUsuario = '$idUsuario'";
                $resultado = $objConexion->ejecutarSQL($sqlUsuario);

                if ($resultado) {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title fw-bold">¡Baja Lógica Exitosa!</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">¡El usuario ha sido dado de baja correctamente del sistema!</p>
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
                                    <h5 class="modal-title fw-bold">Error de Baja</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">No se pudo realizar la baja lógica del usuario.</p>
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
    public function verificarMail($email, $idUsuarioActual = null) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $emailEscaped = mysqli_real_escape_string($link, trim($email));

        $sql = "SELECT COUNT(*) as total FROM usuario WHERE email = '$emailEscaped' AND activo = 1";

        // Si se pasa un ID/Documento, se excluye al usuario actual
        if (!empty($idUsuarioActual)) {
            $idEscaped = mysqli_real_escape_string($link, trim($idUsuarioActual));
            $sql .= " AND IDUsuario != '$idEscaped'"; 
        }

        $resultado = mysqli_query($link, $sql);
        if ($resultado && $row = mysqli_fetch_assoc($resultado)) {
            return ($row['total'] > 0); // Devuelve true si el mail ya existe
        }

        return false;
    }
    public function verificarCedula($cedula, $idUsuarioActual = null) {
        
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $cedulaEscaped = mysqli_real_escape_string($link, trim($cedula));

        $sql = "SELECT COUNT(*) as total FROM usuario WHERE cedula = '$cedulaEscaped' AND activo = 1";

        //Si se pasa un ID, se excluye al usuario actual 
        if (!empty($idUsuarioActual)) {
            $idEscaped = mysqli_real_escape_string($link, trim($idUsuarioActual));
            $sql .= " AND IDUsuario != '$idEscaped'"; 
        }

        $resultado = mysqli_query($link, $sql);
        if ($resultado && $row = mysqli_fetch_assoc($resultado)) {
            return ($row['total'] > 0); //Devuelve true si la cedula ya existe en otro usuario activo
        }

        return false;
    }
}
?>