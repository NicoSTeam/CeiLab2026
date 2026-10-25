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
    // Método para listar un equipo por disponibilidad en un rango horario determinado
    public function listarEquiposDisponibles($fecha, $horaInicio, $horaFin, $filtroTipo = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $fechaEscaped = mysqli_real_escape_string($link, trim($fecha));
        $inicioEscaped = mysqli_real_escape_string($link, trim($horaInicio));
        $finEscaped = mysqli_real_escape_string($link, trim($horaFin));

        $sql = "
            SELECT e.IDEquipo, e.Estado, e.Observaciones, e.Tipo, e.IDSala_ceilab, e.Disponibilidad 
            FROM equipo e
            WHERE e.activo = 1 
              AND e.Disponibilidad = 1
        ";

        if (!empty($filtroTipo)) {
            $tipoEscaped = mysqli_real_escape_string($link, trim($filtroTipo));
            $sql .= " AND e.Tipo = '$tipoEscaped'";
        }

        // Uso NOT EXISTS para excluir los equipos ocupados en ese rango horario
        $sql .= " AND NOT EXISTS (
                  SELECT 1
                  FROM tiene_equipos te
                  JOIN reservas r ON te.IDReservas = r.IDReservas
                  WHERE te.IDEquipo = e.IDEquipo
                    AND r.Fecha = '$fechaEscaped'
                    AND r.activo = 1
                    AND ('$inicioEscaped' < r.HoraFin AND '$finEscaped' > r.HoraInicio)
              )
            ORDER BY e.Tipo, e.IDEquipo ASC;
        ";

        $resultado = mysqli_query($link, $sql);
        $equipos = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $equipos[] = $row;
            }
        }
        return $equipos;
    }
    // Método para obtener los tipos de equipos para el selector
    public function obtenerTiposEquipos() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $sql = "SELECT DISTINCT Tipo FROM equipo WHERE activo = 1 AND Tipo IS NOT NULL AND Tipo != '' ORDER BY Tipo ASC";
        $resultado = mysqli_query($link, $sql);
        $tipos = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $tipos[] = $row['Tipo'];
            }
        }
        return $tipos;
    }
}
?>