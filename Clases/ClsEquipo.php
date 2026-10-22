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

        $disponibilidad = ($estado === 'Disponible') ? 1 : 0;

        try {
            // Se incluye activo = 1 por defecto al registrar
            $sql = "INSERT INTO equipo (Estado, Observaciones, Tipo, IDSala_ceilab, Disponibilidad, activo) 
                    VALUES ('$estado', '$observaciones', '$tipo', '$idSala', '$disponibilidad', 1)";
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    // Método para listar equipos activos filtrando por tipo y disponibilidad
    public function listarEquipos($filtroTipo = '', $filtroDisponibilidad = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $sql = "SELECT IDEquipo, Estado, Observaciones, Tipo, IDSala_ceilab, Disponibilidad 
                FROM equipo WHERE activo = 1";

        if (!empty($filtroTipo)) {
            $tipoEscaped = mysqli_real_escape_string($link, $filtroTipo);
            $sql .= " AND Tipo = '$tipoEscaped'";
        }
        if ($filtroDisponibilidad !== '' && $filtroDisponibilidad !== null) {
            $dispEscaped = mysqli_real_escape_string($link, $filtroDisponibilidad);
            $sql .= " AND Disponibilidad = '$dispEscaped'";
        }

        $sql .= " ORDER BY IDEquipo ASC";

        $resultado = mysqli_query($link, $sql);
        
        $equipos = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $equipos[] = $row;
            }
        }
        return $equipos;
    }

    // Método para obtener equipo por su ID
    public function obtenerEquipoPorId($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idEquipo = mysqli_real_escape_string($link, $idEquipo);
        $sql = "SELECT IDEquipo, Estado, Observaciones, Tipo, IDSala_ceilab, Disponibilidad FROM equipo WHERE IDEquipo = '$idEquipo' AND activo = 1";
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

    // Método para realizar la baja lógica de un equipo 
    public function eliminarEquipo($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idEquipo = mysqli_real_escape_string($link, $idEquipo);
        $sql = "UPDATE equipo SET activo = 0 WHERE IDEquipo = '$idEquipo'";
        
        try {
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}
?>