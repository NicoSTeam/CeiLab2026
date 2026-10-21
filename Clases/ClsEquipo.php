<?php
require_once __DIR__ . '/../Persistencia/connect.php';

class ClsEquipo {

    // Método para registrar un nuevo equipo
    public function registrarEquipo($estado, $observaciones, $tipo, $idSala) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $estado = mysqli_real_escape_string($link, trim($estado));
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));
        $tipo = mysqli_real_escape_string($link, trim($tipo));
        $idSala = mysqli_real_escape_string($link, trim($idSala));

        // 1 si es Disponible, 0 para cualquier otro estado
        $disponibilidad = ($estado === 'Disponible') ? 1 : 0;

        try {
            $sql = "INSERT INTO equipo (Estado, Observaciones, Tipo, IDSala_ceilab, Disponibilidad) 
                    VALUES ('$estado', '$observaciones', '$tipo', '$idSala', '$disponibilidad')";
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
    // Método para listar todos los equipos registrados
    public function listarEquipos() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $sql = "SELECT e.IDEquipo, e.Estado, e.Observaciones, e.Tipo, e.IDSala_ceilab, e.Disponibilidad 
                FROM equipo e";
        $resultado = mysqli_query($link, $sql);
        
        $equipos = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $equipos[] = $row;
            }
        }
        return $equipos;
    }

    // Método para obtener un equipo por su ID (para edición)
    public function obtenerEquipoPorId($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idEquipo = mysqli_real_escape_string($link, $idEquipo);
        $sql = "SELECT IDEquipo, Estado, Observaciones, Tipo, IDSala_ceilab, Disponibilidad FROM equipo WHERE IDEquipo = '$idEquipo'";
        $resultado = mysqli_query($link, $sql);

        if ($resultado && $row = mysqli_fetch_assoc($resultado)) {
            return $row;
        }
        return null;
    }

    // Método para modificar un equipo
    public function modificarEquipo($idEquipo, $estado, $observaciones, $tipo, $idSala) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idEquipo = mysqli_real_escape_string($link, trim($idEquipo));
        $estado = mysqli_real_escape_string($link, trim($estado));
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));
        $tipo = mysqli_real_escape_string($link, trim($tipo));
        $idSala = mysqli_real_escape_string($link, trim($idSala));

        //1 si es Disponible, 0 para cualquier otro estado
        $disponibilidad = ($estado === 'Disponible') ? 1 : 0;

        try {
            $sql = "UPDATE equipo SET 
                    Estado = '$estado', 
                    Observaciones = '$observaciones', 
                    Tipo = '$tipo', 
                    IDSala_ceilab = '$idSala', 
                    Disponibilidad = '$disponibilidad' 
                    WHERE IDEquipo = '$idEquipo'";
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    // Método para eliminar un equipo
    public function eliminarEquipo($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idEquipo = mysqli_real_escape_string($link, $idEquipo);
        $sql = "DELETE FROM equipo WHERE IDEquipo = '$idEquipo'";
        
        try {
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}
?>