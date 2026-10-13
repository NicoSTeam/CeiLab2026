<?php
    include ("../Persistencia/connect.php");
    $con = new Conexion();
    $nombre = $_POST ["fNombre"];
    $correo = $_POST ["fCorreo"];
    $pass = md5($_POST ["fContrasena"]);
    $fNac = $_POST ["fFechaNacimiento"];
    $rol = $_POST ["fRol"];
    $nombrearchivo = $_FILES['fArchivo']['name'];
    $rutaDestino ="../imagenes/";
    $rutaArchivo = $rutaDestino.$nombrearchivo;

     if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['EnviarAdm'])) {       
        // Verificar si se envió un archivo
            if (!empty($_FILES['fArchivo']['name'])) {
                $nombreArchivo = $_FILES['fArchivo']['name'];
                $rutaArchivo = $rutaDestino.$nombrearchivo;
        
                // Mover el archivo a la ubicación deseada
                if (move_uploaded_file($_FILES['fArchivo']['tmp_name'], $rutaArchivo)) {
                    $sql = "INSERT INTO usuario (nombre, correo, password, fecha, permisos, imagen) VALUES ('$nombre', '$correo', '$pass', '$fNac', '$rol', '$nombreArchivo')";
                    $con->ejecutarSQL($sql);
                    header("location: ../indexAdmin.php");
                    exit;
                } else {
                    echo "Error al mover el archivo";
                }
            } else {
                $nombreArchivo = null;
                $sql = "INSERT INTO usuario (nombre, correo, password, fecha, permisos, imagen) VALUES ('$nombre', '$correo', '$pass', '$fNac', '$rol', '$nombreArchivo')";
                $con->ejecutarSQL($sql);
                header("location: ../indexAdmin.php");
                exit;
            }
            
           
    }elseif($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['EnviarUsr'])){
        $permiso="usuario";
        $sql ="INSERT INTO usuario(nombre, correo, password, fecha, permisos) VALUES ('$nombre', '$correo', '$pass', '$fNac', '$permiso')";
        $con->ejecutarSQL($sql);
        header("location: ../index.php");

    }elseif($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['Eliminardatos'])){
        $sql = "delete from usuario where correo='$correo'";
        $con->ejecutarSQL($sql);
        header("location: ../indexAdmin.php");

    }elseif($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['Modificardatos'])){
        
        if (!empty($_FILES['fArchivo']['name'])) {
            $nombreArchivo = $_FILES['fArchivo']['name'];
            $rutaArchivo = $rutaDestino.$nombrearchivo;
    
            // Mover el archivo a la ubicación deseada
            if (move_uploaded_file($_FILES['fArchivo']['tmp_name'], $rutaArchivo)) {
        
            $sql = "UPDATE usuario SET nombre = '$nombre', password = '$pass', fecha = '$fNac', permisos = '$rol', imagen = '$nombrearchivo' WHERE correo = '$correo'";
            $con->ejecutarSQL($sql);
            header("location: ../indexAdmin.php");
            exit;
                } else {
                    echo "Error al mover el archivo";
                }
        } else {
                $nombreArchivo = null;
                $sql = "UPDATE usuario SET nombre = '$nombre', correo ='$correo', password = '$pass', fecha = '$fNac', permisos = '$rol', imagen = '$nombrearchivo' WHERE correo = '$correo'";
                $con->ejecutarSQL($sql);
                header("location: ../indexAdmin.php");
                exit;
            }
    }
?>