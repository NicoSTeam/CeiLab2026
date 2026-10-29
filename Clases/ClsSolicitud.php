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
    //Lista solicitudes aplicando filtros opcionales
    public function listarSolicitudesFiltradas($idUsuario = '', $estado = '', $tipoEquipo = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $sql = "SELECT s.*, u.Nombre AS NombreUsuario 
                FROM solicitud_material s 
                JOIN usuario u ON s.IDUsuario = u.IDUsuario 
                WHERE s.activo = 1";
        
        if (!empty($idUsuario)) {
            $idUsuario = mysqli_real_escape_string($link, $idUsuario);
            $sql .= " AND s.IDUsuario = '$idUsuario'";
        }
        if (!empty($estado)) {
            $estado = mysqli_real_escape_string($link, $estado);
            $sql .= " AND s.Estado = '$estado'";
        }
        if (!empty($tipoEquipo)) {
            $tipoEquipo = mysqli_real_escape_string($link, $tipoEquipo);
            $sql .= " AND s.tipoEquipo = '$tipoEquipo'";
        }
        
        $sql .= " ORDER BY s.Fecha DESC, s.IDSolicitud DESC";
        
        $resultado = mysqli_query($link, $sql);
        $solicitudes = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $solicitudes[] = $row;
            }
        }
        return $solicitudes;
    }
    //Obtengo lista de usuarios que tienen solicitudes (para el filtro)
    public function listarUsuariosConSolicitudes() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $sql = "SELECT DISTINCT u.IDUsuario, u.Nombre FROM usuario u JOIN solicitud_material s ON u.IDUsuario = s.IDUsuario WHERE s.activo = 1";
        $resultado = mysqli_query($link, $sql);
        $usuarios = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $usuarios[] = $row;
            }
        }
        return $usuarios;
    }
    //Obtengo lista de tipos de equipos solicitados (para el filtro)
    public function listarTiposEquiposSolicitados() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $sql = "SELECT DISTINCT tipoEquipo FROM solicitud_material WHERE activo = 1";
        $resultado = mysqli_query($link, $sql);
        $tipos = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $tipos[] = $row['tipoEquipo'];
            }
        }
        return $tipos;
    }
    //Modifico la solicitud
    public function actualizarSolicitud($idSolicitud, $tipoEquipo, $estado, $descripcion, $cantidad, $fecha) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $idSolicitud = intval($idSolicitud);
        $tipoEquipo = mysqli_real_escape_string($link, trim($tipoEquipo));
        $estado = mysqli_real_escape_string($link, trim($estado));
        $descripcion = mysqli_real_escape_string($link, trim($descripcion));
        $cantidad = intval($cantidad);
        $fecha = mysqli_real_escape_string($link, trim($fecha));

        $sql = "UPDATE solicitud_material 
                SET tipoEquipo = '$tipoEquipo', 
                    Estado = '$estado', 
                    Descripcion = '$descripcion', 
                    Cantidad = $cantidad ,
                    Fecha = '$fecha' 
                WHERE IDSolicitud = $idSolicitud AND activo = 1";
        
        return mysqli_query($link, $sql);
    }
}
?>