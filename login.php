<?php
// Iniciar la sesión
session_start();

// Incluir el archivo de conexión a la base de datos
include("Persistencia/connect.php");
$con = new Conexion();

// Obtener los datos de inicio de sesión del formulario
$correo = $_POST["fCorreo"];
$contrasenia = $_POST["fContrasena"];

// Validar los datos de inicio de sesión
$sql = "SELECT * FROM usuario WHERE correo = '$correo' AND contrasenia = '$contrasenia'";
$resultado = $con->ejecutarSQL($sql);

// Comprobar si se encontró el usuario
if ($resultado->num_rows == 1) {
    // Guardar el correo en la sesión para mantener al usuario logueado
    $_SESSION["correo"] = $correo;
    
    // Redirigir a index.html
    header("Location: index.html");
    exit();
} else {
    // Mostrar mensaje si no encuentra al usuario
    echo "usuario no valido";
}
?>