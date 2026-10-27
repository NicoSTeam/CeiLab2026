<?php

require_once __DIR__ . '/../Persistencia/connect.php';

class ClsSolicitud {

    //Método para registrar una nueva solicitud
    public function registrarSolicitud($tipoEquipo, $nuevoTipo, $descripcion, $cantidad, $idUsuario, $fecha) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        //Si eligió otro, guardamos el texto personalizado ingresado de lo contrario lo que seleccione
        $tipoFinal = ($tipoEquipo === 'OTRO') ? trim($nuevoTipo) : trim($tipoEquipo);
        
        $tipoFinalClean = mysqli_real_escape_string($link, $tipoFinal);
        $descripcionClean = mysqli_real_escape_string($link, $descripcion);
        $cantidad = intval($cantidad);
        $idUsuario = intval($idUsuario);
        $fecha = mysqli_real_escape_string($link, $fecha);
        $estado = 'pendiente'; //Estado inicial
        $activo = 1;

        $sql = "INSERT INTO solicitud_material (tipoEquipo, Estado, Descripcion, Cantidad, IDUsuario, Fecha, activo) 
                VALUES ('$tipoFinalClean', '$estado', '$descripcionClean', $cantidad, $idUsuario, '$fecha', $activo)";
        
        return mysqli_query($link, $sql);
    }
    //Método para listar solicitudes activas
    public function listarSolicitudes() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $sql = "SELECT s.*, u.Nombre AS UsuarioNombre 
                FROM solicitud s
                JOIN usuario u ON s.IDUsuario = u.IDUsuario
                WHERE s.activo = 1
                ORDER BY s.Fecha DESC, s.IDSolicitud DESC";
                
        $resultado = mysqli_query($link, $sql);
        $solicitudes = [];

        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $solicitudes[] = $row;
            }
        }
        return $solicitudes;
    }
}
?>