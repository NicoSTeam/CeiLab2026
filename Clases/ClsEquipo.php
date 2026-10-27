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
    public function listarEquiposDisponibles($fecha, $horaInicio, $horaFin, $tipo = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $fecha = mysqli_real_escape_string($link, $fecha);
        $horaInicio = mysqli_real_escape_string($link, strlen($horaInicio) == 5 ? $horaInicio . ':00' : $horaInicio);
        $horaFin = mysqli_real_escape_string($link, strlen($horaFin) == 5 ? $horaFin . ':00' : $horaFin);
        
        $filtroTipo = !empty($tipo) ? "AND e.Tipo = '" . mysqli_real_escape_string($link, $tipo) . "'" : "";

        $sql = "
            SELECT e.* 
            FROM equipo e
            WHERE e.activo = 1 
              AND e.Disponibilidad = 1
              $filtroTipo
              -- 1. Excluir equipos ocupados por reservas activas
              AND NOT EXISTS (
                  SELECT 1 
                  FROM tiene_equipos te
                  JOIN reservas r ON te.IDReservas = r.IDReservas
                  WHERE te.IDEquipo = e.IDEquipo
                    AND r.activo = 1 
                    AND r.Fecha = '$fecha'
                    AND (TIME('$horaInicio') < TIME(r.HoraFin) AND TIME('$horaFin') > TIME(r.HoraInicio))
              )
              -- 2. Excluir equipos con préstamos activos (vinculados por orden de IDOperaciones)
              AND NOT EXISTS (
                  SELECT 1 
                  FROM operaciones o_prestamo
                  WHERE o_prestamo.IDEquipo = e.IDEquipo
                    AND o_prestamo.Accion = 1 
                    AND o_prestamo.Fecha = '$fecha'
                    AND o_prestamo.HoraInicio IS NOT NULL 
                    AND o_prestamo.HoraFin IS NOT NULL
                    AND TIME('$horaInicio') < COALESCE(
                        (
                            SELECT MIN(o_dev.HoraInicio)
                            FROM operaciones o_dev
                            WHERE o_dev.IDEquipo = e.IDEquipo
                              AND o_dev.Accion = 2 
                              AND o_dev.Fecha = '$fecha'
                              AND o_dev.IDOperaciones > o_prestamo.IDOperaciones
                        ),
                        TIME(o_prestamo.HoraFin)
                    )
                    AND TIME('$horaFin') > TIME(o_prestamo.HoraInicio)
              )
            ORDER BY e.Tipo ASC, e.IDEquipo ASC;
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