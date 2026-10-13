<?php
    include ("../Persistencia/connect.php");

   class ClsUsuarios {
    
    public function registrarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturamos los datos enviados desde el formulario con sus prefijos 'f'
            $nombre = $_POST['fnombre'] ?? '';
            $cedula = $_POST['fcedula'] ?? '';
            $email = $_POST['femail'] ?? '';
            $direccion = $_POST['fdireccion'] ?? '';
            $observaciones = $_POST['fobservaciones'] ?? '';
            $rol = $_POST['frol'] ?? '';
            $contrasenia = password_hash($_POST['fcontrasenia'] ?? '', PASSWORD_DEFAULT);

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
                // PASO 2: Buscar el ID del usuario usando su cédula (que acabamos de insertar)
                $sqlBuscarId = "SELECT IDUsuario FROM usuario WHERE cedula = '$cedula' ORDER BY IDUsuario DESC LIMIT 1";
                $resultadoId = mysqli_query($link, $sqlBuscarId);

                if ($row = mysqli_fetch_assoc($resultadoId)) {
                    $idUsuario = $row['IDUsuario'];

                    // PASO 3: Insertar el rol en la tabla vinculada usando ese ID
                    $sqlRol = "INSERT INTO rol (IDUsuario, ROL) VALUES ('$idUsuario', '$rol')";
                    $resultadoRol = mysqli_query($link, $sqlRol);

                    if ($resultadoRol) {
                        echo "<div class='alert alert-success m-3 text-center'>¡Usuario registrado y rol asignado con éxito! <a href='../Formularios/acceso.html' class='alert-link'>Iniciar sesión</a></div>";
                    } else {
                        echo "<div class='alert alert-danger m-3 text-center'>Error al insertar el rol: " . mysqli_error($link) . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger m-3 text-center'>El usuario se creó, pero no se pudo recuperar su ID.</div>";
                }
            } else {
                echo "<div class='alert alert-danger m-3 text-center'>Error al registrar el usuario: " . mysqli_error($link) . "</div>";
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
}

// Instanciamos la clase y ejecutamos el método de registro automáticamente al recibir el POST
$objUsuario = new ClsUsuarios();
$objUsuario->registrarUsuario();
?>