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
    public function modificarEquipo($idOriginal, $nuevoId, $estado, $observaciones, $tipo, $idSala, $disponibilidad) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idOriginal = mysqli_real_escape_string($link, trim($idOriginal));
        $nuevoId = mysqli_real_escape_string($link, trim($nuevoId));
        $estado = mysqli_real_escape_string($link, trim($estado));
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));
        $tipo = mysqli_real_escape_string($link, trim($tipo));
        $idSala = mysqli_real_escape_string($link, trim($idSala));
        $disponibilidad = mysqli_real_escape_string($link, trim($disponibilidad));

        // Si cambió el ID, verificamos que el nuevo ID no exista
        if ($idOriginal !== $nuevoId) {
            $sqlCheck = "SELECT IDEquipo FROM equipo WHERE IDEquipo = '$nuevoId'";
            $resultadoCheck = mysqli_query($link, $sqlCheck);
            if ($resultadoCheck && mysqli_num_rows($resultadoCheck) > 0) {
                return false; // El nuevo ID ya está en uso
            }
        }

        try {
            $sql = "UPDATE equipo SET 
                    IDEquipo = '$nuevoId', 
                    Estado = '$estado', 
                    Observaciones = '$observaciones', 
                    Tipo = '$tipo', 
                    IDSala_ceilab = '$idSala', 
                    Disponibilidad = '$disponibilidad' 
                    WHERE IDEquipo = '$idOriginal'";
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