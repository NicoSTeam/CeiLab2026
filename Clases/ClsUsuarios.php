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
            // Hasheamos la contraseña por seguridad antes de guardarla
            $contrasenia = password_hash($_POST['fcontrasenia'] ?? '', PASSWORD_DEFAULT);

            // Instanciamos la clase Conexion para obtener el enlace y escapar los datos
            $objConexion = new Conexion();
            $link = $objConexion->conectar();

            // Limpiamos los datos para evitar inyecciones SQL básicas en MySQLi
            $nombre = mysqli_real_escape_string($link, $nombre);
            $cedula = mysqli_real_escape_string($link, $cedula);
            $email = mysqli_real_escape_string($link, $email);
            $direccion = mysqli_real_escape_string($link, $direccion);
            $observaciones = mysqli_real_escape_string($link, $observaciones);

            // Armamos la sentencia SQL de inserción
            $sql = "INSERT INTO usuario (nombre, cedula, email, direccion, observaciones, contraseña) 
                    VALUES ('$nombre', '$cedula', '$email', '$direccion', '$observaciones', '$contrasenia')";

            // Ejecutamos la consulta utilizando el método de tu clase
            $resultado = $objConexion->ejecutarSQL($sql);

            if ($resultado) {
                echo "<div class='alert alert-success m-3 text-center'>¡Usuario registrado con éxito en la base de datos! <a href='../Formularios/acceso.html' class='alert-link'>Iniciar sesión</a></div>";
            } else {
                echo "<div class='alert alert-danger m-3 text-center'>Error al registrar el usuario en la base de datos.</div>";
            }
        }
    }
}

// Instanciamos la clase y ejecutamos el método de registro automáticamente al recibir el POST
$objUsuario = new ClsUsuarios();
$objUsuario->registrarUsuario();
?>