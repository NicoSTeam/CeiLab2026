<?php
require_once __DIR__ . '/../Persistencia/connect.php';

class ClsCalendarioDOT {

    //Registrar un nuevo horario de disponibilidad del DOT. 
    public function registrarHorario($idUsuario, $fecha, $horaInicio, $horaFin, $descripcion) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $sql = "INSERT INTO horarios_dot (IDusuario, fecha, hora_inicio, hora_fin, descripcion) VALUES (?, ?, ?, ?, ?)";
        $stmt = $link->prepare($sql);
        
        if (!$stmt) {
            die("Error en prepare: " . $link->error);
        }

        $stmt->bind_param("issss", $idUsuario, $fecha, $horaInicio, $horaFin, $descripcion);
        
        if (!$stmt->execute()) {
            die("Error al ejecutar la inserción: " . $stmt->error);
        }

        return true;
    }
    
    //Obtener todos los horarios registrados para un mes y año determinados.
    public function obtenerHorariosMes($anio, $mes) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $sql = "SELECT h.idDot, h.fecha, DATE_FORMAT(h.hora_inicio, '%H:%i') AS inicio, DATE_FORMAT(h.hora_fin, '%H:%i') AS fin, h.descripcion, u.nombre AS nombre_dot
                FROM horarios_dot h
                INNER JOIN usuario u ON h.IDusuario = u.IDusuario
                WHERE YEAR(h.fecha) = ? AND MONTH(h.fecha) = ?
                ORDER BY h.hora_inicio ASC";
        
        $stmt = $link->prepare($sql);
        $stmt->bind_param("ii", $anio, $mes);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $resultados = $result->fetch_all(MYSQLI_ASSOC);
        
        $horarios = [];

        foreach ($resultados as $row) {
            $horarios[$row['fecha']][] = [
                'idDot' => $row['idDot'],
                'inicio' => $row['inicio'],
                'fin' => $row['fin'],
                'descripcion' => $row['descripcion'],
                'nombre_dot' => $row['nombre_dot']
            ];
        }

        return $horarios;
    }
    //Eliminar un horario registrado mediante su idDot.
    public function eliminarHorario($idDot) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $sql = "DELETE FROM horarios_dot WHERE idDot = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $idDot);
        return $stmt->execute();
    }
}